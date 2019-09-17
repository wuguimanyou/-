<?php

header("Content-type: text/html; charset=utf-8"); 
require('../../../config.php');
require('../../../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../../../back_init.php');
$link = mysql_connect(DB_HOST,DB_USER, DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
mysql_query("SET NAMES UTF8");
require('../../../proxy_info.php');
$head=1;//头部文件  0基本设置,1基金明细

//接受页面参数
$pagenum   = 1; //第几页
$pagesize  = 20;
$begintime = "";
$endtime   = "";
if(!empty($_GET["pagenum"])){
   $pagenum = $configutil->splash_new($_GET["pagenum"]);
}
$start = ($pagenum-1) * $pagesize;
$end   = $pagesize;

if(!empty($_GET["search_batchcode"])){
   $search_batchcode = $configutil->splash_new($_GET["search_batchcode"]);
}

if(!empty($_GET["search_name"])){
   $search_name = $configutil->splash_new($_GET["search_name"]);
}
if(!empty($_GET["begintime"])){
   $begintime = $configutil->splash_new($_GET["begintime"]);
}
if(!empty($_GET["endtime"])){
   $endtime = $configutil->splash_new($_GET["endtime"]);
}

$total_charitable = 0;
$query="select sum(charitable) as charitable from weixin_users where isvalid=true and customer_id=".$customer_id;
$result = mysql_query($query) or die('Query failed: ' . mysql_error());				   
while ($row = mysql_fetch_object($result)) {
	$total_charitable = $row->charitable;
}
/*输出数据语句*/
$total_charitable = round($total_charitable,2);  

$query = "select wu.name,wu.weixin_name,clt.batchcode,clt.reward,clt.createtime,clt.charitable from charitable_log_t clt inner join weixin_users wu on clt.user_id = wu.id where clt.isvalid=true and wu.isvalid=true and clt.paytype in (0,1) and clt.customer_id=".$customer_id;
/*输出数据语句*/	

/*统计数据数量*/

$query_num = "select count(1) as wcount from charitable_log_t clt inner join weixin_users wu on clt.user_id = wu.id where clt.isvalid=true and wu.isvalid=true and clt.paytype in (0,1) and clt.customer_id=".$customer_id;
/*统计数据数量*/

$sql = "";
 if(!empty($search_batchcode)){			   
	$sql .= " and clt.batchcode like '%".$search_batchcode."%'";
}
if(!empty($search_name)){			   
	$sql .= " and ((wu.name like '%".$search_name."%')";
	$sql .= " or (wu.weixin_name like '%".$search_name."%'))";
}
if(!empty($begintime)){			   
	$sql .= " and UNIX_TIMESTAMP(clt.createtime)>".strtotime($begintime);
}
if(!empty($endtime)){			   
	$sql .= " and UNIX_TIMESTAMP(clt.createtime)<".strtotime($endtime);
}
/*运行统计数据数量*/
$query_num .= $sql;
$result_num = mysql_query($query_num) or die('Query_num failed: ' . mysql_error());
$wcount     = 0;//数据数量
$page       = 0;//分页数
while ($row_num = mysql_fetch_object($result_num)) {
	$wcount =  $row_num->wcount ;
}			
$page=ceil($wcount/$end);
/*运行统计数据数量*/

$query .=  $sql." ORDER BY clt.id DESC limit ".$start.",".$end; 
?>  
<!doctype html>
<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<title></title>
<link rel="stylesheet" type="text/css" href="../../../common/css_V6.0/content.css">
<link rel="stylesheet" type="text/css" href="../../../common/css_V6.0/content<?php echo $theme; ?>.css">
<link rel="stylesheet" type="text/css" href="../../Common/css/Mode/welfare/set.css">
<script type="text/javascript" src="../../../common/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="../../../js/tis.js"></script>
<script type="text/javascript" src="../../../common/utility.js" charset="utf-8"></script>
<script type="text/javascript" src="../../../common/js/jquery.blockUI.js"></script>
<script charset="utf-8" src="../../../common/js/jquery.jsonp-2.2.0.js"></script>
<script type="text/javascript" src="../../../js/WdatePicker.js"></script>
<style> 
table#WSY_t1 td {
    text-align: center;
}
tr {
    line-height: 22px;
}
</style>
<title>基金明细</title>
<meta http-equiv="content-type" content="text/html;charset=UTF-8">
</head>
<body> 
	<!--内容框架-->
	<div class="WSY_content">
		<!--列表内容大框-->
		<div class="WSY_columnbox">
			<!--列表头部切换开始-->
			<?php
			include("basic_head.php"); 
			?>
			<!--列表头部切换结束-->
			<div class="WSY_remind_main">
				<dl class="WSY_remind_dl02" style="margin-left: 36px;">
					<dt style="line-height:28px;" class="WSY_left">已确定慈善分：</dt>
					<dd>
						<span style="padding-left:10px;color:red;font-size:24px;font-weight:bold">￥<?php echo $total_charitable;?></span>
					</dd>
				</dl>
				<form class="search" id="search_form" style="margin-left:18px; margin-top: 18px;">
					<div class="WSY_list" style="margin-top: 18px;">
						<li class="WSY_position_text">
							<a>订单号：<input type="text" name="search_batchcode" id="search_batchcode" value="<?php echo $search_batchcode; ?>"></a>
							<a>姓名：<input type="text" name="search_name" id="search_name" value="<?php echo $search_name; ?>"></a>
							<a>
								捐助时间：
								<span id="searchtype3" class="display">
									<input type="text" class="input Wdate" style="border: 1px solid #CFCBCB;height: 24px;margin-bottom: 5px;border-radius: 2px;" onclick="WdatePicker({dateFmt:'yyyy-MM-dd'});" id="begintime" name="AccTime_A" value="<?php echo $begintime; ?>" maxlength="21" id="K_1389249066532" />
									-
									<input type="text" class="input  Wdate"  style="border: 1px solid #CFCBCB;height: 24px;margin-bottom: 5px;border-radius: 2px;"  onclick="WdatePicker({dateFmt:'yyyy-MM-dd'});" id="endtime" name="AccTime_B" value="<?php echo $endtime; ?>" maxlength="20" id="K_1389249066580" />
								</span>
							</a>
							<input type="button" class="search_btn" onclick="searchForm();" value="搜 索"> 
						</li>
						
					</div>     
				</form>	  
				<table width="97%" class="WSY_table" id="WSY_t1">
					<thead class="WSY_table_header">
						<th width="20%">名称</th>
						<th width="20%">订单号</th>
						<th width="20%">状态</th> 
						<th width="20%">捐助金额</th> 
						<th width="20%">慈善积分</th> 
						<th width="20%">下单时间</th>
					</thead>
					<tbody>
					   <?php 
						$batchcode 	 = -1; //订单号
						$createtime  = ""; //订单确认时间
						$reward	   	 = 0; //订单总慈善金
						$paytype   	 = -1;//订单慈善分订单状态
						$name	     = "匿名"; //购买人名字
						$weixin_name = "匿名"; //购买人微信名字
						$names		 = "匿名"; 
						$charitable  = 0; 
						//echo $query;
						$result = mysql_query($query) or die('Query failed: ' . mysql_error());				   
						while ($row = mysql_fetch_object($result)) {
						$name	 	 = $row->name;
						$weixin_name = $row->weixin_name;
						$user_id     = $row->user_id;
						$createtime  = $row->createtime;
						$reward   	 = $row->reward;
						$batchcode   = $row->batchcode;
						$charitable  = $row->charitable;
						$paytype   = $row->paytype;
						switch($paytype){
							case -1:  
								$paytype_str = "未支付";
								break;
							case 0:  
								$paytype_str = "已到账";
								break;
							case 1:  
								$paytype_str = "已到账";
								break;
							case 2:  
								$paytype_str = "已退货";
								break;
							case 3:  
								$paytype_str = "已退款";
								break;
						}
						
						$names               = $name ."(".$weixin_name.")";
					   ?>
						<tr>
						   <td><?php echo $names; ?></td>
						   <td><a href="../../Order/order/order.php?&search_batchcode=<?php echo $batchcode; ?>"><?php echo $batchcode; ?></a></td>
						   <td><?php echo $paytype_str; ?></td>
						   <td><?php echo $reward; ?></td>
						   <td><?php echo $charitable; ?></td>
						   <td><?php echo $createtime; ?></td>
						</tr>
					   <?php } ?>
					    					
					</tbody>					
				</table>
				<div class="blank20"></div>
				<div id="turn_page"></div>
				<!--翻页开始-->
				<div class="WSY_page">
        	
				</div>
				<!--翻页结束-->
			</div>
		</div>
	</div>

	
<script src="../../../js/fenye/jquery.page1.js"></script>
<script>
var customer_id = "<?php echo $customer_id_en;?>";
var pagenum     = <?php echo $pagenum ?>;
var count       = <?php echo $page ?>;//总页数	
</script>

<?php mysql_close($link);?>	

<script type="text/javascript" src="../../../common/js_V6.0/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="../../../common/js_V6.0/content.js"></script>
<script type="text/javascript" src="../../Common/js/Mode/charitable/charitable_detail.js"></script>
</body>
</html>