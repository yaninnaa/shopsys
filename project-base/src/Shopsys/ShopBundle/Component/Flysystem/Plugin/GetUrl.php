<?php

declare(strict_types=1);

namespace Shopsys\ShopBundle\Component\Flysystem\Plugin;

use Barryvdh\elFinderFlysystemDriver\Plugin\GetUrl as BaseGetUrl;

class GetUrl extends BaseGetUrl
{
    /**
     * @var array
     */
    private $options;

    /**
     * @param array $options
     */
    public function __construct(array $options = null)
    {
        $this->options = $options;
    }

    /**
     * Get the URL using a `getUrl()` method on the adapter.
     *
     * @param  string $path
     * @return string
     */
    protected function getFromMethod($path)
    {
        if (!empty($this->options['URL'])) {
            return $this->options['URL'] . str_replace($this->options['path'], '', $path);
        }

        return parent::getFromMethod($path);
    }
}
