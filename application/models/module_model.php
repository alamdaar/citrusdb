<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 
 * Module class for citrus modules like customer, billing, services, and support
 * 
 * @author Paul Yasi and David Olivier
 *
 */

class Module_model extends CI_Model
{
	function __construct()
    {
        parent::__construct();
    }
    
    public function permission($user, $modulename)
    {
		// Check for permissions to view module
    	$groupname = array();
    	$modulelist = array();
    	$query = "SELECT * FROM groups WHERE groupmember = ?";
    	$result = $this->db->query($query, array($user))
			or die ("First Permission Query Failed");	

    	foreach ($result->result() as $myresult)
		{
			array_push($groupname,$myresult->groupname);
    	}
		
    	$groups = array_unique($groupname);
    	array_push($groups,$user);
	
	    $query = "SELECT user,permission FROM module_permissions WHERE modulename = ?";
	    $result = $this->db->query($query, array($modulename))
			or die ("Second Permission Query Failed");
		
	    foreach ($result->result() as $myresult)
		{
			if (in_array ($myresult->user, $groups))
	        {
	            if ($myresult->permission == 'r')
	            {
	            	return array ('view' => TRUE);
	            }
    	        if ($myresult->permission == 'c')
       	     	{
       	     		return array ('create' => TRUE);
            	}
            	if ($myresult->permission == 'm')
            	{
            		return array ('modify' => TRUE);
            	}
            	if ($myresult->permission == 'd')
            	{
            		return array ('remove' => TRUE);
            	}
            	if ($myresult->permission == 'f')
            	{
                	return array ('view' => TRUE, 'create' => TRUE, 'modify' => TRUE, 'remove' => TRUE);
            	}
        	}
    	}
    } // end permission function

	
	function permission_error()
	{
		die ("You don't have permission to use this function");
	}
    
    
    public function module_permission_list($user)
    {
    	// get a list of modules we are allowed to view
    	$groupname = array();
    	$modulelist = array();
		$query = "SELECT * FROM groups WHERE groupmember = ?";
		$result = $this->db->query($query, array($user))
			or die ("module_permission groups select failed");

		foreach($result->result() as $myresult)
		{
			array_push($groupname,$myresult->groupname);
		}
		
    	$groups = array_unique($groupname);
    	array_push($groups,$this->user);

    	while (list($key,$value) = each($groups))
    	{
        	$query = "SELECT * FROM module_permissions WHERE user = ? ";
			$result = $this->db->query($query, array($value))
				or die ("select module_permissions failed");
			foreach($result->result() as $myresult)
			{
        		array_push($modulelist,$myresult->modulename);
    		}
    	}
    	
    	return array_unique($modulelist);
    
    }
	
    
    public function modulelist()
    {
		$query = "SELECT * FROM modules ORDER BY sortorder";
		$result = $this->db->query($query) or die ("modulelist query failed");
		
		return $result->result_array();
    }

	
	/*
	 * ------------------------------------------------------------------------
	 *  insert a new module into the modules table
	 * ------------------------------------------------------------------------
	 */
	public function addmodule($commonname, $modulename, $sortorder)
	{
		$query = "INSERT INTO modules (commonname,modulename,sortorder) ".
			"VALUES (?,?,?)";
		$result = $this->db->query($query, array($commonname, $modulename, $sortorder)) 
			or die ("addmodule query failed");
	}


	/*
	 * ------------------------------------------------------------------------
	 *  get module permission information
	 * ------------------------------------------------------------------------
	 */
	function get_module_permissions($module)
	{
		$query = "SELECT * FROM module_permissions WHERE modulename = ? ".
			"ORDER BY user";
		$result = $this->db->query($query, array($module)) 
			or die ("module permissions query failed");

		return $result->result_array();
	}


	function add_permissions($module, $permission, $usergroup)
	{
		$query = "INSERT INTO module_permissions (modulename,permission,user) values(?,?,?)";
		$result = $this->db->query($query, array($module, $permission, $usergroup)) 
			or die ("add permissions query failed");
	}


	function remove_permission($pid)
	{
		$query = "DELETE FROM module_permissions WHERE id = ?";
		$result = $this->db->query($query, array($pid)) or die ("remove permission query failed");
	}

}
