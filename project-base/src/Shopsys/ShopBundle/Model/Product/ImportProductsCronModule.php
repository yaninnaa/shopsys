<?php

namespace Shopsys\ShopBundle\Model\Product;

use Shopsys\ShopBundle\Component\Cron\SimpleCronModuleInterface;
use Shopsys\ShopBundle\Model\Localization\Localization;
use Shopsys\ShopBundle\Model\Pricing\Vat\VatFacade;
use Shopsys\ShopBundle\Model\Product\Unit\UnitFacade;
use Symfony\Bridge\Monolog\Logger;

class ImportProductsCronModule implements SimpleCronModuleInterface
{
    const DOMAIN_ID = 1;
    const LOCALE = 'cs';

    const PRODUCT_DATA_URL = 'https://private-2f283-patro.apiary-mock.com/products';

    /**
     * @var \Shopsys\ShopBundle\Model\Product\ProductFacade
     */
    private $productFacade;

    /**
     * @var \Shopsys\ShopBundle\Model\Product\ProductEditDataFactory
     */
    private $productEditDataFactory;

    /**
     * @var \Shopsys\ShopBundle\Model\Pricing\Vat\VatFacade
     */
    private $vatFacade;

    public function __construct(
        ProductFacade $productFacade,
        ProductEditDataFactory $productEditDataFactory,
        VatFacade $vatFacade
    ) {
        $this->productFacade = $productFacade;
        $this->productEditDataFactory = $productEditDataFactory;
        $this->vatFacade = $vatFacade;
    }

    /**
     * @inheritdoc
     */
    public function setLogger(Logger $logger)
    {
    }

    public function run()
    {
        $apiProductsJsonData = file_get_contents(self::PRODUCT_DATA_URL);
        $apiProductsData = json_decode($apiProductsJsonData, true);
        $this->importApiProductsData($apiProductsData);
    }

    /**
     * @param array $apiProductsData
     */
    public function importApiProductsData(array $apiProductsData)
    {
        foreach ($apiProductsData as $apiProductData) {
            $apiId = $apiProductData['id'];

            $product = $this->productFacade->findByApiId($apiId);

            if ($product === null) {
                $productEditData = $this->productEditDataFactory->createDefault();

                $this->fillProductEditData($productEditData, $apiProductData);

                $this->productFacade->create($productEditData);
            } else {
                $productEditData = $this->productEditDataFactory->createFromProduct($product);

                $this->fillProductEditData($productEditData, $apiProductData);

                $this->productFacade->edit($product->getId(), $productEditData);
            }
        }
    }

    /**
     * @param \Shopsys\ShopBundle\Model\Product\ProductEditData $productEditData
     * @param array $apiProductData
     */
    private function fillProductEditData(ProductEditData $productEditData, array $apiProductData)
    {
        $productEditData->productData->name[self::LOCALE] = $apiProductData['id'];
        $productEditData->productData->price = $apiProductData['price_without_vat'];
        $productEditData->productData->vat = $this->vatFacade->getVatByPercent($apiProductData['vat_percent']);
        $productEditData->productData->ean = $apiProductData['ean'];
        $productEditData->descriptions[self::DOMAIN_ID] = $apiProductData['description'];
        $productEditData->productData->usingStock = true;
        $productEditData->productData->stockQuantity = $apiProductData['stock_quantity'];
    }
}
