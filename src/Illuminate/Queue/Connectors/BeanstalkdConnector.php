<?php

namespace Illuminate\Queue\Connectors;

use Pheanstalk\Connection;
use Pheanstalk\Pheanstalk;
use Pheanstalk\PheanstalkInterface;
use Illuminate\Queue\BeanstalkdQueue;

class BeanstalkdConnector implements ConnectorInterface
{
    /**
     * Establish a queue connection.
     *
     * @param  array  $config
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        $retryAfter = isset($config['retry_after']) ? $config['retry_after'] : Pheanstalk::DEFAULT_TTR;

        return new BeanstalkdQueue($this->pheanstalk($config), $config['queue'], $retryAfter);
    }

    /**
     * Create a Pheanstalk instance.
     *
     * @param  array  $config
     * @return \Pheanstalk\Pheanstalk
     */
    protected function pheanstalk(array $config)
    {
        return new Pheanstalk(
            $config['host'],
            isset($config['port']) ? $config['port'] : PheanstalkInterface::DEFAULT_PORT,
            isset($config['timeout']) ? $config['timeout'] : Connection::DEFAULT_CONNECT_TIMEOUT,
            isset($config['persistent']) ? $config['persistent'] : false
        );
    }
}
