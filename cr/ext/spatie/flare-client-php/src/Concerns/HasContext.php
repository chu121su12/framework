<?php

namespace Spatie\FlareClient\Concerns;

trait HasContext
{
    protected /*?string */$messageLevel = null;

    protected /*?string */$stage = null;

    /**
     * @var array<string, mixed>
     */
    protected /*array */$userProvidedContext = [];

    public function stage(/*?string */$stage = null)/*: self*/
    {
        $stage = backport_type_check('?string', $stage);

        $this->stage = $stage;

        return $this;
    }

    public function messageLevel(/*?string */$messageLevel = null)/*: self*/
    {
        $messageLevel = backport_type_check('?string', $messageLevel);

        $this->messageLevel = $messageLevel;

        return $this;
    }

    /**
     * @param string $groupName
     * @param mixed $default
     *
     * @return array<int, mixed>
     */
    public function getGroup(/*string */$groupName = 'context', $default = [])/*: array*/
    {
        $groupName = backport_type_check('string', $groupName);

        return isset($this->userProvidedContext[$groupName]) ? $this->userProvidedContext[$groupName] : $default;
    }

    public function context(/*string */$key, /*mixed */$value)/*: self*/
    {
        $value = backport_type_check('mixed', $value);

        $key = backport_type_check('string', $key);

        return $this->group('context', [$key => $value]);
    }

    /**
     * @param string $groupName
     * @param array<string, mixed> $properties
     *
     * @return $this
     */
    public function group(/*string */$groupName, array $properties)/*: self*/
    {
        $groupName = backport_type_check('string', $groupName);

        $group = isset($this->userProvidedContext[$groupName]) ? $this->userProvidedContext[$groupName] : [];

        $this->userProvidedContext[$groupName] = array_merge_recursive_distinct(
            $group,
            $properties
        );

        return $this;
    }
}
