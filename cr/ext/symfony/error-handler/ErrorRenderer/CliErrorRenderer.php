<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\ErrorHandler\ErrorRenderer;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;

// Help opcache.preload discover always-needed symbols
class_exists(CliDumper::class);

class CliErrorRenderer_render_class extends CliDumper {
            protected function supportsColors()/*: bool*/
            {
                $outputStream = $this->outputStream;
                $this->outputStream = fopen('php://stdout', 'w');

                try {
                    return parent::supportsColors();
                } finally {
                    $this->outputStream = $outputStream;
                }
            }
        }

/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
class CliErrorRenderer implements ErrorRendererInterface
{
    /**
     * {@inheritdoc}
     */
    public function render(/*\Throwable */$exception)/*: FlattenException*/
    {
        backport_type_throwable($exception);

        $cloner = new VarCloner();
        $dumper = new CliErrorRenderer_render_class;

        return FlattenException::createFromThrowable($exception)
            ->setAsString($dumper->dump($cloner->cloneVar($exception), true));
    }
}
