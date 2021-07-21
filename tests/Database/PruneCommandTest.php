<?php

namespace Illuminate\Tests\Database;

use Illuminate\Container\Container;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Database\Console\PruneCommand;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Events\ModelsPruned;
use Illuminate\Events\Dispatcher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class PruneCommandTest extends TestCase
{
    use \PHPUnit\Framework\PhpUnit8Assert;
    use \PHPUnit\Framework\PhpUnit8Expect;

    protected function setUp()////: void
    {
        parent::setUp();

        Container::setInstance($container = new Container);

        $container->singleton(DispatcherContract::class, function () {
            return new Dispatcher();
        });

        $container->alias(DispatcherContract::class, 'events');
    }

    public function testPrunableModelWithPrunableRecords()
    {
        $output = $this->artisan(['--model' => PrunableTestModelWithPrunableRecords::class]);

        $actual = <<<'EOF'
10 [Illuminate\Tests\Database\PrunableTestModelWithPrunableRecords] records have been pruned.
20 [Illuminate\Tests\Database\PrunableTestModelWithPrunableRecords] records have been pruned.

EOF;

        $this->assertSameStringDifferentLineEndings($actual, $output->fetch());
    }

    public function testPrunableTestModelWithoutPrunableRecords()
    {
        $output = $this->artisan(['--model' => PrunableTestModelWithoutPrunableRecords::class]);

        $actual = <<<'EOF'
No prunable [Illuminate\Tests\Database\PrunableTestModelWithoutPrunableRecords] records found.

EOF;
        $this->assertSameStringDifferentLineEndings($actual, $output->fetch());
    }

    public function testNonPrunableTest()
    {
        $output = $this->artisan(['--model' => NonPrunableTestModel::class]);

        $actual = <<<'EOF'
No prunable [Illuminate\Tests\Database\NonPrunableTestModel] records found.

EOF;

        $this->assertSameStringDifferentLineEndings($actual, $output->fetch());
    }

    protected function artisan($arguments)
    {
        $input = new ArrayInput($arguments);
        $output = new BufferedOutput;

        tap(new PruneCommand())
            ->setLaravel(Container::getInstance())
            ->run($input, $output);

        return $output;
    }

    public function tearDown()////: void
    {
        parent::tearDown();

        Container::setInstance(null);
    }
}

class PrunableTestModelWithPrunableRecords extends Model
{
    use MassPrunable;

    public function pruneAll()
    {
        event(new ModelsPruned(static::class, 10));
        event(new ModelsPruned(static::class, 20));

        return 20;
    }
}

class PrunableTestModelWithoutPrunableRecords extends Model
{
    use Prunable;

    public function pruneAll()
    {
        return 0;
    }
}

class NonPrunableTestModel extends Model
{
    // ..
}
