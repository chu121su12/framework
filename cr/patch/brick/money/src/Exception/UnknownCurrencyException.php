<?php

////declare(strict_types=1);

namespace Brick\Money\Exception;

/**
 * Exception thrown when attempting to create a Currency from an unknown currency code.
 */
class UnknownCurrencyException extends MoneyException
{
    /**
     * @param string|int $currencyCode
     *
     * @return UnknownCurrencyException
     */
    public static function unknownCurrency($currencyCode)//// : self
    {
        return new self('Unknown currency code: ' . $currencyCode);
    }

    /**
     * @param string $countryCode
     *
     * @return UnknownCurrencyException
     */
    public static function noCurrencyForCountry(string $countryCode)//// : self
    {
        $countryCode = cast_to_string($countryCode);

        return new self('No currency found for country ' . $countryCode);
    }

    /**
     * @param string $countryCode
     * @param array  $currencyCodes
     *
     * @return UnknownCurrencyException
     */
    public static function noSingleCurrencyForCountry(string $countryCode, array $currencyCodes)//// : self
    {
        $countryCode = cast_to_string($countryCode);

        $currencyCodes = cast_to_string($currencyCodes);

        return new self('No single currency for country ' . $countryCode . ': ' . implode(', ', $currencyCodes));
    }
}
