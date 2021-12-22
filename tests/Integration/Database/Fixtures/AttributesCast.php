<?php

namespace Illuminate\Tests\Integration\Database;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class TestEloquentModelWithAttributeCast extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var string[]
     */
    protected $guarded = [];

    public function uppercase(): Attribute
    {
        return new Attribute(
            function ($value) {
                return strtoupper($value);
            },
            function ($value) {
                return strtoupper($value);
            }
        );
    }

    public function address(): Attribute
    {
        return new Attribute(
            function ($value, $attributes) {
                if (is_null($attributes['address_line_one'])) {
                    return;
                }

                return new AttributeCastAddress($attributes['address_line_one'], $attributes['address_line_two']);
            },
            function ($value) {
                if (is_null($value)) {
                    return [
                        'address_line_one' => null,
                        'address_line_two' => null,
                    ];
                }

                return ['address_line_one' => $value->lineOne, 'address_line_two' => $value->lineTwo];
            }
        );
    }

    public function options(): Attribute
    {
        return new Attribute(
            function ($value) {
                return backport_json_decode($value, true);
            },
            function ($value) {
                return json_encode($value);
            }
        );
    }

    public function birthdayAt(): Attribute
    {
        return new Attribute(
            function ($value) {
                return Carbon::parse($value);
            },
            function ($value) {
                return $value->format('Y-m-d');
            }
        );
    }

    public function password(): Attribute
    {
        return new Attribute(null, function ($value) {
            return hash('sha256', $value);
        });
    }
}

class AttributeCastAddress
{
    public $lineOne;
    public $lineTwo;

    public function __construct($lineOne, $lineTwo)
    {
        $this->lineOne = $lineOne;
        $this->lineTwo = $lineTwo;
    }
}
