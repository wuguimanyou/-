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

//$ert_arr = json_encode($ert_arr);
//var_dump($ert_arr);
//var_dump($express_arr);
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>物流公司设置</title>
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
				$header = 3;
				include("head.php");
				
			?>			
        <!--权限管理代码开始-->
		<form action="express_company.class.php?customer_id=<?php echo $customer_id_en;?>&op=<?php echo $op;?>&tem_id=<?php echo $tem_id; ?>" method="post" id="myform" onsubmit = "return check();">
			<div class="WSY_data">
				<div class="WSY_competence">
					<p>物流公司名称：<input type="text" name="title"  id="title" value="<?php echo $title;?>"><i>长度为1~16位字符</i></p>					
				</div>
				<div class="WSY_text_input"><input class="WSY_button" type="button" id="formid" value="提交" onclick="check()"><br class="WSY_clearfloat"></div>
			</div>
		</form>
        <!--权限管理代码结束-->
	</div>
<script>
    
	// ---------提交------start
	var title_v = '<?php echo $title;?>';
	function check(){
		var title = document.getElementById('title').value;
		if( title == "" ){
			win_alert('请输入名称');
			return false;
		}
		document.getElementById("myform").submit();		
	}
		
</script>
</body>
</html>
