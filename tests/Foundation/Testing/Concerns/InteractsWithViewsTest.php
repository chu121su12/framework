<?php

namespace Illuminate\Tests\Foundation\Bootstrap\Testing\Concerns;

use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Illuminate\View\Component;
use Orchestra\Testbench\TestCase;

class InteractsWithViewsTest_testComponentCanAccessPublicProperties_class extends Component
        {
            public $foo = 'bar';

            public function speak()
            {
                return 'hello';
            }

            public function render()
            {
                return 'rendered content';
            }
        }


class InteractsWithViewsTest extends TestCase
{
    use InteractsWithViews;

    public function testBladeCorrectlyRendersString()
    {
        $string = (string) $this->blade('@if(true)test @endif');

        $this->assertEquals('test ', $string);
    }

    public function testComponentCanAccessPublicProperties()
    {
        $exampleComponent = new InteractsWithViewsTest_testComponentCanAccessPublicProperties_class;

        $component = $this->component(get_class($exampleComponent));

        $this->assertEquals('bar', $component->foo);
        $this->assertEquals('hello', $component->speak());
        $component->assertSee('content');
    }
}
