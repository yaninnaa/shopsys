<?php

namespace Tests\ShopBundle\Database\Model\Product;

use Shopsys\ShopBundle\Component\Domain\Domain;
use Shopsys\ShopBundle\Model\Category\CategoryRepository;
use Shopsys\ShopBundle\Model\Customer\CurrentCustomer;
use Shopsys\ShopBundle\Model\Customer\CustomerFacade;
use Shopsys\ShopBundle\Model\Customer\User;
use Shopsys\ShopBundle\Model\Product\Accessory\ProductAccessoryRepository;
use Shopsys\ShopBundle\Model\Product\Brand\BrandRepository;
use Shopsys\ShopBundle\Model\Product\Detail\ProductDetailFactory;
use Shopsys\ShopBundle\Model\Product\Filter\ProductFilterCountRepository;
use Shopsys\ShopBundle\Model\Product\ProductOnCurrentDomainFacade;
use Shopsys\ShopBundle\Model\Product\ProductRepository;
use Tests\ShopBundle\Test\DatabaseTestCase;

class RecentlyBoughtProductsTest extends DatabaseTestCase
{
    /**
     * @return \Shopsys\ShopBundle\Model\Customer\User
     */
    private function getUserWithoutOrders()
    {
        $customerFacade = $this->getServiceByType(CustomerFacade::class);
        /* @var $customerFacade \Shopsys\ShopBundle\Model\Customer\CustomerFacade */

        // Demo user with ID 6 (vitek@netdevelo.cz) has no orders and has both filled contact address and telephone
        return $customerFacade->getUserById(6);
    }

    /**
     * @param \Shopsys\ShopBundle\Model\Customer\User $user
     * @return \Shopsys\ShopBundle\Model\Product\ProductOnCurrentDomainFacade
     */
    private function createProductOnCurrentDomainFacadeForUser(User $user)
    {
        $domain = $this->getServiceByType(Domain::class);
        /* @var $domain \Shopsys\ShopBundle\Component\Domain\Domain */
        $domain->switchDomainById($user->getDomainId());

        $currentCustomerMock = $this->getMockBuilder(CurrentCustomer::class)
            ->setMethods(['findCurrentUser', 'getPricingGroup'])
            ->disableOriginalConstructor()
            ->getMock();
        $currentCustomerMock->method('findCurrentUser')
            ->willReturn($user);
        $currentCustomerMock->method('getPricingGroup')
            ->willReturn($user->getPricingGroup());

        return new ProductOnCurrentDomainFacade(
            $this->getServiceByType(ProductRepository::class),
            $domain,
            $this->getServiceByType(ProductDetailFactory::class),
            $currentCustomerMock,
            $this->getServiceByType(CategoryRepository::class),
            $this->getServiceByType(ProductFilterCountRepository::class),
            $this->getServiceByType(ProductAccessoryRepository::class),
            $this->getServiceByType(BrandRepository::class)
        );
    }
}
