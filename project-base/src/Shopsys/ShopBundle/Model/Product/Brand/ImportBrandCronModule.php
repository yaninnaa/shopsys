<?php

namespace Shopsys\ShopBundle\Model\Product\Brand;

use Shopsys\ShopBundle\Component\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class ImportBrandCronModule implements SimpleCronModuleInterface
{
    const BRAND_DATA_URL = 'https://private-2f283-patro.apiary-mock.com/brands';

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
        d($brandData);
    }
}
