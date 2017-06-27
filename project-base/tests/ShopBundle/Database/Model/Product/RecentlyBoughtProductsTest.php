<?php

namespace Tests\ShopBundle\Database\Model\Product;

use DateTime;
use Shopsys\ShopBundle\Component\DataFixture\PersistentReferenceFacade;
use Shopsys\ShopBundle\Component\Domain\Domain;
use Shopsys\ShopBundle\DataFixtures\Base\OrderStatusDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\PaymentDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\TransportDataFixture;
use Shopsys\ShopBundle\Model\Category\CategoryRepository;
use Shopsys\ShopBundle\Model\Customer\CurrentCustomer;
use Shopsys\ShopBundle\Model\Customer\CustomerFacade;
use Shopsys\ShopBundle\Model\Customer\User;
use Shopsys\ShopBundle\Model\Order\FrontOrderData;
use Shopsys\ShopBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\ShopBundle\Model\Order\OrderFacade;
use Shopsys\ShopBundle\Model\Order\Preview\OrderPreviewFactory;
use Shopsys\ShopBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\ShopBundle\Model\Product\Accessory\ProductAccessoryRepository;
use Shopsys\ShopBundle\Model\Product\Brand\BrandRepository;
use Shopsys\ShopBundle\Model\Product\Detail\ProductDetail;
use Shopsys\ShopBundle\Model\Product\Detail\ProductDetailFactory;
use Shopsys\ShopBundle\Model\Product\Filter\ProductFilterCountRepository;
use Shopsys\ShopBundle\Model\Product\ProductFacade;
use Shopsys\ShopBundle\Model\Product\ProductOnCurrentDomainFacade;
use Shopsys\ShopBundle\Model\Product\ProductRepository;
use Tests\ShopBundle\Test\DatabaseTestCase;

class RecentlyBoughtProductsTest extends DatabaseTestCase
{
    public function testThereAreNoRecentlyBoughtProductsWhenNoOrderExists()
    {
        $user = $this->getUserWithoutOrders();
        $productOnCurrentDomainFacade = $this->createProductOnCurrentDomainFacadeForUser($user);

        $recentlyBought = $productOnCurrentDomainFacade->getRecentlyBoughtProductsDetails();

        $this->assertEmpty($recentlyBought);
    }

    public function testProductsInOrderAreReturnedAsRecentlyBoughtProducts()
    {
        $firstProduct = $this->getProduct(1);
        $secondProduct = $this->getProduct(2);

        $user = $this->getUserWithoutOrders();
        $this->createOrder($user, [new QuantifiedProduct($firstProduct, 1), new QuantifiedProduct($secondProduct, 1)]);
        $productOnCurrentDomainFacade = $this->createProductOnCurrentDomainFacadeForUser($user);

        $recentlyBought = $productOnCurrentDomainFacade->getRecentlyBoughtProductsDetails();

        $this->assertEquals([$firstProduct, $secondProduct], $this->getProductsFromProductDetails($recentlyBought));
    }

    public function testRecentlyBoughtProductsAreSortedByOrderDateTime()
    {
        $firstProduct = $this->getProduct(1);
        $secondProduct = $this->getProduct(2);

        $user = $this->getUserWithoutOrders();
        $this->createOrder($user, [new QuantifiedProduct($firstProduct, 1)], new DateTime('- 1 day'));
        $this->createOrder($user, [new QuantifiedProduct($secondProduct, 1)]);
        $productOnCurrentDomainFacade = $this->createProductOnCurrentDomainFacadeForUser($user);

        $recentlyBought = $productOnCurrentDomainFacade->getRecentlyBoughtProductsDetails();

        $this->assertEquals([$secondProduct, $firstProduct], $this->getProductsFromProductDetails($recentlyBought));
    }

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

    /**
     * @param int $productId
     * @return \Shopsys\ShopBundle\Model\Product\Product
     */
    private function getProduct($productId)
    {
        $productFacade = $this->getServiceByType(ProductFacade::class);
        /* @var $productFacade \Shopsys\ShopBundle\Model\Product\ProductFacade */

        return $productFacade->getById($productId);
    }

    /**
     * @param \Shopsys\ShopBundle\Model\Customer\User $user
     * @param \Shopsys\ShopBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param \DateTime|null $createdAt
     * @return \Shopsys\ShopBundle\Model\Order\Order
     */
    private function createOrder(
        User $user,
        array $quantifiedProducts,
        DateTime $createdAt = null
    ) {
        $orderFacade = $this->getServiceByType(OrderFacade::class);
        /* @var $orderFacade \Shopsys\ShopBundle\Model\Order\OrderFacade */
        $orderPreviewFactory = $this->getServiceByType(OrderPreviewFactory::class);
        /* @var $orderPreviewFactory \Shopsys\ShopBundle\Model\Order\Preview\OrderPreviewFactory */
        $currencyFacade = $this->getServiceByType(CurrencyFacade::class);
        /* @var $currencyFacade \Shopsys\ShopBundle\Model\Pricing\Currency\CurrencyFacade */
        $persistentReferenceFacade = $this->getServiceByType(PersistentReferenceFacade::class);
        /* @var $persistentReferenceFacade \Shopsys\ShopBundle\Component\DataFixture\PersistentReferenceFacade */

        $domain = $user->getDomainId();
        $currency = $currencyFacade->getDomainDefaultCurrencyByDomainId($domain);
        $orderStatus = $persistentReferenceFacade->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW);
        $transport = $persistentReferenceFacade->getReference(TransportDataFixture::TRANSPORT_PERSONAL);
        $payment = $persistentReferenceFacade->getReference(PaymentDataFixture::PAYMENT_CASH);

        $orderData = new FrontOrderData();
        $orderData->domainId = $domain;
        $orderData->currency = $currency;
        $orderData->status = $orderStatus;
        $orderData->transport = $transport;
        $orderData->payment = $payment;
        $orderData->createdAt = $createdAt;
        $orderFacade->prefillFrontOrderData($orderData, $user);

        $orderPreview = $orderPreviewFactory->create($currency, $domain, $quantifiedProducts, $transport, $payment, $user);

        return $orderFacade->createOrder($orderData, $orderPreview, $user);
    }

    /**
     * @param \Shopsys\ShopBundle\Model\Product\Detail\ProductDetail[] $productDetails
     * @return \Shopsys\ShopBundle\Model\Product\Product[]
     */
    private function getProductsFromProductDetails(array $productDetails)
    {
        $products = array_map(
            function (ProductDetail $productDetail) {
                return $productDetail->getProduct();
            },
            $productDetails
        );

        return $products;
    }
}
