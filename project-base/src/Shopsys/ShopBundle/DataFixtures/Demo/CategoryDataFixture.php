<?php

namespace Shopsys\ShopBundle\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\ShopBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\ShopBundle\Component\Domain\Domain;
use Shopsys\ShopBundle\DataFixtures\Base\CategoryRootDataFixture;
use Shopsys\ShopBundle\Model\Category\CategoryData;

class CategoryDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    const CATEGORY_ELECTRONICS = 'category_electronics';
    const CATEGORY_TV = 'category_tv';
    const CATEGORY_PHOTO = 'category_photo';
    const CATEGORY_PRINTERS = 'category_printers';
    const CATEGORY_PC = 'category_pc';
    const CATEGORY_PHONES = 'category_phones';
    const CATEGORY_COFFEE = 'category_coffee';
    const CATEGORY_BOOKS = 'category_books';
    const CATEGORY_TOYS = 'category_toys';
    const CATEGORY_GARDEN_TOOLS = 'category_garden_tools';
    const CATEGORY_FOOD = 'category_food';

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function load(ObjectManager $manager)
    {
        $categoryData = new CategoryData();

        $categoryData->name = [
            'cs' => 'Elektro',
            'en' => 'Electronics',
        ];
        $categoryData->descriptions = [
            Domain::FIRST_DOMAIN_ID => 'Spotřební elektronika zahrnuje elektronická zařízení každodenního (nebo alespoň častého)'
                . ' použití pro komunikaci, v kanceláři i doma.',
        ];
        $categoryData->parent = $this->getReference(CategoryRootDataFixture::CATEGORY_ROOT);
        $electronicsCategory = $this->createCategory($categoryData, self::CATEGORY_ELECTRONICS);

        $categoryData->name = [
            'cs' => 'Televize, audio',
            'en' => 'TV, audio',
        ];
        $categoryData->descriptions = [
            Domain::FIRST_DOMAIN_ID => 'Televize (z řeckého tele – daleko a latinského vize – vidět) je široce používané'
                . ' jednosměrné dálkové plošné vysílání (tzv. broadcasting) a individuální přijímání televizního vysílání'
                . ' – obrazu a zvuku do televizoru. Výrazně přispívá k celkové socializaci lidí takřka po celém světě.',
        ];
        $categoryData->parent = $electronicsCategory;
        $this->createCategory($categoryData, self::CATEGORY_TV);

        $categoryData->name = [
            'cs' => 'Fotoaparáty',
            'en' => 'Cameras & Photo',
        ];
        $categoryData->descriptions = [
            Domain::FIRST_DOMAIN_ID => 'Fotoaparát je zařízení sloužící k pořizování a zaznamenání fotografií. Každý fotoaparát'
                . ' je v principu světlotěsně uzavřená komora s malým otvorem (nebo nějakou složitější optickou soustavou –'
                . ' objektivem), jímž dovnitř vstupuje světlo, a nějakým druhem světlocitlivé záznamové vrstvy na druhé straně'
                . ', na níž dopadající světlo kreslí obraz.',
        ];
        $this->createCategory($categoryData, self::CATEGORY_PHOTO);

        $categoryData->name = [
            'cs' => 'Tiskárny',
            'en' => 'Printers',
        ];
        $categoryData->descriptions = [
            Domain::FIRST_DOMAIN_ID => 'Tiskárna je periferní výstupní zařízení, které slouží k přenosu dat uložených v'
                . ' elektronické podobě na papír nebo jiné médium (fotopapír, kompaktní disk apod.). Tiskárnu připojujeme'
                . ' k počítači, ale může fungovat i samostatně.',
        ];
        $this->createCategory($categoryData, self::CATEGORY_PRINTERS);

        $categoryData->name = [
            'cs' => 'Počítače & příslušenství',
            'en' => 'Personal Computers & accessories',
        ];
        $categoryData->descriptions = [
            Domain::FIRST_DOMAIN_ID => 'Počítač je zařízení a výpočetní technika, která zpracovává data pomocí předem'
                . ' vytvořeného programu. Současný počítač je elektronický a skládá se z hardwaru, který představuje fyzické'
                . ' části počítače (mikroprocesor, klávesnice, monitor atd.) a ze softwaru (operační systém a programy). Počítač'
                . ' je zpravidla ovládán uživatelem, který poskytuje počítači data ke zpracování prostřednictvím jeho vstupních'
                . ' zařízení a počítač výsledky prezentuje pomocí výstupních zařízení. V současnosti jsou počítače využívány'
                . ' téměř ve všech oborech lidské činnosti.',
        ];
        $this->createCategory($categoryData, self::CATEGORY_PC);

        $categoryData->name = [
            'cs' => 'Mobilní telefony',
            'en' => 'Mobile Phones',
        ];
        $categoryData->descriptions = [
            Domain::FIRST_DOMAIN_ID => 'Mobilní telefony umožňují nejen komunikaci v rámci mobilní sítě, ale i spojení s pevnou'
                . ' telefonní sítí přímo volbou telefonního čísla na vestavěné klávesnici a poskytují širokou škálu dalších'
                . ' telekomunikačních služeb, jako jsou SMS, MMS, WAP a připojení na Internet. Protože patří k nejrozšířenějším'
                . ' elektronickým zařízením, výrobci je vybavují také dalšími funkcemi.',
        ];
        $this->createCategory($categoryData, self::CATEGORY_PHONES);

        $categoryData->name = [
            'cs' => 'Kávovary',
            'en' => 'Coffee Machines',
        ];
        $categoryData->descriptions = [
            Domain::FIRST_DOMAIN_ID => 'Kávovar je stroj určený pro výrobu kávy takovým způsobem, aby se voda nemusela vařit'
                . ' v oddělené nádobě. Existuje obrovské množství kávovarů, nicméně princip přípravy kávy je vždy stejný: do'
                . ' kovového či papírového filtru se vloží rozemletá káva. Filtr s kávou se vloží do kávovaru, kde se přes něj'
                . ' (většinou pod tlakem) nechá přetéct horká voda vytékající do připravené nádoby na kávu (hrnek, sklenici apod.).',
        ];
        $this->createCategory($categoryData, self::CATEGORY_COFFEE);

        $categoryData->name = [
            'cs' => 'Knihy',
            'en' => 'Books',
        ];
        $categoryData->descriptions = [
            Domain::FIRST_DOMAIN_ID => 'Kniha je sešitý nebo slepený svazek listů nebo skládaný arch papíru, kartonu, pergamenu'
                . ' nebo jiného materiálu, popsaný, potištěný nebo prázdný s vazbou a opatřený přebalem.',
        ];
        $categoryData->parent = $this->getReference(CategoryRootDataFixture::CATEGORY_ROOT);
        $this->createCategory($categoryData, self::CATEGORY_BOOKS);

        $categoryData->name = [
            'cs' => 'Hračky a další',
            'en' => 'Toys',
        ];
        $categoryData->descriptions = [
            Domain::FIRST_DOMAIN_ID => 'Hračka je předmět používaný ke hře dětí, ale někdy i dospělých. Slouží k upoutání'
                . ' pozornosti dítěte, jeho zabavení, ale také k rozvíjení jeho motorických a psychických schopností. Hračky'
                . ' existují již po tisíce let. Panenky, zvířátka, vojáčci a miniatury nástrojů dospělých jsou nacházeny'
                 . ' v archeologických vykopávkách od nepaměti.',
        ];
        $this->createCategory($categoryData, self::CATEGORY_TOYS);

        $categoryData->name = [
            'cs' => 'Zahradní náčiní',
            'en' => 'Gargen tools',
        ];
        $categoryData->descriptions = [
            Domain::FIRST_DOMAIN_ID => 'Oddělení zahradního náčiní je jedno z největších oddělení v naší nabídce. Za pozornost'
                . ' stojí zejména naše filtry různých druhů nečistot, avšak doporučujeme popatřit zrakem i na naše boční držáky'
                . ' plechů.',
        ];
        $this->createCategory($categoryData, self::CATEGORY_GARDEN_TOOLS);

        $categoryData->name = [
            'cs' => 'Jídlo',
            'en' => 'Food',
        ];
        $categoryData->descriptions = [
            Domain::FIRST_DOMAIN_ID => 'Potravina je výrobek nebo látka určená pro výživu lidí a konzumovaná ústy v nezměněném'
                . ' nebo upraveném stavu. Potraviny se dělí na poživatiny a pochutiny. Potraviny mohou být rostlinného, živočišného'
                . ' nebo jiného původu.',
        ];
        $this->createCategory($categoryData, self::CATEGORY_FOOD);
    }

    /**
     * @param \Shopsys\ShopBundle\Model\Category\CategoryData $categoryData
     * @param string|null $referenceName
     * @return \Shopsys\ShopBundle\Model\Category\Category
     */
    private function createCategory(CategoryData $categoryData, $referenceName = null)
    {
        $categoryFacade = $this->get('shopsys.shop.category.category_facade');
        /* @var $categoryFacade \Shopsys\ShopBundle\Model\Category\CategoryFacade */

        $category = $categoryFacade->create($categoryData);
        if ($referenceName !== null) {
            $this->addReference($referenceName, $category);
        }

        return $category;
    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return [
            CategoryRootDataFixture::class,
        ];
    }
}
