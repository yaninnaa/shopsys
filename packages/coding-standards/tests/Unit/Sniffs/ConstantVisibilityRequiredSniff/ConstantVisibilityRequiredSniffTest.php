<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Sniffs\ConstantVisibilityRequiredSniff;

use Symplify\EasyCodingStandardTester\Testing\AbstractCheckerTestCase;

final class ConstantVisibilityRequiredSniffTest extends AbstractCheckerTestCase
{
    public function testCorrect(): void
    {
        $this->doTestCorrectFile(__DIR__ . '/Correct/Annotation.php');
        $this->doTestCorrectFile(__DIR__ . '/Correct/SingleValueWithoutNamespace.php');
        $this->doTestCorrectFile(__DIR__ . '/Correct/SingleValueAfterMethodWithoutNamespace.php');
        $this->doTestCorrectFile(__DIR__ . '/Correct/MultipleValues.php');
        $this->doTestCorrectFile(__DIR__ . '/Correct/Mixed.php');
        $this->doTestCorrectFile(__DIR__ . '/Correct/noClass.php');
        $this->doTestCorrectFile(__DIR__ . '/Correct/OutsideClass.php');
    }

    public function testWrong(): void
    {
        $this->doTestWrongFile(__DIR__ . '/Wrong/SingleValue.php');
        $this->doTestWrongFile(__DIR__ . '/Wrong/MissingAnnotation.php');
        $this->doTestWrongFile(__DIR__ . '/Wrong/Mixed.php');
        $this->doTestWrongFile(__DIR__ . '/Wrong/MixedAtTheEnd.php');
        $this->doTestWrongFile(__DIR__ . '/Wrong/MixedInTheMiddle.php');
        $this->doTestWrongFile(__DIR__ . '/Wrong/SingleValueAfterMethodWithoutNamespaceWrong.php');
    }

    /**
     * @return string
     */
    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }
}
