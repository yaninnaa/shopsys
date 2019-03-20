<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Sniffs\ForceLateStaticBindingForProtectedConstantsSniff;

class SingleValue
{
    protected const A = 'value';
    /**
     * @access protected
     */
    const B = 'value';
    public const C = 'value';

    public function method()
    {
        echo self::A;
        echo self::B;
        echo self::C;
    }
}
