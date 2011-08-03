<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Billing extends App_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('customer_model');
		$this->load->model('billing_model');
		$this->load->model('module_model');
	}		
	
    /*
     * ------------------------------------------------------------------------
     * Customer overview of everything
     * ------------------------------------------------------------------------
     */
    public function index()
    {
		// check permissions	
		$permission = $this->module_model->permission($this->user, 'billing');

		if ($permission['view'])
		{
			// load the module header common to all module views
			$this->load->view('module_header_view');
			
			// get the billing id
			$billing_id = $this->billing_model->default_billing_id($this->account_number);
			
			// show the billing information (name, address, etc)
			$data = $this->billing_model->record($billing_id);
			$this->load->view('billing/index_view', $data);
			
			// show any alternate billing types
			$data['alternate'] = $this->billing_model->alternates($this->account_number, $billing_id);
			$this->load->view('billing/alternate_view', $data);
			
			// the history listing tabs
			$this->load->view('historyframe_tabs_view');			
			
			// the html page footer
			$this->load->view('html_footer_view');
			
		}
		else
		{
			
			$this->module_model->permission_error();
			
		}	
		
	}
	
	public function edit($billing_id)
	{
		// check permissions
		$permission = $this->module_model->permission($this->user, 'billing');
		
		if ($permission['modify'])
		{
			// the module header common to all module views
			$this->load->view('module_header_view');
			
			// show the edit customer form
			$data = $this->billing_model->record($billing_id);
			$this->load->view('billing/edit_view', $data);

			// the history listing tabs
			$this->load->view('historyframe_tabs_view');			
			
			// show html footer
			$this->load->view('html_footer_view');
		}
		else
		{
			$this->module_model->permission_error();
		}
	}

	
	/*
	 * ------------------------------------------------------------------------
	 *  save the input from the edit billing view
	 * ------------------------------------------------------------------------
	 */
	public function save()
	{
		// check if there is a non-masked credit card number in the input
		// if the second cararcter is a * then it's already masked

		$newcc = FALSE; // set to false so we don't replace it unnecessarily

		// get some inputs that we need to process this input
		$billing_id = $this->input->post('billing_id');
		$billing_type = $this->input->post('billing_type');
		$from_date = $this->input->post('from_date');
		$creditcard_number = $this->input->post('creditcard_number');
		
		// check if the credit card entered already masked and not blank
		// eg: a replacement was not entered
		if ($creditcard_number[1] <> '*' AND $creditcard_number <> '')
		{			
			// destroy the output array before we use it again
			unset($encrypted);

			// load the encryption helper for use when calling gpg things
			$this->load->helper('encryption');

			// run the gpg command
			$encrypted = encrypt_command($this->config->item('gpg_command'), $creditcard_number);

			// if there is a gpg error, stop here
			if (substr($encrypted,0,5) == "error")
			{				
				die ("Credit Card Encryption Error: $encrypted");
			}

			// change the ouput array into ascii ciphertext block
			$encrypted_creditcard_number = $encrypted;

			// wipe out the middle of the creditcard_number before it gets inserted
			$length = strlen($creditcard_number);
			$firstdigit = substr($creditcard_number, 0,1);
			$lastfour = substr($creditcard_number, -4);
			$creditcard_number = "$firstdigit" . "***********" . "$lastfour";
			
			$newcc = TRUE;
		}
		
		// fill in the billing data array with new info
		$billing_data = array(
			'name' => $this->input->post('name'),
			'company' => $this->input->post('company'),
			'street' => $this->input->post('street'),
			'city' => $this->input->post('city'),
			'state'=> $this->input->post('state'),
			'zip' => $this->input->post('zip'),
			'country' => $this->input->post('country'),
			'phone' => $this->input->post('phone'),
			'fax' => $this->input->post('fax'),
			'billing_type' => $this->input->post('billing_type'),
			'creditcard_expire'=> $this->input->post('creditcard_expire'),
			'next_billing_date' => $this->input->post('next_billing_date'),
			'from_date' => $this->input->post('from_date'),
			'payment_due_date' => $this->input->post('payment_due_date'),
			'notes' => $this->input->post('notes'),
			'pastdue_exempt' => $this->input->post('pastdue_exempt'),
			'po_number'=> $this->input->post('po_number'),
			'automatic_receipt' => $this->input->post('automatic_receipt'), 
			'contact_email' => $this->input->post('contact_email')
			);

		// check if rerun_date should be NULL or not
		$rerun_date = $this->input->post('rerun_date');
		if ($rerun_date == "0000-00-00")
		{
			// rerun date is null
			$billing_data['rerun_date'] = NULL;
		}		
		else
		{
			// rerun date has something in it
			$billing_data['rerun_date'] = $rerun_date;
		}
		
		if ($newcc == TRUE)
		{
			// insert with a new credit card and encrypted ciphertext
			$billing_data['encrypted_creditcard_number'] = $encrypted_creditcard_number;
			$billing_data['creditcard_number'] = $this->input->post('creditcard_number');
			$billing_data['creditcard_expire'] = $this->input->post('creditcard_expire');
		}
		elseif ($creditcard_number == '')
		{
			$billing_data['encrypted_creditcard_number'] = NULL;
			$billing_data['creditcard_number'] = NULL;
			$billing_data['creditcard_expire'] = NULL;		
		}
		
		// save the data to the customer record
		$data = $this->billing_model->save_record($billing_id, $billing_data);			

		// set the to_date automatically
		$this->billing_model->automatic_to_date($from_date, $billing_type, $billing_id);

		// add a log entry that this billing record was edited
		$this->log_model->activity($this->user,$this->account_number,'edit','billing',$billing_id,'success');

		print "<h3>". lang('changessaved') ."<h3>";

		redirect('/billing');
	}


	/*
	 * -------------------------------------------------------------------------
	 *  add billing function, ask if the user wants to add a billing record
	 *  with a specific organization id
	 * -------------------------------------------------------------------------
	 */
	public function addbilling()
	{
		// check permissions
		$permission = $this->module_model->permission($this->user, 'billing');
		
		if ($permission['modify'])
		{
			// the module header common to all module views
			$this->load->view('module_header_view');
			
			// show the add billing record form
			$this->load->view('billing/add_billing_record_view');

			// the history listing tabs
			$this->load->view('historyframe_tabs_view');			
			
			// show html footer
			$this->load->view('html_footer_view');
		}
		else
		{
			$this->module_model->permission_error();
		}
	}

	

	/*
	 * ------------------------------------------------------------------------
	 *  create a new billing record, called by the prompt that asks if they
	 *  want to create a new billing record under a specific organization id
	 * ------------------------------------------------------------------------
	 */
	public function create()
	{
		// check permissions
		$permission = $this->module_model->permission($this->user, 'customer');

		$organization_id = $this->input->post('organization_id');
		
		
		if ($permission['create'])
		{

			$customer_data = array(
					'name' => $this->input->post('name'),
					'company' => $this->input->post('company'),
					'street' => $this->input->post('street'),
					'city' => $this->input->post('city'),
					'state' => $this->input->post('state'),
					'country' => $this->input->post('country'),
					'zip' => $this->input->post('zip'),
					'phone' => $this->input->post('phone'),
					'fax' => $this->input->post('fax'),
					'source' => $this->input->post('source'),
					'contact_email' => $this->input->post('contact_email'),
					'secret_question' => $this->input->post('secret_question'),
					'secret_answer' => $this->input->post('secret_answer')
					);

			// put the data in a new customer record
			$data = $this->customer_model->create_record($customer_data);		

			// log this record creation
			$this->log_model->activity($this->user,$this->account_number,'create',
					'customer',0,'success');

		}
		else
		{
			$this->module_model->permission_error();
		}
	}


	/*
	 * ------------------------------------------------------------------------
	 *  asks the user whether they are sure they want to cancel this customer
	 * ------------------------------------------------------------------------
	 */
	public function cancel()
	{
		// check permissions
		$permission = $this->module_model->permission($this->user, 'customer');

		if ($permission['remove'])
		{
			// load the service model to check carrier dependent
			$this->load->model('service_model');			

			// check if the services on the account are carrier_dependent
			// if it is carrier dependent, then send user to the
			// carrier dependent cancel form instead of the regular cancel system
			$dependent = $this->service_model->carrier_dependent($this->account_number);

			if ($dependent == true) {
				// print a message that this customer is carrier dependent
				echo "<h3>" . lang('carrierdependentmessage') . "</h3><p align=center>";

				// get the dependent_cancel_url from the settings table
				$query = "SELECT dependent_cancel_url FROM settings WHERE id = 1";
				$result = $this->db->query($query) or die ("$l_queryfailed");
				$myresult = $result->row();
				$dependent_cancel_url = $myresult->dependent_cancel_url;

				// print a link to the url to fill out the carrier dependent cancel form
				print "<a href=\"$dependent_cancel_url$this->account_number\" target=\"_BLANK\">" . lang('cancelcustomer') . "</a></p>";

			}

			// check if the user has manager privileges
			$result = $this->db->get_where('user', array('username' => $this->user), 1);
			//$query = "SELECT * FROM user WHERE username='$user'";
			//$result = $DB->Execute($query) or die ("$l_queryfailed");
			$myresult = $result->row();
			$manager = $myresult->manager;

			if ($dependent == false OR $manager == 'y') {
				// show the regular cancel form for non carrier dependent and for managers
				// ask if they are sure they want to cancel this customer
				print "<br><br>";
				print "<h4>" . lang('areyousurecancel') .": $this->account_number</h4>";
				print "<table cellpadding=15 cellspacing=0 border=0 width=720>";
				print "<td align=right width=240>";

				// if they hit yes, this will sent them into the delete.php file and remove the service on their next billing anniversary

				print "<form style=\"margin-bottom:0;\" action=\"" . $this->url_prefix . "index.php/customer/whycancel\" method=post>";
				print "<input name=whycancel type=submit value=\"". lang('yes') . "\" class=smallbutton></form></td>";

				// if they hit no, send them back to the service edit screen

				print "<td align=left width=240>";
				print "<form style=\"margin-bottom:0;\" action=\"" . $this->url_prefix . "index.php/customer\" method=post>";
				print "<input name=done type=submit value=\" ". lang('no') . " \" class=smallbutton>";
				print "</form></td>";

				// if they hit Remove Now, send them to delete.php and remove the 
				// service on the next available work date, the next valid billing date

				print "<td align=left width=240>";
				print "<form style=\"margin-bottom:0;\" action=\"" . $this->url_prefix . "index.php/customer/whycancel/now\" method=post>";
				print "<input name=whycancel type=submit value=\"". lang('remove_now') . "\" class=smallbutton>";  
				print "</form></td>";

				print "</table>";
				print "</blockquote>";
			}

		}
		else 
		{
			$this->module_model->permission_error();
		}
	}


	/*
	 * -------------------------------------------------------------------------
	 *  asks the user why this customer is canceling their account
	 *  optionally add a /now to the input to cancel today intead of anniversary date
	 * -------------------------------------------------------------------------
	 */


	/*
	 * -------------------------------------------------------------------------
	 *  marks a customer record as canceled and moves their services to history
	 *  optionally add a /now to the input to cancel today intead of anniversary date
	 * -------------------------------------------------------------------------
	 */
	public function delete($now = NULL)
	{
		// check permissions
		$permission = $this->module_model->permission($this->user, 'customer');

		if ($permission['remove'])
		{
			// load the models for functions we use
			$this->load->model('billing_model');			
			$this->load->model('ticket_model');			
			$this->load->model('log_model');			
			$this->load->model('service_model');			

			// get the cancel reason input
			$cancel_reason = $this->input->post('cancel_reason');


			// set the removal date correctly for now or later
			if ($now == "on") {
				// they should be removed as immediately as possible
				//so use the next billing date as the removal date
				$removal_date = $this->billing_model->get_nextbillingdate();
			} else {
				// figure out the customer's current next billing anniversary date
				$query = "SELECT b.next_billing_date FROM customer c " .
					"LEFT JOIN billing b ON c.default_billing_id = b.id ".
					"WHERE c.account_number = '$this->account_number'";
				$result = $this->db->query($query) or die ("$query $l_queryfailed");
				$myresult = $result->row_array();
				$next_billing_date = $myresult['next_billing_date'];

				// split date into pieces
				$datepieces = explode('-', $next_billing_date);

				$myyear = $datepieces[0];
				$mymonth = $datepieces[1]; 
				$myday = $datepieces[2]; 

				// removal date is normally the anniversary billing date
				$removal_date  = $next_billing_date;

				// today's date
				$today  = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d"), date("Y")));
				// if the next billing date is less than today, remove them next available day
				if ($removal_date < $today) 
				{
					$removal_date = $this->billing_model->get_nextbillingdate();
				}
			}

			// figure out all the services that the customer has and delete each one.
			$query = "SELECT * FROM user_services 
				WHERE account_number = '$this->account_number' AND removed <> 'y'";
			$result = $this->db->query($query) or die ("query failed");
			foreach ($result->result_array() as $myserviceresult) 
			{
				$userserviceid = $myserviceresult['id'];
				$this->service_model->delete_service($userserviceid,'canceled',
						$removal_date);
				$this->log_model->activity($this->user,$this->account_number,
						'delete','service',$userserviceid,'success');
			}

			// set cancel date and removal date of customer record
			$query = "UPDATE customer ".
				"SET cancel_date = CURRENT_DATE, ". 
				"cancel_reason = '$cancel_reason' ".
				"WHERE account_number = '$this->account_number'";
			$result = $this->db->query($query) or die ("query failed");

			// set next_billing_date to NULL since it normally won't be billed again
			$query = "UPDATE billing ".
				"SET next_billing_date = NULL ". 
				"WHERE account_number = '$this->account_number'";
			$result = $this->db->query($query) or die ("query failed");   

			// get the text of the cancel reason to use in the note
			$query = "SELECT * FROM cancel_reason " . 
				"WHERE id = '$cancel_reason'";
			$result = $this->db->query($query) or die ("query failed");
			$myresult = $result->row_array();
			$cancel_reason_text = $myresult['reason'];

			// add cancel ticket to customer_history
			// if they are carrier dependent, send a note to
			// the billing_noti
			$desc = lang('canceled') . ": $cancel_reason_text";
			$this->ticket_model->create_ticket($this->user, NULL, 
					$this->account_number, 'automatic', $desc);

			// get the billing_id for the customer's payment_history
			$query = "SELECT default_billing_id FROM customer " . 
				"WHERE account_number = '$this->account_number'";
			$result = $this->db->query($query) or die ("$l_queryfailed");
			$myresult = $result->row_array();
			$default_billing_id = $myresult['default_billing_id'];

			// add a canceled entry to the payment_history
			$query = "INSERT INTO payment_history ".
				"(creation_date, billing_id, status) ".
				"VALUES (CURRENT_DATE,'$default_billing_id','canceled')";
			$paymentresult = $this->db->query($query) or die ("$l_queryfailed");

			// log this customer being canceled/deleted
			$this->log_model->activity($this->user,$this->account_number,
					'cancel','customer',0,'success');

			// redirect them to the customer page	
			redirect('/customer');

		}
		else 
		{
			$this->module_model->permission_error();
		}


	}

	public function rerun()
	{
	if ($pallow_modify)    
	{       
		include('./modules/billing/rerun.php');    
	}  else permission_error();
	}

	/*
	 * --------------------------------------------------------------------------------
	 *  ask the user if they are sure they want to uncancel this customer
	 * --------------------------------------------------------------------------------
	 */
	public function turnoff()
	{
		
	if ($pallow_modify)    
	{       
		include('./modules/billing/turnoff.php');    
	}  else permission_error();
	}

	
	/*
	 * --------------------------------------------------------------------------------
	 *  perform the uncancel by undeleting the customer record and associated things
	 * --------------------------------------------------------------------------------
	 */


	public function resetaddr()
	{
		// reset the address to the current customer address
	    if ($pallow_modify)
    	{
       		include('./modules/billing/resetaddr.php');
    	} else permission_error();
	}
	


	public function cancelwfee()
	{
		if ($pallow_modify)
		{
			include('./modules/billing/cancelwfee.php');
		}  else permission_error();
	}


	public function collections()
	{
		if ($pallow_modify)
		{
			include('./modules/billing/collections.php');
		}  else permission_error();
	}


	public function waiting()
	{
		if ($pallow_modify)
		{
			include('./modules/billing/waiting.php');
		}  else permission_error();
	}


	public function authorized()
	{
		if ($pallow_modify)
		{
			include('./modules/billing/authorized.php');
		}  else permission_error();
	}


	public function asciiarmor()
	{
		if ($pallow_modify)
		{
			include('./modules/billing/asciiarmor.php');
		}  else permission_error();
	}


	public function nsf()
	{
		if ($pallow_modify)
		{
			include('./modules/billing/nsf.php');
		}  else permission_error();
	}


	public function receipt()
	{
		if ($pallow_modify)
		{
			include('./modules/billing/receipt.php');
		}  else permission_error();
	}

	public function deletepayment()
	{
		if ($pallow_modify)
		{
			include('./modules/billing/deletepayment.php');
		}  else permission_error();
	}


	public function createinvoice()
	{
		if ($pallow_modify)
		{
			include('./modules/billing/createinvoice.php');
		}  else permission_error();
	}


	public function cancelnotice()
	{
		if ($pallow_modify)
		{
			include('./modules/billing/cancelnotice.php');
		}  else permission_error();
	}


	public function shutoffnotice()
	{
		if ($pallow_modify)
		{
			include('./modules/billing/shutoffnotice.php');
		}  else permission_error();
	}


	public function collectionsnotice()
	{
		if ($pallow_modify)
		{
			include('./modules/billing/collectionsnotice.php');
		}  else permission_error();
	}



}

/* End of file customer */
/* Location: ./application/controllers/customer.php */