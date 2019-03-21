<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\CsFixer\Phpdoc;

use PhpCsFixer\DocBlock\DocBlock;
use PhpCsFixer\Fixer\DefinedFixerInterface;
use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

final class NoUselessAccessFixer implements FixerInterface, DefinedFixerInterface
{
    private const ACCESS_TOKENS = [
        \T_PUBLIC,
        \T_PROTECTED,
        \T_PRIVATE,
    ];

    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new FixerDefinition(
            '`@access` annotations should be omitted from PHPDoc when it is useless',
            [
                new CodeSample(
                    '<?php
class Foo
{
    /**
     * @internal
     * @access private
     */
    private $bar;
}
'
                ),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_DOC_COMMENT) && $tokens->isAnyTokenKindsFound(self::ACCESS_TOKENS);
    }

    /**
     * {@inheritdoc}
     */
    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        foreach ($tokens->findGivenKind(\T_DOC_COMMENT) as $index => $token) {
            $docBlock = new DocBlock($token->getContent());

            $annotations = $docBlock->getAnnotationsOfType('access');

            if (empty($annotations)) {
                continue;
            }

            $nextMeaningfulTokenId = $tokens[$tokens->getNextMeaningfulToken($index)]->getId();

            if (\in_array($nextMeaningfulTokenId, self::ACCESS_TOKENS, true)) {
                foreach ($annotations as $annotation) {
                    $annotation->remove();
                }

                if ($docBlock->getContent() === '' || $this->isDocBlockEmpty($docBlock)) {
                    $tokens->clearTokenAndMergeSurroundingWhitespace($index);
                } else {
                    $tokens[$index] = new Token([T_DOC_COMMENT, $docBlock->getContent()]);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isRisky(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Shopsys/phpdoc_no_useless_access';
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority(): int
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(\SplFileInfo $file): bool
    {
        return true;
    }

    /**
     * @param \PhpCsFixer\DocBlock\DocBlock $docBlock
     * @return bool
     */
    private function isDocBlockEmpty(DocBlock $docBlock): bool
    {
        foreach ($docBlock->getLines() as $line) {
            if ($line->containsUsefulContent()) {
                return false;
            }
        }

        return true;
    }
}
