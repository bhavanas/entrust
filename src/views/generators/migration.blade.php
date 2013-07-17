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
            $table->increments('id');
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
            $table->increments('id');
            $table->string('name');
            $table->string('display_name');
            $table->timestamps();
        });

        // Creates the permission_role (Many-to-Many relation) table
        Schema::create('permission_role', function($table)
        {
            $table->increments('id');
            $table->integer('permission_id')->unsigned()->index();
            $table->integer('role_id')->unsigned()->index();
            $table->unique(array('permission_id','role_id'));
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
        Schema::drop('roles');
        Schema::drop('permissions');
        Schema::drop('permission_role');
    }

}
