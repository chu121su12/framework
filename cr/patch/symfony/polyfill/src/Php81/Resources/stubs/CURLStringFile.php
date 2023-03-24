<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (\PHP_VERSION_ID >= 70400 && extension_loaded('curl')) {
    /**
     * @property string $data
     */
    class CURLStringFile extends CURLFile
    {
        private $data;

        public function __construct(/*string */$data, /*string */$postname, /*string */$mime = 'application/octet-stream')
        {
            $data = backport_type_check('string', $data);

            $postname = backport_type_check('string', $postname);

            $mime = backport_type_check('string', $mime);

            $this->data = $data;
            parent::__construct('data://application/octet-stream;base64,'.base64_encode($data), $mime, $postname);
        }

        public function __set(/*string */$name, $value)/*: void*/
        {
            $name = backport_type_check('string', $name);

            if ('data' !== $name) {
                $this->$name = $value;

                return;
            }

            if (is_object($value) ? !method_exists($value, '__toString') : !is_scalar($value)) {
                throw new \TypeError('Cannot assign '.gettype($value).' to property CURLStringFile::$data of type string');
            }

            $this->name = 'data://application/octet-stream;base64,'.base64_encode($value);
        }

        public function __isset(/*string */$name)/*: bool*/
        {
            $name = backport_type_check('string', $name);

            return isset($this->$name);
        }

        public function &__get(/*string */$name)
        {
            $name = backport_type_check('string', $name);

            return $this->$name;
        }
    }
}
