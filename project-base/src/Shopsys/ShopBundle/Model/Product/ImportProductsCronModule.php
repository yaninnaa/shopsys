<?php

namespace Shopsys\ShopBundle\Model\Product;

use Shopsys\ShopBundle\Component\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class ImportProductsCronModule implements SimpleCronModuleInterface
{
    /**
     * @inheritdoc
     */
    public function setLogger(Logger $logger)
    {
    }

    public function run()
    {
        d('cron runs');
    }
}