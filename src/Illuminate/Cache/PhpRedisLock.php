<?php

namespace Illuminate\Cache;

use Illuminate\Redis\Connections\PhpRedisConnection;

class PhpRedisLock extends RedisLock
{
    /**
     * Create a new phpredis lock instance.
     *
     * @param  \Illuminate\Redis\Connections\PhpRedisConnection  $redis
     * @param  string  $name
     * @param  int  $seconds
     * @param  string|null  $owner
     * @return void
     */
    public function __construct(PhpRedisConnection $redis, /*string */$name, /*int */$seconds, /*?string */$owner = null)
    {
        $name = cast_to_string($name);
        $seconds = cast_to_int($seconds);
        $owner = cast_to_string($owner, null);

        parent::__construct($redis, $name, $seconds, $owner);
    }

    /**
     * {@inheritDoc}
     */
    public function release()
    {
        return (bool) $this->redis->eval_(
            LuaScripts::releaseLock(),
            1,
            $this->name,
            ...$this->redis->pack([$this->owner])
        );
    }
}
