<?php
//require('../logs.php');   
header("Content-type: text/html; charset=utf-8"); 
require('../../../config.php');
require('../../../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../../../back_init.php');
require('../../../common/utility.php');
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');

require('../../../proxy_info.php');
mysql_query("SET NAMES UTF8");
require('../../../common/utility_4m.php');
require('../../../common/tupian/CreateExpQR.php');
require('../../../auth_user.php');
$u4m = new Utiliy_4m();
$rearr = $u4m->is_4M($customer_id);

//是4m分销
$is_shopgeneral = $rearr[0]  ;
//厂家编号
$adminuser_id = $rearr[1] ;
//是否是厂家总店
$is_samelevel = $rearr[2] ;
//总店模板编号
$general_template_id = $rearr[3] ;
//总店商家编号
$general_customer_id = $rearr[4] ;

//是否本身就是厂家总店
//1：厂家总店； 2：代理商总店
$owner_general = $rearr[5] ;

$orgin_adminuser_id = $rearr[6] ;

//获取下级所有的权限控制 by @ye
$getAllSubcontrol = $u4m->getAllSubcontrol($adminuser_id);

//var_dump($getAllSubcontrol);
//查询商家是否有上传产品权限
$is_upload_pros = $u4m->check_cus_authority($customer_id,$getAllSubcontrol,1);

//查询商家是否有修改产品价格权限
$is_change_pros_price = $u4m->check_cus_authority($customer_id,$getAllSubcontrol,2);

/* echo $_SESSION['is_auth_user'].'==='.$_SESSION['user_id']; */
$stock_pidarr="";
if(!empty($_GET["stock_pidarr"])){		
	$stock_pidarr =$configutil->splash_new($_GET["stock_pidarr"]);
}
if(!empty($_GET["adminuser_id"])){	
	$adminuser_id = $configutil->splash_new($_GET["adminuser_id"]);
}
if(!empty($_GET["orgin_adminuser_id"])){
	$orgin_adminuser_id = $configutil->splash_new($_GET["orgin_adminuser_id"]);
}
if(!empty($_GET["owner_general"])){
	$owner_general = $configutil->splash_new($_GET["owner_general"]);
}

$description="";
$product_id=-1;
$product_name="";
$product_orgin_price=0;
$product_now_price=0;
$product_type_id=-1;
$product_introduce="";
$product_description="";
$specifications="";
$customer_service="";
$product_voice="";
$product_vedio="";
$product_unit="";
$product_asort=-1;
$product_isout=0;
$product_isnew=0;
$product_ishot=0;
$product_issnapup=0;
$product_is_free_shipping=0;  //是否包邮
$product_isvp =0;	//是否属于vp产品，1：是；0：否
$vp_score =0;	    //vp值,vp产品消费累积满多少vp值可以提现佣金
$product_tradeprices="";
$product_propertyids = "";
$product_storenum=1;
$product_need_score=0;

$product_cost_price=0; //供货价
$product_for_price=0; //成本价
$product_foreign_mark="";
$imgids="";

$pro_discount=0;
$pro_reward=-1;
$issell=0;
$type_ids="";
$default_imgurl="";
$class_imgurl="";
$product_asort_value=0;
$sell_count=0;
$show_sell_count=999;
$define_share_image_flag=0;
$supply_id=-1;
$product_id=-1;
$detail_template_type=1;
$define_share_image="";
$install_price = 0;
$weight = 0;
$product_weight = 0;
$nowprice_title="";
$pro_area = -1;

$agent_discount      = 0;//代理商折扣
$pro_card_level_id   = -1;//购买产品需要的会员卡等级ID
$isOpenAgent         = 0;//是否开启代理商
$isOpenInstall       = 0;//安装平台
$isOpenPublicWelfare = 0;//公益基金
$cashback            = -1;//返现金额
$cashback_r          = -1;//返现比例 
$is_identity         = 0;//产品是否需要身份证购买开关
$shop_is_identity    = 1;//身份证购买开关
$is_invoice 		 = 0;//发票开关

$is_Pinformation    =  0;//必填信息产品开关1：开 0：关
$freight_id    		= -1;//运费模板ID
$is_virtual    		=  0;//是否为虚拟产品 0:非虚拟产品,1:虚拟产品
$is_currency 		= 0;//是否购物币产品 0：不是（默认） 1：是
$is_guess_you_like 	= 0;//是否猜您喜欢产品 0：不是（默认） 1：是
$back_currency 		= 0;//购物币返佣金额
//$first_division 	= 0;//一级分佣金额
$express_type 		= 0;//邮费计费方式
$isscore 			= 0;//积分专区
$init_reward 		= 0;//分佣总比例
$issell		 		= 0;//是否开启分销
$detail_template_type= 0;//详情模板分类类型


$query ="select isOpenPublicWelfare,nowprice_title,isOpenInstall,isOpenAgent,pro_card_level,shop_card_id,is_identity,issell,detail_template_type,init_reward from weixin_commonshops where isvalid=true and customer_id=".$customer_id;
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
   $isOpenPublicWelfare  = $row->isOpenPublicWelfare;	//公益基金
   $isOpenInstall 		 = $row->isOpenInstall;	//安装平台
   $isOpenAgent 		 = $row->isOpenAgent;	//是否开启代理商
   $base_nowprice_title  = $row->nowprice_title;
   $pro_card_level		 = $row->pro_card_level;//购买产品需要会员卡级别开关
   $shop_card_id		 = $row->shop_card_id;//会员卡ID
   $shop_is_identity 	 = $row->is_identity;
   $issell               = $row->issell;
   $detail_template_type = $row->detail_template_type;
   $init_reward 		 = $row->init_reward;
}

$nowprice_title 	 = "现价";

	
$is_charitable        = 0;//慈善开关
$charitable_propotion = 0;//慈善最低比例
$query ="select is_charitable,charitable_propotion from charitable_set_t where isvalid=true and customer_id=".$customer_id;
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
	$is_charitable        = $row->is_charitable;
	$charitable_propotion = $row->charitable_propotion;
}
	
$is_QR = 0;
$buystart_time = "";
$countdown_time = "";
$product_type_parent_id = -1;
$donation_rate = 0;
if(!empty($_GET["product_id"])){
   $product_id = $configutil->splash_new($_GET["product_id"]);
 
   $query="select customer_id,name,donation_rate,orgin_price,asort_value,unit,default_imgurl,class_imgurl,cost_price,foreign_mark,pro_discount,pro_reward,now_price,need_score,type_id,introduce,description,specifications,customer_service,product_voice,product_vedio,asort,isout,isnew,ishot,issnapup,isvp,is_free_shipping,vp_score,storenum,tradeprices,propertyids,type_ids,sell_count,show_sell_count,define_share_image,is_supply_id,install_price,weight,agent_discount,nowprice_title,is_QR,pro_card_level_id,cashback,cashback_r,pro_area,buystart_time,countdown_time,is_identity,for_price,is_Pinformation,freight_id,is_virtual,is_invoice,is_currency,is_guess_you_like,back_currency,express_type,isscore from weixin_commonshop_products where isvalid=true and id=".$product_id;
  // file_put_contents('hello.txt',$query);
   $result = mysql_query($query) or die('Query failed1: ' . mysql_error());
   while ($row = mysql_fetch_object($result)) {
      $donation_rate               = $row->donation_rate;
      $product_name                = $row->name;
	  $product_orgin_price         = $row->orgin_price;
	  $product_now_price           = $row->now_price;
	  $product_unit                = $row->unit;
	  $product_type_id             = $row->type_id;
	  $product_introduce           = $row->introduce;
	  $product_description         = $row->description;
	  $specifications       	   = $row->specifications;
	  $customer_service            = $row->customer_service;
	  $product_voice           	   = $row->product_voice;
	  $product_vedio               = $row->product_vedio;
	  $product_asort               = $row->asort;
	  $product_isout               = $row->isout;
	  $is_QR                       = $row->is_QR;
	  $product_isnew               = $row->isnew;
	  $product_ishot               = $row->ishot;
	  $product_issnapup            = $row->issnapup;
	  $product_is_free_shipping    = $row->is_free_shipping;
	  $product_isvp                = $row->isvp;
	  $vp_score                    = $row->vp_score;
	  $product_tradeprices         = $row->tradeprices;
	  $product_propertyids         = $row->propertyids;
	  $product_storenum            = $row->storenum;
	  $pro_area                    = $row->pro_area;
	  $product_need_score          = $row->need_score;
	  $pro_discount                = $row->pro_discount;
	  $pro_reward                  = $row->pro_reward;
	  $customer_id                 = $row->customer_id;
	  $type_ids                    = $row->type_ids;
	  $default_imgurl              = $row->default_imgurl;
	  $class_imgurl                = $row->class_imgurl;
	  $buystart_time               = $row->buystart_time;//商品抢购开始时间
	  $countdown_time              = $row->countdown_time;//商品抢购结束时间
	  $product_cost_price          = $row->cost_price;
	  $product_for_price		   = $row->for_price;
	  $product_foreign_mark        = $row->foreign_mark;
	  $sell_count                  = $row->sell_count;
	  $show_sell_count             = $row->show_sell_count;
	  $product_asort_value         = $row->asort_value;
	  $define_share_image          = $row->define_share_image;
	  $supply_id                   = $row->is_supply_id;//供应商ID
	  $define_share_image_flag     = $define_share_image?1:0;
	  $install_price               = $row->install_price;
	  $product_weight              = $row->weight;//产品重量
	  $agent_discount              = $row->agent_discount;//代理商折扣
	  $nowprice_title              = $row->nowprice_title;//"现价"自定义名称
	  $pro_card_level_id           = $row->pro_card_level_id;//购买产品需要的会员卡等级ID
	  $cashback                    = $row->cashback;
	  $cashback_r                  = $row->cashback_r;
	  $is_identity                 = $row->is_identity;
	  $is_Pinformation			   = $row->is_Pinformation;//必填信息产品开关1：开 0：关
	  $freight_id			   	   = $row->freight_id;	   //运费模板ID
	  $is_virtual			       = $row->is_virtual?1:0;	   //是否为虚拟产品 0:非虚拟产品,1:虚拟产品
	  $is_invoice 				   = $row->is_invoice;	//发票开关
	  $is_currency 				   = $row->is_currency;//是否购物币产品
	  $is_guess_you_like 		   = $row->is_guess_you_like;//是否猜您喜欢产品
	  $back_currency			   = $row->back_currency;
	 // $first_division			   = $row->first_division;
	  $express_type			   	   = $row->express_type;
	  $isscore			   	   	   = $row->isscore;//积分专区
	  break;
  }

  $query_type = "select parent_id from weixin_commonshop_types where isvalid = true and id = ".$product_type_id;
   $result_type = mysql_query($query_type) or die('L163 : Query failed1: ' . mysql_error());
   if($row_type = mysql_fetch_object($result_type)){
	   $product_type_parent_id = $row_type->parent_id;
   }
   
  $query="select id from weixin_commonshop_product_imgs where isvalid=true and product_id=".$product_id;
  $result = mysql_query($query) or die('Query failed4: ' . mysql_error());
  while ($row = mysql_fetch_object($result)) {
     $imgid = $row->id;
	 $imgids = $imgids.$imgid."_";
  }
}


if($imgids!=""){
   $imgids = rtrim($imgids,"_");
}
$propertylst= new ArrayList();
if($product_propertyids!=""){
   $propertyarr = explode("_",$product_propertyids);
   $len = count($propertyarr);
   for($i=0;$i<$len;$i++){
       $propertyid= $propertyarr[$i];
	   $propertylst->add($propertyid);
   }
}

$pidpnames="";

$head = 10;
if(!empty($_GET["head"])){
    $head = $configutil->splash_new($_GET["head"]);
}
$pagenum = 1;
if(!empty($_GET["pagenum"])){
    $pagenum = $configutil->splash_new($_GET["pagenum"]);
}


$typeLst =  new ArrayList();
//$typeLst->Add($product_type_id);
$typeid_arr = explode(",",$type_ids);
$tlen = count($typeid_arr);
if($tlen>0){
	for($i=0;$i<$tlen; $i++){
		$stid = $typeid_arr[$i];
		$typeLst->Add($stid);
	}
}


//代理模式,分销商城的功能项是 266
$is_distribution=0;//渠道取消代理商功能
$is_disrcount=0;
$query1="select count(1) as is_disrcount from customer_funs cf inner join columns c where c.isvalid=true and cf.isvalid=true and cf.customer_id=".$customer_id." and c.sys_name='商城代理模式' and c.id=cf.column_id";
$result1 = mysql_query($query1) or die('W_is_disrcount Query failed: ' . mysql_error());  
while ($row = mysql_fetch_object($result1)) {
   $is_disrcount = $row->is_disrcount;
   break;
}
if($is_disrcount>0){
   $is_distribution=1;
}

//供应商模式,渠道开通与不开通
$is_supplierstr=0;//渠道取消供应商功能
$sp_count=0;//渠道取消供应商功能
$sp_query="select count(1) as sp_count from customer_funs cf inner join columns c where c.isvalid=true and cf.isvalid=true and cf.customer_id=".$customer_id." and c.sys_name='商城供应商模式' and c.id=cf.column_id";
$sp_result = mysql_query($sp_query) or die('W_is_supplier Query failed: ' . mysql_error());  
while ($row = mysql_fetch_object($sp_result)) {
   $sp_count = $row->sp_count;
   break;
}
if($sp_count>0){
   $is_supplierstr=1;
}

//扫码模式,渠道开通与不开通
$is_scancode=0;//渠道取消供应商功能
$sc_count=0;//渠道取消供应商功能
$sc_query="select count(1) as sc_count from customer_funs cf inner join columns c where c.isvalid=true and cf.isvalid=true and cf.customer_id=".$customer_id." and c.sys_name='商城扫码模式' and c.id=cf.column_id";
$sc_result = mysql_query($sc_query) or die('W_is_scancode Query failed: ' . mysql_error());  
while ($row = mysql_fetch_object($sc_result)) {
   $sc_count = $row->sc_count;
   break;
}
if($sc_count>0){
   $is_scancode=1;
}

$query_cashback="select count(1) as count_cashback from customer_funs cf inner join columns c where c.isvalid=true and cf.isvalid=true and cf.customer_id=".$customer_id." and c.sys_name='消费返现' and c.id=cf.column_id";
$is_opencashback=0; //是否开通了消费返现 0不开通 1开通
$count_cashback=0;
$result_cashback = mysql_query($query_cashback) or die('W_count_cashback Query failed: ' . mysql_error());  
while ($row = mysql_fetch_object($result_cashback)) {
   $count_cashback = $row->count_cashback;
   break;
}
if($count_cashback>0){
   $is_opencashback=1;
}

$query="select product_num from customers where isvalid=true and id=".$customer_id;
$result = mysql_query($query) or die('Query failed: ' . mysql_error());  	
while ($row = mysql_fetch_object($result)) {
   $product_num = $row->product_num;//最多上架商品数量
   break;
}
$query="select isout,count(1) as num from weixin_commonshop_products where isout=0 and isvalid=true and customer_id=".$customer_id;
$result = mysql_query($query) or die('Query failed: ' . mysql_error());  	
while ($row = mysql_fetch_object($result)) {
   $num = $row->num;//已经上架商品数量
   break;
}

$auth_user_id = -1;
if($_SESSION['is_auth_user']=='yes' && $_SESSION['user_id']){
	$auth_user_id = $_SESSION['user_id'];
}

/* $query = "select cb_condition from weixin_commonshop_cashback where isvalid=true and customer_id=".$customer_id." limit 0,1";
$result = mysql_query($query) or die('L324: ' . mysql_error());  	
while ($row = mysql_fetch_object($result)) {
   $cb_condition = $row->cb_condition;   //返现金额模式    0：固定金额  1：产品价格按比例
} */

$sql = "select * from weixin_commonshops_extend where isvalid=true and customer_id=".$customer_id;
$result1 = mysql_query($sql) or die('Query failed: ' . mysql_error());
while ($row1 = mysql_fetch_object($result1)) {
		$is_Pinformation_b=$row1->is_Pinformation;//必填信息大开关1：开 0：关
}

//全球分红奖励
$sql1 = "SELECT isOpenGlobal,Global_all FROM weixin_globalbonus where isvalid=true and customer_id=".$customer_id;
$res1 =  mysql_query($sql1) or die('Query failed: ' . mysql_error());
$row1=mysql_fetch_assoc($res1);
$isOpenGlobal = $row1['isOpenGlobal'];
if($isOpenGlobal==0){
	$globalbonus_pro = 0;
}else{
	$globalbonus_pro = $row1['Global_all'];
}

//查询是否开启团队奖励喝团队奖励
$query = "select is_team,is_shareholder from weixin_commonshops where isvalid=true and customer_id=".$customer_id." limit 0,1";
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
$is_team = 0; 
$is_shareholder = 0;
while ($row = mysql_fetch_object($result)) {
	$is_team 		= $row->is_team;//是否开启区域奖励
	$is_shareholder = $row->is_shareholder;
}	
//团队比例
$query_team = "SELECT team_all from weixin_commonshop_team where isvalid=true and customer_id=".$customer_id." limit 0,1";
$result_team = mysql_query($query_team);
while($row=mysql_fetch_assoc($result_team)){
	if($is_team==0){
		$team_all = 0;	
	}else{
		$team_all=$row['team_all'];
	}
	
}
//股东比例
$query_shareholder = "SELECT shareholder_all FROM weixin_commonshop_shareholder where isvalid=true and customer_id=".$customer_id;
$result_shareholder = mysql_query($query_shareholder);
while($row = mysql_fetch_assoc($result_shareholder)){
	if($is_shareholder==0){
		$shareholder_all = 0;
	}else{
		$shareholder_all = $row['shareholder_all'];
	}
	
}

$all = 1-($globalbonus_pro+$team_all+$shareholder_all);


$query = "select cb_condition,cashback,cashback_r from weixin_commonshop_cashback where isvalid=true and customer_id=".$customer_id." limit 0,1";
$result = mysql_query($query) or die('L39: '.mysql_error());
$cb_condition = 0;
$public_cashback = 0;
$public_cashback_r = 0;
while($row = mysql_fetch_object($result)){
	$cb_condition = $row->cb_condition;    //返现金额模式    0：固定金额  1：产品价格按比例
	$public_cashback = $row->cashback;
	$public_cashback_r = $row->cashback_r;
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>添加产品</title>
<link rel="stylesheet" type="text/css" href="../../../common/css_V6.0/content.css">

<link rel="stylesheet" type="text/css" href="../../../common/css_V6.0/content<?php echo $theme; ?>.css">
<link rel="stylesheet" type="text/css" href="../../Common/css/Product/product.css">
<link rel="stylesheet" type="text/css" href="../../Common/css/Mode/charitable/set_up.css">
<script type="text/javascript" src="../../../common/js_V6.0/assets/js/jquery.min.js"></script>
<script type="text/javascript" src="../../../common/utility.js"></script>
<script type="text/javascript" src="../../../common_shop/jiushop/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="../../../js/WdatePicker.js"></script><!--添加时间插件-->

<!--编辑器多图片上传引入开始--->
<script type="text/javascript" src="/weixin/plat/Public/js/jquery.dragsort-0.5.2.min.js"></script>
<script type="text/javascript" src="/weixin/plat/Public/swfupload/swfupload/swfupload.js"></script>
<script type="text/javascript" src="/weixin/plat/Public/swfupload/js/swfupload.queue.js"></script>
<script type="text/javascript" src="/weixin/plat/Public/swfupload/js/fileprogress.js"></script>
<script type="text/javascript" src="/weixin/plat/Public/swfupload/js/handlers.js"></script>

<style type="text/css">
.show_price{
	color:red;
}
.del{
	color:blue;
}
.del:hover{
	cursor:pointer;
}
</style>
<!--编辑器多图片上传引入结束--->
<script type="text/javascript">
Array.prototype.contains = function(item){
   return RegExp("\\b"+item+"\\b").test(this);
};
ppriceHash = new Hashtable();
var oldProArray = new Array();
var i = 0;
var supply_id = "<?php echo $supply_id; ?>";//供应商ID
<?php 
$query="select proids,orgin_price,now_price,storenum,need_score,cost_price,foreign_mark,unit,weight,for_price from weixin_commonshop_product_prices where product_id=".$product_id;
$result = mysql_query($query) or die('Query failed7: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {

   $proids = $row->proids;
   $orgin_price = $row->orgin_price;
   $now_price = $row->now_price;
   $storenum = $row->storenum;
   $need_score = $row->need_score;
   $cost_price = $row->cost_price;
   $for_price = $row->for_price;
   $foreign_mark = $row->foreign_mark;
   $unit = $row->unit;
   $weight = $row->weight;
?>
  
  var proids = "<?php echo $proids; ?>";
  var orgin_price = '<?php echo $orgin_price; ?>';
  var now_price = '<?php echo $now_price; ?>';
  var storenum = '<?php echo $storenum; ?>';
  var need_score = '<?php echo $need_score; ?>';
  var cost_price = '<?php echo $cost_price; ?>';
  var for_price = '<?php echo $for_price; ?>';
  var foreign_mark = "<?php echo $foreign_mark; ?>";
  var unit = "<?php echo $unit; ?>";
  var weight = "<?php echo $weight; ?>";
 
  ppriceHash.add(proids,orgin_price+"_"+now_price+"_"+storenum+"_"+need_score+"_"+cost_price+"_"+foreign_mark+"_"+unit+"_"+weight+"_"+for_price);
  if(proids!=""){
	  var proArr = proids.split("_");
	  for(var j = 0 ; j < proArr.length ; j++){
		  if(i == 0){
			  oldProArray[j] = new Array();
		  }
		  if(oldProArray[j].contains(proArr[j]) == false){
			 var length = oldProArray[j].length;
			 oldProArray[j][length] = proArr[j];
		  }
	  }
  }
  i++;
<?php }
?>
</script>
<style type="text/css">
	
</style>
</head>

<body>
	<!--内容框架-->
	<div class="WSY_content">

		<!--列表内容大框-->
		<div class="WSY_columnbox">
			<?php require('public/head.php');?>

  <!--关注用户开始-->
  <form id="frm_product" class="r_con_form" method="post" action="save_product.php?head=<?php echo $head?>&customer_id=<?php echo $customer_id_en; ?>&pagenum=<?php echo $pagenum; ?>&adminuser_id=<?php echo $adminuser_id; ?>&owner_general=<?php echo $owner_general; ?>&orgin_adminuser_id=<?php echo $orgin_adminuser_id; ?>" enctype="multipart/form-data">
	<div class="WSY_data">
		
		 <dl class="WSY_bulkbox w90px">
        	<dt>产品名称：</dt>
            <dd><input type="text" name="name" id="name" value="<?php echo $product_name;?>"></dd>
        </dl>
        <dl class="WSY_bulkdl w90px">
        	<dt style="margin-top:5px;">隶属分类：</dt>
            <dd class="WSY_ddbulk types">
				<?php
				$query_type_parent = "select id,name from weixin_commonshop_types where isvalid = true and customer_id = ".$customer_id." and parent_id = -1";
				//echo $query_type_parent;
				$result_type_parent = mysql_query($query_type_parent) or die("L161 query error : ".mysql_error());
				while($row_type_parent = mysql_fetch_object($result_type_parent)){ 
					$ptid = $row_type_parent->id;
					$ptname = $row_type_parent->name;
				
					$query_type_child = "select id,name from weixin_commonshop_types where isvalid = true and customer_id = ".$customer_id." and parent_id = ".$ptid;
					$result_type_child = mysql_query($query_type_child) or die("L169 query error : ".mysql_error());
					$childs = mysql_num_rows($result_type_child);
				?>
				<div class="WSY_divbulk">
                	<h1 class="noeh1">
						<?php if($childs <= 0){ ?>
							<input type="checkbox" name="types" value="<?php echo $ptid;?>" id="ck_p<?php echo $ptid;?>" 
							<?php if($typeLst->Contains($ptid)){ echo "checked";}?>  onclick="add_relation_pros('<?php echo $ptid?>',this)">
						<?php }?><label for="ck_p<?php echo $ptid;?>"><?php echo $ptname;?></label>
                    </h1>
					<?php if($childs > 0){?>
                    <ul class="twoul">
						<?php
						while($row_type_child = mysql_fetch_object($result_type_child)){ 
							$pcid = $row_type_child->id;
							$pcname = $row_type_child->name;
						?>
                    	<li><input type="checkbox" name="types" value="<?php echo $pcid;?>" id="ck_p<?php echo $ptid;?>_c<?php echo $pcid;?>"
						<?php if($typeLst->Contains($pcid)){ echo "checked"; }?> onclick="add_relation_pros('<?php echo $pcid?>',this)">
						<label for="ck_p<?php echo $ptid;?>_c<?php echo $pcid;?>" >
						<?php echo $pcname;?></label></li>
                        <?php 
					}?>
                    </ul>
					<?php 
					}?>
                </div>
			<?php }?>
            </dd>
        </dl>
        
        <div class="WSY_bulkbox02">
			<div class="WSY_bulkbox_top">
				<div class="WSY_bulkboximgbox">
					<div class="WSY_bulkboximg" id="WSY_bulkboximg">
						<p>封面图片</p>
						<iframe src="iframe_images_defaultproduct.php?customer_id=<?php echo $customer_id_en; ?>&product_id=<?php echo $product_id; ?>&default_imgurl=<?php echo $default_imgurl; ?>" height=200 width=1024 FRAMEBORDER=0 SCROLLING=no></iframe>
					</div>
					<input type=hidden name="default_imgurl" id="default_imgurl" value="<?php echo $default_imgurl ; ?>" />
				</div>
		   
				<div class="WSY_bulkboximgbox">
					<div class="WSY_bulkboximg" id="WSY_bulkboximg">
						<p>分类页图片</p>
						<iframe src="iframe_class_images_defaultproduct.php?customer_id=<?php echo $customer_id_en; ?>&product_id=<?php echo $product_id; ?>&default_imgurl=<?php echo $default_imgurl; ?>" height=200 width=1024 FRAMEBORDER=0 SCROLLING=no></iframe>
					</div>
					<input type=hidden name="class_imgurl" id="class_imgurl" value="<?php echo $class_imgurl ; ?>" />
				</div>
			</div>
        </div>
		<div class="WSY_bulkbox02">
            <div class="WSY_bulkboximgbox">
                <div class="WSY_bulkboximg" id="WSY_bulkboximg" style="height:260px">
                    <p>产品图片</p>
                    <dl style="margin:30px 0px 0px 0px">
                       <iframe id="frmProImgs" src="iframe_images.php?customer_id=<?php echo $customer_id_en; ?>&product_id=<?php echo $product_id; ?>&detail_template_type=<?php echo $detail_template_type; ?>" height="400" width="1024" FRAMEBORDER=0 SCROLLING=no></iframe>
                    </dl>
                </div>
				<input type=hidden name="imgids" id="imgids" value="<?php echo $imgids ; ?>" />
				<!--<div class="WSY_bulkboximg" style="height:260px">
                    <p>简短介绍</p>
                    <dl class="WSY_bulkdl">
						<span style="color:red;font-size: 14px;">（建议控制在30个字符以内）</span>
                    	<dd><textarea name="introduce" class="briefdesc"><?php echo $product_introduce; ?></textarea></dd>
                    </dl>
                </div>-->
            </div>
		</div>
          
		  <dl class="WSY_bulkdl w90px">
                <dt style="margin-top:20px;">产品属性：</dt>
                <ul class="WSY_bulkul wdw">
				<?php
				/*
				父级 ：1没有关联  2选中的分类关联（父级）属性  3选中的分类关联（子级）属性   显示
				子级： 1没有关联  2选中的分类关联（子级） 3产品选择的属性 显示
				不显示的场景：已经关联分类而未被选中的属性
				*/
					/*********** 查询选中的分类中是否有关联的属性 start	***********/
				
					$new_typeid_arr = array();
					for($i=0;$i<count($typeid_arr);$i++){		//查找子类已经父类
						if($typeid_arr[$i] =='' || $typeid_arr[$i] == 0 || $typeid_arr[$i] == null){	//清除无效的数值
							continue;
						}
						array_push($new_typeid_arr,$typeid_arr[$i]);		//重新组合数组
						
						//查找父类，不排除属性关联的是父类分类
						$type_parent_id = -1;
						$query = "select parent_id from weixin_commonshop_types where isvalid=true and customer_id=".$customer_id." and id=".$typeid_arr[$i]."";
						//echo $query;
						$result = mysql_query($query) or die('Query failed: ' . mysql_error());  
						while ($row = mysql_fetch_object($result)){			
							$type_parent_id 		= $row->parent_id;
						}
						if($type_parent_id>0){								//当找到父类则加入数组
							array_push($new_typeid_arr,$type_parent_id);
						}
						
					}
					
					$typeid_arr_str = implode(',',$new_typeid_arr) ;		//数组转字符串
										
					if(count($new_typeid_arr)>0){							//当选择了分类才查找关联的属性
						
						$product_sel_type_pros = array();
						$extends_id = -1;									
						$extends_pros_id = -1;									
						$query3 = "select id,relation_type_id,pros_id from weixin_commonshop_pros_extends where isvalid=true and customer_id=".$customer_id." and relation_type_id in (".$typeid_arr_str.")";
						//echo $query3;
						$result3 = mysql_query($query3) or die('L628 Query failed: ' . mysql_error());  
						while ($row3 = mysql_fetch_object($result3)){			
							$extends_id = $row3->id;
							$extends_pros_id = $row3->pros_id;
							array_push($product_sel_type_pros,$extends_pros_id);
						}
						
					}
											
					/*********** 查询选中的分类中是否有关联的属性 end	***********/	
					
					/*查找已选的属性和父类属性 start*/
					
					$new_pros_arr = array();				//所选的属性以及父类属性
					for($i=0;$i<count($propertyarr);$i++){
						if($propertyarr[$i] =='' || $propertyarr[$i] == 0 || $propertyarr[$i] == null){	//清除无效的数值
							continue;
						}
						array_push($new_pros_arr,$propertyarr[$i]);		//重新组合数组
						
						//查找父类
						$pro_parent_id = -1;
						$query = "select parent_id from weixin_commonshop_pros where isvalid=true and customer_id=".$customer_id." and id=".$propertyarr[$i]."";
						//echo $query;
						$result = mysql_query($query) or die('Query failed: ' . mysql_error());  
						while ($row = mysql_fetch_object($result)){			
							$pro_parent_id 		= $row->parent_id;
						}
						if($pro_parent_id>0){								//当找到父类则加入数组
							array_push($new_pros_arr,$pro_parent_id);
						}
						
					}
						//var_dump($new_pros_arr)	;			
					/*查找已选的属性和父类属性 end*/
					
					/*************************查找父子类属性 start**************************/			
					$pros_array = array();	
					$parent_id = -1;		//父类ID
					$parent_name = '';		//父类名称
					 if($supply_id<0){
						  $query="select id,name from weixin_commonshop_pros where isvalid=true and parent_id=-1 and customer_id=".$customer_id." and supply_id<0";
					 }else{
						  $query="select id,name from weixin_commonshop_pros where isvalid=true and parent_id=-1 and customer_id=".$customer_id." and supply_id=".$supply_id;
					 }
					 $result = mysql_query($query) or die('L643 Query failed11: ' . mysql_error());
				     while ($row = mysql_fetch_object($result)) {
					    $parent_id = $row->id;
					    $parent_name = $row->name;
						
						$is_parent_type = 0;									
						if(in_array($parent_id,$product_sel_type_pros)){		//查询所选的分类中是否已经关联属性，0表示没有，1表示有
							$is_parent_type = 1;
						}
						
						//查询该属性是否关联分类
						$extends_id = -1;																	
						$query3 = "select id,relation_type_id from weixin_commonshop_pros_extends where isvalid=true and customer_id=".$customer_id." and pros_id=".$parent_id."";
						$result3 = mysql_query($query3) or die('L654 Query failed: ' . mysql_error());  
						while ($row3 = mysql_fetch_object($result3)){			
							$extends_id = $row3->id;
						}
												
						//累加选中的子类分类中是否有关联属性，0表示无，1表示有
					   $tem_array = array();
					   $sum_is_child_type = 0;		
					   $query2="select id,name from weixin_commonshop_pros where isvalid=true and parent_id=".$parent_id;
					   $result2 = mysql_query($query2) or die('L663 Query failed12: ' . mysql_error());
					   while ($row2 = mysql_fetch_object($result2)) {
						   $p_id = $row2->id;
						   $p_name = $row2->name;
						   
						    $is_child_type = 0;
							if(in_array($p_id,$product_sel_type_pros)){			//查询所选的分类中是否已经关联属性，有则显示
								$is_child_type = 1;
							}
							$sum_is_child_type += $is_child_type;
							
							//查询该属性是否关联分类，无则默认显示	
							$extends_id2 = -1;																
							$query4 = "select id,relation_type_id from weixin_commonshop_pros_extends where isvalid=true and customer_id=".$customer_id." and pros_id=".$p_id."";
							$result4 = mysql_query($query4) or die('L675 Query failed: ' . mysql_error());  
							while ($row4 = mysql_fetch_object($result4)){			
								$extends_id2 = $row4->id;
							}
						   
								
							  array_push($tem_array,
								  array(
								   'child_id'=>$p_id,
								   'child_name'=>$p_name,
								   'is_child_type'=>$is_child_type,
								   'extends_id2'=>$extends_id2,
								   )
							   );
						   
					   }
						//echo $is_parent_type.'_'.$extends_id.'_'.$sum_is_child_type.'_'.$parent_id.'<br>';
						
						//if($is_parent_type==1 || $extends_id<0 ||$sum_is_child_type ==1){				//显示的场景：该父类属性关联了选择的分类，该父类属性未被关联，该子类被选择的分类关联
						if(!($is_parent_type==0 && $extends_id>0 && $sum_is_child_type ==0) || in_array($parent_id,$new_pros_arr) ){				//显示的场景：该父类属性关联了选择的分类，该父类属性未被关联，该子类被选择的分类关联
					?>	
					<dd pro_parent_id="<?php echo $parent_id;?>">
						<div class="WSY_cloropbox">
							<span><?php echo $parent_name; ?></span><input type="hidden" name="hidden_parent" value="<?php echo $parent_id;?>"/>
							<div class="WSY_clorop">
						<?php	
								//遍历所有子类属性
								foreach($tem_array as $key => $value){
									$p_id 				= $value['child_id'];
									$p_name 			= $value['child_name'];
									$p_is_child_type 	= $value['is_child_type'];
									$p_extends_id2 		= $value['extends_id2'];
									
								
								//echo $p_is_child_type.'_'.$p_extends_id2.'_'.$p_id.'<br>';								
							   //if($p_is_child_type==1 || $p_extends_id2<0){			//显示场景：该子级未被关联，该子级被选择的分类关联
							   if(!($p_is_child_type==0 && $p_extends_id2>0) || in_array($p_id,$new_pros_arr)){
						?>
									<p><input type="checkbox" data_name="prop_<?php echo $parent_id; ?>" data_pid="<?php echo $p_id; ?>" data_text="<?php echo $p_name; ?>" data_parent="<?php echo $parent_id; ?>" value="<?php echo $p_id; ?>" <?php if($propertylst->Contains($p_id)){ $pidpnames = $pidpnames.$p_id.",".$parent_name."(".$p_name."),".$parent_id."_"; ?>checked<?php } ?> name="ptids" onclick="chkPro();">
											<?php echo $p_name; ?>
											<input type="hidden" id="<?php echo $p_id; ?>" value="<?php echo $p_name; ?>"/>
									</p>
						<?php	   
							   } 
							}
								 /* array_push($pros_array,array(
								 'parent_id'=>  $parent_id,
								 'parent_name'=>  $parent_name,
								 'is_parent_type'=>  $is_parent_type,
								 'child'=>  $tem_array
								  ));*/ 
						?>	
							</div>
						</div>
					</dd>
					
					<?php
					
						}	  
					 }
					 //var_dump($pros_array);
					 /*************************查找父子类属性 end**************************/
				   ?>
		
					 <?php 
                     $pidpnames = rtrim($pidpnames,"_");
					?>	
					<input type=hidden name="pidpnames" id="pidpnames" value="<?php echo $pidpnames; ?>" />
					<input type=hidden name="propertyids" id="propertyids" value="<?php echo $product_propertyids; ?>"/>		
                </ul>
            </dl>
		  
           
			<dl class="WSY_bulkdl WSY_bulkdldt w90px" style="display:none;">
        	<dt style="width:90px;">"现价"自定义:</dt>
				<dd style="line-height:24px"><input type="text" name="define_price_tag" id="now_money" value="<?php echo $nowprice_title?$nowprice_title:"现价"; ?>" class="form_input num_check" size="5" maxlength="10">（默认 "现价"）</dd>
			</dl>
            <dl class="WSY_bulkdl w90px">
                <dt>产品价格：</dt>
                <div class="WSY_bulkul01box" id="div_proprices">
                    <div class="WSY_bulkul01">
                        <span class="WSY_red">现价和成本一致,则不返佣</span><br>
                        <li>原价￥：<input type="text" name="orgin_price" value="<?php echo $product_orgin_price; ?>" class="form_input num_check" size="5" maxlength="10"></li>
                        <li><?php if($nowprice_title){echo $nowprice_title;}else if($base_nowprice_title){echo $base_nowprice_title;}else{echo "现价";}?>:￥<input type="text" name="now_price" value="<?php echo $product_now_price; ?>" class="form_input num_check" size="5" maxlength="10"></li>
                       
                        <li>单位：<input type="text" name="unit" value="<?php echo $product_unit; ?>" class="form_input" size="5" maxlength="10"></li>
                        <li>重量：<input type="text" name="weight" value="<?php echo $product_weight; ?>" class="form_input" size="5" maxlength="10">KG</li>
                        <li>库存：<input type="text" name="storenum" value="<?php echo $product_storenum; ?>" class="form_input num_check" size="5" maxlength="10"></li>
						
                    </div>
                     <div class="WSY_bulkul02box">
                     	<div class="WSY_bulkul02">
                        	<span class="WSY_red">属性价格</span>
                            <ul class="WSY_bulkul01">
                                <li class="WSY_bulkuli_red">颜色(红色)</li>
                                <li>原价￥：<input type="text" value="150"></li>
                                <li>现价￥：<input type="text" value="100"></li>
                                <li>成本：<input type="text" value="0"></li>
                                <li>单位：<input type="text" value=""></li>
                                <li>所需积分：<input type="text" value="0"></li>
                                <li>库存：<input type="text" value="10000"></li>
                                <li>外部标识：<input type="text" value=""></li>
                            </ul>
							 <ul class="WSY_bulkul01">
                                <li class="WSY_bulkuli_red">颜色(红色)</li>
                                <li>原价￥：<input type="text" value="150"></li>
                                <li>现价￥：<input type="text" value="100"></li>
                                <li>成本：<input type="text" value="0"></li>
                                <li>单位：<input type="text" value=""></li>
                                <li>所需积分：<input type="text" value="0"></li>
                                <li>库存：<input type="text" value="10000"></li>
                                <li>外部标识：<input type="text" value=""></li>
                            </ul>
                        </div>
                    </div>
                </div>
				<input type=hidden id="hide_proprice" name="hide_proprice" value="" />
				<input type=hidden id="hide_selpros" name="hide_selpros" value="" />
            </dl>
		
			<dl class="WSY_bulkdl WSY_bulkdl03 w90px">
                <dt>运费模板：</dt>
                <dd class="dd_margin">
					<select name="freight_id">
						<option value="-1" >无</option>
						<?php 
							$express_id   = -1;
							$express_name = "";
							$query = "SELECT id,title FROM express_template_t where customer_id=".$customer_id." and isvalid=true ";
							//默认平台运费模板
							$express_sql = " and supply_id=-1 ";
							if( 0 < $supply_id ){
								//供应商运费模板
								$express_sql = " and supply_id=".$supply_id;
							}
							$query .= $express_sql;														
							$result = mysql_query($query) or die('Query failed: ' . mysql_error());  
							while ($row = mysql_fetch_object($result)) {
								$express_id	   = $row->id;
								$express_name  = $row->title;
							
					    ?>	
						<option value="<?php echo $express_id;?>" <?php if($express_id == $freight_id){ echo 'selected="selected"'; } ?> ><?php echo $express_name;?></option>
						<?php 	
						}								
						?>
					</select>
				</dd>
				<dd><a href="freight_log.php?customer_id=<?php echo $customer_id_en;?>&pid=<?php echo $product_id;?>"><img style="width:20px;" title="查看修改日志" src="../../Common/images/Base/basicdesign/icon-log.png"/></a></dd>
            </dl>

			<dl class="WSY_bulkdl WSY_bulkdl03 w90px">
                <dt>邮费计费方式：</dt>
                <dd>
					<dd class="dd_margin"><input type="radio" name="express_type" id="express_type_1" value="1" <?php if(1==$express_type){echo 'checked=checked';}?>><label for="express_type_1">按件数</label></dd>
					<dd class="dd_margin"><input type="radio" name="express_type" id="express_type_2" value="2" <?php if(2==$express_type){echo 'checked=checked';}?>><label for="express_type_2">按重量</label></dd>
				</dd>
            </dl>
			<!-- <dl class="WSY_bulkdl WSY_bulkdldt w90px">
                <dt>一级分佣金额：</dt>
                <dd style="line-height:20px">
					<input name="first_division" id="first_division" type="text" value="<?php echo $first_division;?>" style="border:none;background-color:#FBFBFB;text-align:center;" readonly><span style="margin-top:0">元</span>
				</dd>
            </dl> -->
			<?php if( $is_charitable ){ ?>
			<dl class="WSY_bulkdl WSY_bulkdldt w90px">
                <dt>捐赠比率：</dt>
                <dd>
					<input name="donation_rate" id="donation_rate" type="text" value="<?php echo $donation_rate;?>">
					<i class="WSY_red">比例范围(<?php echo $charitable_propotion ?>~1)</i>
				</dd>
            </dl>
			<?php } ?>
            <dl class="WSY_bulkdl WSY_bulkdldt w90px">
                <dt>真实销售量：</dt>
                <dd style="line-height:20px"><?php echo $sell_count; ?></dd>
            </dl>
            <dl class="WSY_bulkdl WSY_bulkdldt w90px">
                <dt>虚拟销售量：</dt>
                <dd><input type="text" id="show_sell_count" name="show_sell_count" value="<?php echo $show_sell_count; ?>"><i class="WSY_red">显示销量=虚拟销售量+真实销售量</i></dd>
            </dl>
            <dl class="WSY_bulkdl WSY_bulkdl03 w90px">
                <dt>产品分享图片：</dt>
                <dd class="dd_margin"><input type="radio" id="define_share_image_flag_0" name="define_share_image_flag" value="0" <?php if($define_share_image_flag==0){ ?>checked<?php } ?> >
					<label for="define_share_image_flag_0">默认</label></dd>
                <dd class="dd_margin"><input type="radio" id="define_share_image_flag_1" name="define_share_image_flag" value="1" <?php if($define_share_image_flag==1){ ?>checked<?php } ?>>
					<label for="define_share_image_flag_1">自定义</label></dd>
                <!--上传文件代码开始-->
                <div class="uploader white"  id="define_share_image_div" <?php if(!$define_share_image_flag) echo "style='display:none'"; ?>>
                    <input type="text" class="filename" readonly />
                    <input type="button" name="file" class="button" value="上传..."/>
                    <input type="file" size="30" name='new_define_share_image' id='new_define_share_image'/>
					<input type='hidden' name='now_define_share_image' id='now_define_share_image' value='<?php echo $define_share_image;?>'>
                </div>
                <!--上传文件代码结束-->
            </dl>
			
			<dl class="WSY_bulkdl WSY_bulkdl03 w90px" <?php if(false == $is_scancode){ echo 'style="display:none"';  $is_QR=0;}  ?> >
				<dt>二维码核销：</dt>
				<dd class="dd_margin"><input type="radio" value="0" <?php if($is_QR==0){ ?>checked<?php } ?> name="is_QR" id="is_QR_0" ><label for="is_QR_0">关</label></dd>
				<dd class="dd_margin"><input type="radio" value="1" <?php if($is_QR==1){ ?>checked<?php } ?> name="is_QR" id="is_QR_1"><label for="is_QR_1">开</label></dd>
			</dl>
			<?php 
			if( $shop_is_identity ){
			?>
			<dl class="WSY_bulkdl WSY_bulkdl03 w90px">
				<dt>身份证购买：</dt>
				<dd class="dd_margin"><input type="radio" value="0" <?php if( 0 == $is_identity ){ ?> checked <?php } ?> name="is_identity" id="is_identity_0"><label for="is_QR_0">关</label></dd>
				<dd class="dd_margin"><input type="radio" value="1" <?php if( 1 == $is_identity ){ ?> checked <?php } ?> name="is_identity" id="is_identity_1"><label for="is_QR_1">开</label></dd>
			</dl>
			<?php } ?>	
			<dl class="WSY_bulkdl WSY_bulkdl03 w90px">
                <dt>产品所属城市：</dt>
                <dd class="dd_margin">
					<select name="city_id">
						<option value="-1" >无</option>
						<?php 
					
					$query_area='select id,province,city,area from weixin_commonshop_product_area where isvalid=true and customer_id='.$customer_id;
					$result_area = mysql_query($query_area) or die('Query_area failed: ' . mysql_error());  
					while ($row_area = mysql_fetch_object($result_area)) {				
						$province=  $row_area->province;
						$city=   $row_area->city;
						$city_id=   $row_area->id;
					?>	
						<option value="<?php echo $city_id;?>" <?php if($city_id == $pro_area){ echo 'selected="selected"'; } ?> ><?php echo $province.$city;?></option>
					<?php 	
					}								
					?>
					</select>
				</dd>
            </dl>
			  
            <dl class="WSY_bulkdl w90px">
                <dt>其他属性：</dt>
                <dd class="WSY_bulkdldd dd_margin"><input type="checkbox" <?php if($product_isout){?>checked<?php } ?> id="chk_isout" onclick="changeOut(this);"><label for="chk_isout">下架</label></dd>
                <dd class="WSY_bulkdldd dd_margin"><input type="checkbox" <?php if($product_isnew){?>checked<?php } ?> id="chk_isnew" onclick="changeNew(this);"><label for="chk_isnew">新品</label></dd>
                <dd class="WSY_bulkdldd dd_margin"><input type="checkbox" <?php if($product_ishot){?>checked<?php } ?> id="chk_ishot" onclick="changeHot(this);"><label for="chk_ishot">热卖</label></dd>
				<dd class="WSY_bulkdldd dd_margin"><input type="checkbox" <?php if($product_issnapup){?>checked<?php } ?> id="chk_issnapup" onclick="changeSnap(this);"><label for="chk_issnapup">抢购</label><img style="width:12px;" id="snapup_product" src="../../Common/images/Base/help.png"></dd>
                <dd class="WSY_bulkdldd dd_margin"><input type="checkbox" <?php if($product_isvp){?>checked<?php } ?> id="chk_isvp" onclick="changeVp(this);"><label for="chk_isvp" style="float:left">vp产品</label><img style="width:12px;" id="vp_product" src="../../Common/images/Base/help.png"></dd>
                <dd class="WSY_bulkdldd dd_margin"><input type="checkbox" <?php if($is_virtual){?>checked<?php } ?> id="chk_virtual" onclick="changeVirtual(this);"><label for="chk_virtual" style="float:left">虚拟产品</label><img style="width:12px;" id="product_virtual" src="../../Common/images/Base/help.png"></dd>
                <dd class="WSY_bulkdldd dd_margin"><input type="checkbox" <?php if($is_currency){?>checked<?php } ?> id="chk_currency" onclick="changeCurrency(this);"><label for="chk_currency">购物币产品</label></dd>
				<dd class="WSY_bulkdldd dd_margin"><input type="checkbox" <?php if($is_guess_you_like){?>checked<?php } ?> id="chk_guess_you_like" onclick="changeGuess_you_like(this);"><label for="chk_guess_you_like">猜您喜欢产品</label><img style="width:12px;" id="product_guess_you_like" src="../../Common/images/Base/help.png"></dd>
				<dd class="WSY_bulkdldd dd_margin"><input type="checkbox" <?php if($product_is_free_shipping){?>checked<?php } ?> id="chk_freeshipping" onclick="changeFree_shipping(this);"><label for="chk_freeshipping">包邮</label></dd>
				<dd class="WSY_bulkdldd dd_margin"><input type="checkbox" <?php if($isscore){?>checked<?php } ?> id="chk_isscore" onclick="changeisscore(this);"><label for="chk_isscore">积分专区</label></dd>
            </dl>
            
			<dl class="WSY_bulkdl" id="back_currency" style="<?php if($is_currency){?>display:block;<?php }else{?>display:none;<?php }?>">
            	<dt style="width:120px">返佣购物币：</dt>
            	<dd><input type="text" value="<?php echo $back_currency;?>" name="back_currency" id="backcurrency" style="width:150px;height:20px;padding-left:5px;border-radius:2px;margin-top:0;margin-right:5px;border:1px solid #ccc;"></dd>
            </dl>
            
			<dl class="WSY_bulkdl WSY_bulkdldt w90px snap_up">
                <dt style="width:120px">商品抢购开始时间：</dt>
                <dd><input type="text" style="width:200px" id="buystart_time" name="buystart_time" value="<?php echo $buystart_time; ?>"  onclick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm',minDate:'2015-10-25 10:00',maxDate:'2018-10-25 21:30'});" ></dd>
            </dl>
			<dl class="WSY_bulkdl WSY_bulkdldt w90px snap_up">
                <dt style="width:120px">商品抢购结束时间：</dt>
                <dd><input type="text" style="width:200px" id="countdown_time" name="countdown_time" value="<?php echo $countdown_time; ?>"  onclick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm',minDate:'2015-10-25 10:00',maxDate:'2018-10-25 21:30'});" ></dd>
            </dl>		
			<dl class="WSY_bulkdl w90px" id="vp_score">
				<dt>vp值：</dt>
                <dd class="WSY_bulkdldd dd_margin">
					<input class="weiz_input" type="text" name="vp_score" value="<?php echo $vp_score; ?>" />
				</dd>
            </dl>

            <div class="WSY_remind_main">
				<dl class="WSY_bulkdl  w90px">
					<dt>发票支持：</dt>	
					<?php if($is_invoice==1){ ?>
					<ul style="background-color: rgb(255, 113, 112);margin-top:2px;">
						<p style="color: rgb(255, 255, 255); margin: 0px 0px 0px 22px;">开</p>
						<li onclick="change_is_invoice(0)" class="WSY_bot" style="left: 0px;"></li>
						<span onclick="change_is_invoice(1)" class="WSY_bot2" style="display: none; left: 0px;"></span>
					</ul>
					<?php }else{ ?>
					<ul style="background-color: rgb(203, 210, 216);margin-top:2px;">
						<p style="color: rgb(127, 138, 151); margin: 0px 0px 0px 6px;">关</p>
						<li onclick="change_is_invoice(0)" class="WSY_bot" style="display: none; left: 30px;"></li>
						<span onclick="change_is_invoice(1)" class="WSY_bot2" style="display: block; left: 30px;"></span>
					</ul>
					<?php } ?>
				</dl>
				<input type="hidden" name="is_invoice" id="is_invoice" value="<?php echo $is_invoice;?>">
			</div>
			
			<!-- 必填信息start -->
			<?php if($is_Pinformation_b==1){ ?>
			<div class="WSY_remind_main">
				<dl class="WSY_bulkdl  w90px">
					<dt>必填信息：</dt>	
					<?php if($is_Pinformation==1){ ?>
					<ul style="background-color: rgb(255, 113, 112);margin-top:2px;">
						<p style="color: rgb(255, 255, 255); margin: 0px 0px 0px 22px;">开</p>
						<li onclick="chage_Pinformation(0)" class="WSY_bot" style="left: 0px;"></li>
						<span onclick="chage_Pinformation(1)" class="WSY_bot2" style="display: none; left: 0px;"></span>
					</ul>
					<?php }else{ ?>
					<ul style="background-color: rgb(203, 210, 216);margin-top:2px;">
						<p style="color: rgb(127, 138, 151); margin: 0px 0px 0px 6px;">关</p>
						<li onclick="chage_Pinformation(0)" class="WSY_bot" style="display: none; left: 30px;"></li>
						<span onclick="chage_Pinformation(1)" class="WSY_bot2" style="display: block; left: 30px;"></span>
					</ul>
					<?php } ?>
				</dl>
			</div>
			<input type="hidden" name="is_Pinformation_b" id="is_Pinformation_b" value="<?php echo $is_Pinformation_b; ?>" />
			<input type="hidden" name="is_Pinformation" id="is_Pinformation" value="<?php echo $is_Pinformation; ?>" />

			<?php } if($is_Pinformation_b==1){ ?>

			<div class="div_show" id="mess" > 
				<dl class="WSY_remind_dl02">
					<table width="50%" class="WSY_table" id="WSY_t1">
						<thead class="WSY_table_header">
							<th width="25%" class="WSY_table_little">信息</th>
							<th width="25%" class="WSY_table_little">操作</th>
						</thead>
						<?php 
						$query    = "select id,name from weixin_commonshop_product_information_t where isvalid=true and customer_id=".$customer_id." and p_id=".$product_id;
						//echo $query;
						$result   = mysql_query($query) or die('Query failed: ' . mysql_error());
						$rcount_q = mysql_num_rows($result);
						$mess_num = 1;
						if( 0 < $rcount_q ){
							
							while ($row = mysql_fetch_object($result)) {
								$name        = $row->name; 
								$name_id     = $row->id; 
							?>
							<tr class="diy_one_two" id="diy_item_<?php echo $mess_num; ?>">
								<input type=hidden name="name_id<?php echo $mess_num; ?>" id="name_id<?php echo $mess_num; ?>" value="<?php echo $name_id; ?>" />
								<td>
									<input type=text class="singletext_con" name="singletext_con_<?php echo $mess_num; ?>" id="singletext_con<?php echo $mess_num; ?>" value="<?php echo $name; ?>">	
								</td>
								<td>
									<a title="删除" href="javascript:mess_del(<?php echo $mess_num; ?>);"><img src="../../../common/images_V6.0/operating_icon/icon04.png"></a>&nbsp;
									<a title="添加" href="javascript:mess_add(1);"><img src="../../../common/images_V6.0/operating_icon/icon45.png"></a>
								</td>
							</tr>
						<?php 
								$mess_num++;
							}
						}else{  
						?>
							<tr class="diy_one_two" id="diy_item_<?php echo $mess_num; ?>">
								<input type=hidden name="name_id<?php echo $mess_num; ?>" id="name_id" value="-1" />
								<td>
									<input type=text class="singletext_con" name="singletext_con_<?php echo $mess_num; ?>" id="singletext_con<?php echo $mess_num; ?>" value="<?php echo $name; ?>" />
								</td>
								<td>
									<a title="删除" href="javascript:mess_del(<?php echo $mess_num; ?>);"><img src="../../../common/images_V6.0/operating_icon/icon04.png"></a>&nbsp;
									<a title="添加" href="javascript:mess_add(1);"><img src="../../../common/images_V6.0/operating_icon/icon05.png"></a>
								</td>
							</tr>
						<?php } ?>
					</table>
				</dl>
			</div>
			<?php } ?>
			
			<dl class="WSY_bulkdl w90px">
				<dt>排序位置：</dt>
                <dd class="WSY_bulkdldd dd_margin">
					<input class="weiz_input" type="text" name="asort_value" value="<?php echo $product_asort_value; ?>" />(按降序排序)
					<?php
						if($product_num !=-1 and $num>=$product_num ){ 
					?>
					(<span style="color:red">你上架的商品已经超过<?php echo $product_num;?>件了！</span>)
					<?php 
						}
					?>
				</dd>
            </dl>
			<?php if($issell){ ?>
			 <div class="w_leftkjbox">
            	<div class="w_leftkj">
                   <dl class="WSY_bulkdl WSY_bulkdldt w90px">
                        <dt>购买折扣率：</dt>
                        <dd><input type="text" name="pro_discount" id="pro_discount" style="width:50px;" value="<?php echo $pro_discount; ?>"><i style="color:#646464">% (0:表示无折扣)</i></dd>
                    </dl>
                    <dl class="WSY_bulkdl WSY_bulkdldt w90px">
                        
                        <dt>产品分佣比例：</dt>
                        <dd><input type="text" name="pro_reward" id="pro_reward" style="width:50px;" value="<?php echo $pro_reward; ?>"><i style="color:#646464">（0～1）</i></dd>
						 <dd><font style="color:red;margin-left:10px;">（填写0则不分佣，填写-1则按分佣总比例<?php echo $init_reward;?>）</font></dd>
                    </dl>
					<?php if($isOpenInstall){?>
					 <dl class="WSY_bulkdl WSY_bulkdldt w90px">
                        <dt>产品安装费：</dt>
                        <dd><input type="text" name="install_price" id="install_price" class="form_input" style="width:50px;" value="<?php echo $install_price; ?>" />元</dd>
                    </dl>
					<?php }?>
                </div>
                <div class="w_leftkj">
					<?php if($is_distribution){?>
						<dl class="WSY_bulkdl WSY_bulkdldt w90px">
							<dt>代理商折扣率：</dt>
							<dd><input type="text" name="agent_discount" id="agent_discount" style="width:50px;" value="<?php echo $agent_discount; ?>" >%</dd>
						</dl>
					<?php }?>
					<?php if($is_opencashback){?>
                    <dl class="WSY_bulkdl WSY_bulkdldt w90px">
						<?php if($cb_condition==0){?>
                        <dt style="width: 140px;">返现金额（固定金额）：</dt>
                        <dd><input type="text" name="cashback" id="cashback" style="width:50px;" value="<?php echo $cashback; ?>"><i style="color:#646464">元</i><font style="color:red;margin-left:10px;">填写0则不返现，填写-1则按公共设置返<?php echo $public_cashback;?>元</font></dd>
						<?php }else{?>
						<dt style="width: 180px;">返现金额（产品价格按比例）：</dt>
                        <dd><input type="text" name="cashback_r" id="cashback_r" style="width:50px;" value="<?php echo $cashback_r; ?>"><i style="color:#646464">（0～1）<font style="color:red;margin-left:10px;">填写0则不返现，填写-1则按公共设置返<?php echo $public_cashback_r*100;?>%</font></i></dd>
						<?php }?>
                    </dl>
					<?php }?>
                </div>
            </div>
			<?php }?>
			<?php if($pro_card_level){?>
			<dl class="WSY_bulkdl WSY_bulkdl03 w90px">
                <dt>需要会员级别：</dt>
                <dd>
					<select name="pro_card_level_id" id="pro_card_level_id">
						<option value="-1"  <?php if($pro_card_level_id==-1){ ?>selected<?php } ?>>不限制</option>
						<?php 
					   $query="select id,title from weixin_card_levels where isvalid=true and card_id=".$shop_card_id;
					   $result = mysql_query($query) or die('Query failed: ' . mysql_error());
					   while ($row = mysql_fetch_object($result)) {
						   $cid = $row->id;
						   $cname = $row->title;
					?>   
						<option value="<?php echo $cid;?>" <?php if($pro_card_level_id==$cid){ echo 'selected="selected"'; } ?> ><?php echo $cname;?></option>
					<?php 	
					}								
					?>
					</select>
				</dd>
            </dl>
			<?php } ?>
		
			<dl class="WSY_bulkdl WSY_bulkdldt w90px">
                <dt style="width:120px">语音链接：</dt>
                <dd><input type="text" style="width:200px" id="product_voice" name="product_voice" value="<?php echo $product_voice; ?>" placeholder="请输入链接"></dd>
				<dd style="color:red">（必须填写MP3等格式外链接,方法一：本地文件->上传至QQ邮箱中转站->下载该文件->复制下载链接->从http截取至MP3->得到链接）</dd>
            </dl>
			<dl class="WSY_bulkdl WSY_bulkdldt w90px">
                <dt style="width:120px">视频链接：</dt>
                <dd><input type="text" style="width:200px" id="product_vedio" name="product_vedio" value='<?php echo $product_vedio; ?>' placeholder='请输入链接'></dd>
				<dd style="color:red">（请填写通用代码，如腾讯视频->分享->复制相关代码，若为本地文件请先行存至网盘）</dd>
            </dl>
            <dl class="WSY_bulkdl w90px">
            
                <dt class="editor edit1" style="background-color:white;" id="edit1">详细介绍</dt>
				<dt class="editor edit2" id="edit2">产品规格</dt>
				<dt class="editor edit3" id="edit3">售后保障</dt>
                <div class="text_box input description">
                	<textarea id="editor1"   name="description"><?php echo $product_description; ?></textarea><!-- 详细介绍 -->
                </div> 
				<div class="text_box input specifications" style="display:none">
                	<textarea id="editor2"   name="specifications"><?php echo $specifications; ?></textarea><!-- 产品规格 -->
                </div> 
				<div class="text_box input service" style="display:none">
                	<textarea id="editor3"   name="service"><?php echo $customer_service; ?></textarea><!-- 售后保障 -->
                </div> 
            </dl>
			</div>
            <div class="WSY_text_input01">
                <div class="WSY_text_input"><button class="WSY_button" id="btnSave" type="button" onclick="saveProduct()">提交保存</button></div>
                <div class="WSY_text_input"><button class="WSY_button" onclick="javascript:history.go(-1);" type="button">返回</button></div>
            </div>
		</div>
	</div>
	<input type=hidden name="stock_pidarr" id="stock_pidarr" value="<?php echo $stock_pidarr; ?>" />
	<input type=hidden name="keyid" id="keyid" value="<?php echo $product_id; ?>" />
	<input type=hidden name="isout" id="isout" value=<?php echo $product_isout; ?> />
	<input type=hidden name="isnew" id="isnew" value=<?php echo $product_isnew; ?> />
	<input type=hidden name="ishot" id="ishot" value=<?php echo $product_ishot; ?> />
	<input type=hidden name="issnapup" id="issnapup" value=<?php echo $product_issnapup; ?> />
	<input type=hidden name="isvp"  id="isvp"  value=<?php echo $product_isvp; ?> />
	<input type=hidden name="is_virtual"  id="is_virtual"  value=<?php echo $is_virtual; ?>  />
	<input type=hidden name="is_charitable"  id="is_charitable"  value=<?php echo $is_charitable; ?> />
	<input type=hidden name="charitable_propotion"  id="charitable_propotion"  value=<?php echo $charitable_propotion; ?> />
	<input type=hidden name="pro_price_detail" id="pro_price_detail" />
	<input type=hidden name="tradeprices" id="tradeprices" value="<?php echo $product_tradeprices; ?>" />
	<input type=hidden name="type_ids" id="type_ids" value="<?php echo $type_ids; ?>" />
	<input type=hidden name="type_id" id="type_id" value="<?php echo $type_id; ?>" />
	<input type=hidden name="auth_user_id" id="auth_user_id" value=<?php echo $auth_user_id; ?> />
	<input type=hidden name="mess_num" id="mess_num" value="<?php echo $mess_num; ?>" />
	<input type=hidden name="is_currency" id="is_currency" value="<?php echo $is_currency; ?>" />
	<input type=hidden name="is_guess_you_like" id="is_guess_you_like" value="<?php echo $is_guess_you_like; ?>" />
	<input type=hidden name="is_free_shipping" id="is_free_shipping" value="<?php echo $product_is_free_shipping; ?>" />
	<input type=hidden name="isscore" id="isscore" value="<?php echo $isscore; ?>" />
</form>
</div>

<!--配置ckeditor和ckfinder-->

<!--编辑器多图片上传引入开始-->
<script type="text/javascript" src="../../../../weixin/plat/Public/js/jquery.dragsort-0.5.2.min.js"></script>
<script type="text/javascript" src="../../../../weixin/plat/Public/swfupload/swfupload/swfupload.js"></script>
<script type="text/javascript" src="../../../../weixin/plat/Public/swfupload/js/swfupload.queue.js"></script>
<script type="text/javascript" src="../../../../weixin/plat/Public/swfupload/js/fileprogress.js"></script>
<script type="text/javascript" src="../../../../weixin/plat/Public/swfupload/js/handlers.js"></script> 
<!--编辑器多图片上传引入结束-->
<script type="text/javascript" src="../../../common/js_V6.0/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="../../../common/js_V6.0/content.js"></script>
<script charset="utf-8" src="../../../common/js/layer/V2_1/layer.js"></script>
<script>


CKEDITOR.replace( 'editor1', //详细介绍
{
extraAllowedContent: 'img iframe[*]',
filebrowserBrowseUrl : '../../../../weixin/plat/Public/ckfinder/ckfinder.html',
filebrowserImageBrowseUrl : '../../../../weixin/plat/Public/ckfinder/ckfinder.html?Type=Images',
filebrowserFlashBrowseUrl : '../../../../weixin/plat/Public/ckfinder/ckfinder.html?Type=Flash',
filebrowserUploadUrl : '../../../../weixin/plat/Public/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
filebrowserImageUploadUrl : '../../../../weixin/plat/Public/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images',
filebrowserFlashUploadUrl : '../../../../weixin/plat/Public/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash'
});
CKEDITOR.replace( 'editor2',//产品规格
{
extraAllowedContent: 'img iframe[*]',
filebrowserBrowseUrl : '../../../../weixin/plat/Public/ckfinder/ckfinder.html',
filebrowserImageBrowseUrl : '../../../../weixin/plat/Public/ckfinder/ckfinder.html?Type=Images',
filebrowserFlashBrowseUrl : '../../../../weixin/plat/Public/ckfinder/ckfinder.html?Type=Flash',
filebrowserUploadUrl : '../../../../weixin/plat/Public/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
filebrowserImageUploadUrl : '../../../../weixin/plat/Public/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images',
filebrowserFlashUploadUrl : '../../../../weixin/plat/Public/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash'
});
CKEDITOR.replace( 'editor3',//售后保障
{
extraAllowedContent: 'img iframe[*]',
filebrowserBrowseUrl : '../../../../weixin/plat/Public/ckfinder/ckfinder.html',
filebrowserImageBrowseUrl : '../../../../weixin/plat/Public/ckfinder/ckfinder.html?Type=Images',
filebrowserFlashBrowseUrl : '../../../../weixin/plat/Public/ckfinder/ckfinder.html?Type=Flash',
filebrowserUploadUrl : '../../../../weixin/plat/Public/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
filebrowserImageUploadUrl : '../../../../weixin/plat/Public/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images',
filebrowserFlashUploadUrl : '../../../../weixin/plat/Public/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash'
});


var pro_reward = '<?php echo $pro_reward;?>';		//产品分佣比例
var init_reward = '<?php echo $init_reward;?>';		//总返佣比例
var promoter_reward = '<?php echo $all;?>';			//推广员返佣比例
//如果产品分佣比例,则使用总分佣比例
if( pro_reward == -1 ){	
	var pro_reward = init_reward;		//总返佣比例
}


function setParentImgIds(ids){
   $("#imgids").attr("value",ids);
}
function chkPro(){ // this, pid , parent_name , parent_id
	 var allpids = $("input[name='hidden_parent']");
	 var str = "";
	 var parentArray = new Array();
	 for(var i = 0 , index = 0; i < allpids.length ; i++ ){ //循环所有属性类型
		 var parent_id = allpids[i].value;
		 var allprops = $("input[data_name='prop_"+parent_id+"']:checked");  //获取属性类型中选中的属性值
		if(allprops.length > 0){
			var childArray = new Array();
			for(var j = 0 ; j < allprops.length; j++ ){ //将每个选中的属性值信息拼接添加到数组
				var ckprop = allprops[j];
				//console.log("ckprop : "+ckprop);
				var pid = $(ckprop).attr("data_pid");
				var text = $(ckprop).attr("data_text");
				var parent = $(ckprop).attr("data_parent");
				childArray[j] = pid+","+text+","+parent;
			}
			parentArray[index] = childArray;
			index++;
		 }
	 }
	 setPropPrice(parentArray);
}

setPropPrice(oldProArray);


function setPropPrice(propArrays){
	var cIndex = propArrays.length - 1;
	var counter = new Array();
	var str = getAppendText("" , "" , "");
    if(propArrays.length > 0){
		var total = 1;
		for(var i = 0 ; i<propArrays.length ; i++){
			counter[i] = 0;
			total = total * propArrays[i].length;
		}
		for(var j = 0;j < total ; j ++){ //笛卡尔乘积
			var ids = "";
			var text = "";
			for(var index = 0 ; index < propArrays.length ; index++){
				var props = propArrays[index][counter[index]].split(",");
				if(props.length == 1){
					props[1] = $("#"+props[0]).val();
				}
				if(index > 0){
					ids += "_";
					text += " - ";
				}
				ids += props[0];
				text += props[1];
			}
			str = getAppendText(str , ids , text);
			console.log(" ids : "+ ids +"  text : "+text);
			calcIndex();
		}
	}
	var divp = document.getElementById("div_proprices");
	str = str + "</div></div>";
	divp.innerHTML = str;
	function calcIndex(){
		counter[cIndex]++; 
		if(counter[cIndex] >= propArrays[cIndex].length){
			counter[cIndex] = 0 ; 
			cIndex --;  
			if(cIndex >= 0){
				calcIndex();
			} 
			cIndex = propArrays.length -1;
		}
	}
}
console.log(ppriceHash);	

function getAppendText(str,pid,text){
		var orgin_price="";
		var now_price="";
		var storenum="";
		var need_score="";
		var cost_price="";
		var foreign_mark="";
		var unit="";
		var weight="";
		var for_price = "";
		if(ppriceHash.contains(pid)){
			var onprice = ppriceHash.items(pid);
			onprices =  onprice.split("_");
			orgin_price = onprices[0];
			now_price = onprices[1];
			storenum = onprices[2];
			need_score = onprices[3];
			cost_price = onprices[4];
			foreign_mark = onprices[5];
			unit = onprices[6];
			weight = onprices[7];
			for_price = onprices[8];
		}
		if(pid!=""){
			str = str + '<ul class="WSY_bulkul01">';
			str = str + '<li class="WSY_bulkuli_red">'+text+'</li>';
			str = str +" <li>原价:￥<input type=\"text\" name=\"pro_orgin_price\" value=\""+orgin_price+"\" class=\"form_input num_check \" size=\"5\" maxlength=\"10\"></li>";
			str = str +" <li class='now_price'><?php if($nowprice_title){echo $nowprice_title;}else if($base_nowprice_title){echo $base_nowprice_title;}else{echo "现价";}?>:￥<input type=\"text\" name=\"pro_now_price\" value=\""+now_price+"\" class=\"form_input num_check calc_np\" size=\"5\" maxlength=\"10\"></li>";
			str = str +" <li class='for_price'>成本:￥<input type=\"text\" name=\"pro_for_price\" value=\""+for_price+"\" class=\"form_input num_check calc_fp\" size=\"5\" maxlength=\"10\"></li>";
			str = str +" <li class='base_price'>供货价:￥<input type=\"text\" name=\"pro_cost_price\" value=\""+cost_price+"\" class=\"form_input num_check calc_bp\" size=\"5\" maxlength=\"10\"></li>";
			str = str +" <li>单位:<input type=\"text\" name=\"pro_unit\" value=\""+unit+"\" class=\"form_input\" size=\"5\" maxlength=\"10\"></li>";
			str = str +" <li>重量:<input type=\"text\" name=\"pro_weight\" value=\""+weight+"\" class=\"form_input\" size=\"5\" maxlength=\"10\">KG</li>";
			str = str +" <li>所需积分: <input type=\"text\" name=\"pro_need_score\" value=\""+need_score+"\" class=\"form_input num_check\" size=\"5\" maxlength=\"10\"></li>";
			str = str +" <li>库存: <input type=\"text\" name=\"pro_storenum\" value=\""+storenum+"\" class=\"form_input num_check\" size=\"5\" maxlength=\"10\"></li>";
			str = str +" <li>外部标识: <input type=\"text\" name=\"pro_foreign_mark\" value=\""+foreign_mark+"\" class=\"form_input\" size=\"5\" maxlength=\"50\"></li>";
			var reward = 0;
			if(pro_reward !="" && (parseFloat(pro_reward) >= -1 && parseFloat(pro_reward) <1 ) &&  //佣金比
				now_price != "" && parseFloat(now_price) > 0 && //现价
				cost_price != "" && parseFloat(cost_price) >= 0 && for_price != "" && parseFloat(for_price) >= 0){ //成本
				reward = calcReward(now_price,for_price,cost_price,pro_reward);
				//console.log("now_price : "+now_price+" ; cost_price : "+cost_price+" ; profit : "+profit+" ; reward : "+reward);
			}
			str = str +" <li class='show_price'>"+(reward > 0 ? "（总返佣金额：￥"+reward+"）" : "" )+"</li>";
			str = str +" <li class='del'>删除</li>";
			str = str +" <input type=hidden name=\"proids\" value=\""+pid+"\" />";
			str = str +"</ul>";
		}else{
			str = str + "<div class='WSY_bulkul01'>";
			str = str + '<span class="WSY_red">现价和成本一致,则不返佣</span><br>';
			str = str +"  <li>原价:￥<input type=\"text\" name=\"orgin_price\" value=\"<?php echo $product_orgin_price; ?>\" class=\"form_input num_check\" size=\"5\" maxlength=\"10\"> </li>";
			str = str +" <li class='now_price'> <?php if($nowprice_title){echo $nowprice_title;}else if($base_nowprice_title){echo $base_nowprice_title;}else{echo "现价";}?>:￥<input type=\"text\" name=\"now_price\" value=\"<?php echo $product_now_price; ?>\" class=\"form_input num_check calc_np\" size=\"5\" maxlength=\"10\"> </li>";
			str = str +" <li class='for_price' > 成本:￥<input type=\"text\" name=\"for_price\" value=\"<?php echo $product_for_price; ?>\" class=\"form_input num_check calc_fp\" size=\"5\" maxlength=\"10\"> </li>";
			str = str +" <li class='base_price'>供货价:￥<input type=\"text\" name=\"cost_price\" value=\"<?php echo $product_cost_price; ?>\" class=\"form_input num_check calc_bp\" size=\"5\" maxlength=\"10\"></li>";
			str = str +" <li> 单位:<input type=\"text\" name=\"unit\" value=\"<?php echo $product_unit; ?>\" class=\"form_input\" size=\"5\" maxlength=\"10\"> </li>";
			str = str +" <li> 重量:<input type=\"text\" name=\"weight\" value=\"<?php echo $product_weight; ?>\" class=\"form_input\" size=\"5\" maxlength=\"10\">KG</li>";
			str = str +" <li> 所需积分: <input type=\"text\" name=\"need_score\" value=\"<?php echo $product_need_score; ?>\" class=\"form_input num_check\" size=\"5\" maxlength=\"10\"> </li>";
			str = str +" <li> 库存: <input type=\"text\" name=\"storenum\" value=\"<?php echo $product_storenum; ?>\" class=\"form_input num_check\" size=\"5\" maxlength=\"10\"> </li>";
			str = str +" <li> 外部标识: <input type=\"text\" name=\"foreign_mark\" value=\"<?php echo $product_foreign_mark; ?>\" class=\"form_input\" size=\"5\" maxlength=\"50\"></li>";
			
			var reward = 0;
			var now_price = '<?php echo $product_now_price; ?>';
			var cost_price = '<?php echo $product_cost_price; ?>';
			var for_price = '<?php echo $product_for_price; ?>';
			if(pro_reward !="" && (parseFloat(pro_reward) >=-1 && parseFloat(pro_reward) < 1 ) &&  //佣金比
				now_price != "" && parseFloat(now_price) > 0 && //现价
				cost_price != "" && parseFloat(cost_price) >= 0 ){ //成本
				reward = calcReward(now_price,for_price,cost_price,pro_reward);
				//console.log("now_price : "+now_price+" ; cost_price : "+cost_price+" ; profit : "+profit+" ; reward : "+reward);
			}
			str = str +" <li class='show_price'>"+(reward > 0 ? "（总返佣金额：￥"+reward+"）" : "" )+"</li>";
			str = str +"</div>";
			str = str + '<div class="WSY_bulkul02box" style="width:95%">';
            str = str + '<div class="WSY_bulkul02">';
            str = str + '<span class="WSY_red">属性价格</span>';
		}
		return str;
}

function calcReward(now_price,for_price,base_price,rate){
	var profit = parseFloat(now_price) - parseFloat(base_price);
	var reward = (parseFloat(now_price)- parseFloat(for_price))* parseFloat(rate);
	reward = reward > profit ? profit : reward;
	reward = reward.toFixed(2);
	return reward;
}

$(document).ready(function() {
	//var first_division = 0;
	//var init_reward_1 = <?php echo $init_reward_1;?>;
	$("input[name='define_share_image_flag']").click(
		function() {
		var $selectedvalue = this.value;
		if ($selectedvalue == 1) {
			$("#define_share_image_div").show();
		}
		else {
			$("#define_share_image_div").hide();
		}
	});
	$("#pro_reward").on("blur",function(){
		var rate = $(this).val();
		//如果产品分佣比例,则使用总分佣比例
		if(rate == -1){
			rate = init_reward;
		}
		if(isNaN(rate) && (parseFloat(rate) <= -1 || parseFloat(rate) >= 1)){
			alert("请输入正确的佣金比！");
			return;
		}
		var all_show = $(".show_price");
		var i = 1;
		all_show.each(function(){
			var np = $(this).siblings(".now_price").find("input").val();
			var bp = $(this).siblings(".base_price").find("input").val();
			var fp = $(this).siblings(".for_price").find("input").val();
			if(np != "" && bp != "" && fp != ""){
				np = parseFloat(np);
				bp = parseFloat(bp);
				fp = parseFloat(fp);
				reward = calcReward(np,fp,bp,rate);
				$(this).text("（总返佣金额：￥"+reward+"）");
				/*if(1==i){
					first_division = parseFloat(init_reward_1 * reward * promoter_reward,2).toFixed(2);
					if(first_division<0){
						first_division = 0;
					}
					$('#first_division').val(first_division);
				}*/
				i++;
			}
		});
	});
	$("#div_proprices").on("blur",".calc_bp",function(){
		//console.log("成本 - blur ");
		var rate = $("#pro_reward").val();
		//如果产品分佣比例,则使用总分佣比例
		if(rate == -1){
			rate = init_reward;
		}
		if(!isNaN(rate) && parseFloat(rate) >= -1){
			var np = $(this).parent().siblings(".now_price").find("input").val();
			var bp = $(this).val();
			var fp = $(this).parent().siblings(".for_price").find("input").val();
			if(np != "" && bp != "" && fp != ""){
				np = parseFloat(np);
				bp = parseFloat(bp);
				fp = parseFloat(fp);
				reward = calcReward(np,fp,bp,rate);
				$(this).parent().siblings(".show_price").text("（总返佣金额：￥"+reward+"）");
				/*
				first_division = parseFloat(init_reward_1 * reward * promoter_reward,2).toFixed(2);
				if(first_division<0){
					first_division = 0;
				}
				$('#first_division').val(first_division);*/
			}
		}
	});
	$("#div_proprices").on("blur",".calc_np",function(){
		//console.log("现价 - blur ");
		var rate = $("#pro_reward").val();
		//如果产品分佣比例,则使用总分佣比例
		if(rate == -1){
			rate = init_reward;
		}
		if(!isNaN(rate) && parseFloat(rate) >= -1){
			var np = $(this).val();
			var bp = $(this).parent().siblings(".base_price").find("input").val();
			var fp = $(this).parent().siblings(".for_price").find("input").val();
			if(np != "" && bp != "" && fp != ""){
				np = parseFloat(np);
				bp = parseFloat(bp);
				fp = parseFloat(fp);
				reward = calcReward(np,fp,bp,rate);
				$(this).parent().siblings(".show_price").text("（总返佣金额：￥"+reward+"）");
				/*
				first_division = parseFloat(init_reward_1 * reward * promoter_reward,2).toFixed(2);
				if(first_division<0){
					first_division = 0;
				}
				$('#first_division').val(first_division);*/
			}
		}
	});
	$("#div_proprices").on("blur",".calc_fp",function(){
		//console.log("现价 - blur ");
		var rate = $("#pro_reward").val();
		//如果产品分佣比例,则使用总分佣比例
		if(rate == -1){
			rate = init_reward;
		}
		if(!isNaN(rate) && parseFloat(rate) >= -1){
			var np = $(this).parent().siblings(".now_price").find("input").val();
			var bp = $(this).parent().siblings(".base_price").find("input").val();//
			var fp = $(this).val();
			if(np != "" && bp != "" && fp != ""){
				np = parseFloat(np);
				bp = parseFloat(bp);
				fp = parseFloat(fp);
				reward = calcReward(np,fp,bp,rate);
				$(this).parent().siblings(".show_price").text("（总返佣金额：￥"+reward+"）");
				/*
				first_division = parseFloat(init_reward_1 * reward * promoter_reward,2).toFixed(2);
				if(first_division<0){
					first_division = 0;
				}
				$('#first_division').val(first_division);*/
			}
		}
	});
	$("#div_proprices").on("click",".del",function(){
		$(this).parent().remove();
	});
}); 


function setParentDefaultimgurl(default_imgurl){

    document.getElementById("default_imgurl").value=default_imgurl;
}
function setParentClassDefaultimgurl(class_imgurl){

    document.getElementById("class_imgurl").value=class_imgurl;
}
customer_id_en = '<?php echo $customer_id_en;?>';
page_index = 4;
var search_type_id = '';

function add_relation_pros(relation_type_id,obj){
	$this = $(obj);
	var check = $this.attr('checked');

	if(check =='checked'){				//选中才添加属性
	
		$.ajax({ 
			type: "post",
			url: "product_data.php?op=check_pros_extends&customer_id=<?php echo $customer_id_en;?>",
			async: true,
			data: { data: relation_type_id},
			success: function (result) {
				var result = eval('(' + result + ')'); 
				
				if(result.code==10008){
					var pro_parent_id = result.data['pro_id'];
					var pro_parent_name = result.data['pros_name'];				
					var pro_dd = $('.wdw>dd');		//获取所有的属性dd
					var is_had = 0;					//是否已经存在该属性
					$.each(pro_dd,function(){
						
						var dd_pro_parent_id = $(this).attr('pro_parent_id');
						if(dd_pro_parent_id == pro_parent_id){	//查找到+1
							is_had++;
						}
					});
					
					if(is_had ==0){		//当已经存在该属性则不需要加载
						
						var html = '<dd class="add_relation_pros_'+relation_type_id+'" pro_parent_id="'+pro_parent_id+'"><div class="WSY_cloropbox" >';
						html     +='<span>'+pro_parent_name+'</span><input type="hidden" name="hidden_parent" value="'+pro_parent_id+'"><div class="WSY_clorop">';
						var html2 = '';
						for(var i=0;i<result.data['pros_child_data'].length;i++){
							
							var pro_id = result.data['pros_child_data'][i][0];
							var pro_name = result.data['pros_child_data'][i][1];
							html2 += '<p><input type="checkbox" data_name="prop_'+pro_parent_id+'" data_pid="'+pro_id+'" data_text="'+pro_name+'" data_parent="'+pro_parent_id+'" value="'+pro_id+'" name="ptids" onclick="chkPro();">  '+pro_name+'<input type="hidden" id="'+pro_id+'" value="'+pro_name+'"></p>'; 
						}
						
						html		+=	html2	+'</div></div></dd>';
						//console.log(html);
						$('.wdw').append(html);
					}
					
				}
				
			}    
		})
	}else{					//清除新增加的属性，本身就有则不清除
		$('.add_relation_pros_'+relation_type_id).remove();
	}
	
}
</script>

<script> 
	var mess_num = <?php echo $mess_num;?>-1;//必填信息
</script>
<script type="text/javascript" src="../../Common/js/Product/product_common.js"></script>
<script type="text/javascript" src="../../Common/js/Product/product/product.js"></script>
</body>
</html>
