<?php

namespace Tests\ShopBundle\Acceptance\acceptance;

use Tests\ShopBundle\Acceptance\acceptance\PageObject\Admin\LoginPage;
use Tests\ShopBundle\Test\Codeception\AcceptanceTester;

class LoginAsCustomerCest
{
    public function testLoginAsCustomer(AcceptanceTester $me, LoginPage $loginPage)
    {
        $me->wantTo('login as a customer from admin');
        $loginPage->loginAsAdmin();
        $me->amOnPage('/admin/customer/edit/2');
        $me->clickByText('Přihlásit za uživatele');
        $me->switchToLastOpenedWindow();
        $me->seeCurrentPageEquals('/');
        $me->see('Pozor! Jste jako administrátor přihlášen za zákazníka.');
        $me->see('Igor Anpilogov');
    }
}
