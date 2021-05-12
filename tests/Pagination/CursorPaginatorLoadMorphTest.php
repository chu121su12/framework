<?php

namespace Illuminate\Tests\Pagination;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\AbstractCursorPaginator;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class CursorPaginatorLoadMorphTest_testCollectionLoadMorphCanChainOnThePaginator_class extends AbstractCursorPaginator
        {
            //
        }

class CursorPaginatorLoadMorphTest extends TestCase
{
    public function testCollectionLoadMorphCanChainOnThePaginator()
    {
        $relations = [
            'App\\User' => 'photos',
            'App\\Company' => ['employees', 'calendars'],
        ];

        $items = m::mock(Collection::class);
        $items->shouldReceive('loadMorph')->once()->with('parentable', $relations);

        $p = (new CursorPaginatorLoadMorphTest_testCollectionLoadMorphCanChainOnThePaginator_class)->setCollection($items);

        $this->assertSame($p, $p->loadMorph('parentable', $relations));
    }
}
