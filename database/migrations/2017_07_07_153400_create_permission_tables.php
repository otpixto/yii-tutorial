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

        Schema::create($tableNames['permissions'], function (Blueprint $table) {
            $table->increments('id');
            $table->string('category_name');
            $table->string('code')->unique();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        Schema::create($tableNames['roles'], function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->unique();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
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

        \Spatie\Permission\Models\Role::create([
            'name'          => 'Администратор',
            'code'          => 'admin',
        ]);

        \Spatie\Permission\Models\Role::create([
            'name'      => 'Оператор',
            'code'      => 'operator',
        ]);

        \Spatie\Permission\Models\Role::create([
            'name'      => 'Исполнитель',
            'code'      => 'executor',
        ]);

        \Spatie\Permission\Models\Role::create([
            'name'      => 'Контроль',
            'code'      => 'control',
        ]);

        \Spatie\Permission\Models\Permission::create([
            'category_name' => 'Обращения',
            'name'          => 'Создание',
            'code'          => 'tickets.create',
        ]);

        \Spatie\Permission\Models\Permission::create([
            'category_name' => 'Обращения',
            'name'          => 'Удаление',
            'code'          => 'tickets.delete',
        ]);

        \Spatie\Permission\Models\Permission::create([
            'category_name' => 'Пользователи',
            'name'          => 'Создание',
            'code'          => 'users.create',
        ]);

        \Spatie\Permission\Models\Permission::create([
            'category_name' => 'Пользователи',
            'name'          => 'Редактирование',
            'code'          => 'users.edit',
        ]);

        \Spatie\Permission\Models\Permission::create([
            'category_name' => 'Пользователи',
            'name'          => 'Удаление',
            'code'          => 'users.delete',
        ]);

        \Spatie\Permission\Models\Permission::create([
            'category_name' => 'Роли',
            'name'          => 'Создание',
            'code'          => 'roles.create',
        ]);

        \Spatie\Permission\Models\Permission::create([
            'category_name' => 'Роли',
            'name'          => 'Редактирование',
            'code'          => 'roles.edit',
        ]);

        \Spatie\Permission\Models\Permission::create([
            'category_name' => 'Роли',
            'name'          => 'Удаление',
            'code'          => 'roles.delete',
        ]);

        \Spatie\Permission\Models\Permission::create([
            'category_name' => 'Права доступа',
            'name'          => 'Создание',
            'code'          => 'perms.create',
        ]);

        \Spatie\Permission\Models\Permission::create([
            'category_name' => 'Права доступа',
            'name'          => 'Редактирование',
            'code'          => 'perms.edit',
        ]);

        \Spatie\Permission\Models\Permission::create([
            'category_name' => 'Права доступа',
            'name'          => 'Удаление',
            'code'          => 'perms.delete',
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tableNames = config('permission.table_names');
        Schema::drop($tableNames['model_has_roles']);
        Schema::drop($tableNames['model_has_permissions']);
        Schema::drop($tableNames['role_has_permissions']);
        Schema::drop($tableNames['roles']);
        Schema::drop($tableNames['permissions']);
    }
}
