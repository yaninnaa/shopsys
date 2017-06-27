<?php

namespace Tests\ShopBundle\Acceptance\acceptance;

use Tests\ShopBundle\Acceptance\acceptance\PageObject\Admin\LoginPage;
use Tests\ShopBundle\Test\Codeception\AcceptanceTester;

class HomepageArticleCreateAndCheckCest
{
    public function testCreateAndSeeArticleOnHomepage(AcceptanceTester $me, LoginPage $loginPage)
    {
        $me->wantTo('login to admin, create article and see it on homepage');
        $loginPage->loginAsAdmin();
        $me->amOnPage('/admin/article/new/');
        $me->see('New article');
        $me->selectOptionByCssAndValue('select[name="article_form[placement]"]', 'homepage');
        $me->fillFieldByName('article_form[name]', 'My awesome homepage article');
        $me->executeJS('CKEDITOR.instances.article_form_text.setData(\'This article shows how acceptance tests works.\')');
        $me->clickByName('article_form[save]');
        $me->see('Article My awesome homepage article created');

        $me->amOnPage('/');
        $me->see('My awesome homepage article');
    }
}
