<?php

namespace Illuminate\Tests\View\Blade;

use Illuminate\View\Component;
use Illuminate\View\ComponentAttributeBag;
use Mockery as m;

class BladeComponentsTest extends AbstractBladeTestCase
{
    use \PHPUnit\Framework\PhpUnit8Assert;

    public function testComponentsAreCompiled()
    {
        $this->assertSame('<?php $__env->startComponent(\'foo\', ["foo" => "bar"]); ?>', $this->compiler->compileString('@component(\'foo\', ["foo" => "bar"])'));
        $this->assertSame('<?php $__env->startComponent(\'foo\'); ?>', $this->compiler->compileString('@component(\'foo\')'));
    }

    public function testClassComponentsAreCompiled()
    {
        $this->assertSameStringDifferentLineEndings('<?php if (isset($component)) { $__componentOriginal32877a641c21ac6579f6376333c8770674a6058f = $component; } ?>
<?php $component = Illuminate\Tests\View\Blade\ComponentStub::class; ?>
<?php $component = $component::resolve(["foo" => "bar"] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName(\'test\'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>', $this->compiler->compileString('@component(\'Illuminate\Tests\View\Blade\ComponentStub::class\', \'test\', ["foo" => "bar"])'));
    }

    public function testEndComponentsAreCompiled()
    {
        $this->compiler->newComponentHash('foo');

        $this->assertSame('<?php echo $__env->renderComponent(); ?>', $this->compiler->compileString('@endcomponent'));
    }

    public function testEndComponentClassesAreCompiled()
    {
        $this->compiler->newComponentHash('foo');

        $this->assertSameStringDifferentLineEndings('<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal79aef92e83454121ab6e5f64077e7d8a)): ?>
<?php $component = $__componentOriginal79aef92e83454121ab6e5f64077e7d8a; ?>
<?php unset($__componentOriginal79aef92e83454121ab6e5f64077e7d8a); ?>
<?php endif; ?>', $this->compiler->compileString('@endcomponentClass'));
    }

    public function testSlotsAreCompiled()
    {
        $this->assertSame('<?php $__env->slot(\'foo\', null, ["foo" => "bar"]); ?>', $this->compiler->compileString('@slot(\'foo\', null, ["foo" => "bar"])'));
        $this->assertSame('<?php $__env->slot(\'foo\'); ?>', $this->compiler->compileString('@slot(\'foo\')'));
    }

    public function testEndSlotsAreCompiled()
    {
        $this->assertSame('<?php $__env->endSlot(); ?>', $this->compiler->compileString('@endslot'));
    }

    public function testPropsAreExtractedFromParentAttributesCorrectlyForClassComponents()
    {
        $attributes = new ComponentAttributeBag(['foo' => 'baz', 'other' => 'ok']);

        $component = m::mock(Component::class);
        $component->shouldReceive('withName', 'test');
        $component->shouldReceive('shouldRender')->andReturn(false);

        Component::resolveComponentsUsing(function () use ($component) { return $component; });

        $template = $this->compiler->compileString('@component(\'Illuminate\Tests\View\Blade\ComponentStub::class\', \'test\', ["foo" => "bar"])');

        ob_start();
        eval(" ?> $template <?php endif; ");
        ob_get_clean();
    }
}

class ComponentStub extends Component
{
    public function render()
    {
        return '';
    }
}
