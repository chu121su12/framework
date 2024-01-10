<?php

namespace Illuminate\Tests\Integration\Database\MySql;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\RequiresOperatingSystem;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use stdClass;

/**
 * @requires extension pdo_mysql
 * @requires OS Linux|Darwin
 */
#[RequiresOperatingSystem('Linux|Darwin')]
#[RequiresPhpExtension('pdo_mysql')]
class DatabaseMySqlSchemaBuilderAlterTableWithEnumTest extends MySqlTestCase
{
    protected function afterRefreshingDatabase()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->integer('id');
            $table->string('name');
            $table->string('age');
            $table->enum('color', ['red', 'blue']);
        });
    }

    protected function destroyDatabaseMigrations()
    {
        Schema::drop('users');
    }

    public function testRenameColumnOnTableWithEnum()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('name', 'username');
        });

        $this->assertTrue(Schema::hasColumn('users', 'username'));
    }

    public function testChangeColumnOnTableWithEnum()
    {
        if (\version_compare(\PHP_VERSION, '8.0', '>=')) {
            $this->markTestSkipped('Needs fix on Doctrine\DBAL\Schema\Table.');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('age')->change();
        });

        $this->assertSame('int', Schema::getColumnType('users', 'age'));
    }

    public function testGetAllTablesAndColumnListing()
    {
        $tables = Schema::getAllTables();

        $this->assertCount(2, $tables);
        $tableProperties = array_values((array) $tables[0]);
        $this->assertEquals(['migrations', 'BASE TABLE'], $tableProperties);

        $this->assertInstanceOf(stdClass::class, $tables[1]);

        $tableProperties = array_values((array) $tables[1]);
        $this->assertEquals(['users', 'BASE TABLE'], $tableProperties);

        $columns = Schema::getColumnListing('users');

        foreach (['id', 'name', 'age', 'color'] as $column) {
            $this->assertContains($column, $columns);
        }

        Schema::create('posts', function (Blueprint $table) {
            $table->integer('id');
            $table->string('title');
        });
        $tables = Schema::getAllTables();
        $this->assertCount(3, $tables);
        Schema::drop('posts');
    }
}
