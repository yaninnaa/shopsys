<?php

namespace Shopsys\ShopBundle\Model\Product;

use Shopsys\ShopBundle\Component\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class ImportProductsCronModule implements SimpleCronModuleInterface
{
    const PRODUCT_DATA_URL = 'https://private-2f283-patro.apiary-mock.com/products';

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
        d($apiProductsData);
    }
}
