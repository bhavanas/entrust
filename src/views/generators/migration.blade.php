{{ '<?php' }}

use Illuminate\Database\Migrations\Migration;

class EntrustSetupTables extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Creates the roles table
        Schema::create('roles', function($table)
        {
            $table->increments('id')->unsigned();
            $table->string('name')->unique();
            $table->string('permissions');
            $table->boolean('active');
            $table->timestamps();
            $table->softDeletes();
        });

        // Creates the assigned_roles for each project(Many-to-Many relation) table
        Schema::create('assigned_roles_in_project', function($table)
        {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->index('user_id');
            $table->integer('role_id')->unsigned();
            $table->index('role_id');
            $table->integer('project_id')->unsigned();
            $table->index('project_id');
            $table->foreign('user_id')->references('id')->on('users'); // assumes a users table
            $table->foreign('role_id')->references('id')->on('roles');
            $table->foreign('project_id')->references('id')->on('projects'); // assumes a projects table
        });

        // Creates the permissions table
        Schema::create('permissions', function($table)
        {
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->string('display_name');
            $table->timestamps();
        });

        // Creates the projects table
        Schema::create('projects', function($table)
        {
            $table->increments('id');
            $table->string('name')->unique();
            $table->text('desc')->nullable();
            $table->boolean('active');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
        });


        // Creates the permission_role (Many-to-Many relation) table
        Schema::create('permission_role', function($table)
        {
            $table->increments('id')->unsigned();
            $table->integer('permission_id')->unsigned();
            $table->integer('role_id')->unsigned();
            $table->foreign('permission_id')->references('id')->on('permissions'); // assumes a users table
            $table->foreign('role_id')->references('id')->on('roles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('assigned_roles');
        Schema::drop('permission_role');
        Schema::drop('roles');
        Schema::drop('permissions');
    }

}
