<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Mime\Crypto;

/**
 * A helper providing autocompletion for available DkimSigner options.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
final class DkimOptions
{
    private $options = [];

    public function toArray() //// array
    {
        return $this->options;
    }

    /**
     * @return $this
     */
    public function algorithm(/*string */$algo) /// self
    {
        $algo = backport_type_check('string', $algo);

        $this->options['algorithm'] = $algo;

        return $this;
    }

    /**
     * @return $this
     */
    public function signatureExpirationDelay($show) /// self
    {
        $show = backport_type_check('int', $show);

        $this->options['signature_expiration_delay'] = $show;

        return $this;
    }

    /**
     * @return $this
     */
    public function bodyMaxLength($max) /// self
    {
        $max = backport_type_check('int', $max);

        $this->options['body_max_length'] = $max;

        return $this;
    }

    /**
     * @return $this
     */
    public function bodyShowLength($show) /// self
    {
        $show = backport_type_check('bool', $show);

        $this->options['body_show_length'] = $show;

        return $this;
    }

    /**
     * @return $this
     */
    public function headerCanon($canon) /// self
    {
        $canon = backport_type_check('string', $canon);

        $this->options['header_canon'] = $canon;

        return $this;
    }

    /**
     * @return $this
     */
    public function bodyCanon($canon) /// self
    {
        $canon = backport_type_check('string', $canon);

        $this->options['body_canon'] = $canon;

        return $this;
    }

    /**
     * @return $this
     */
    public function headersToIgnore(array $headers) /// self
    {
        $this->options['headers_to_ignore'] = $headers;

        return $this;
    }
}
