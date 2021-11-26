<?php

namespace Laravel\Telescope\Storage;

use Illuminate\Http\Request;

class EntryQueryOptions
{
    /**
     * The batch ID that entries should belong to.
     *
     * @var string
     */
    public $batchId;

    /**
     * The tag that must belong to retrieved entries.
     *
     * @var string
     */
    public $tag;

    /**
     * The family hash that must belong to retrieved entries.
     *
     * @var string
     */
    public $familyHash;

    /**
     * The ID that all retrieved entries should be less than.
     *
     * @var mixed
     */
    public $beforeSequence;

    /**
     * The list of UUIDs of entries tor retrieve.
     *
     * @var mixed
     */
    public $uuids;

    /**
     * The number of entries to retrieve.
     *
     * @var int
     */
    public $limit = 50;

    /**
     * Create new entry query options from the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return static
     */
    public static function fromRequest(Request $request)
    {
        return (new static)
                ->batchId($request->batch_id)
                ->uuids($request->uuids)
                ->beforeSequence($request->before)
                ->tag($request->tag)
                ->familyHash($request->family_hash)
                ->limit(isset($request->take) ? $request->take : 50);
    }

    /**
     * Create new entry query options for the given batch ID.
     *
     * @param  string  $batchId
     * @return static
     */
    public static function forBatchId(/*?string */$batchId = null)
    {
        $batchId = cast_to_string($batchId, null);

        return (new static)->batchId($batchId);
    }

    /**
     * Set the batch ID for the query.
     *
     * @param  string  $batchId
     * @return $this
     */
    public function batchId(/*?string */$batchId = null)
    {
        $batchId = cast_to_string($batchId, null);

        $this->batchId = $batchId;

        return $this;
    }

    /**
     * Set the list of UUIDs of entries tor retrieve.
     *
     * @param  array  $uuids
     * @return $this
     */
    public function uuids(/*?*/array $uuids = null)
    {
        $this->uuids = $uuids;

        return $this;
    }

    /**
     * Set the ID that all retrieved entries should be less than.
     *
     * @param  mixed  $id
     * @return $this
     */
    public function beforeSequence($id)
    {
        $this->beforeSequence = $id;

        return $this;
    }

    /**
     * Set the tag that must belong to retrieved entries.
     *
     * @param  string  $tag
     * @return $this
     */
    public function tag(/*?string */$tag = null)
    {
        $tag = cast_to_string($tag, null);

        $this->tag = $tag;

        return $this;
    }

    /**
     * Set the family hash that must belong to retrieved entries.
     *
     * @param  string  $familyHash
     * @return $this
     */
    public function familyHash(/*?string */$familyHash = null)
    {
        $familyHash = cast_to_string($familyHash, null);

        $this->familyHash = $familyHash;

        return $this;
    }

    /**
     * Set the number of entries that should be retrieved.
     *
     * @param  int  $limit
     * @return $this
     */
    public function limit(/*int */$limit)
    {
        $limit = cast_to_int($limit);

        $this->limit = $limit;

        return $this;
    }
}
