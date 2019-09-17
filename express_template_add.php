<?php
header("Content-type: text/html; charset=utf-8"); 
require('../../../config.php');
require('../../../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../../../back_init.php');
$link =    mysql_connect(DB_HOST,DB_USER, DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
mysql_query("SET NAMES UTF8");
require('../../../proxy_info.php');

$title = "";  //快递模板名称  
if(!empty($_GET["title"])){
	$title = $configutil->splash_new($_GET["title"]);
}
$tem_id = -1;  //快递模板ID
if(!empty($_GET["tem_id"])){
	$tem_id = $configutil->splash_new($_GET["tem_id"]);
}

$action = "";  //操作 add:新增 edit:修改
if(!empty($_GET["action"])){
	$action = $configutil->splash_new($_GET["action"]);
}
$op = 'add';
if($_GET["op"]){
	$op	=	$configutil->splash_new($_GET["op"]);	
}

$query = 'SELECT id,express_id FROM express_relation_t where isvalid=true and customer_id='.$customer_id.' and tem_id='.$tem_id.' and supply_id=-1';
$query = $query." order by id desc";
//echo $query;
$result= mysql_query($query) or die('Query failed: ' . mysql_error());
$ert_id  = -1;  //快递模板关联ID
$express_id  = -1;  //快递规则ID
$ert_arr = array();
$express_arr = array();
while ($row = mysql_fetch_object($result)) {
	$ert_id =  $row->id ;
	$express_id =  $row->express_id ;
	array_push($ert_arr,$ert_id);
	array_push($express_arr,$express_id);
}
//$ert_arr = json_encode($ert_arr);
//var_dump($ert_arr);
//var_dump($express_arr);
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>运费模板设置</title>
<link type="text/css" rel="stylesheet" rev="stylesheet" href="../../../css/css2.css" media="all">
<link href="../../../common/add/css/global.css" rel="stylesheet" type="text/css">
<link href="../../../common/add/css/main.css" rel="stylesheet" type="text/css">
<link href="../../../common/add/css/shop.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="../../../common/css_V6.0/content<?php echo $theme; ?>.css">
<link rel="stylesheet" type="text/css" href="../../../common/css_V6.0/content.css">
<script type="text/javascript" src="../../../js/tis.js"></script>
<script type="text/javascript" src="../../../common/utility.js"></script>
<script type="text/javascript" src="../../../common/js/jquery-2.1.0.min.js"></script>
<script type="text/javascript" src="../../../common/js_V6.0/content.js"></script> 
<style>
	label input[type="radio"]{
		width: auto;
  		height: auto;
	}
</style>
</head>

<body>   
	<!--内容框架-->
	<div class="WSY_content">
		<!--列表内容大框-->
		<div class="WSY_columnbox">
			<?php 
				//头部列表
				$header = 0;
				include("head.php");
				
			?>			
        <!--权限管理代码开始-->
		<form action="express_template.class.php?customer_id=<?php echo $customer_id_en;?>&op=<?php echo $op;?>&tem_id=<?php echo $tem_id?>" method="post" id="myform" onsubmit = "return check();">
			<div class="WSY_data">
				<div class="WSY_competence">
					<p>快递模板名称：<input type="text" name="title"  id="title" value="<?php echo $title;?>"><i>长度为1~16位字符</i></p>
					<!--列表头部切换开始-->
					<div class="WSY_competence_header">
						<h3 id="h3">全选<input id="checkAllChange" type="checkbox"></h3>
					</div>
					<!--列表头部切换结束-->
					<div>
						<ul id="all" class="WSY_competenceul">
						<?php 
						$query = 'SELECT id,name,price,is_include,region,cost,expressCode FROM weixin_expresses where isvalid=true and customer_id='.$customer_id;
						$query = $query." order by id desc";
						$result= mysql_query($query) or die('Query failed: ' . mysql_error());
						$keyid = -1;  //快递规则ID
						$name  = "";  //快递规则名称
						while ($row = mysql_fetch_object($result)) {
							$keyid =  $row->id ;
							$name  = $row->name;
						?>
							<dd>
							   <input type="checkbox" class="express_id" name="express_id[]" data-id="<?php echo $keyid?>" value="<?php echo $keyid?>"  <?php if(in_array($keyid,$express_arr)){echo 'checked';}?>/><lable><?php echo $name?></lable>
							</dd>
						<?php }	?>
						</ul>
					</div>
					
				
				</div>
				
					<div class="WSY_text_input01">
						<div class="WSY_text_input">
							<input class="WSY_button" type="button" id="formid" value="提交" onclick="check()">
						</div>
						<div class="WSY_text_input">
							<input type="button" class="WSY_button" value="取消" onclick="javascript:history.go(-1);">
						</div>
					</div>
				
			</div>
		</form>
        <!--权限管理代码结束-->
	</div>
<script>
    
	// ---------全选效果------start
	var check_num = 0;
	$(function(){
		$("#checkAllChange").click(function() { // 全选/取消全选
			
			if (this.checked == true) { 
				$(".express_id").each(function() { 
				this.checked = true; 
			
				}); 
			} else { 
				$(".express_id").each(function() { 
				this.checked = false; 
				}); 
			} 
		}); 

	}); 
	// ---------全选效果------end
	
	// ---------提交------start
	var title_v = '<?php echo $title;?>';
	function check(){
		var title = document.getElementById('title').value;
		if( title == "" ){
			win_alert('请输入名称');
			return false;
		}
		
		var express_ids = $('.express_id:checked').val();
		if(express_ids == null)
		{
			win_alert('请选择快递规则！');
			return false;
		}
	
		if(title == title_v){
			document.getElementById("myform").submit();
		}else{
			$.ajax({
				url: 'express_template.class.php?op=checkTitle',
				data: {title:title},
				type: 'post',
				dataType: 'json',
				success:function(res){
					console.log(res.status);
					if(res.status==1){
						win_alert('模板名称重名，请重新命名！');
						return false;
					}else{
						document.getElementById("myform").submit();
					}
				},
				error:function(er){
					
				}
			})
		}
		
		
	}
		
</script>
</body>
</html>
