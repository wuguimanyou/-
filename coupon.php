<?php
header("Content-type: text/html; charset=utf-8"); 
session_cache_limiter( "private, must-revalidate" ); 
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../common/utility.php');
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('order_Form Could not select database');

//头文件----start
require('../common/common_from.php');
//头文件----end

$n_p = 0;		//不包含运费的订单金额
if(!empty($_POST["n_p"])){
	$n_p = $configutil->splash_new($_POST["n_p"]);
	$_SESSION['n_p'] = $n_p;
}
if($n_p==0 && !empty($_SESSION['n_p']) && $_SESSION['n_p']!=0){
	$n_p = $_SESSION['n_p'];
}
$href_url = 'order_form.php?customer_id='.$customer_id_en;
$w = 1;		//判断从哪里进来，0:从个人中心进来，不可选择 1:下单页面可选择
if(empty($_GET["w"])){
	$w = 0;	
	$href_url = 'my_moneybag.php?customer_id='.$customer_id_en;
}

/**	   是否存在可领取代金券  开始  **/
$CouponId = -1;
$is_coupon = -1;
$query = "select CouponId,is_coupon from weixin_commonshops where customer_id=".$customer_id;
$row = mysql_query($query);
while($rod = mysql_fetch_object($row)){
	$CouponId = $rod->CouponId;
	$is_coupon = $rod->is_coupon;
}
//file_put_contents("0821.txt","query====".$query."\r\n",FILE_APPEND);

if($CouponId>0 && $is_coupon==1){
	$v_id = -1;
	$MinMoney = 0;
	$MaxMoney = 0;
	$NeedMoney = 0;
	$Days = 0;
	$type = 0;
	$CanGetNum = 0;
	$DaysType = 0;
	$createtime = '';
	$query = "select * from weixin_commonshop_coupons where isvalid=true and is_open=1 and class_type=1 and id=".$CouponId;
	//file_put_contents("0821.txt","query====".$query."\r\n",FILE_APPEND);
	$row = mysql_query($query);
	while($rod = mysql_fetch_object($row)){
		$v_id = $rod->id;
		$MinMoney = $rod->MinMoney;
		$MaxMoney = $rod->MaxMoney;
		$NeedMoney = $rod->NeedMoney;
		$isvalid = $rod->isvalid;
		$Days = $rod->Days;
		$type = $rod->type;
		$CanGetNum = $rod->CanGetNum;
		$DaysType = $rod->DaysType;
		$createtime = $rod->createtime;
		if($MinMoney==$MaxMoney){
			$Money = $MinMoney;
		}else{
			$Money = $MinMoney." - ".$MaxMoney;
		}

		$deadline = $Days;
	}
}

$query3 = "SELECT COUNT(1) AS C_Count FROM weixin_commonshop_couponusers WHERE user_id=".$user_id." AND customer_id=".$customer_id." AND isvalid=true AND type=1 AND v_id=".$CouponId." and createtime like '%".$day_times."%'";
//file_put_contents("0821.txt","query====".$query."\r\n",FILE_APPEND);
$C_Count = 0;
$row3 = mysql_query($query3) or die('W3663 Query failed: ' . mysql_error());
while ($rod3 = mysql_fetch_object($row3)) {
	$C_Count=  $rod3->C_Count;		//今天领取的张数	300
}
$http_host = "xmy.xmtzxm.net";
$query = 'SELECT http_host FROM customer_host where   customer_id='.$customer_id;
	$result = mysql_query($query) or die('Query failed66: ' . mysql_error());
	while ($row = mysql_fetch_object($result)) {
	   $http_hosta = $row->http_host; 
	   break;
	}
	if($http_hosta){
		 $_SESSION["http_host"]= $http_hosta;
		 $http_host = $http_hosta;


	}
if($DaysType == 1){			//截止类型    固定时间
	if($C_Count < $CanGetNum && strtotime($day_time) < strtotime($deadline)){
		$url  =  "http://".$http_host."/weixinpl/common_shop/jiushop/vouchers.php?user_id=".$user_id."&customer_id=".$customer_id_en."&w=".$w ; 
		
		echo "<script> {location.href='$url'} </script>";
	}
}else{
	////file_put_contents("0821.txt","C_Count====".$CanGetNum."\r\n",FILE_APPEND);
	if($C_Count < $CanGetNum){
		$url  =  "http://".$http_host."/weixinpl/common_shop/jiushop/vouchers.php?user_id=".$user_id."&customer_id=".$customer_id_en."&w=".$w ; 
		
		echo "<script> {location.href='$url'} </script>";
	}
}
/**	   是否存在可领取代金券  结束  **/

?>
<!DOCTYPE html>
<html>
	<head>
		<title>代金券</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
        <meta content="telephone=no" name="format-detection">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
	    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link rel="stylesheet" type="text/css" href="css/coupon.css">
        <script type="text/javascript" src="js/jquery-2.1.3.min.js"></script>
		<link type="text/css" rel="stylesheet" href="./css/order_css/global.css" />  
		<link rel="stylesheet" href="css/css_orange.css" />
		<style> 
			/**加载效果旋转**/  
			@-webkit-keyframes rotate {0% {-webkit-transform: rotate(0deg);}50% {-webkit-transform: rotate(180deg);}100% {-webkit-transform: rotate(360deg);}}  
			@keyframes rotate {0% {transform: rotate(0deg);}50% {transform: rotate(180deg);}100% {transform: rotate(360deg);}}  
			  
			.loadmore {display:block;line-height: 50px;text-align:center;color:#ccc;font-size:14px;}  
			.loadmore span{height:20px;width: 20px;border-radius: 100%;display:inline-block;margin:10px;border:2px solid #f60;border-bottom-color: transparent;vertical-align: middle;-webkit-animation: rotate 0.75s 0 linear infinite;animation: rotate 0.75s 0 linear infinite;}  
			.loadover{/*position:relative;margin:0 12px;padding:24px 0;height:20px;line-height:20px;color:#909090;text-align: center;*/}  
			.loadover{margin-top: 20px;text-align: center;margin-bottom: 35px;}
			.loadover span{position:relative;display:inline-block;padding:0 6px;height:20px;z-index:2}  
			.loadover:after {content:''position: absolute;left: 0;top: 50%;width: 100%;height: 1px;background: #DCDCDC;z-index: 1;}  
			.clear{clear:both;}

 //ld 点击效果
        .button{ 
        	-webkit-transition-duration: 0.4s; /* Safari */
        	transition-duration: 0.4s;
        }

        .buttonclick:hover{
        	box-shadow:  0 0 5px 0 rgba(0,0,0,0.24);
        }

		</style>
   
	</head>
	<body>
        <div class="main_c">
        
            <div class="header">
                <a href="javascript:location.href='<?php echo $href_url;?>';"><img src="images/return.png" class="return"></a>
                <span class="title">我的代金券</span>
                <!--<span class="new">新增</span>-->
            </div> 
			
            <div class="content">
				
				<div class="not-coupon" style="display:none">
					<img src="images/co.png">
					<p>暂无优惠券可使用</p>
				</div>
            </div>
            <!--<p class="fix-bottom">没有更多可用券了丨<span style="yellow-text">查看过期券</span></p>-->
        </div>
		<!--引入侧边栏 start-->
		<?php  include_once('float.php');?>
		<!--引入侧边栏 end-->
<script type="text/javascript">

//全局参数部分
var ajax_data = {
	
	customer_id	:	'<?php echo $customer_id;?>',
	user_id	 	:	'<?php echo $user_id;?>',
	
	start 		:	0,		//读取数据开始位置	
	end   		:	8,		//数据加载数量	
	finished	:	0, 		//防止未加载完再次执行
	sover		:	0  		//数据是否已经加载完
	
}


$(function(){	
	
	
	Get_coupon();	//首次获取代金卷
	
	//滑动加载数据（显示的数据高度必须大于窗口高度才会触发）
	$(window).scroll(function() {	
		
		var scrollTop = $(window).scrollTop(); 			//滑动距离 
		var scrollHeight = $(document).height();  		//内容的高度
		var windowHeight = $(window).height();			//窗口高度
			
		if (scrollTop + windowHeight >= scrollHeight) {		//当滑动距离+内容的高度 > 窗口的高度 = 则加载数据
			
			loadmore();  								//加载数据的函数
					
		} 
	});
	 
	

	
});


/***************函数部分**************/
//首次获取代金券
function Get_coupon(){
	$.ajax({
		type: 'POST',  
		url: 'coupon_get.php', 
		data:{
			customer_id: '<?php echo $customer_id;?>',
			user_id: '<?php echo $user_id;?>'
		},
		dataType: 'json',
		success: function(result){
		
			if( result["check"] == "ok" ){
				if(result["resu_num"]>0){
					$('.not-coupon').hide();
					showAlertMsg("提示",'领取成功首次代金券',"知道了");	//弹出警告							
				}
			}
			
		}
	});
	loadmore(); 	//加载首次获取代金券
}
	
 
 
//加载完  
function loadover(sover){ 

	if(sover==1)  
	{     
		<?php 
		if($w ==1 ){?>							
			var overtext="不选择任何优惠";  
			
			var txt='<div class="loadover loadover_add" onclick="Change(-1)";><span class="sp">'+overtext+'</span></div>' ;
			
			$("body").append(txt); 	
			
		<?php }else{?>
			var overtext="Duang～到底了";  
			if($(".loadover").length>0)  
			{  
				$(".loadover span").eq(0).html(overtext);  
			}  
			else  
			{  
				var txt='<div class="loadover"><span>'+overtext+'</span></div>'  
				$("body").append(txt);  
			} 
		<?php }?>						
	}  
} 
 
 
//加载更多 
function loadmore(){  

	if(ajax_data.finished==0 && ajax_data.sover==0)  
	{  
		
			var txt='<div class="loadmore"><span class="loading"></span>加载中..</div>'  ;
			$("body").append(txt);  
			  
			//防止未加载完再次执行  
			ajax_data.finished=1;  
	 

			$.ajax({  
				type: 'POST',  
				url: 'coupon_page.php', 
				data:{
						start 		:	ajax_data.start,		//读取数据开始位置	
						end   		:	ajax_data.end,		//数据加载数量
						customer_id	:	ajax_data.customer_id,
						user_id	 	:	ajax_data.user_id,
				
				},
				dataType: 'json',  
				success: function(data){
									
					ajax_callback(data);
						
				},  
				error: function(xhr, type){  
					alert('Ajax error!');  
				}  
			}); 
		
	}  
}  
 
 function ajax_callback(data){
	 var result = '' ; 
					
	for(var i = 0 ; i < data.coupon.length; i++){ 
	
		 result+= '<div class="not-used button buttonclick" <?php if($w){?>  onclick="Change('+data.coupon[i].keyid+')" <?php }?>><div class="money-box"><p class="money"><span>￥</span>'+data.coupon[i].Money+'</p><p class="cont" id="NeedMoney_'+data.coupon[i].keyid+'" data-needmoney="'+data.coupon[i].NeedMoney+'"  data-money="'+data.coupon[i].Money+'">满'+data.coupon[i].NeedMoney+'可用</p></div><div class="text-box"><p class="name">代金券</p><p class="text-cont">有效期至'+data.coupon[i].deadline+'</p><p class="text-cont">'+data.coupon[i].type_str+'</p></div><div class="clear"></div></div>'
		
	} 
	
	ajax_data.start += data.coupon.length;	 //赋值下一次读取数据开始位置	
	
	// 为了测试，延迟1秒加载  	
	setTimeout(function(){
		
		$(".loadmore").remove(); 
		
		$('.content').append(result);  		//加载数据到body	
		
		ajax_data.finished=0;  				//允许下一次查询开始	
		
		//最后一页
		if( ajax_data.end > data.coupon.length ){  		//每次异步加载的数据量大于加载出来的数据，说明数据已经加载完，下次无需加载
		  
			ajax_data.sover=1;  			
			
		}

		//判断是否加载完数据，显示加载完毕标签
		if($('.not-used').length == 0){
			
			$('.not-coupon').show();		//显示加载完毕标签 
			
		}else{
			loadover(ajax_data.sover); 		//显示加载完毕标签 
		}
		
	},1000); 

	 
 }
 
 /***************函数部分**************/
</script> 

<script>


var n_p = "<?php echo $n_p;?>";//订单金额(不包括运费)
var user_id = "<?php echo $user_id;?>";
var coupon_object = localStorage.getItem('coupon_'+user_id); 	//读取localStorage的数据
var coupon_object_arr = JSON.parse(coupon_object);				//json转数组
//console.log(coupon_object_arr);

var envent_object = localStorage.getItem('envent_'+user_id); 	//读取localStorage的数据
var envent_object_arr = JSON.parse(envent_object);				//json转数组
//console.log(envent_object_arr);
//确认订单选择代金卷
function Change(id){
		
	var NeedMoney;
	var money ;
	if(id>0){				
		NeedMoney = $("#NeedMoney_"+id).data("needmoney");
		money = $("#NeedMoney_"+id).data("money");			
		if(n_p<NeedMoney){
			showAlertMsg("提示",'金额未达到,不可选',"知道了");	//弹出警告
			return;
		}
	}	
	
	/*保存到本地存储*/
	var arr = new Array(id,NeedMoney,money,user_id);			
	var rtn_array_json = JSON.stringify(arr);				//转JSON					
	localStorage.setItem('coupon_'+user_id,rtn_array_json);	//存入localStorage

	envent_object_arr.event1 = 'coupon';
	//console.log(envent_object_arr);
	var event_arr_json = JSON.stringify(envent_object_arr);			//转JSON	
	//console.log(event_arr_json);
	localStorage.setItem('envent_'+user_id,event_arr_json);			//初始化被动事件存入localStorage
	
	
	/*保存到本地存储*/
	
	history.replaceState({},'','order_form.php?customer_id<?php echo $customer_id_en ;?>');	
		
	/* 将GET方法改为POST ----start---*/
	var strurl = "order_form.php?customer_id=<?php echo $customer_id_en;?>";
	
	var objform = document.createElement('form');
	document.body.appendChild(objform);
	
	/*var rtn_coupon_array = new Array();	
	rtn_coupon_array.push(NeedMoney,money);
	
	//选择的代金券ID		
	var obj_p = document.createElement("input");
	obj_p.type = "hidden";
	objform.appendChild(obj_p);
	obj_p.value = id;
	obj_p.name = 'select_coupon_id';
	
	//代金券信息
	var obj_p = document.createElement("input");
	obj_p.type = "hidden";
	objform.appendChild(obj_p);
	obj_p.value = rtn_coupon_array;
	obj_p.name = 'rtn_coupon_array'*/;
	
	objform.action = strurl;
	objform.method = "POST"
	objform.submit();
	/* 将GET方法改为POST ----end---*/
			
	
}

</script>
<script type="text/javascript" src="./js/global.js"></script>
 </body>
	<?php mysql_close($link);?>
</html>