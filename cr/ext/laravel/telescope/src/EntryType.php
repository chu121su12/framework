<?php

namespace Laravel\Telescope;

class EntryType
{
    const BATCH = 'batch';
    const CACHE = 'cache';
    const COMMAND = 'command';
    const DUMP = 'dump';
    const EVENT = 'event';
    const EXCEPTION = 'exception';
    const JOB = 'job';
    const LOG = 'log';
    const MAIL = 'mail';
    const MODEL = 'model';
    const NOTIFICATION = 'notification';
    const QUERY = 'query';
    const REDIS = 'redis';
    const REQUEST = 'request';
    const SCHEDULED_TASK = 'schedule';
    const GATE = 'gate';
    const VIEW = 'view';
}
