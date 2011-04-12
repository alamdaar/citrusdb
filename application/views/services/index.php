<?php   
// Copyright (C) 2002-2010  Paul Yasi (paul at citrusdb.org)
// read the README file for more information

/*----------------------------------------------------------------------------*/
// Check for authorized accesss
/*----------------------------------------------------------------------------*/
if(constant("INDEX_CITRUS") <> 1){
  echo "You must be logged in to run this.  Goodbye.";
  exit;	
}

if (!defined("INDEX_CITRUS")) {
  echo "You must be logged in to run this.  Goodbye.";
  exit;
}

if (!isset($base->input['history'])) { $base->input['history'] = ""; }
if (!isset($base->input['vendor'])) { $base->input['vendor'] = ""; }
if (!isset($base->input['fieldassets'])) { $base->input['fieldassets'] = ""; }
if (!isset($base->input['category'])) { $base->input['category'] = ""; }

// GET Variables
$history = $base->input['history'];
$vendor = $base->input['vendor'];
$fieldassets = $base->input['fieldassets'];
$category = $base->input['category'];

//session_start();
$account_number = $_SESSION['account_number'];
require_once('./include/permissions.inc.php');
if ($edit) {
  if ($pallow_modify) {
    include('./modules/services/edit.php');
  }  else permission_error();
 }
 else if ($create) {
   if ($pallow_create) {
     include('./modules/services/create.php');
   } else permission_error();
 }
 else if ($delete) {
   if ($pallow_remove) {
     include('./modules/services/delete.php');
   } else permission_error();
 }
 else if ($fieldassets) {
   if ($pallow_create) {
     include('./modules/services/fieldassets.php');
   } else permission_error();
 }
 else if ($history) {
   if ($pallow_view) {
     include('./modules/services/history.php');
   }
 }
 else if ($vendor) {
   if ($pallow_view) {
     include('./modules/services/vendor_history.php');
   }
 }
 else if ($pallow_view) {

   // hide the Add Service function if the customer is canceled
   $query = "SELECT cancel_date FROM customer ".
     "WHERE account_number = $account_number AND cancel_date is NULL";
   $result = $DB->Execute($query) or die ("$l_queryfailed");
   $notcanceled = $result->RowCount();
   if ($notcanceled == 1) {
     echo "<a href=\"index.php?load=services&type=module&create=on\">".
       "[ $l_addservice ]</a>";
   }

   echo "&nbsp;&nbsp;".
     "<a href=\"index.php?load=services&type=module&history=on\">".
     "[ $l_history ]</a> &nbsp;&nbsp;&nbsp;&nbsp;";

   if ($category) {
     // print the showall tab as unactive
     echo "<a href=\"index.php?load=services&type=module\" style=\"font-weight: normal; border: 1px solid #eee; padding-left: 5px; padding-right: 5px; background-color: #eee;\">$l_showall</a> ";
   } else {
     // print the showall tab as active
     echo "<a href=\"index.php?load=services&type=module\" style=\"font-weight: normal; border: 1px solid #ccd; padding-left: 5px; padding-right: 5px; background-color: #ccd;\">$l_showall</a> ";     
   }

   // show links to limit listing to certain categories of services
   // assigned to this customer
   $query = "SELECT DISTINCT category FROM user_services AS user, ".
     "master_services AS master ".
     "WHERE user.master_service_id = master.id ".
     "AND user.account_number = '$account_number' AND removed <> 'y' ".
     "ORDER BY category";
   $DB->SetFetchMode(ADODB_FETCH_ASSOC);
   $result = $DB->Execute($query) or die ("$l_queryfailed");
   while ($myresult = $result->FetchRow()) {
     $categoryname = $myresult['category'];
     if ($category == $categoryname) {
       echo "<a href=\"index.php?load=services&type=module&".
	 "category=$categoryname\" style=\"font-weight: normal; border: 1px solid #ccd; padding-left: 5px; padding-right: 5px; background-color: #ccd;\">$categoryname</a> \n";
     } else {
       echo "<a href=\"index.php?load=services&type=module&".
	 "category=$categoryname\" style=\"font-weight: normal; border: 1px solid #eee; padding-left: 5px; padding-right: 5px; background-color: #eee;\">$categoryname</a> \n";       
     }
   }
   
   echo "<table cellpadding=0 border=0 cellspacing=0 width=720><td valign=top>".
     "<table cellpadding=3 cellspacing=1 border=0 width=720>".
     "<td bgcolor=\"#ccccdd\"><b>$l_id</b></td>".
     "<td bgcolor=\"#ccccdd\"><b>$l_service</b></td>".
     "<td bgcolor=\"#ccccdd\"><b>$l_detail1</b></td>".
     "<td bgcolor=\"#ccccdd\"><b>$l_detail2</b></td>".
     "<td bgcolor=\"#ccccdd\"><b>$l_price</b></td>".
     "<td bgcolor=\"#ccccdd\"><b>$l_freq</b></td>".
     "<td bgcolor=\"#ccccdd\"><b>$l_billingid</b></td>".
     "<td bgcolor=\"#ccccdd\"><b>$l_salesperson</b></td><td></td>";

   // select all the user information in user_services and connect it with the 
   // master_services description and cost

   // check if they asked for just a specific category
   if ($category) {
     //echo "<tr><td colspan=9 bgcolor=\"#ccccdd\"><b style=\"font-size: 14pt;\">$l_category: $category</b></td><tr>";
     
     $query = "SELECT user.*, master.service_description, master.options_table, ".
       "master.pricerate, master.frequency, master.support_notify, ".
       "master.organization_id master_organization_id ".
       "FROM user_services AS user, master_services AS master ".
       "WHERE user.master_service_id = master.id ".
       "AND user.account_number = '$account_number' AND removed <> 'y' ".
       "AND master.category = '$category' ".
       "ORDER BY user.usage_multiple DESC, master.pricerate DESC";
   } else {
     $query = "SELECT user.*, master.service_description, master.options_table, ".
       "master.pricerate, master.frequency, master.support_notify, ".
       "master.organization_id master_organization_id ".
       "FROM user_services AS user, master_services AS master ".
       "WHERE user.master_service_id = master.id ".
       "AND user.account_number = '$account_number' AND removed <> 'y' ".
       "ORDER BY user.usage_multiple DESC, master.pricerate DESC";
   }
   
   $DB->SetFetchMode(ADODB_FETCH_ASSOC);
   $result = $DB->Execute($query) or die ("$l_queryfailed");

   // Print HTML table of user_services
   while ($myresult = $result->FetchRow()) {
     // select the options_table to get data for the details column
     
     $options_table = $myresult['options_table'];
     $id = $myresult['id'];
     if ($options_table <> '') {
       // get the data from the options table and put into variables
       $query = "SELECT * FROM $options_table WHERE user_services = '$id'";
       $DB->SetFetchMode(ADODB_FETCH_NUM);
       $optionsresult = $DB->Execute($query) or die ("$l_queryfailed");
       $myoptions = $optionsresult->fields;
       if (count($myoptions) >= 3) { 
         $optiondetails = $myoptions[2]; 
       } else { 
         $optiondetails = ''; 
       }
       if (count($myoptions) >= 4) { 
         $optiondetails2 = $myoptions[3]; 
       } else {
         $optiondetails2 = '';
       }
     } else {
       $optiondetails = '';
       $optiondetails2 = '';
     }
     $master_service_id = $myresult['master_service_id'];
     $service_organization_id = $myresult['master_organization_id'];
     $start_datetime = $myresult['start_datetime'];
     $billing_id = $myresult['billing_id'];
     $pricerate = $myresult['pricerate'];
     $usage_multiple = $myresult['usage_multiple'];
     $frequency = $myresult['frequency'];
     $salesperson = $myresult['salesperson'];
     $service_description = $myresult['service_description']; // from the LEFT JOINED master_services table
     $support_notify = $myresult['support_notify']; // from the LEFT JOINED master_services table

     // get the data from the billing tables to compare service and billing frequency
     $query = "SELECT * FROM billing b ".
       "LEFT JOIN billing_types t ON b.billing_type = t.id ".
       "LEFT JOIN general g ON g.id = b.organization_id ".
       "WHERE b.id = '$billing_id'";
     $DB->SetFetchMode(ADODB_FETCH_ASSOC);
     $freqoutput = $DB->Execute($query) or die ("$l_queryfailed");
     $freqresult = $freqoutput->fields;
     $billing_freq = $freqresult['frequency'];
     $billing_organization_id = $freqresult['organization_id'];
     $org_name = $freqresult['org_name'];
     // multiply the pricerate and the usage_multiple to get the price to show
     $totalprice = sprintf("%.2f",$pricerate * $usage_multiple);

     print "\n<tr onMouseOver='h(this);' onmouseout='deh(this);' onmouseup='window.location.href=\"index.php?load=services&type=module&edit=on&userserviceid=$id&servicedescription=$service_description&optionstable=$options_table&editbutton=Edit\";' bgcolor=\"#ddddee\">";
     print "\n".
       "<td>$id</td>".
       "<td>$service_description</td>".
       "<td>$optiondetails</td>".
       "<td>$optiondetails2</td>".
       "<td>$totalprice</td>".
       "<td>$frequency</td>".
       "<td>$billing_id ($org_name)</td>".
       "<td>$salesperson</td><td>";
     if ($frequency > $billing_freq) { 
       print "<b>$l_fixbillingfrequencyerror</b>";
     };
     if ($service_organization_id <> $billing_organization_id) {
       print "<b>$l_orgmismatch</b>";
     }
     print "<form style=\"margin-bottom:0;\" action=\"index.php\" method=post>";
     print "<input type=hidden name=load value=services>";
     print "<input type=hidden name=type value=module>";
     print "<input type=hidden name=edit value=on>";
     print "<input type=hidden name=userserviceid value=\"$id\">";
     print "<input type=hidden name=servicedescription value=\"$service_description\">";
     print "<input type=hidden name=optionstable value=\"$options_table\">";
     print "<input name=editbutton type=submit value=\"$l_edit\" ".
       "class=smallbutton></form>";

     print "<form style=\"margin-bottom:0;\" action=\"index.php\" method=post>";
     print "<input type=hidden name=load value=support>";
     print "<input type=hidden name=type value=module>";
     print "<input type=hidden name=serviceid value=\"$id\">";     
     if ($support_notify) {
       print "<input name=openticket type=submit value=\"$l_notify $support_notify\" ".
	 "class=smallbutton></form></td></tr>";  	
     } else {
       print "<input name=openticket type=submit value=\"$l_openticket\" ".
	 "class=smallbutton></form></td></tr>";     
     }

     // check for taxes for this service
     $mytaxoutput = checktaxes($DB, $id);
     echo $mytaxoutput;

   }
   
   print "</table></td></table></form>";

 } else permission_error();



// query the taxes and fees that this customer has
function checktaxes($DB, $user_services_id) {
  global $lang;
  include ("$lang");  
  
  $query = "SELECT ts.id ts_id, ts.master_services_id ts_serviceid, ".
    "ts.tax_rate_id ts_rateid, ms.id ms_id, ".
    "ms.service_description ms_description, ms.pricerate ms_pricerate, ".
    "ms.frequency ms_freq, tr.id tr_id, tr.description tr_description, ".
    "tr.rate tr_rate, tr.if_field tr_if_field, tr.if_value tr_if_value, ".
    "tr.percentage_or_fixed tr_percentage_or_fixed, ".
    "us.master_service_id us_msid, us.billing_id us_bid, us.removed us_removed, ".
    "us.account_number us_account_number, te.account_number te_account_number, ".
    "te.tax_rate_id te_tax_rate_id, te.customer_tax_id te_customer_tax_id, ".
    "te.expdate te_expdate ".
    "FROM taxed_services ts ".
    "LEFT JOIN user_services us ON us.master_service_id = ts.master_services_id ".
    "LEFT JOIN master_services ms ON ms.id = ts.master_services_id ".
    "LEFT JOIN tax_rates tr ON tr.id = ts.tax_rate_id ". 
    "LEFT JOIN tax_exempt te ON te.account_number = us.account_number ".
    "AND te.tax_rate_id = tr.id ".
    "WHERE us.removed = 'n' AND us.id = '$user_services_id'";
  
  $DB->SetFetchMode(ADODB_FETCH_ASSOC);
  $result = $DB->Execute($query) or die ("$l_queryfailed");
  
  // Print the taxes and fees that this customer's services have
  
  //echo "<p><b>$l_taxesandfees</b><br>".
  //  "<table cellpadding=0 border=0 cellspacing=0 width=720><td valign=top>".
  //  "<table cellpadding=2 cellspacing=1 border=0 width=720>".
  //  "<td bgcolor=\"#ccccdd\"><b>$l_service</b></td>".
  //  "<td bgcolor=\"#ccccdd\"><b>$l_taxdescription</b></td>".
  //  "<td bgcolor=\"#ccccdd\"><b>$l_taxamount</b></td>".
  //  "<td></td>";
  
  while ($taxresult = $result->FetchRow()) {
    $account_number = $taxresult['us_account_number'];
    $service_description = $taxresult['ms_description'];
    $tax_description = $taxresult['tr_description'];
    $freqmultiplier = $taxresult['ms_freq'];	
    $if_field = $taxresult['tr_if_field'];
    $if_value = $taxresult['tr_if_value'];
    $tax_rate_id = $taxresult['tr_id'];
    $percentage_or_fixed = $taxresult['tr_percentage_or_fixed'];
    $tax_exempt_rate_id = $taxresult['te_tax_rate_id'];
    $customer_tax_id = $taxresult['te_customer_tax_id'];
    $customer_tax_id_expdate = $taxresult['te_expdate'];
    
    // check the if_field before printing to see if the tax applies
    // to this customer
    if ($if_field <> '') 
      {
	$ifquery = "SELECT $if_field FROM customer ".
	  "WHERE account_number = '$account_number'";
	$DB->SetFetchMode(ADODB_FETCH_NUM);
	$ifresult = $DB->Execute($ifquery) or die ("$l_queryfailed");	
	$myifresult = $ifresult->fields;
	$checkvalue = $myifresult[0];
      } else {
      $checkvalue = TRUE;
      $if_value = TRUE;	
    }

    // check for any case, so lower them here
    $checkvalue = strtolower($checkvalue);
    $if_value = strtolower($if_value);
    
    if ($checkvalue == $if_value) {
      // check that they are not exempt
      if ($tax_exempt_rate_id <> $tax_rate_id) {
	// check if it is a percentage or fixed amount
	if ($percentage_or_fixed == "percentage") {
	  if ($freqmultiplier > 0) {
	    $tax_amount = $taxresult['tr_rate']
	      * $taxresult['ms_pricerate'] * $freqmultiplier;
	  } else {
	    $tax_amount = $taxresult['tr_rate']
	      * $taxresult['ms_pricerate'];
	  }
	} else {
	  // then it is a fixed amount not multiplied by the price
	  $tax_amount = $taxresult['tr_rate'];
	}
	
	// round the tax to two decimal places
	$tax_amount = sprintf("%.2f", $tax_amount);
	
	print "<tr><td></td>".
	  "<td bgcolor=\"#eeeeff\" style=\"font-size: 8pt;\" ".
	  "colspan=3>$tax_description</td>".
	  "<td bgcolor=\"#eeeeff\"  style=\"font-size: 8pt;\" ".
	  "colspan=4>$tax_amount</td>".
	  "<td bgcolor=\"#eeeeff\" style=\"font-size: 8pt;\">".
	  "<form style=\"margin-bottom:0;\" action=\"index.php\" method=post>".
	  "<input type=hidden name=load value=services>".
	  "<input type=hidden name=type value=module>".
	  "<input type=hidden name=edit value=on>".
	  "<input type=hidden name=taxrate value=\"$tax_rate_id\">".
	  "<input name=exempt type=submit value=\"$l_exempt\" ".
	  "class=smallbutton></form></td></tr>";
	
      } else {
	// print the exempt tax
	print "<tr style=\"font-size: 9pt;\"><td></td>".
	  "<td bgcolor=\"#eeeeff\" style=\"font-size: 8pt;\" ".
	  "colspan=3>$tax_description</td>".
	  "<td bgcolor=\"#eeeeff\" style=\"font-size: 8pt;\" ".
	  "colspan=4>$l_exempt: $customer_tax_id ".
	  "$customer_tax_id_expdate</td>".
	  "<td bgcolor=\"#eeeeff\" style=\"font-size: 8pt;\">".
	  "<form style=\"margin-bottom:0;\" action=\"index.php\" method=post>".
	  "<input type=hidden name=load value=services>".
	  "<input type=hidden name=type value=module>".
	  "<input type=hidden name=edit value=on>".
	  "<input type=hidden name=taxrate value=\"$tax_rate_id\">".
	  "<input name=notexempt type=submit value=\"$l_notexempt\" ".
	  "class=smallbutton></form></td></tr>";
      } // end if exempt tax

    } // end if_field

  } // end while

} // end checktaxes function


?>