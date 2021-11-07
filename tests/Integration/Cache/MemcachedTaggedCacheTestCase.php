<?php

namespace Illuminate\Tests\Integration\Cache;

use Illuminate\Support\Facades\Cache;

/**
 * @requires extension memcached
 */
class MemcachedTaggedCacheTestCase extends MemcachedIntegrationTestCase
{
    public function testMemcachedCanStoreAndRetrieveTaggedCacheItems()
    {
        $this->markTestSkipped('The test may fail under full integration test. Test by filtering this method by itself.');

        $store = Cache::store('memcached');

        $store->tags(['people', 'artists'])->put('John', 'foo', 2);
        $store->tags(['people', 'authors'])->put('Anne', 'bar', 2);

        $this->assertSame('foo', $store->tags(['people', 'artists'])->get('John'));
        $this->assertSame('bar', $store->tags(['people', 'authors'])->get('Anne'));

        $store->tags(['people', 'artists'])->put('John', 'baz');
        $store->tags(['people', 'authors'])->put('Anne', 'qux');

        $this->assertSame('baz', $store->tags(['people', 'artists'])->get('John'));
        $this->assertSame('qux', $store->tags(['people', 'authors'])->get('Anne'));

        $store->tags('authors')->flush();
        $this->assertNull($store->tags(['people', 'authors'])->get('Anne'));

        $store->tags(['people', 'authors'])->flush();
        $this->assertNull($store->tags(['people', 'artists'])->get('John'));
    }

    public function testMemcachedCanStoreManyTaggedCacheItems()
    {
        $this->markTestSkipped('The test may fail under full integration test. Test by filtering this method by itself.');

        $store = Cache::store('memcached');

        $store->tags(['people', 'artists'])->putMany(['John' => 'foo', 'Jane' => 'bar'], 2);

        $this->assertSame('foo', $store->tags(['people', 'artists'])->get('John'));
        $this->assertSame('bar', $store->tags(['people', 'artists'])->get('Jane'));

        $store->tags(['people', 'artists'])->putMany(['John' => 'baz', 'Jane' => 'qux']);

        $this->assertSame('baz', $store->tags(['people', 'artists'])->get('John'));
        $this->assertSame('qux', $store->tags(['people', 'artists'])->get('Jane'));

        $store->tags(['people', 'artists'])->putMany(['John' => 'baz', 'Jane' => 'qux'], -1);

        $this->assertNull($store->tags(['people', 'artists'])->get('John'));
        $this->assertNull($store->tags(['people', 'artists'])->get('Jane'));
    }
}
