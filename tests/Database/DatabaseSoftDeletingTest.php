<?php

namespace Illuminate\Tests\Database;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class SoftDeletingModel extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $dateFormat = 'Y-m-d H:i:s';
}

class DatabaseSoftDeletingTest_testDeletedAtIsUniqueWhenAlreadyExists_Class extends SoftDeletingModel
{
    protected $dates = ['deleted_at'];
}

class DatabaseSoftDeletingTest_testExistingCastOverridesAddedDateCast_Class extends SoftDeletingModel
{
    protected $casts = ['deleted_at' => 'bool'];
}

class DatabaseSoftDeletingTest_testExistingMutatorOverridesAddedDateCast_Class extends SoftDeletingModel
{
    protected function getDeletedAtAttribute()
    {
        return 'expected';
    }
}

class DatabaseSoftDeletingTest_testCastingToStringOverridesAutomaticDateCastingToRetainPreviousBehaviour_Class extends SoftDeletingModel
{
    protected $casts = ['deleted_at' => 'string'];
}

class DatabaseSoftDeletingTest extends TestCase
{
    public function testDeletedAtIsAddedToDateCasts()
    {
        $model = new SoftDeletingModel;

        $this->assertContains('deleted_at', $model->getDates());
    }

    public function testDeletedAtIsUniqueWhenAlreadyExists()
    {
        $model = new DatabaseSoftDeletingTest_testDeletedAtIsUniqueWhenAlreadyExists_Class;
        $entries = array_filter($model->getDates(), function ($attribute) {
            return $attribute === 'deleted_at';
        });

        $this->assertCount(1, $entries);
    }

    public function testDeletedAtIsCastToCarbonInstance()
    {
        Carbon::setTestNow(Carbon::now());
        $expected = Carbon::createFromFormat('Y-m-d H:i:s', '2018-12-29 13:59:39');
        $model = new SoftDeletingModel(['deleted_at' => $expected->format('Y-m-d H:i:s')]);

        $this->assertInstanceOf(Carbon::class, $model->deleted_at);
        $this->assertTrue($expected->eq($model->deleted_at));
    }

    public function testExistingCastOverridesAddedDateCast()
    {
        $model = new DatabaseSoftDeletingTest_testExistingCastOverridesAddedDateCast_Class(['deleted_at' => '2018-12-29 13:59:39']);

        $this->assertTrue($model->deleted_at);
    }

    public function testExistingMutatorOverridesAddedDateCast()
    {
        $model = new DatabaseSoftDeletingTest_testExistingMutatorOverridesAddedDateCast_Class(['deleted_at' => '2018-12-29 13:59:39']);

        $this->assertSame('expected', $model->deleted_at);
    }

    public function testCastingToStringOverridesAutomaticDateCastingToRetainPreviousBehaviour()
    {
        $model = new DatabaseSoftDeletingTest_testCastingToStringOverridesAutomaticDateCastingToRetainPreviousBehaviour_Class(['deleted_at' => '2018-12-29 13:59:39']);

        $this->assertSame('2018-12-29 13:59:39', $model->deleted_at);
    }
}
