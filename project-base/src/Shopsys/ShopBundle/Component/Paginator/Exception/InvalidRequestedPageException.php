<?php

namespace Shopsys\ShopBundle\Component\Paginator\Exception;

use Exception;
use Shopsys\ShopBundle\Component\Paginator\Exception\PaginatorException;

class InvalidRequestedPageException extends Exception implements PaginatorException
{
    /**
     * @var string
     */
    private $redirectRoute;

    /**
     * @var array
     */
    private $redirectParameters;

    /**
     * @param string $message
     * @param string $redirectRoute
     * @param array $redirectParameters
     * @param \Exception|null $previous
     */
    public function __construct($message = '', $redirectRoute, array $redirectParameters, Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->redirectRoute = $redirectRoute;
        $this->redirectParameters = $redirectParameters;
    }

    /**
     * @return string
     */
    public function getRedirectRoute()
    {
        return $this->redirectRoute;
    }

    /**
     * @return array
     */
    public function getRedirectParameters()
    {
        return $this->redirectParameters;
    }
}
