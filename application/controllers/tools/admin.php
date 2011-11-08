<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends App_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('customer_model');
		$this->load->model('module_model');
		$this->load->model('user_model');
		$this->load->model('admin_model');
	}		


	function organization($id = NULL)
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		// if no id is specified, set to 1
		if (!$id) 
		{
			$id = 1;
		}

		$data['org_list'] = $this->admin_model->org_list();
		$data['org'] = $this->admin_model->get_organization($id);
		
		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/organization_view', $data);
	}


	function updateorganization()
	{
		$id = $this->input->post('id');

		$org_data = array(
				'org_name' => $this->input->post('org_name'),
				'org_street' => $this->input->post('org_street'),
				'org_city' => $this->input->post('org_city'),
				'org_state' => $this->input->post('org_state'),
				'org_country' => $this->input->post('org_country'),
				'org_zip' => $this->input->post('org_zip'),
				'phone_sales' => $this->input->post('phone_sales'),
				'email_sales' => $this->input->post('email_sales'),
				'phone_billing' => $this->input->post('phone_billing'),
				'email_billing' => $this->input->post('email_billing'),
				'phone_custsvc' => $this->input->post('phone_custsvc'),
				'email_custsvc' => $this->input->post('email_custsvc'),
				'ccexportvarorder' => $this->input->post('ccexportvarorder'),
				'regular_pastdue' => $this->input->post('regular_pastdue'),
				'regular_turnoff' => $this->input->post('regular_turnoff'),
				'regular_canceled' => $this->input->post('regular_canceled'),
				'dependent_pastdue' => $this->input->post('dependent_pastdue'),
				'dependent_shutoff_notice' => $this->input->post('dependent_shutoff_notice'),
				'dependent_turnoff' => $this->input->post('dependent_turnoff'),
				'dependent_canceled' => $this->input->post('dependent_canceled'),
				'default_invoicenote' => $this->input->post('default_invoicenote'),
				'pastdue_invoicenote' => $this->input->post('pastdue_invoicenote'),
				'turnedoff_invoicenote' => $this->input->post('turnedoff_invoicenote'),
				'collections_invoicenote' => $this->input->post('collections_invoicenote'),
				'declined_subject' => $this->input->post('declined_subject'),
				'declined_message' => $this->input->post('declined_message'),
				'invoice_footer' => $this->input->post('invoice_footer'),
				'einvoice_footer' => $this->input->post('einvoice_footer'),
				'exportprefix' => $this->input->post('exportprefix') 
					);

		$this->admin_model->update_organization($id, $org_data);

		redirect("/tools/admin/organization/".$id);
	}


	function addorganization()
	{
		$newid = $this->admin_model->add_organization();
		redirect("/tools/admin/organization/".$newid);
	}


	function settings()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$data['set'] = $this->admin_model->get_settings();

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/settings_view', $data);
	}


	function savesettings()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$settings_array = array(
				'path_to_ccfile' => $this->input->post('path_to_ccfile'),
				'default_group' => $this->input->post('default_group'),
				'default_billing_group' => $this->input->post('default_billing_group'),
				'default_shipping_group' => $this->input->post('default_shipping_group'),
				'billingdate_rollover_time' => $this->input->post('billingdate_rollover_time'),
				'billingweekend_sunday' => $this->input->post('billingweekend_sunday'),
				'billingweekend_monday' => $this->input->post('billingweekend_monday'),
				'billingweekend_tuesday' => $this->input->post('billingweekend_tuesday'),
				'billingweekend_wednesday' => $this->input->post('billingweekend_wednesday'),
				'billingweekend_thursday' => $this->input->post('billingweekend_thursday'),
				'billingweekend_friday' => $this->input->post('billingweekend_friday'),
				'billingweekend_saturday' => $this->input->post('billingweekend_saturday'),
				'dependent_cancel_url' => $this->input->post('dependent_cancel_url')
				);

		$this->admin_model->update_settings($settings_array);

		redirect('/tools/admin/settings');

	}


	function users()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$data['users'] = $this->admin_model->get_users();

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/users_view', $data);
	}

	
	function newuser()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/newuser_view');
	}


	function savenewuser()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$new_user_name = $this->input->post('new_user_name');
		$password1 = $this->input->post('password1');
		$password2 = $this->input->post('password2');
		$real_name = $this->input->post('real_name');
		$admin = $this->input->post('admin');
		$manager = $this->input->post('manager');

		$feedback = $this->user_model->user_register($new_user_name,$password1,$password2,$real_name,$admin,$manager);

		if ($feedback['status'] == TRUE)
		{
			$this->load->model('settings_model');
			$default_group = $this->settings_model->get_default_group();

			// if there is a default group, add them to that group
			if ($default_group != '')
			{
				$this->user_model->add_user_to_group($default_group, $new_user_name);
			}
		}

		echo '<FONT COLOR="RED"><H2>'.$feedback['message'].'</H2></FONT>';
		echo "<p>$new_user_name<p>$password1<p>$password2<p>$real_name";

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/newuser_view');
	}


	function edituser($userid)
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$data['userid'] = $userid;
		$data['u'] = $this->user_model->get_user_info($userid);

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/edituser_view', $data);
	}


	function saveedituser()
	{
		$userid = $this->input->post('userid');

		$userinfo = array(
				'username' => $this->input->post('username'),
				'real_name' => $this->input->post('realname'),
				'admin' => $this->input->post('admin'),
				'manager' => $this->input->post('manager'),
				'email' => $this->input->post('email'),
				'screenname' => $this->input->post('screenname'),
				'email_notify' => $this->input->post('email_notify'),
				'screenname_notify' => $this->input->post('screenname_notify')
				);

		$this->user_model->update_user_info($userid, $userinfo);

		redirect("/tools/admin/edituser/".$userid);
	}


	function deleteuser($uid)
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$data['uid'] = $uid;

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/deleteuser_view', $data);
	}


	function savedeleteuser()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$uid = $this->input->post('uid');

		$username = $this->user_model->get_username($uid);

		$this->user_model->delete_user($uid);

		$this->user_model->delete_username_from_groups($username);

		// redirect back to the user list page
		redirect('/tools/admin/users');
	}

	function groups()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}
		
		$data['groups'] = $this->admin_model->get_groups();

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/groups_view', $data);
	}


	function addgroup()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}
		
		$data['users'] = $this->user_model->list_users();

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/addgroup_view', $data);
	}


	function saveaddgroup()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$membername = $this->input->post('membername');
		$groupname = $this->input->post('groupname');

		$this->user_model->add_to_group($groupname, $membername);

		print "<h3>$l_changessaved</h3>";

		redirect("/tools/admin/groups");
	}


	function deletegroup($gid)
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$data['gid'] = $gid;

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/deletegroup_view', $data);
	}


	function savedeletegroup()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$gid = $this->input->post('gid');

		$this->user_model->delete_group($gid);

		// redirect back to the group list page
		redirect("/tools/admin/groups");
	}


	function modules()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$data['modules'] = $this->module_model->modulelist();

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/modules_view', $data);
	}

	function addmodule()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/addmodule_view');
	}


	function saveaddmodule()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$commonname = $this->input->post('commonname');
		$modulename = $this->input->post('modulename');
		$sortorder = $this->input->post('sortorder');

		$this->module_model->addmodule($commonname, $modulename, $sortorder);
		print "<h3>Modules Updated</h3>";

		redirect('/tools/admin/modules');
	}


	function modulepermissions($modulename)
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$data['module'] = $modulename;
		$data['permissions'] = $this->module_model->get_module_permissions($modulename);

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/modulepermissions_view', $data);
	}


	function addmodulepermissions($modulename)
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$data['module'] = $modulename;
		$data['permissions'] = $this->module_model->get_module_permissions($modulename);
		$data['groupslist'] = $this->user_model->list_groups();
		$data['userslist'] = $this->user_model->list_users();

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/addmodulepermissions_view', $data);
	}


	function savemodulepermissions()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}
		$module = $this->input->post('module');
		$permission = $this->input->post('permission');
		$usergroup = $this->input->post('usergroup');

		$this->module_model->add_permissions($module, $permission, $usergroup);

		print lang('changessaved');

		redirect("/tools/admin/modulepermissions/".$module);

	}

	function removemodulepermissions($pid, $module)
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$data['pid'] = $pid;
		$data['module'] = $module;
		
		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/removemodulepermissions_view', $data);
	}


	function saveremovemodulepermissions()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		//GET Variables
		$module = $this->input->post('module');
		$deletenow = $this->input->post('deletenow');
		$pid = $this->input->post('pid');

		$this->module_model->remove_permission($pid);

		print lang('changessaved');

		redirect("/tools/admin/modulepermissions/".$module);
	}


	function billingtypes()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$data['billingtypes'] = $this->admin_model->get_billing_types();

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/billingtypes_view', $data);
	}


	function addbillingtype()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$method = $base->input['method'];
		$frequency = $base->input['frequency'];
		$name = $base->input['name'];
		$remove = $base->input['remove'];
		$typeid = $base->input['typeid'];
		$deletenow = $base->input['deletenow'];

		// add a billing type
		$query = "INSERT INTO billing_types (name,frequency,method) VALUES ('$name','$frequency','$method')";
		$result = $DB->Execute($query) or die ("$l_queryfailed");
		print "<h3>$l_changessaved</h3> [<a href=\"index.php?load=billing&tooltype=module&type=tools\">done</a>]";

		redirect('/tools/admin/billingtypes');
	}


	function removebillingtype($typeid)
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$data['typeid'] = $typeid;

		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/removebillingtype_view', $data);
	}


	function saveremovebillingtype()
	{
		// check if the user has manager privileges first
		$myresult = $this->user_model->user_privileges($this->user);

		if ($myresult['manager'] == 'n') 
		{
			echo lang('youmusthaveadmin')."<br>";
			exit; 
		}

		$typeid = $this->input->post('typeid');

		$this->admin_model->remove_billing_type($typeid);

		// remove the billing type
		$query = "DELETE FROM billing_types WHERE id = ?";
		$result = $this->db->query($query, array($typeid)) or die ("remove billing type query failed");

		redirect('/tools/admin/billingtypes');
	}


	function services()
	{
		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/services_view');
	}


	function mergeaccounts()
	{
		// load the header without the sidebar to get the stylesheet in there
		$this->load->view('header_no_sidebar_view');

		$this->load->view('tools/admin/mergeaccounts_view');
	}
}

/* End of file admin */
/* Location: ./application/controllers/tools/admin.php */