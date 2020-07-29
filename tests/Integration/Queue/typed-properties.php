<?php

namespace Illuminate\Tests\Integration\Queue;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Queue\SerializesModels;

class TypedPropertyTestClass
{
    use SerializesModels;

    public $user;

    public $unitializedUser;

    protected $id;

    private $names;

    public function __construct(ModelSerializationTestUser $user, $id, array $names)
    {
        $this->user = $user;
        $this->id = $id;
        $this->names = $names;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getNames()
    {
        return $this->names;
    }
}

class TypedPropertyCollectionTestClass
{
    use SerializesModels;

    public $users;

    public function __construct(Collection $users)
    {
        $this->users = $users;
    }
}
