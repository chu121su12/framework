<?php

namespace Illuminate\Tests\Foundation;

use Illuminate\Foundation\Vite;
use Illuminate\Routing\UrlGenerator;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class FoundationViteTest extends TestCase
{
    protected function setUp()/*: void*/
    {
        app()->instance('url', tap(
            m::mock(UrlGenerator::class),
            function ($url) {
                return $url
                    ->shouldReceive('asset')
                    ->andReturnUsing(function ($value) { return "https://example.com{$value}"; });
            }
        ));
    }

    protected function tearDown()/*: void*/
    {
        $this->cleanViteManifest();
        $this->cleanViteHotFile();
        m::close();
    }

    public function testViteWithJsOnly()
    {
        $this->makeViteManifest();

        $vite = new Vite;
        $result = $vite('resources/js/app.js');

        $this->assertSame('<script type="module" src="https://example.com/build/assets/app.versioned.js"></script>', $result->toHtml());
    }

    public function testViteWithCssAndJs()
    {
        $this->makeViteManifest();

        $vite = new Vite;
        $result = $vite(['resources/css/app.css', 'resources/js/app.js']);

        $this->assertSame(
            '<link rel="stylesheet" href="https://example.com/build/assets/app.versioned.css" />'
            .'<script type="module" src="https://example.com/build/assets/app.versioned.js"></script>',
            $result->toHtml()
        );
    }

    public function testViteWithCssImport()
    {
        $this->makeViteManifest();

        $vite = new Vite;
        $result = $vite('resources/js/app-with-css-import.js');

        $this->assertSame(
            '<link rel="stylesheet" href="https://example.com/build/assets/imported-css.versioned.css" />'
            .'<script type="module" src="https://example.com/build/assets/app-with-css-import.versioned.js"></script>',
            $result->toHtml()
        );
    }

    public function testViteWithSharedCssImport()
    {
        $this->makeViteManifest();

        $vite = new Vite;
        $result = $vite(['resources/js/app-with-shared-css.js']);

        $this->assertSame(
            '<link rel="stylesheet" href="https://example.com/build/assets/shared-css.versioned.css" />'
            .'<script type="module" src="https://example.com/build/assets/app-with-shared-css.versioned.js"></script>',
            $result->toHtml()
        );
    }

    public function testViteHotModuleReplacementWithJsOnly()
    {
        $this->makeViteHotFile();

        $vite = new Vite;
        $result = $vite('resources/js/app.js');

        $this->assertSame(
            '<script type="module" src="http://localhost:3000/@vite/client"></script>'
            .'<script type="module" src="http://localhost:3000/resources/js/app.js"></script>',
            $result->toHtml()
        );
    }

    public function testViteHotModuleReplacementWithJsAndCss()
    {
        $this->makeViteHotFile();

        $vite = new Vite;
        $result = $vite(['resources/css/app.css', 'resources/js/app.js']);

        $this->assertSame(
            '<script type="module" src="http://localhost:3000/@vite/client"></script>'
            .'<link rel="stylesheet" href="http://localhost:3000/resources/css/app.css" />'
            .'<script type="module" src="http://localhost:3000/resources/js/app.js"></script>',
            $result->toHtml()
        );
    }

    protected function makeViteManifest()
    {
        app()->singleton('path.public', function () { return __DIR__; });

        if (! file_exists(public_path('build'))) {
            mkdir(public_path('build'));
        }

        $manifest = json_encode([
            'resources/js/app.js' => [
                'file' => 'assets/app.versioned.js',
            ],
            'resources/js/app-with-css-import.js' => [
                'file' => 'assets/app-with-css-import.versioned.js',
                'css' => [
                    'assets/imported-css.versioned.css',
                ],
            ],
            'resources/js/app-with-shared-css.js' => [
                'file' => 'assets/app-with-shared-css.versioned.js',
                'imports' => [
                    '_someFile.js',
                ],
            ],
            'resources/css/app.css' => [
                'file' => 'assets/app.versioned.css',
            ],
            '_someFile.js' => [
                'css' => [
                    'assets/shared-css.versioned.css',
                ],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        file_put_contents(public_path('build/manifest.json'), $manifest);
    }

    protected function cleanViteManifest()
    {
        if (file_exists(public_path('build/manifest.json'))) {
            unlink(public_path('build/manifest.json'));
        }

        if (file_exists(public_path('build'))) {
            rmdir(public_path('build'));
        }
    }

    protected function makeViteHotFile()
    {
        app()->singleton('path.public', function () { return __DIR__; });

        file_put_contents(public_path('hot'), 'http://localhost:3000');
    }

    protected function cleanViteHotFile()
    {
        if (file_exists(public_path('hot'))) {
            unlink(public_path('hot'));
        }
    }
}
