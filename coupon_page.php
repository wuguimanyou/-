<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
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
$start = 0;
if( $_POST["start"] != "" ){
	$start = $configutil->splash_new($_POST["start"]);	
}
$end = 5;
if( $_POST["end"] != "" ){
	$end = $configutil->splash_new($_POST["end"]);	
}

/*****************只显示未使用，未过期的代金卷*******************/

$keyid 		= -1;	// 代金劵id
$Money 		= 0;	//代金劵金额
$NeedMoney 	= 0;	//使用代金劵的限制金额
$deadline	= "1970-01-01";//截止日期
$type_str   = "仅限在线支付";//类型
$data 		= '';
$overload   = 0;	//是否继续加载 1:不加载 0:继续加载
$query = "SELECT id,Money,deadline,NeedMoney FROM weixin_commonshop_couponusers WHERE user_id=".$user_id." AND customer_id=".$customer_id." AND isvalid=true AND type=1 AND is_used=0 AND deadline >='".date("Y")."-".date("m")."-".date("d")." ".date("H").":".date("i").":".date("s")."'  order by id desc limit ".$start.",".$end."";
//echo $query;
$result = mysql_query($query) or die('W3663 Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
	$keyid 	   =  $row->id;	
	$deadline  =  $row->deadline;	
	$Money 	   = round($row->Money,2);
	$NeedMoney = round($row->NeedMoney,2);
	$overload  = 1 ;
	
	$data .='{
				"keyid": "'.$keyid.'",
				"deadline": "'.$deadline.'",
				"Money": "'.$Money.'",
				"NeedMoney": "'.$NeedMoney.'",
				"type_str": "'.$type_str.'"
			},';
	
}
$data=rtrim($data,',');
$data = '{ "coupon":['.$data.'],"overload":[{"status": '.$overload.'}]}';
echo $data;
mysql_close($link);	

?>