<?php

namespace Shopsys\FrameworkBundle\Model\Product\Search\Export;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductCalculatedPrice;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibility;

class ProductSearchExportRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var ParameterRepository
     */
    private $parameterRepository;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em, ParameterRepository $parameterRepository)
    {
        $this->em = $em;
        $this->parameterRepository = $parameterRepository;
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @param int $startFrom
     * @param int $batchSize
     * @return array
     */
    public function getProductsData(int $domainId, string $locale, int $startFrom, int $batchSize): array
    {
        $queryBuilder = $this->createQueryBuilder($domainId, $locale)
            ->setFirstResult($startFrom)
            ->setMaxResults($batchSize);

        $query = $queryBuilder->getQuery();

        $result = [];
        foreach ($query->getResult() as $productCalculatedPrice) {
            /** @var ProductCalculatedPrice $productCalculatedPrice */
            $product = $productCalculatedPrice->getProduct();
            $flagIds = [];
            foreach ($product->getFlags() as $flag) {
                $flagIds[] = ['id' => $flag->getId()];
            }
            $productParameterValues = $this->parameterRepository->getProductParameterValuesByProduct($product);
            $parameters = [];
            foreach ($productParameterValues as $productParameterValue) {
                $parameters[] = [
                    'parameter_id' => $productParameterValue->getParameter()->getId(),
                    'parameter_value_id' => $productParameterValue->getValue()->getId(),
                ] ;
            }
            $result[] = [
                'id' => $product->getId(),
                'catnum' => $product->getCatnum(),
                'partno' => $product->getPartno(),
                'ean' => $product->getEan(),
                'name' => $product->getName($domainId),
                'description' => $product->getDescription($domainId),
                'shortDescription' => $product->getShortDescription($domainId),
                'brand_id' => $product->getBrand()->getId(),
                'flags' => $flagIds,
                'in_stock' => $product->getCalculatedAvailability()->getDispatchTime() === 0,
                'price' => [
                    'pricing_group_id' => $productCalculatedPrice->getPricingGroup()->getId(),
                    'cost' => $productCalculatedPrice->getPriceWithVat() !== null ? floatval($productCalculatedPrice->getPriceWithVat()->getAmount()) : 0,
                ],
                'parameters' => $parameters,
            ];
        }

        return $result;
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function createQueryBuilder(int $domainId, string $locale): QueryBuilder
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('pcp')
            ->from(ProductCalculatedPrice::class, 'pcp')
            ->join(Product::class, 'p', Join::WITH, 'pcp.product = p.id')
            ->where('p.variantType != :variantTypeVariant')
            ->join(ProductVisibility::class, 'prv', Join::WITH, 'prv.product = p.id')
                ->andWhere('prv.domainId = :domainId')
                ->andWhere('prv.visible = TRUE')
            ->join('p.calculatedAvailability', 'a')
            ->join('p.flags', 'f')
            ->join('p.brand', 'b')
            ->orderBy('p.id');

        $queryBuilder->setParameter('domainId', $domainId)
            ->setParameter('variantTypeVariant', Product::VARIANT_TYPE_VARIANT);

        return $queryBuilder;
    }
}
