<?php

namespace Shopsys\ShopBundle\Model\Product\Brand;

use Shopsys\ShopBundle\Component\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class ImportBrandCronModule implements SimpleCronModuleInterface
{
    const BRAND_DATA_URL = 'https://private-2f283-patro.apiary-mock.com/brands';

    /**
     * @var \Shopsys\ShopBundle\Model\Product\Brand\BrandFacade
     */
    private $brandFacade;

    public function __construct(BrandFacade $brandFacade)
    {
        $this->brandFacade = $brandFacade;
    }

    /**
     * @inheritdoc
     */
    public function setLogger(Logger $logger)
    {
    }

    public function run()
    {
        $brandJsonData = file_get_contents(self::BRAND_DATA_URL);
        $brandData = json_decode($brandJsonData, true);

        $this->importBrandsData($brandData);
    }

    /**
     * @param array $importedBrandsData
     */
    public function importBrandsData(array $importedBrandsData)
    {
        foreach ($importedBrandsData as $importedBrandData) {
            $apiId = (int)$importedBrandData['id'];

            $brand = $this->brandFacade->findByApiId($apiId);

            $brandData = new BrandData();
            if ($brand === null) {
                $brandData->apiId = $apiId;
                $brandData->name = $importedBrandData['name'];
                $this->brandFacade->create($brandData);
            } else {
                $brandData->setFromEntity($brand);
                $brandData->name = $importedBrandData['name'];
                $this->brandFacade->edit($brand->getId(), $brandData);
            }
        }
    }
}
