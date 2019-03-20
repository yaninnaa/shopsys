<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Sniffs;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SlevomatCodingStandard\Helpers\AnnotationHelper;
use SlevomatCodingStandard\Helpers\ConstantHelper;
use SlevomatCodingStandard\Helpers\TokenHelper;

class ForceLateStaticBindingForProtectedConstantsSniff implements Sniff
{
    /**
     * {@inheritdoc}
     */
    public function register(): array
    {
        return [\T_CLASS];
    }

    /**
     * {@inheritdoc}
     */
    public function process(File $file, $classPosition)
    {
        $tokens = $file->getTokens();

        $protectedConstants = $this->getAllProtectedConstantsInClass($file);

        $selfPositions = TokenHelper::findNextAll($file, \T_SELF, $classPosition);

        foreach ($selfPositions as $selfPosition) {
            $doubleColonPosition = TokenHelper::findNextEffective($file, $selfPosition + 1);
            if ($tokens[$doubleColonPosition]['code'] !== T_DOUBLE_COLON) {
                return;
            }

            $stringPosition = TokenHelper::findNextEffective($file, $doubleColonPosition + 1);
            if ($tokens[$stringPosition]['code'] !== T_STRING) {
                return;
            }

            if (strtolower($tokens[$stringPosition]['content']) === 'class') {
                return;
            }

            $positionAfterString = TokenHelper::findNextEffective($file, $stringPosition + 1);
            if ($tokens[$positionAfterString]['code'] === T_OPEN_PARENTHESIS) {
                return;
            }

            $constantName = $tokens[$stringPosition]['content'];

            if (\in_array($constantName, $protectedConstants, true)) {
                $file->addFixableError(
                    'For better extensibility use late static binding.',
                    $selfPosition,
                    self::class
                );

                $file->fixer->beginChangeset();
                $file->fixer->replaceToken($selfPosition, 'static');
                $file->fixer->endChangeset();
            }
        }
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $file
     * @return array
     */
    private function getAllProtectedConstantsInClass(File $file): array
    {
        $constPositions = TokenHelper::findNextAll($file, \T_CONST, 0);

        $protectedConstants = [];

        foreach ($constPositions as $constPosition) {
            $protectedModifierPosition = TokenHelper::findPreviousLocal($file, \T_PROTECTED, $constPosition);
            if ($protectedModifierPosition !== null) {
                $protectedConstants[] = ConstantHelper::getName($file, $constPosition);

                continue;
            }

            $accessAnnotations = AnnotationHelper::getAnnotationsByName($file, $constPosition, '@access');
            foreach ($accessAnnotations as $accessAnnotation) {
                if ($accessAnnotation->getContent() === 'protected') {
                    $protectedConstants[] = ConstantHelper::getName($file, $constPosition);

                    continue;
                }
            }
        }

        return $protectedConstants;
    }
}
