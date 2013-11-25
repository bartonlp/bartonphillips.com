<?php

include_once('tbs_class.php') ;
include_once('tbs_plugin_html.php'); // Plug-in for selecting HTML items.

$TBS = new clsTinyButStrong ;
$TBS->LoadTemplate('tbs_us_examples_form.htm') ;

$typelist = array('<other>'=>'-','Mister'=>'Mr','Madame'=>'Mme','Missis'=>'Ms') ; 
$TBS->MergeBlock('typeblk',$typelist) ; 

if (!isset($_POST)) $_POST=&$HTTP_POST_VARS ;
if (!isset($_POST['x_type'])) { 
  $x_type = '-' ; 
  $x_name = '' ; 
  $x_subname = '' ; 
  $msg_text = 'Enter your information and click on [Validate].' ; 
  $msg_color = '#0099CC' ; //blue
} else { 
  $msg_text = '' ;
  $msg_body = array();
	$x_type = $_POST['x_type'] ;
	$x_name = $_POST['x_name'] ;
	$x_subname = $_POST['x_subname'] ; 
  if (trim($x_type)=='-')   $msg_body[] = ' your gender' ; 
  if (trim($x_name)=='')    $msg_body[] = ' your last name' ; 
  if (trim($x_subname)=='') $msg_body[] = ' your first name' ; 
  if (count($msg_body)==0) {
    $msg_text = 'Thank you.' ; 
    $msg_color = '#336600' ; //green
	} else {
    $msg_text = 'Please enter' . join($msg_body, ' and');
    $msg_color = '#990000' ; //red
	}
} 

$TBS->Show() ;

?>
