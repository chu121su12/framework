<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Mailer\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\Mailer\Event\MessageEvents;
use Symfony\Component\Mailer\EventListener\MessageLoggerListener;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
final class MessageDataCollector extends DataCollector
{
    private $events;

    public function __construct(MessageLoggerListener $logger)
    {
        $this->events = $logger->getEvents();
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, /*?\Throwable */$exception = null)
    {
        backport_type_throwable($exception);

        $this->data['events'] = $this->events;
    }

    public function getEvents()/*: MessageEvents*/
    {
        return $this->data['events'];
    }

    /**
     * @internal
     */
    public function base64Encode(/*string */$data)/*: string*/
    {
        $data = backport_type_check('string', $data);

        return base64_encode($data);
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->data = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()/*: string*/
    {
        return 'mailer';
    }
}
