<?php

namespace Illuminate\Tests\Http;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;
use PHPUnit\Framework\TestCase;

class JsonResourceTest_testJsonResourceNullAttributes_class extends Model {};

class JsonResourceTest extends TestCase
{
    public function testJsonResourceNullAttributes()
    {
        $model = new JsonResourceTest_testJsonResourceNullAttributes_class;

        $model->setAttribute('relation_sum_column', null);
        $model->setAttribute('relation_count', null);

        $resource = new JsonResource($model);

        $this->assertNotInstanceOf(MissingValue::class, $resource->whenAggregated('relation', 'column', 'sum'));
        $this->assertNotInstanceOf(MissingValue::class, $resource->whenCounted('relation'));

        $this->assertNull($resource->whenAggregated('relation', 'column', 'sum'));
        $this->assertNull($resource->whenCounted('relation'));
    }
}
