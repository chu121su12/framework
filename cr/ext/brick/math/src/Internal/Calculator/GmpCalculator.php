<?php

/*declare(strict_types=1);*/

namespace Brick\Math\Internal\Calculator;

use Brick\Math\Internal\Calculator;

/**
 * Calculator implementation built around the GMP library.
 *
 * @internal
 *
 * @psalm-immutable
 */
class GmpCalculator extends Calculator
{
    public function add(/*string */$a, /*string */$b)/* : string*/
    {
        $a = backport_type_check('string', $a);

        $b = backport_type_check('string', $b);

        return \gmp_strval(\gmp_add($a, $b));
    }

    public function sub(/*string */$a, /*string */$b)/* : string*/
    {
        $a = backport_type_check('string', $a);

        $b = backport_type_check('string', $b);

        return \gmp_strval(\gmp_sub($a, $b));
    }

    public function mul(/*string */$a, /*string */$b)/* : string*/
    {
        $a = backport_type_check('string', $a);

        $b = backport_type_check('string', $b);

        return \gmp_strval(\gmp_mul($a, $b));
    }

    public function divQ(/*string */$a, /*string */$b)/* : string*/
    {
        $a = backport_type_check('string', $a);

        $b = backport_type_check('string', $b);

        return \gmp_strval(\gmp_div_q($a, $b));
    }

    public function divR(/*string */$a, /*string */$b)/* : string*/
    {
        $a = backport_type_check('string', $a);

        $b = backport_type_check('string', $b);

        return \gmp_strval(\gmp_div_r($a, $b));
    }

    public function divQR(/*string */$a, /*string */$b)/* : array*/
    {
        $a = backport_type_check('string', $a);

        $b = backport_type_check('string', $b);

        list($q, $r) = \gmp_div_qr($a, $b);

        return [
            \gmp_strval($q),
            \gmp_strval($r)
        ];
    }

    public function pow(/*string */$a, /*int */$e)/* : string*/
    {
        $a = backport_type_check('string', $a);

        $e = backport_type_check('int', $e);

        return \gmp_strval(\gmp_pow($a, $e));
    }

    public function modInverse(/*string */$x, /*string */$m)/* : ?string*/
    {
        $x = backport_type_check('string', $x);

        $m = backport_type_check('string', $m);

        $result = \gmp_invert($x, $m);

        if ($result === false) {
            return null;
        }

        return \gmp_strval($result);
    }

    public function modPow(/*string */$base, /*string */$exp, /*string */$mod)/* : string*/
    {
        $base = backport_type_check('string', $base);

        $exp = backport_type_check('string', $exp);

        $mod = backport_type_check('string', $mod);

        return \gmp_strval(\gmp_powm($base, $exp, $mod));
    }

    public function gcd(/*string */$a, /*string */$b)/* : string*/
    {
        $a = backport_type_check('string', $a);

        $b = backport_type_check('string', $b);

        return \gmp_strval(\gmp_gcd($a, $b));
    }

    public function fromBase(/*string */$number, /*int */$base)/* : string*/
    {
        $number = backport_type_check('string', $number);

        $base = backport_type_check('int', $base);

        return \gmp_strval(\gmp_init($number, $base));
    }

    public function toBase(/*string */$number, /*int */$base)/* : string*/
    {
        $number = backport_type_check('string', $number);

        $base = backport_type_check('int', $base);

        return \gmp_strval($number, $base);
    }

    /**
     * @deprecated
     */
    public function and_(/*string */$a, /*string */$b)/* : string*/
    {
        $a = backport_type_check('string', $a);

        $b = backport_type_check('string', $b);

        return \gmp_strval(\gmp_and($a, $b));
    }

    /**
     * @deprecated
     */
    public function or_(/*string */$a, /*string */$b)/* : string*/
    {
        $a = backport_type_check('string', $a);

        $b = backport_type_check('string', $b);

        return \gmp_strval(\gmp_or($a, $b));
    }

    /**
     * @deprecated
     */
    public function xor_(/*string */$a, /*string */$b)/* : string*/
    {
        $a = backport_type_check('string', $a);

        $b = backport_type_check('string', $b);

        return \gmp_strval(\gmp_xor($a, $b));
    }

    public function sqrt(/*string */$n)/* : string*/
    {
        $n = backport_type_check('string', $n);

        return \gmp_strval(\gmp_sqrt($n));
    }
}
