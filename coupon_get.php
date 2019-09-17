<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
require('../common/utility_shop.php');

$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');

$user_id = -1;
if(!empty($_POST["user_id"])){
	$user_id = $configutil->splash_new($_POST["user_id"]);	
}
$customer_id = -1;
if(!empty($_POST["customer_id"])){
	$customer_id = $configutil->splash_new($_POST["customer_id"]);	
}
//送首次代金券
$Coupon_msg	 = new Utility_Coupon();
$data 		 = $Coupon_msg->create_coupon($customer_id,$user_id,-1,2);
echo $data;
mysql_close($link);	

?>