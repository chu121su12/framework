<?php

namespace Illuminate\Queue\Connectors;

use Illuminate\Queue\BeanstalkdQueue;
use Pheanstalk\Connection;
use Pheanstalk\Pheanstalk;

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
        return new BeanstalkdQueue(
            $this->pheanstalk($config),
            $config['queue'],
            isset($config['retry_after']) ? $config['retry_after'] : Pheanstalk::DEFAULT_TTR,
            isset($config['block_for']) ? $config['block_for'] : 0,
            isset($config['after_commit']) ? $config['after_commit'] : null
        );
    }

    /**
     * Create a Pheanstalk instance.
     *
     * @param  array  $config
     * @return \Pheanstalk\Pheanstalk
     */
    protected function pheanstalk(array $config)
    {
        return Pheanstalk::create(
            $config['host'],
            isset($config['port']) ? $config['port'] : Pheanstalk::DEFAULT_PORT,
            isset($config['timeout']) ? $config['timeout'] : Connection::DEFAULT_CONNECT_TIMEOUT
        );
    }
}
