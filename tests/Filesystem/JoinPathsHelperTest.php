<?php

namespace Illuminate\Tests\Filesystem;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresOperatingSystem;
use PHPUnit\Framework\TestCase;

use function Illuminate\Filesystem\join_paths;

class JoinPathsHelperTest_unixDataProvider_class_1
        {
            public function __toString()
            {
                return 'objecty';
            }
        }

class JoinPathsHelperTest_unixDataProvider_class_2
        {
            public function __toString()
            {
                return '0';
            }
        }

class JoinPathsHelperTest_windowsDataProvider_class_1
        {
            public function __toString()
            {
                return 'objecty';
            }
        }

class JoinPathsHelperTest_windowsDataProvider_class_2
        {
            public function __toString()
            {
                return '0';
            }
        }

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
        yield ['very/Basic/Functionality.php', join_paths('very', 'Basic', 'Functionality.php')];
        yield ['segments/get/ltrimed/by_directory/separator.php', join_paths('segments', '/get/ltrimed', '/by_directory/separator.php')];
        yield ['only/\\os_separator\\/\\get_ltrimmed.php', join_paths('only', '\\os_separator\\', '\\get_ltrimmed.php')];
        yield ['/base_path//does_not/get_trimmed.php', join_paths('/base_path/', '/does_not', '/get_trimmed.php')];
        yield ['Empty/0/1/Segments/00/Get_removed.php', join_paths('Empty', '', '0', null, 0, false, [], '1', 'Segments', '00', 'Get_removed.php')];
        yield ['', join_paths(null, null, '')];
        yield ['1/2/3', join_paths(1, 0, 2, 3)];
        yield ['app/objecty', join_paths('app', 
            new JoinPathsHelperTest_unixDataProvider_class_1
        )];
        yield ['app/0', join_paths('app', 
            new JoinPathsHelperTest_unixDataProvider_class_2
        )];
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
        yield ['app\Basic\Functionality.php', join_paths('app', 'Basic', 'Functionality.php')];
        yield ['segments\get\ltrimed\by_directory\separator.php', join_paths('segments', '\get\ltrimed', '\by_directory\separator.php')];
        yield ['only\\/os_separator/\\/get_ltrimmed.php', join_paths('only', '/os_separator/', '/get_ltrimmed.php')];
        yield ['\base_path\\\\does_not\get_trimmed.php', join_paths('\\base_path\\', '\does_not', '\get_trimmed.php')];
        yield ['Empty\0\1\Segments\00\Get_removed.php', join_paths('Empty', '', '0', null, 0, false, [], '1', 'Segments', '00', 'Get_removed.php')];
        yield ['', join_paths(null, null, '')];
        yield ['1\2\3', join_paths(1, 2, 3)];
        yield ['app\\objecty', join_paths('app', 
            new JoinPathsHelperTest_windowsDataProvider_class_1
        )];
        yield ['app\\0', join_paths('app', 
            new JoinPathsHelperTest_windowsDataProvider_class_2
        )];
    }
}
