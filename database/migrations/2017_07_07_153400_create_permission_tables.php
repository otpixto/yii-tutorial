<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePermissionTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $tableNames = config('permission.table_names');
        $foreignKeys = config('permission.foreign_keys');

        try
        {

            Schema::create($tableNames['permissions'], function (Blueprint $table) {
                $table->increments('id');
                $table->string('code')->unique();
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
                $table->softDeletes();
            });

            Schema::create($tableNames['roles'], function (Blueprint $table) {
                $table->increments('id');
                $table->string('code')->unique();
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
                $table->softDeletes();
            });

            Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $foreignKeys) {
                $table->integer('permission_id')->unsigned();
                $table->morphs('model');

                $table->foreign('permission_id')
                    ->references('id')
                    ->on($tableNames['permissions'])
                    ->onDelete('cascade');

                $table->primary(['permission_id', 'model_id', 'model_type']);
            });

            Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $foreignKeys) {
                $table->integer('role_id')->unsigned();
                $table->morphs('model');

                $table->foreign('role_id')
                    ->references('id')
                    ->on($tableNames['roles'])
                    ->onDelete('cascade');

                $table->primary(['role_id', 'model_id', 'model_type']);
            });

            Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames) {
                $table->integer('permission_id')->unsigned();
                $table->integer('role_id')->unsigned();

                $table->foreign('permission_id')
                    ->references('id')
                    ->on($tableNames['permissions'])
                    ->onDelete('cascade');

                $table->foreign('role_id')
                    ->references('id')
                    ->on($tableNames['roles'])
                    ->onDelete('cascade');

                $table->primary(['permission_id', 'role_id']);
            });

            \Iphome\Permission\Models\Role::create([
                'name'          => 'Администратор',
                'code'          => 'admin',
            ]);

            \Iphome\Permission\Models\Permission::create([
                'name'          => 'Администрирование',
                'code'          => 'admin',
            ]);

            \Iphome\Permission\Models\Permission::create([
                'name'          => 'Пользователи',
                'code'          => 'admin.users',
            ]);

            \Iphome\Permission\Models\Permission::create([
                'name'          => 'Создание пользователей',
                'code'          => 'admin.users.create',
            ]);

            \Iphome\Permission\Models\Permission::create([
                'name'          => 'Редактирование пользователей',
                'code'          => 'admin.users.edit',
            ]);

            \Iphome\Permission\Models\Permission::create([
                'name'          => 'Удаление пользователей',
                'code'          => 'admin.users.delete',
            ]);

            \Iphome\Permission\Models\Permission::create([
                'name'          => 'Роли',
                'code'          => 'admin.roles',
            ]);

            \Iphome\Permission\Models\Permission::create([
                'name'          => 'Создание ролей',
                'code'          => 'admin.roles.create',
            ]);

            \Iphome\Permission\Models\Permission::create([
                'name'          => 'Редактирование ролей',
                'code'          => 'admin.roles.edit',
            ]);

            \Iphome\Permission\Models\Permission::create([
                'name'          => 'Удаление ролей',
                'code'          => 'admin.roles.delete',
            ]);

            \Iphome\Permission\Models\Permission::create([
                'name'          => 'Права',
                'code'          => 'admin.perms',
            ]);

            \Iphome\Permission\Models\Permission::create([
                'name'          => 'Создание прав',
                'code'          => 'admin.perms.create',
            ]);

            \Iphome\Permission\Models\Permission::create([
                'name'          => 'Редактирование прав',
                'code'          => 'admin.perms.edit',
            ]);

            \Iphome\Permission\Models\Permission::create([
                'name'          => 'Удаление прав',
                'code'          => 'admin.perms.delete',
            ]);

        }
        catch ( PDOException $e )
        {
            $this->down();
            throw $e;
        }
        catch ( \Illuminate\Database\QueryException $e )
        {
            $this->down();
            throw $e;
        }
        catch ( Exception $e )
        {
            $this->down();
            throw $e;
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tableNames = config('permission.table_names');
        Schema::dropIfExists($tableNames['model_has_roles']);
        Schema::dropIfExists($tableNames['model_has_permissions']);
        Schema::dropIfExists($tableNames['role_has_permissions']);
        Schema::dropIfExists($tableNames['roles']);
        Schema::dropIfExists($tableNames['permissions']);
    }
}
