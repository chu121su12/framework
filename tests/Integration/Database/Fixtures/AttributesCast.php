<?php

namespace Illuminate\Tests\Integration\Database;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

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
        return Attribute::make(
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

    public function virtual(): Attribute
    {
        return new Attribute(
            function () {
                return collect();
            }
        );
    }

    public function virtualString(): Attribute
    {
        return new Attribute(
            function () {
                return Str::random(10);
            }
        );
    }

    public function virtualObject(): Attribute
    {
        return new Attribute(
            function () {
                return new AttributeCastAddress(Str::random(10), Str::random(10));
            }
        );
    }

    public function virtualDateTime(): Attribute
    {
        return new Attribute(
            function () {
                return Date::now()->addSeconds(mt_rand(0, 10000));
            }
        );
    }

    public function virtualObjectWithoutCachingFluent(): Attribute
    {
        return (new Attribute(
            function () {
                return new AttributeCastAddress(Str::random(10), Str::random(10));
            }
        ))->withoutObjectCaching();
    }

    public function virtualDateTimeWithoutCachingFluent(): Attribute
    {
        return (new Attribute(
            function () {
                return Date::now()->addSeconds(mt_rand(0, 10000));
            }
        ))->withoutObjectCaching();
    }

    public function virtualObjectWithoutCaching(): Attribute
    {
        return Attribute::get(function () {
            return new AttributeCastAddress(Str::random(10), Str::random(10));
        })->withoutObjectCaching();
    }

    public function virtualDateTimeWithoutCaching(): Attribute
    {
        return Attribute::get(function () {
            return Date::now()->addSeconds(mt_rand(0, 10000));
        })->withoutObjectCaching();
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
