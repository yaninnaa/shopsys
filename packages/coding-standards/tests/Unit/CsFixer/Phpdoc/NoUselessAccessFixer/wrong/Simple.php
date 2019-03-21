<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer\Phpdoc\NoUselessAccessFixer;

class Simple
{
    /**
     * @access private
     */
    public const A = 'value';
    /** @access public */
    protected const B = 'value';
}
