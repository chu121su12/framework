<?php

namespace Facade\FlareClient\Concerns;

trait HasContext
{
    /** @var string|null */
    private $messageLevel;

    /** @var string|null */
    private $stage;

    /** @var array */
    private $userProvidedContext = [];

    public function stage(/*?string */$stage)
    {
        $stage = cast_to_string($stage, null);

        $this->stage = $stage;

        return $this;
    }

    public function messageLevel(/*?string */$messageLevel)
    {
        $messageLevel = cast_to_string($messageLevel, null);

        $this->messageLevel = $messageLevel;

        return $this;
    }

    public function getGroup(/*string */$groupName = 'context', $default = [])/*: array*/
    {
        $groupName = cast_to_string($groupName);

        return isset($this->userProvidedContext[$groupName]) ? $this->userProvidedContext[$groupName] : $default;
    }

    public function context($key, $value)
    {
        return $this->group('context', [$key => $value]);
    }

    public function group(/*string */$groupName, array $properties)
    {
        $groupName = cast_to_string($groupName);

        $group = isset($this->userProvidedContext[$groupName]) ? $this->userProvidedContext[$groupName] : [];

        $this->userProvidedContext[$groupName] = array_merge_recursive_distinct(
            $group,
            $properties
        );

        return $this;
    }
}
