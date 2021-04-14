<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

if (! class_exists('AnonymousFixtureModifyPeopleTable2015')) {
class AnonymousFixtureModifyPeopleTable2015 extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('people', function (Blueprint $table) {
            $table->string('first_name')->nullable();
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
            $table->dropColumn('first_name');
        });
    }
};
}

return new AnonymousFixtureModifyPeopleTable2015;

// if (\version_compare(\PHP_VERSION, '7.0.0', '<')) {
//     return new AnonymousFixtureModifyPeopleTable2015;
// } else {
//     return new class extends AnonymousFixtureModifyPeopleTable2015;
// }
