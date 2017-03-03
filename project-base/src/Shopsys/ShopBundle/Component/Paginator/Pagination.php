<?php

namespace Shopsys\ShopBundle\Component\Paginator;

use Shopsys\ShopBundle\Twig\RequestExtension;
use Symfony\Component\HttpFoundation\Request;

class Pagination
{
    const DEFAULT_PAGE_QUERY_PARAMETER = 'page';

    /**
     * @var \Shopsys\ShopBundle\Twig\RequestExtension
     */
    private $requestExtension;

    public function __construct(RequestExtension $requestExtension)
    {
        $this->requestExtension = $requestExtension;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $pageQueryParameter
     * @return int
     */
    public function getPage(Request $request, $pageQueryParameter = self::DEFAULT_PAGE_QUERY_PARAMETER)
    {
        $requestedPage = $request->get($pageQueryParameter);
        if (!$this->isRequestedPageValid($requestedPage)) {
            throw new \Shopsys\ShopBundle\Component\Paginator\Exception\InvalidRequestedPageException(
                'Requested invalid page in query parameter "' . $pageQueryParameter . ' ". It must be a number greater '
                . 'than one or it must not be requested at all. User should be redirected to the first page.',
                $this->requestExtension->getRoute(),
                $this->getRequestParametersWithoutPage($pageQueryParameter)
            );
        }

        return $requestedPage === null ? 1 : (int)$requestedPage;
    }

    /**
     * @param string|null $page
     * @return bool
     */
    private function isRequestedPageValid($page)
    {
        return $page === null || (preg_match('@^([2-9]|[1-9][0-9]+)$@', $page));
    }

    /**
     * @param string$pageQueryParameter
     * @return array
     */
    private function getRequestParametersWithoutPage($pageQueryParameter)
    {
        $parameters = $this->requestExtension->getAllRequestParams();
        unset($parameters[$pageQueryParameter]);

        return $parameters;
    }
}
