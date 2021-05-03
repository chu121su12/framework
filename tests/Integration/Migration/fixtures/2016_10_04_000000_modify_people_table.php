<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

if (! class_exists('AnonymousFixtureModifyPeopleTable2016')) {
class AnonymousFixtureModifyPeopleTable2016 extends Migration 
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('people', function (Blueprint $table) {
            $table->string('last_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('people', function (Blueprint $table) {
            $table->dropColumn('last_name');
        });
    }
}
}

return new AnonymousFixtureModifyPeopleTable2016;

// if (\version_compare(\PHP_VERSION, '7.0.0', '<')) {
//     return new AnonymousFixtureModifyPeopleTable2016;
// } else {
//     return new class extends AnonymousFixtureModifyPeopleTable2016;
// }
