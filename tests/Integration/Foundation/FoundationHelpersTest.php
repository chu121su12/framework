<?php

namespace Illuminate\Tests\Integration\Foundation;

use Exception;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase;

class FoundationHelpersTest_testRescue_class 
        {
            public function test($a)
            {
                if (! is_int($a)) {
                    throw class_exists('TypeError')
                        ? new \TypeError
                        : new \ErrorException;
                }

                return $a;
            }
        }

class FoundationHelpersTest extends TestCase
{
    public function testRescue()
    {
        $this->assertEquals(
            'rescued!',
            rescue(function () {
                throw new Exception;
            }, 'rescued!')
        );

        $this->assertEquals(
            'rescued!',
            rescue(function () {
                throw new Exception;
            }, function () {
                return 'rescued!';
            })
        );

        $this->assertEquals(
            'no need to rescue',
            rescue(function () {
                return 'no need to rescue';
            }, 'rescued!')
        );

        $testClass = new FoundationHelpersTest_testRescue_class;

        $this->assertEquals(
            'rescued!',
            rescue(function () use ($testClass) {
                $testClass->test([]);
            }, 'rescued!')
        );
    }

    public function testMixReportsExceptionWhenAssetIsMissingFromManifest()
    {
        $handler = new FakeHandler;
        $this->app->instance(ExceptionHandler::class, $handler);
        $manifest = $this->makeManifest();

        mix('missing.js');

        $this->assertInstanceOf(Exception::class, $handler->reported[0]);
        $this->assertSame('Unable to locate Mix file: /missing.js.', $handler->reported[0]->getMessage());

        unlink($manifest);
    }

    public function testMixSilentlyFailsWhenAssetIsMissingFromManifestWhenNotInDebugMode()
    {
        $this->app['config']->set('app.debug', false);

        $manifest = $this->makeManifest();

        $path = mix('missing.js');

        $this->assertSame('/missing.js', $path);

        unlink($manifest);
    }

    public function testMixThrowsExceptionWhenAssetIsMissingFromManifestWhenInDebugMode()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unable to locate Mix file: /missing.js.');

        $this->app['config']->set('app.debug', true);

        $manifest = $this->makeManifest();

        try {
            mix('missing.js');
        } catch (Exception $e) {
            throw $e;
        } finally { // make sure we can cleanup the file
            unlink($manifest);
        }
    }

    public function testMixOnlyThrowsAndReportsOneExceptionWhenAssetIsMissingFromManifestWhenInDebugMode()
    {
        $handler = new FakeHandler;
        $this->app->instance(ExceptionHandler::class, $handler);
        $this->app['config']->set('app.debug', true);

        $manifest = $this->makeManifest();

        Route::get('test-route', function () {
            mix('missing.js');
        });

        $this->get('/test-route');

        $this->assertCount(1, $handler->reported);

        unlink($manifest);
    }

    public function testFakeReturnsSameInstance()
    {
        app()->instance('config', new ConfigRepository([]));

        $this->assertSame(fake(), fake());
        $this->assertSame(fake(), fake('en_US'));
        $this->assertSame(fake('en_AU'), fake('en_AU'));
        $this->assertNotSame(fake('en_US'), fake('en_AU'));

        app()->flush();
    }

    public function testFakeUsesLocale()
    {
        if (\version_compare(\PHP_VERSION, '7.1.0', '>=')) {
            $mt_srand = function ($x) { mt_srand($x, MT_RAND_PHP); };
        } else {
            $mt_srand = function ($x) { mt_srand($x); };
        }

        $mt_srand(12345);
        app()->instance('config', new ConfigRepository([]));

        // Should fallback to en_US
        $this->assertSame('Arkansas', fake()->state());
        $this->assertContains(fake('de_DE')->state(), [
            'Baden-Württemberg', 'Bayern', 'Berlin', 'Brandenburg', 'Bremen', 'Hamburg', 'Hessen', 'Mecklenburg-Vorpommern', 'Niedersachsen', 'Nordrhein-Westfalen', 'Rheinland-Pfalz', 'Saarland', 'Sachsen', 'Sachsen-Anhalt', 'Schleswig-Holstein', 'Thüringen',
        ]);
        $this->assertContains(fake('fr_FR')->region(), [
            'Auvergne-Rhône-Alpes', 'Bourgogne-Franche-Comté', 'Bretagne', 'Centre-Val de Loire', 'Corse', 'Grand Est', 'Hauts-de-France',
            'Île-de-France', 'Normandie', 'Nouvelle-Aquitaine', 'Occitanie', 'Pays de la Loire', "Provence-Alpes-Côte d'Azur",
            'Guadeloupe', 'Martinique', 'Guyane', 'La Réunion', 'Mayotte',
        ]);

        app()->instance('config', new ConfigRepository(['app' => ['faker_locale' => 'en_AU']]));
        $mt_srand(4);

        // Should fallback to en_US
        $this->assertSame('Australian Capital Territory', fake()->state());
    }

    protected function makeManifest($directory = '')
    {
        app()->usePublicPath(__DIR__);

        $path = public_path(Str::finish($directory, '/').'mix-manifest.json');

        touch($path);

        // Laravel mix prints JSON pretty and with escaped
        // slashes, so we are doing that here for consistency.
        $content = json_encode(['/unversioned.css' => '/versioned.css'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        file_put_contents($path, $content);

        return $path;
    }
}

class FakeHandler
{
    public $reported = [];

    public function report($exception)
    {
        $this->reported[] = $exception;
    }

    public function render($exception)
    {
        //
    }
}
