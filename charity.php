<?php
header("Content-type: text/html; charset=utf-8"); 
require('../../../config.php');
require('../../../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../../../back_init.php');
$link = mysql_connect(DB_HOST,DB_USER, DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
mysql_query("SET NAMES UTF8");
require('../../../proxy_info.php');
$head=4;//头部文件  0基本设置,1基金明细

//接受页面参数
$pagenum = 1;
$pagesize = 20;
if(!empty($_GET["pagenum"])){
   $pagenum = $configutil->splash_new($_GET["pagenum"]);
}
$start = ($pagenum-1) * $pagesize;
$end = $pagesize;

 if(!empty($_GET["search_id"])){
   $search_id = $configutil->splash_new($_GET["search_id"]);
}

if(!empty($_GET["search_name"])){
   $search_name = $configutil->splash_new($_GET["search_name"]);
}

/*输出数据语句*/
$query = "select id,charity_name,money from charitable_charity_t where isvalid=true and customer_id=".$customer_id;
/*输出数据语句*/
/*统计数据数量*/
$query_num = "select count(1) as wcount from charitable_charity_t where isvalid=true and customer_id=".$customer_id;
/*统计数据数量*/
$sql = "";
if(!empty($search_id)){			   
	$sql .= " and id like '%".$search_id."%'";
}
if(!empty($search_name)){			   
	$sql .= " and charity_name like '%".$search_name."%'";
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
$query .=  $sql." limit ".$start.",".$end; 
?>  
<!doctype html>
<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<title></title>
<link rel="stylesheet" type="text/css" href="../../../common/css_V6.0/content.css">
<link rel="stylesheet" type="text/css" href="../../../common/css_V6.0/content<?php echo $theme; ?>.css">
<link type="text/css" rel="stylesheet" rev="stylesheet" href="../../../css/inside.css" media="all">
<link rel="stylesheet" type="text/css" href="../../Common/css/Mode/welfare/set.css">
<script type="text/javascript" src="../../../common/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="../../../js/tis.js"></script>
<script type="text/javascript" src="../../../common/utility.js" charset="utf-8"></script>
<script type="text/javascript" src="../../../common/js/jquery.blockUI.js"></script>
<script charset="utf-8" src="../../../common/js/jquery.jsonp-2.2.0.js"></script>
<script type="text/javascript" src="../../../js/WdatePicker.js"></script>
<script type="text/javascript" src="../../../common/js/inside.js"></script>
<style> 
table#WSY_t1 td {
    text-align: center;
}
tr {
    line-height: 22px;
}
.WSY_t4 a img{
	height: 23px;
	width: 23px;
	margin-right: 10px;
}
</style>
<title>慈善机构</title>
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
				<form class="search" id="search_form" style="margin-left:18px; margin-top: 18px;">
					<div class="WSY_list" style="margin-top: 18px;">
						<input type="hidden" name="charity_id" value="<?php echo $charity_id;?>">
						<li class="WSY_position_text" style="display:inline-block;">
							<a>机构编号：<input type="text" name="search_id" id="search_id" value="<?php echo $search_id; ?>"></a>
							<a>机构名：<input type="text" name="search_name" id="search_name" value="<?php echo $search_name; ?>"></a>
							<input type="button" class="search_btn" onclick="searchForm();" value="搜 索"> 
						</li>
						<ol class="WSY_righticon">
							<li><a href="add_charity.php?customer_id=<?php echo $customer_id_en ;?>">添加</a></li>           
						</ol>
					</div>     
				</form>	
				<table width="97%" class="WSY_table" id="WSY_t1">
					<thead class="WSY_table_header">
						<th width="20%">机构编号</th>
						<th width="20%">机构名</th>
						<th width="20%">收到的捐款金额</th>
						<th width="20%">操作</th>
					</thead>
					<tbody>
					<?php 
						$result = mysql_query($query) or die('Query failed: ' . mysql_error());
						while($row = mysql_fetch_object($result)){
							$charity_id=$row->id;
							$charity_name=$row->charity_name;
							$money=$row->money;
							$money = round($money,2);
					?>
						<tr>
							<td><?php echo $charity_id;?></td>
							<td><?php echo $charity_name;?></td>
							<td><?php echo $money;?>元</td>
							<td class="WSY_t4">
								<a href="add_charity.php?charity_id=<?php echo $charity_id ?>&customer_id=<?php echo $customer_id_en; ?>" style="cursor:pointer;" class="WSY_operation" title="编辑"><img src="../../../common/images_V6.0/operating_icon/icon05.png"></a>
								
								<a href="charity_record.php?charity_id=<?php echo $charity_id ?>&customer_id=<?php echo passport_encrypt((string)$customer_id); ?>" style="cursor:pointer;" class="WSY_operation" title="捐款统计"><img src="../../../common/images_V6.0/weixin_apply/Research.png"></a>
								
								<a href="javascript: G.ui.tips.confirm('您确定删除吗？','add_charity.php?charity_id=<?php echo $charity_id;?>&op=del&customer_id=<?php echo $customer_id_en;?>');" title="删除"><img src="../../../common/images_V6.0/operating_icon/icon04.png"></a> 
							</td>
						</tr>
						<?php }?>			
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
<script type="text/javascript" src="../../Common/js/Mode/charitable/charity.js"></script>
</body>
</html>