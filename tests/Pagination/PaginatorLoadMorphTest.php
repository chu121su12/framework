<?php

namespace Illuminate\Tests\Pagination;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\AbstractPaginator;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class PaginatorLoadMorphTest_testCollectionLoadMorphCanChainOnThePaginator_class extends AbstractPaginator 
        {
            //
        }

class PaginatorLoadMorphTest extends TestCase
{
    public function testCollectionLoadMorphCanChainOnThePaginator()
    {
        $relations = [
            'App\\User' => 'photos',
            'App\\Company' => ['employees', 'calendars'],
        ];

        $items = m::mock(Collection::class);
        $items->shouldReceive('loadMorph')->once()->with('parentable', $relations);

        $p = (new PaginatorLoadMorphTest_testCollectionLoadMorphCanChainOnThePaginator_class)
            ->setCollection($items);

        $this->assertSame($p, $p->loadMorph('parentable', $relations));
    }
}
