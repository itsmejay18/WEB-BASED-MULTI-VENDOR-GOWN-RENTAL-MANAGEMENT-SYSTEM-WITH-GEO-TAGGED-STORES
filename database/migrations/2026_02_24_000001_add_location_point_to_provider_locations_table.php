<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('provider_locations', 'location')) {
            Schema::table('provider_locations', function (Blueprint $table) {
                $table->point('location')->nullable()->after('longitude');
            });
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('UPDATE provider_locations SET location = ST_SRID(POINT(longitude, latitude), 4326) WHERE latitude IS NOT NULL AND longitude IS NOT NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('provider_locations', 'location')) {
            Schema::table('provider_locations', function (Blueprint $table) {
                $table->dropColumn('location');
            });
        }
    }
};
