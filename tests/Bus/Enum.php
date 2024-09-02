<?php

namespace Illuminate\Tests\Bus;

enum ConnectionEnum: string
{
    case SQS = 'sqs';
    case REDIS = 'redis';
}

enum QueueEnum: string
{
    case HIGH = 'high';
    case DEFAULT_ = 'default';
}
