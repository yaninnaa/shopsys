<?php

namespace Shopsys\FrameworkBundle\Model\Product\Search;

use Doctrine\ORM\QueryBuilder;
use Elasticsearch\Client;
use Shopsys\FrameworkBundle\Component\Elasticsearch\ElasticsearchStructureManager;
use Shopsys\FrameworkBundle\Model\Customer\CurrentCustomer;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;

class ProductElasticsearchRepository
{
    public const ELASTICSEARCH_INDEX = 'product';

    /**
     * @var string
     */
    protected $indexPrefix;

    /**
     * @var \Elasticsearch\Client
     */
    protected $client;

    /**
     * @var int[][][]
     */
    protected $foundProductIdsCache = [];

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchConverter
     */
    protected $productElasticsearchConverter;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\ElasticsearchStructureManager
     */
    protected $elasticsearchStructureManager;
    /**
     * @var CurrentCustomer
     */
    private $currentCustomer;

    /**
     * @param string $indexPrefix
     * @param \Elasticsearch\Client $client
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchConverter $productElasticsearchConverter
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\ElasticsearchStructureManager $elasticsearchStructureManager
     */
    public function __construct(
        string $indexPrefix,
        Client $client,
        ProductElasticsearchConverter $productElasticsearchConverter,
        ElasticsearchStructureManager $elasticsearchStructureManager,
        CurrentCustomer $currentCustomer
    ) {
        $this->indexPrefix = $indexPrefix;
        $this->client = $client;
        $this->productElasticsearchConverter = $productElasticsearchConverter;
        $this->elasticsearchStructureManager = $elasticsearchStructureManager;
        $this->currentCustomer = $currentCustomer;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $productQueryBuilder
     * @param string|null $searchText
     */
    public function filterBySearchText(QueryBuilder $productQueryBuilder, $searchText, $productFilterData)
    {
        $productIds = $this->getFoundProductIds($productQueryBuilder, $searchText, $productFilterData);

        if (count($productIds) > 0) {
            $productQueryBuilder->andWhere('p.id IN (:productIds)')->setParameter('productIds', $productIds);
        } else {
            $productQueryBuilder->andWhere('TRUE = FALSE');
        }
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $productQueryBuilder
     * @param string|null $searchText
     */
    public function addRelevance(QueryBuilder $productQueryBuilder, $searchText, $productFilterData)
    {
        $productIds = $this->getFoundProductIds($productQueryBuilder, $searchText, $productFilterData);

        if (count($productIds)) {
            $productQueryBuilder->addSelect('field(p.id, ' . implode(',', $productIds) . ') AS HIDDEN relevance');
        } else {
            $productQueryBuilder->addSelect('-1 AS HIDDEN relevance');
        }
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $productQueryBuilder
     * @param $searchText
     * @return int[]
     */
    protected function getFoundProductIds(QueryBuilder $productQueryBuilder, $searchText, $productFilterData)
    {
        $domainId = $productQueryBuilder->getParameter('domainId')->getValue();

        if (!isset($this->foundProductIdsCache[$domainId][$searchText])) {
            $foundProductIds = $this->getProductIdsBySearchText($domainId, $searchText, $productFilterData);

            $this->foundProductIdsCache[$domainId][$searchText] = $foundProductIds;
        }
        d($this->foundProductIdsCache[$domainId][$searchText]);
        return $this->foundProductIdsCache[$domainId][$searchText];
    }

    /**
     * @param int $domainId
     * @return string
     */
    protected function getIndexName(int $domainId): string
    {
        return $this->indexPrefix . self::ELASTICSEARCH_INDEX . $domainId;
    }

    /**
     * @param int $domainId
     * @param string|null $searchText
     * @return int[]
     */
    public function getProductIdsBySearchText(int $domainId, ?string $searchText, $productFilterData): array
    {
        if (!$searchText) {
            return [];
        }
        $parameters = $this->createQuery($this->getIndexName($domainId), $searchText, $productFilterData);
        $result = $this->client->search($parameters);
        d($result);
        return $this->extractIds($result);
    }

    /**
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-body.html
     * @param string $indexName
     * @param string $searchText
     * @return array
     */
    protected function createQuery(string $indexName, string $searchText, ProductFilterData $productFilterData): array
    {
        $brandIds = [];
        foreach ($productFilterData->brands as $brand) {
            $brandIds[] = $brand->getId();
        }

        $flagIds = [];

        foreach ($productFilterData->flags as $flag) {
            $flagIds[] = $flag->getId();
        }

        $parameters = [];

        foreach ($productFilterData->parameters as $parameterFilterData) {
            $parameterValueIds = [];
            foreach ($parameterFilterData->values as $parameterValue) {
                $parameterValueIds[] = $parameterValue->getId();
            }
            if (count($parameterValueIds) !== 0) {
                $parameters[] = ['nested' => [
                    'path' => 'parameters',
                    'query' => [
                        'bool' => [
                            'must' => [
                                'match_all' => new \stdClass()
                            ],
                            'filter' => [
                                ['term' => [
                                    'parameters.parameter_id' => $parameterFilterData->parameter->getId(),
                                ]],
                                ['terms' => [
                                    'parameters.parameter_value_id' => $parameterValueIds,
                                ]],
                            ],
                        ],
                    ],
                ]];
            }
        }



        $query = [
            'index' => $indexName,
            'type' => '_doc',
            'size' => 1000,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            'match_all' => new \stdClass()
                        ],
                        'filter' => [
                            ['nested' => [
                                'path' => 'price',
                                'query' => [
                                    'bool' => [
                                        'must' => [
                                            'match_all' => new \stdClass()
                                        ],
                                        'filter' => [
                                            'range' => [
                                                'price.cost' => [
                                                    'gte' => $productFilterData->minimalPrice === null ? 0 : floatval($productFilterData->minimalPrice->getAmount()),
                                                    'lte' => $productFilterData->maximalPrice === null ? 0 : floatval($productFilterData->maximalPrice->getAmount()),
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ]],
                            ['nested' => [
                                'path' => 'flags',
                                'query' => [
                                    'bool' => [
                                        'must' => [
                                            'match_all' => new \stdClass()
                                        ],
                                        'filter' => [
                                            'terms' => [
                                                'flags.id' => $flagIds,
                                            ],
                                        ],
                                    ],
                                ],
                            ]],
                            $parameters,
                            ['term' => [
                                'in_stock' => $productFilterData->inStock
                            ]],
                            ['terms' => [
                                'brand_id' => $brandIds
                            ]],
                        ],
                    ]
                ],
            ],
        ];
        d($query);
        d(json_encode($query));
        return $query;
    }

    /**
     * @param array $result
     * @return int[]
     */
    protected function extractIds(array $result): array
    {
        $hits = $result['hits']['hits'];
        return array_column($hits, '_id');
    }

    /**
     * @param int $domainId
     * @param array $data
     */
    public function bulkUpdate(int $domainId, array $data): void
    {
        $body = $this->productElasticsearchConverter->convertBulk(
            $this->elasticsearchStructureManager->getIndexName($domainId, self::ELASTICSEARCH_INDEX),
            $data
        );

        $params = [
            'body' => $body,
        ];
        $this->client->bulk($params);
    }

    /**
     * @param int $domainId
     * @param int[] $keepIds
     */
    public function deleteNotPresent(int $domainId, array $keepIds): void
    {
        $this->client->deleteByQuery([
            'index' => $this->elasticsearchStructureManager->getIndexName($domainId, self::ELASTICSEARCH_INDEX),
            'type' => '_doc',
            'body' => [
                'query' => [
                    'bool' => [
                        'must_not' => [
                            'ids' => [
                                'values' => $keepIds,
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }
}
