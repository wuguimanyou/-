<?php
  header("Content-type: text/html; charset=utf-8"); 
  require('../../../config.php');
  require('../../../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
  require('../../../back_init.php');   
 
  $user_id = $configutil->splash_new($_GET["user_id"]); 
  $parent_id = $configutil->splash_new($_GET["parent_id"]);
  $pagenum = $configutil->splash_new($_GET["pagenum"]);



  $link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
  mysql_select_db(DB_NAME) or die('Could not select database');
  mysql_query("SET NAMES UTF8");
  require('../../../proxy_info.php');
  $query = "select is_consume from promoters where isvalid=true and status=1 and user_id = ".$user_id;
  $result = mysql_query($query) or die('Query failed: ' . mysql_error());  
  while ($row = mysql_fetch_object($result)) { 
	$is_consume = $row->is_consume;
	break;
 }
 $query_shareholder="select a_name,b_name,c_name,d_name from weixin_commonshop_shareholder where isvalid=true and customer_id=".$customer_id;
 $result_shareholder=mysql_query($query_shareholder) or die('query_shareholder:'.mysql_error());
 while ($row=mysql_fetch_object($result_shareholder)){
	$a_name=$row->a_name;
	$b_name=$row->b_name;
	$c_name=$row->c_name;
	$d_name=$row->d_name;
} 
?>
<html>
<head>
<link rel="stylesheet" type="text/css" href="../../../common/css_V6.0/content.css">
<link rel="stylesheet" type="text/css" href="../../../common/css_V6.0/content<?php echo $theme; ?>.css">	
<link rel="stylesheet" type="text/css" href="../../Common/css/Base/pay_set/allinpay_set.css">
<link rel="stylesheet" type="text/css" href="../../Common/css/Users/promoter/add_qrsell_account.css">
<script type="text/javascript" src="../../../common/js/jquery-2.1.0.min.js"></script>
<title>修改股东分红等级</title>
<meta http-equiv="content-type" content="text/html;charset=UTF-8">

</head>
<body>
<div class="WSY_content">
	<div class="WSY_columnbox">
	<div class="WSY_column_header">
			<div class="WSY_columnnav">
				<a class="white1">修改股东分红等级</a>
			</div>
		</div>
		<form action="save_change_shareholder.php?customer_id=<?php echo $customer_id_en ?>&user_id=<?php echo $user_id; ?>&parent_id=<?php echo $parent_id; ?>&ois_consume=<?php echo $is_consume; ?>&pagenum=<?php echo $pagenum; ?>"  id="keywordFrm" method="post">
			<div class="WSY_remind_main">
				
				<dl class="WSY_remind_dl02"> 
					<dt>等级列表：</dt>
					<dd>
							  <select name="is_consume" id="is_consume">
				 <option value="0" <?php if($is_consume==0){ ?>selected<?php } ?>>无股东权利</option>
			     <option value="1" <?php if($is_consume==1){ ?>selected<?php } ?>><?php echo $d_name;?></option>
				 <option value="2" <?php if($is_consume==2){ ?>selected<?php } ?>><?php echo $c_name;?></option>
				 <option value="3" <?php if($is_consume==3){ ?>selected<?php } ?>><?php echo $b_name;?></option>
				 <option value="4" <?php if($is_consume==4){ ?>selected<?php } ?>><?php echo $a_name;?></option>
			  </select>	 		
					</dd>
				</dl>	
				
		<div class="submit_div">
			<input type="button" class="WSY_button" value="提交" onclick="submitV(this);" style="cursor:pointer;">
			<input type="button" class="WSY_button" value="取消" onclick="document.location='promoter.php?customer_id=<?php echo $customer_id_en ?>';">
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
	  document.getElementById("keywordFrm").submit();
}</script>
<?php 

mysql_close($link);

?>
</html>