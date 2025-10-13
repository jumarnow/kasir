<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->after('name')->nullable();
        });

        $existingUsernames = [];

        DB::table('users')
            ->select('id', 'name', 'email')
            ->orderBy('id')
            ->get()
            ->each(function ($user) use (&$existingUsernames) {
                $base = Str::slug($user->name ?? '', '_');

                if (empty($base)) {
                    $base = Str::slug(Str::before((string) $user->email, '@'), '_');
                }

                if (empty($base)) {
                    $base = 'user';
                }

                $candidate = $base;
                $suffix = 1;

                while (in_array($candidate, $existingUsernames, true)) {
                    $candidate = $base . $suffix;
                    $suffix++;
                }

                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['username' => $candidate]);

                $existingUsernames[] = $candidate;
            });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_username_unique');
            $table->dropColumn('username');
        });
    }
};
