<?php

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class EloquentRelationAndAtrributeModelStub extends Model
{
    protected $table = 'one_more_table';

    public function field(): Attribute
    {
        return new Attribute(
            function ($value) {
                return $value;
            },
            function ($value) {
                return $value;
            }
        );
    }

    public function parent()
    {
        return $this->belongsTo(self::class);
    }
}
