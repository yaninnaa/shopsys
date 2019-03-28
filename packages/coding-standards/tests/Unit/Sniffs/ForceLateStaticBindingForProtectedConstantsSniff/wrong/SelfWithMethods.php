<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Sniffs\ForceLateStaticBindingForProtectedConstantsSniff;

class SelfWithMethods
{
    public const A = 'value';
    protected const B = 'value';
    private const C = 'value';

    public function method()
    {
        echo self::A;
        self::class;
        self::publicStaticMethod();
        self::protectedStaticMethod();
        self::privateStaticMethod();

        echo self::B;
        self::class;
        self::publicStaticMethod();
        self::protectedStaticMethod();
        self::privateStaticMethod();

        echo self::C;
        self::class;
        self::publicStaticMethod();
        self::protectedStaticMethod();
        self::privateStaticMethod();

        echo self::A;
        echo self::B;
        echo self::C;
    }

    public static function publicStaticMethod()
    {
        echo 'value';
    }

    protected static function protectedStaticMethod()
    {
        echo 'value';
    }

    private static function privateStaticMethod()
    {
        echo 'value';
    }
}
