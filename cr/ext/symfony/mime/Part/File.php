<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Mime\Part;

use Symfony\Component\Mime\MimeTypes;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
class File
{
    private static /*MimeTypes */$mimeTypes;
    private /*string */$path;
    private /*?string */$filename;

    public function __construct(
        /*private *//*string */$path,
        /*private *//*?string */$filename = null
    ) {
        $this->path = backport_type_check('string', $path);
        $this->filename = backport_type_check('?string', $filename);
    }

    public function getPath()/*: string*/
    {
        return $this->path;
    }

    public function getContentType()/*: string*/
    {
        $ext = strtolower(pathinfo($this->path, \PATHINFO_EXTENSION));
        if (! isset(self::$mimeTypes)) {
            self::$mimeTypes = new MimeTypes();
        }

        $mimeTypes = self::$mimeTypes->getMimeTypes($ext);
        return isset($mimeTypes[0]) ? $mimeTypes[0] : 'application/octet-stream';
    }

    public function getSize()/*: int*/
    {
        return filesize($this->path);
    }

    public function getFilename()/*: string*/
    {
        if (! isset($this->filename)) {
            return $this->filename = basename($this->getPath());
        }

        return $this->filename;
    }
}
