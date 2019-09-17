<?php
  header("Content-type: text/html; charset=utf-8"); 
  require('../../../config.php');
  require('../../../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
  require('../../../back_init.php');   

  $user_id = $configutil->splash_new($_GET["user_id"]);
  $parent_id =$configutil->splash_new($_GET["parent_id"]);

  $link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
  mysql_select_db(DB_NAME) or die('Could not select database');
  mysql_query("SET NAMES UTF8");
  require('../../../proxy_info.php');

   $sql = "SELECT agent_name,agent_price,agent_discount from weixin_commonshop_applyagents where isvalid=true and user_id=".$user_id;
   $query = mysql_query($sql);
   while($row=mysql_fetch_object($query)){
   	$agent_name 	= $row->agent_name;//申请代理的级别

   	$agent_price	= $row->agent_price;//代理价格
   	$agent_discount = $row->agent_discount;//代理折扣
   }
   //查找自己的名字、微信名称、代数、推荐人编号
   $query_u = "select name,weixin_name,generation,parent_id from weixin_users where isvalid=true and id=".$user_id." limit 0,1";
  $result_u=mysql_query($query_u)or die('Query failed'.mysql_error());
  while($row=mysql_fetch_object($result_u)){
  	$u_name = $row->name;//名字
  	$u_weixin_name = $row->weixin_name;//微信名称
  	$u_generation = $row->generation;//代数
  	$u_parent_id = $row->parent_id;//推荐人编号
  }
$query = "select id,agent_price,agent_detail,is_showdiscount from weixin_commonshop_agents where isvalid=true and customer_id=".$customer_id;
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
$agent_price=""; 	
$agent_detail="";
$is_showdiscount=0;
while ($row = mysql_fetch_object($result)) {
    $agent_price=$row->agent_price;		//代理商和价格
	$agent_detail=$row->agent_detail;	//代理说明
	$is_showdiscount=$row->is_showdiscount;//是否在代理商申请页面显示折扣
}
$pricearr = explode(",",$agent_price);

$len =  count($pricearr);
$diy_num = $len;





?>
<html>
<head>
<link rel="stylesheet" type="text/css" href="../../../common/css_V6.0/content.css">
<link rel="stylesheet" type="text/css" href="../../../common/css_V6.0/content<?php echo $theme; ?>.css">
<link rel="stylesheet" type="text/css" href="../../Common/css/Base/pay_set/allinpay_set.css">
<link rel="stylesheet" type="text/css" href="../../Common/css/Users/promoter/add_qrsell_account.css">
<script type="text/javascript" src="../../../common/js/jquery-2.1.0.min.js"></script>
<title>修改级别</title>
<meta http-equiv="content-type" content="text/html;charset=UTF-8">

</head>
<body>
<div class="WSY_content">
	<div class="WSY_columnbox">
	<div class="WSY_column_header">
			<div class="WSY_columnnav">
				<a class="white1">修改级别</a>
			</div>
		</div>
		<form action="save_changeagent.php?customer_id=<?php echo $customer_id_en ?>&user_id=<?php echo $user_id; ?>&parent_id=<?php echo $parent_id; ?> ?>"  id="formagent" method="post" >
		<input type="hidden" name="fromw" value="<?php echo $fromw ;?>"/>
		<input type="hidden" name="user_id" value="<?php echo $user_id ;?>"/>
		<input type="hidden" name="parent_id" value="<?php echo $parent_id ;?>"/>
			<div class="WSY_remind_main">
				
				<dl class="WSY_remind_dl02"> 
					<dt>代理商修改级别：</dt>
					<dd>

				
			    <select id="agent_select" name="agent_select" class="agent_select" >
						<?php 
						for($i=0;$i<$len;$i++){
						   $varr= $pricearr[$i];
						   if(empty($varr)){
							  continue;
						   }
						   $vlst = explode("_",$varr);
						   
						   $type = $vlst[0];
						   if(empty($vlst[1])){
							   continue;
						   }
						   $name = $vlst[1];
						   $value = $vlst[2];
						   $discount = $vlst[3];
						?>
					   <option value=<?php echo $pricearr[$i];?> ><?php echo $name;?> 费用:<?php echo $value;?>元 <?php if($is_showdiscount==1){?>折扣:<?php echo $discount;?>%<?php }?></option>
					<?php }?> 
					</select>           	
					</dd>
				</dl>	
				
		<div class="submit_div">
			<input type="button" class="WSY_button" value="提交" onclick="submitV(this);" style="cursor:pointer;">
			<input type="button" class="WSY_button" value="取消" onclick="document.location='agent.php?customer_id=<?php echo $customer_id_en ?>';">
		</div>
	
	
	</form>
	</div>
</div> 	
<script type="text/javascript" src="../../../common/js_V6.0/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="../../../common/js_V6.0/content.js"></script>
<script type="text/javascript" src="../../Common/js/Base/pay_set/allinpay_set.js"></script>

</body>
<script>
function submitV(){
	

	  		document.getElementById("formagent").submit();
	  




var type = $("#agent_select").val();
if(type==1){
	$(".agent_select").show();
}else{
	$(".agent_select").hide();
}

var type = $("#agent_select").val();
if(type==1){
	$(".agent_select").show();
}else{
	$(".agent_select").hide();
}


function changeType(value){
	if(value==1){
		$(".agent_select").show();
	}else{
		$(".agent_select").hide();
	}
}
}



</script>
<?php 

mysql_close($link);

?>
</html>