<?php namespace Zizaco\Entrust;

use LaravelBook\Ardent\Ardent;

class EntrustProject extends Ardent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'projects';

    /**
     * Ardent validation rules
     *
     * @var array
     */
    public static $rules = array(
      'name' => 'required|between:4,50'
    );

    /**
     * Many-to-Many relations with Users
     */
    public function users()
    {
        return $this->belongsToMany('User', 'assigned_roles_in_project');
    }

    /**
     * Many-to-Many relations with Roles
     */
    public function roles()
    {
        return $this->belongsToMany('Role', 'assigned_roles_in_project');
    }




}
