<?php

namespace Illuminate\Tests\Database;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder as Query;
use Illuminate\Tests\Database\DynamicRelationModel2 as Related;
use PHPUnit\Framework\TestCase;

class DynamicRelationModel2_newQuery__class extends Query
        {
            public function __construct()
            {
                //
            }
        };

class DatabaseEloquentDynamicRelationsTest extends TestCase
{
    public function testBasicDynamicRelations()
    {
        DynamicRelationModel::resolveRelationUsing('dynamicRel_2', function () { return new FakeHasManyRel; });
        $model = new DynamicRelationModel;
        $this->assertEquals(['many' => 'related'], $model->dynamicRel_2);
        $this->assertEquals(['many' => 'related'], $model->getRelationValue('dynamicRel_2'));
    }

    public function testBasicDynamicRelationsOverride()
    {
        // Dynamic Relations can override each other.
        DynamicRelationModel::resolveRelationUsing('dynamicRelConflict', function ($m) { return $m->hasOne(Related::class); });
        DynamicRelationModel::resolveRelationUsing('dynamicRelConflict', function (DynamicRelationModel $m) { return new FakeHasManyRel; });

        $model = new DynamicRelationModel;
        $this->assertInstanceOf(HasMany::class, $model->dynamicRelConflict());
        $this->assertEquals(['many' => 'related'], $model->dynamicRelConflict);
        $this->assertEquals(['many' => 'related'], $model->getRelationValue('dynamicRelConflict'));
        $this->assertTrue($model->isRelation('dynamicRelConflict'));
    }

    public function testInharitedDynamicRelations()
    {
        DynamicRelationModel::resolveRelationUsing('inheritedDynamicRel', function () { return new FakeHasManyRel; });
        $model = new DynamicRelationModel;
        $model2 = new DynamicRelationModel2;
        $model4 = new DynamicRelationModel4;
        $this->assertTrue($model->isRelation('inheritedDynamicRel'));
        $this->assertTrue($model4->isRelation('inheritedDynamicRel'));
        $this->assertFalse($model2->isRelation('inheritedDynamicRel'));
        $this->assertEquals($model->inheritedDynamicRel(), $model4->inheritedDynamicRel());
        $this->assertEquals($model->inheritedDynamicRel, $model4->inheritedDynamicRel);
    }

    public function testInheritedDynamicRelationsOverride()
    {
        // Inherited Dynamic Relations can be overriden
        DynamicRelationModel::resolveRelationUsing('dynamicRelConflict', function ($m) { return $m->hasOne(Related::class); });
        $model = new DynamicRelationModel;
        $model4 = new DynamicRelationModel4;
        $this->assertInstanceOf(HasOne::class, $model->dynamicRelConflict());
        $this->assertInstanceOf(HasOne::class, $model4->dynamicRelConflict());
        DynamicRelationModel4::resolveRelationUsing('dynamicRelConflict', function ($m) { return $m->hasMany(Related::class); });
        $this->assertInstanceOf(HasOne::class, $model->dynamicRelConflict());
        $this->assertInstanceOf(HasMany::class, $model4->dynamicRelConflict());
    }

    public function testDynamicRelationsCanNotHaveTheSameNameAsNormalRelations()
    {
        $model = new DynamicRelationModel;

        // Dynamic relations can not override hard-coded methods.
        DynamicRelationModel::resolveRelationUsing('hardCodedRelation', function ($m) { return $m->hasOne(Related::class); });
        $this->assertInstanceOf(HasMany::class, $model->hardCodedRelation());
        $this->assertEquals(['many' => 'related'], $model->hardCodedRelation);
        $this->assertEquals(['many' => 'related'], $model->getRelationValue('hardCodedRelation'));
        $this->assertTrue($model->isRelation('hardCodedRelation'));
    }

    public function testRelationResolvers()
    {
        $model1 = new DynamicRelationModel;
        $model3 = new DynamicRelationModel3;

        // Same dynamic methods with the same name on two models do not conflict or override.
        DynamicRelationModel::resolveRelationUsing('dynamicRel', function ($m) { return $m->hasOne(Related::class); });
        DynamicRelationModel3::resolveRelationUsing('dynamicRel', function (DynamicRelationModel3 $m) { return $m->hasMany(Related::class); });
        $this->assertInstanceOf(HasOne::class, $model1->dynamicRel());
        $this->assertInstanceOf(HasMany::class, $model3->dynamicRel());
        $this->assertTrue($model1->isRelation('dynamicRel'));
        $this->assertTrue($model3->isRelation('dynamicRel'));
    }
}

class DynamicRelationModel extends Model
{
    public function hardCodedRelation()
    {
        return new FakeHasManyRel();
    }
}

class DynamicRelationModel2 extends Model
{
    public function getResults()
    {
        //
    }

    public function newQuery()
    {
        $query = new DynamicRelationModel2_newQuery__class;

        return new Builder($query);
    }
}

class DynamicRelationModel3 extends Model
{
    //
}

class DynamicRelationModel4 extends DynamicRelationModel
{
    //
}

class FakeHasManyRel extends HasMany
{
    public function __construct()
    {
        //
    }

    public function getResults()
    {
        return ['many' => 'related'];
    }
}
