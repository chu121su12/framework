<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Mailer\Header;

use Symfony\Component\Mime\Header\UnstructuredHeader;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class MetadataHeader extends UnstructuredHeader
{
    private $key;

    public function __construct(/*string */$key, /*string */$value)
    {
        $value = backport_type_check('string', $value);

        $key = backport_type_check('string', $key);

        $this->key = $key;

        parent::__construct('X-Metadata-'.$key, $value);
    }

    public function getKey()/*: string*/
    {
        return $this->key;
    }
}
