<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\String\Slugger;

use Symfony\Component\String\AbstractUnicodeString;
use Symfony\Component\String\UnicodeString;
use Symfony\Contracts\Translation\LocaleAwareInterface;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 *
 * @experimental in 5.0
 */
class AsciiSlugger implements SluggerInterface, LocaleAwareInterface
{
    const LOCALE_TO_TRANSLITERATOR_ID = [
        'am' => 'Amharic-Latin',
        'ar' => 'Arabic-Latin',
        'az' => 'Azerbaijani-Latin',
        'be' => 'Belarusian-Latin',
        'bg' => 'Bulgarian-Latin',
        'bn' => 'Bengali-Latin',
        'de' => 'de-ASCII',
        'el' => 'Greek-Latin',
        'fa' => 'Persian-Latin',
        'he' => 'Hebrew-Latin',
        'hy' => 'Armenian-Latin',
        'ka' => 'Georgian-Latin',
        'kk' => 'Kazakh-Latin',
        'ky' => 'Kirghiz-Latin',
        'ko' => 'Korean-Latin',
        'mk' => 'Macedonian-Latin',
        'mn' => 'Mongolian-Latin',
        'or' => 'Oriya-Latin',
        'ps' => 'Pashto-Latin',
        'ru' => 'Russian-Latin',
        'sr' => 'Serbian-Latin',
        'sr_Cyrl' => 'Serbian-Latin',
        'th' => 'Thai-Latin',
        'tk' => 'Turkmen-Latin',
        'uk' => 'Ukrainian-Latin',
        'uz' => 'Uzbek-Latin',
        'zh' => 'Han-Latin',
    ];

    private $defaultLocale;

    /**
     * Cache of transliterators per locale.
     *
     * @var \Transliterator[]
     */
    private $transliterators = [];

    public function __construct($defaultLocale = null)
    {
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale)
    {
        $this->defaultLocale = $locale;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->defaultLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function slug($string, $separator = '-', $locale = null)
    {
        $locale = isset($locale) ? $locale : $this->defaultLocale;

        $transliterator = [];
        if ('de' === $locale || 0 === strpos($locale, 'de_')) {
            // Use the shortcut for German in UnicodeString::ascii() if possible (faster and no requirement on intl)
            $transliterator = ['de-ASCII'];
        } elseif (\function_exists('transliterator_transliterate') && $locale) {
            $transliterator = (array) $this->createTransliterator($locale);
        }

        return (new UnicodeString($string))
            ->ascii($transliterator)
            ->replace('@', $separator.'at'.$separator)
            ->replaceMatches('/[^A-Za-z0-9]++/', $separator)
            ->trim($separator)
        ;
    }

    private function createTransliterator($locale)
    {
        if (\array_key_exists($locale, $this->transliterators)) {
            return $this->transliterators[$locale];
        }

        $localeToTransliteratorId = self::LOCALE_TO_TRANSLITERATOR_ID;

        // Exact locale supported, cache and return
        if ($id = isset($localeToTransliteratorId[$locale]) ? $localeToTransliteratorId[$locale] : null) {
            $bgnTransliterator = \Transliterator::create($id.'/BGN');
            return $this->transliterators[$locale] = isset($bgnTransliterator) ? $bgnTransliterator : \Transliterator::create($id);
        }

        // Locale not supported and no parent, fallback to any-latin
        if (false === $str = strrchr($locale, '_')) {
            return $this->transliterators[$locale] = null;
        }

        // Try to use the parent locale (ie. try "de" for "de_AT") and cache both locales
        $parent = substr($locale, 0, -\strlen($str));

        if ($id = isset($localeToTransliteratorId[$parent]) ? $localeToTransliteratorId[$parent] : null) {
            $bgnTransliterator = \Transliterator::create($id.'/BGN');
            $transliterator = isset($bgnTransliterator) ? $bgnTransliterator : \Transliterator::create($id);
        }

        return $this->transliterators[$locale] = $this->transliterators[$parent] = isset($transliterator) ? $transliterator : null;
    }
}
