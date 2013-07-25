<?php namespace Zizaco\Entrust;

use Symfony\Component\Process\Exception\InvalidArgumentException;

trait HasRole
{
    /**
     * Many-to-Many relations with Role
     */
    public function roles()
    {
        return $this->belongsToMany('Role', 'assigned_roles_in_project')->withPivot('project_id','user_id');
    }

    /**
     * Many-to-Many relations with Projects
     */
    public function projects()
    {
        return $this->belongsToMany('Project', 'assigned_roles_in_project')->withPivot('role_id','user_id');
    }

    /**
    *   \brief inProject
    *
    *       Checks if the user belongs to a project by its name
    *
    *   @author Salvatore D'Agostino Mira Geoscience Ltd
    *   @date  23-07-2013
    *   @param projectName   (STRING)   Project name
    *
    *   @return (BOOL)
    **/
    public function inProject($projectName)
    {
        foreach ($this->projects as $project) {
            if ($project->name == $projectName) {
                return true;
            }
        }
        return false;
    }// END function inProject

    /**
    *   \brief hasRoleOnProject
    *
    *       This function if the user has a Role on a Project
    *
    *   @author Salvatore D'Agostino Mira Geoscience Ltd
    *   @date  23-07-2013
    *   @param roleName    (STRING)   Role Name
    *   @param projectName (STRING)   Project Name
    *
    *   @return (BOOL)
    **/
    public function hasRoleOnProject($roleName,$projectName)
    {
        $role = $this->getRoleForUser($projectName);

        if ($role->name == $roleName) {
            return true;
        } else {
            return false;
        }

    }// END function hasRoleOnProject

    /**
     * Checks if the user has a Role by its name
     *
     * @param string $name Role name.
     *
     * @access public
     *
     * @return boolean
     */
    public function hasRole( $name )
    {
        foreach ($this->roles as $role) {
            if( $role->name == $name )
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has a permission on a project by their
     * names (i.e. permission and project names)
     *
     * @param string $permission Permission string.
     *
     * @param string $projectName Project name string.
     *
     * @access public
     *
     * @return boolean
     */
    public function can( $permission, $projectName )
    {

        $role = $this->getRoleForUser($projectName);
        if (is_object($role)) {
            foreach($role->perms as $perm) {
                if($perm->name == $permission) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Checks role(s) and permission(s) and returns bool, array or both
     * @param string|array $roles Array of roles or comma separated string
     * @param string|array $permissions Array of permissions or comma separated string.
     * @param array $options validate_all (true|false) or return_type (boolean|array|both) Default: false | boolean
     * @return array|bool
     * @throws InvalidArgumentException
     */
    public function ability( $roles, $permissions, $options=array() ) {
        // Convert string to array if that's what is passed in.
        if(!is_array($roles)){
            $roles = explode(',', $roles);
        }
        if(!is_array($permissions)){
            $permissions = explode(',', $permissions);
        }

        // Set up default values and validate options.
        if(!isset($options['validate_all'])) {
            $options['validate_all'] = false;
        } else {
            if($options['validate_all'] != true && $options['validate_all'] != false) {
                throw new InvalidArgumentException();
            }
        }
        if(!isset($options['return_type'])) {
            $options['return_type'] = 'boolean';
        } else {
            if($options['return_type'] != 'boolean' &&
                $options['return_type'] != 'array' &&
                $options['return_type'] != 'both') {
                throw new InvalidArgumentException();
            }
        }

        // Loop through roles and permissions and check each.
        $checkedRoles = array();
        $checkedPermissions = array();
        foreach($roles as $role) {
            $checkedRoles[$role] = $this->hasRole($role);
        }
        foreach($permissions as $permission) {
            $checkedPermissions[$permission] = $this->can($permission);
        }

        // If validate all and there is a false in either
        // Check that if validate all, then there should not be any false.
        // Check that if not validate all, there must be at least one true.
        if(($options['validate_all'] && !(in_array(false,$checkedRoles) || in_array(false,$checkedPermissions))) ||
            (!$options['validate_all'] && (in_array(true,$checkedRoles) || in_array(true,$checkedPermissions)))) {
            $validateAll = true;
        } else {
            $validateAll = false;
        }

        // Return based on option
        if($options['return_type'] == 'boolean') {
            return $validateAll;
        } elseif($options['return_type'] == 'array') {
            return array('roles' => $checkedRoles, 'permissions' => $checkedPermissions);
        } else {
            return array($validateAll, array('roles' => $checkedRoles, 'permissions' => $checkedPermissions));
        }

    }

    /**
     * Alias to eloquent many-to-many relation's
     * attach() method
     *
     * @param mixed $role
     *
     * @param mixed $project
     *
     * @access public
     *
     * @return void
     */
    public function attachRole( $role,$project )
    {
        if( is_object($role))
            $role = $role->getKey();

        if( is_array($role))
            $role = $role['id'];

        if( is_object($project))
            $project = $project->getKey();

        if( is_array($project))
            $project = $project['id'];

        $this->roles()->attach( $role, array("project_id" => $project) );
    }

    /**
     * Alias to eloquent many-to-many relation's
     * detach() method
     *
     * @param mixed $role
     *
     * @access public
     *
     * @return void
     */
    public function detachRole( $role )
    {
        if( is_object($role))
            $role = $role->getKey();

        if( is_array($role))
            $role = $role['id'];

        $this->roles()->detach( $role );
    }

    /**
     * Return Role object of User
     *
     * @param string $projectName
     *
     * @access public
     *
     * @return Role object or false otherwise
     */
    public function getRoleForUser($projectName){
        foreach ($this->projects as $project) {
            if ($project->name == $projectName) {
                $roleId = $project->pivot->role_id;
                $role = EntrustRole::find($roleId);

                if(is_object($role)){
                    return $role;
                }
            }
        }
        return false;
    }
}
