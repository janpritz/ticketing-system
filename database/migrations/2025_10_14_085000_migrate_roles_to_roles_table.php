<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add nullable role_id to users
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->nullable()->after('role');
        });

        // Insert distinct role names from users into roles table
        $distinctRoles = DB::table('users')->whereNotNull('role')->distinct()->pluck('role');
        $now = now();
        foreach ($distinctRoles as $roleName) {
            DB::table('roles')->insertOrIgnore([
                'name' => $roleName,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Build mapping of role name -> id
        $roleMap = DB::table('roles')->pluck('id', 'name')->toArray(); // name => id

        // Update users.role_id based on existing users.role value
        foreach ($roleMap as $name => $id) {
            DB::table('users')->where('role', $name)->update(['role_id' => $id]);
        }

        // Add foreign key constraint (keep nullable to avoid issues)
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('set null');
        });

        // Remove the old role string column
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Re-create role string column
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->nullable()->after('email');
        });

        // Populate role string from roles table via role_id
        $roleMap = DB::table('roles')->pluck('name', 'id')->toArray(); // id => name
        foreach ($roleMap as $id => $name) {
            DB::table('users')->where('role_id', $id)->update(['role' => $name]);
        }

        // Drop foreign key and role_id column
        Schema::table('users', function (Blueprint $table) {
            try {
                $table->dropForeign(['role_id']);
            } catch (\Exception $e) {
                // ignore if constraint does not exist
            }

            if (Schema::hasColumn('users', 'role_id')) {
                $table->dropColumn('role_id');
            }
        });
    }
};