<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('file_path')->nullable()->change();
        });

        DB::table('documents')
            ->whereIn('file_path', ['0', ''])
            ->update(['file_path' => null]);
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('file_path')->nullable(false)->change();
        });
    }
};
