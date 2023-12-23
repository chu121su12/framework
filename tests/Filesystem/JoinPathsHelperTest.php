<?php

namespace Illuminate\Tests\Filesystem;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresOperatingSystem;
use PHPUnit\Framework\TestCase;

use function Illuminate\Filesystem\join_paths;

class JoinPathsHelperTest extends TestCase
{
    /**
     * @requires OS Linux|DAR
     * @dataProvider unixDataProvider
     */
    #[RequiresOperatingSystem('Linux|DAR')]
    #[DataProvider('unixDataProvider')]
    public function testItCanMergePathsForUnix(/*string */$expected, /*string */$given)
    {
        $expected = backport_type_check('string', $expected);

        $given = backport_type_check('string', $given);

        $this->assertSame($expected, $given);
    }

    public static function unixDataProvider()
    {
        yield ['app/Http/Kernel.php', join_paths('app', 'Http', 'Kernel.php')];
        yield ['app/Http/Kernel.php', join_paths('app', '', 'Http', 'Kernel.php')];
    }

    /**
     * @requires OS Windows
     * @dataProvider windowsDataProvider
     */
    #[RequiresOperatingSystem('Windows')]
    #[DataProvider('windowsDataProvider')]
    public function testItCanMergePathsForWindows(/*string */$expected, /*string */$given)
    {
        $expected = backport_type_check('string', $expected);

        $given = backport_type_check('string', $given);

        $this->assertSame($expected, $given);
    }

    public static function windowsDataProvider()
    {
        yield ['app\Http\Kernel.php', join_paths('app', 'Http', 'Kernel.php')];
        yield ['app\Http\Kernel.php', join_paths('app', '', 'Http', 'Kernel.php')];
    }
}
