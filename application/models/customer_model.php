<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 
 * Customer model access to customer table data
 * 
 * @author pyasi
 *
 */

class Customer_Model extends CI_Model
{
	function __construct()
    {
        parent::__construct();
    }
    
    function sidebar($account_number)
    {
    	// show customer account number, name, and company for sidebar
		$query = "SELECT name,company FROM customer ".
	  		"WHERE account_number = $this->account_number";
		$result = $this->db->query($query) or die ("$l_queryfailed");
		$myresult = $result->row();
		$data['account_number'] = $this->account_number;
		$data['acct_name'] = $myresult->name;
		$data['acct_company'] = $myresult->company;
		
		return $data;
    }
    
    function record($account_number)
    {
		// get the customer information
  		$query = "SELECT * FROM customer WHERE account_number = $account_number";
  		$result = $this->db->query($query) or die ("customer info $l_queryfailed");
  		$myresult = $result->row();	
  		
  		// Put values into an array to return

  		return array (
  		'signup_date' => $myresult->signup_date,
  		'name' => $myresult->name,
  		'company' => $myresult->company,
  		'street' => $myresult->street,
  		'city' => $myresult->city,
  		'state' => $myresult->state,
  		'zip' => $myresult->zip,
  		'country' => $myresult->country,
  		'phone' => $myresult->phone,
  		'alt_phone' => $myresult->alt_phone,
  		'fax' => $myresult->fax,
  		'source' => $myresult->source,
  		'contactemail' => $myresult->contact_email,
  		'secret_question' => $myresult->secret_question,
  		'secret_answer' => $myresult->secret_answer,
		'default_billing_id' => $myresult->default_billing_id,
		'cancel_date' => $myresult->cancel_date,
		'account_manager_password' => $myresult->account_manager_password,
  		'cancel_reason_id' => $myresult->cancel_reason,
  		'notes' => $myresult->notes);	
    }
	
}