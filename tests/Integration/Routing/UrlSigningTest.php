<?php

namespace Illuminate\Tests\Integration\Routing;

use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Orchestra\Testbench\TestCase;

/**
 * @group integration
 */
class UrlSigningTest extends TestCase
{
    public function testSigningUrl()
    {
        Route::get('/foo/{id}', function (Request $request, $id) {
            return $request->hasValidSignature() ? 'valid' : 'invalid';
        })->name('foo');

        $this->assertIsString($url = URL::signedRoute('foo', ['id' => 1]));
        $this->assertSame('valid', $this->get($url)->original);
    }

    public function testTemporarySignedUrls()
    {
        Route::get('/foo/{id}', function (Request $request, $id) {
            return $request->hasValidSignature() ? 'valid' : 'invalid';
        })->name('foo');

        Carbon::setTestNow(Carbon::create(2018, 1, 1));
        $this->assertIsString($url = URL::temporarySignedRoute('foo', now()->addMinutes(5), ['id' => 1]));
        $this->assertSame('valid', $this->get($url)->original);

        Carbon::setTestNow(Carbon::create(2018, 1, 1)->addMinutes(10));
        $this->assertSame('invalid', $this->get($url)->original);
    }

    public function testSignedUrlWithUrlWithoutSignatureParameter()
    {
        Route::get('/foo/{id}', function (Request $request, $id) {
            return $request->hasValidSignature() ? 'valid' : 'invalid';
        })->name('foo');

        $this->assertSame('invalid', $this->get('/foo/1')->original);
    }

    public function testSignedMiddleware()
    {
        Route::get('/foo/{id}', function (Request $request, $id) {
            return $request->hasValidSignature() ? 'valid' : 'invalid';
        })->name('foo')->middleware(ValidateSignature::class);

        Carbon::setTestNow(Carbon::create(2018, 1, 1));
        $this->assertIsString($url = URL::temporarySignedRoute('foo', now()->addMinutes(5), ['id' => 1]));
        $this->assertSame('valid', $this->get($url)->original);
    }

    public function testSignedMiddlewareWithInvalidUrl()
    {
        Route::get('/foo/{id}', function (Request $request, $id) {
            return $request->hasValidSignature() ? 'valid' : 'invalid';
        })->name('foo')->middleware(ValidateSignature::class);

        Carbon::setTestNow(Carbon::create(2018, 1, 1));
        $this->assertIsString($url = URL::temporarySignedRoute('foo', now()->addMinutes(5), ['id' => 1]));
        Carbon::setTestNow(Carbon::create(2018, 1, 1)->addMinutes(10));

        $response = $this->get($url);
        $response->assertStatus(403);
    }

    public function testSignedMiddlewareWithRoutableParameter()
    {
        $model = new RoutableInterfaceStub;
        $model->routable = 'routable';

        Route::get('/foo/{bar}', function (Request $request, $routable) {
            return $request->hasValidSignature() ? $routable : 'invalid';
        })->name('foo');

        $this->assertIsString($url = URL::signedRoute('foo', $model));
        $this->assertSame('routable', $this->get($url)->original);
    }

    public function testSignedMiddlewareWithRelativePath()
    {
        Route::get('/foo/relative', function (Request $request) {
            return $request->hasValidSignature($absolute = false) ? 'valid' : 'invalid';
        })->name('foo')->middleware('signed:relative');

        $this->assertIsString($url = 'https://fake.test'.URL::signedRoute('foo', [], null, $absolute = false));
        $this->assertSame('valid', $this->get($url)->original);

        $response = $this->get('/foo/relative');
        $response->assertStatus(403);
    }
}

class RoutableInterfaceStub implements UrlRoutable
{
    public $key;

    public function getRouteKey()
    {
        return $this->{$this->getRouteKeyName()};
    }

    public function getRouteKeyName()
    {
        return 'routable';
    }

    public function resolveRouteBinding($routeKey, $field = null)
    {
        //
    }

    public function resolveChildRouteBinding($childType, $routeKey, $field = null)
    {
        //
    }
}
