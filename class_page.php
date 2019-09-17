<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
require('../customer_id_decrypt.php');
require('../common/utility.php');
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
require('../proxy_info.php');
//头文件----start
require('../common/common_from.php');
//头文件----end
require('select_skin.php');
$new_baseurl = "http://".$http_host; //新商城图片显示
$owner_id =-1; //我的微店分类
if(!empty($_GET["owner_id"])){
   $owner_id=$configutil->splash_new($_GET["owner_id"]);
   $query="select title from weixin_commonshop_owners where isvalid=true and id=".$owner_id;
   
   $result = mysql_query($query) or die('Query failed: ' . mysql_error());   
   while ($row = mysql_fetch_object($result)) {
      $shop_name = $row->title;
	  
	  $query2="select type_id from weixin_commonshop_owner_types where isvalid=true and owner_id=".$owner_id;
	   $result2 = mysql_query($query2) or die('Query failed: ' . mysql_error());   
	   while ($row2 = mysql_fetch_object($result2)) {
		   $o_type_id = $row2->type_id;
		   if($owner_typeids==""){
			  $owner_typeids= $owner_typeids.$o_type_id;
		   }else{
			  $owner_typeids= $owner_typeids.",".$o_type_id;
		   }
	   }
	      
   }
}

//V7.0分类新排序
$sort_str="";
$type_sort="select sort_str from weixin_commonshop_type_sort where customer_id=".$customer_id."";
$result_type=mysql_query($type_sort) or die ('type_sort faild' .mysql_error());
while($row=mysql_fetch_object($result_type)){
   $sort_str=$row->sort_str;									   
}

$query= "select id,name from weixin_commonshop_types where isvalid=true and parent_id=-1 and is_shelves=1 and customer_id=".$customer_id; 
if(!empty($owner_typeids)){
	$query = $query." and id in(".$owner_typeids.")";
}

if($sort_str){
	$query =$query.' order by field(id'.$sort_str.')';  
}
$i=0;
$typearr=[];
$result = mysql_query($query) or die('Query failedff: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
	$pt_id = $row->id;
	$pt_name = $row->name;
	$typearr[]=$pt_id."_".$pt_name;
	
}
$typefirstarr=explode('_',$typearr[0]);
$typefirstid=$typefirstarr[0]; //获取第一个分类ID

$brand_adimg="";
$brand_adurl="#";
$brand="select wc.isOpenBrandSupply as isOpenBrandSupply,wcs.brand_adimg as brand_adimg ,wcs.brand_adurl as brand_adurl from weixin_commonshop_supplys wcs inner join weixin_commonshops wc on wc.isvalid=true and wcs.customer_id=wc.customer_id and wc.customer_id=".$customer_id."";
$result_brand=mysql_query($brand) or die ('brand2 faild' .mysql_error());
while($row=mysql_fetch_object($result_brand)){
	$isOpenBrandSupply=$row->isOpenBrandSupply;
	$brand_adimg=$row->brand_adimg;
	$brand_adurl=$row->brand_adurl;
}
$brandsupply=-1;
if($isOpenBrandSupply){
	$brandsupply=1; //是否开启品牌供应商
}
$page_type="class_page";// 作为底部菜单高亮的判断 list为列表页，class_page 为分类页
?>

<!DOCTYPE html>
<html>
<head>
    <title>分类</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta content="no" name="apple-touch-fullscreen">
    <meta name="MobileOptimized" content="320"/>
    <meta name="format-detection" content="telephone=no">
    <meta name=apple-mobile-web-app-capable content=yes>
    <meta name=apple-mobile-web-app-status-bar-style content=black>
    <meta http-equiv="pragma" content="nocache">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
	<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE8">
    
    <link type="text/css" rel="stylesheet" href="./assets/css/amazeui.min.css" />
    <link type="text/css" rel="stylesheet" href="./css/order_css/global.css" /> 
	<link type="text/css" rel="stylesheet" href="./css/css_<?php echo $skin ?>.css" /> 

    <style type="text/css">
        
        #leftArea{padding:0;width:25%;overflow-y:auto;background-color: #f3f4f6;}
        #rightArea{padding:0px;width:75%;position:relative;overflow: auto;}
        #advertArea{width: 100%;/*position: absolute;*/top:0px;background-color: white;}
        #advertArea .advert{padding:10px 5px 5px 5px;}
        .am-intro-hd h3{font-size:15px;font-weight: normal; color:#a0a0a0;margin-left:5px;}
        .am-intro-hd a{color:#191919;}
        .am-intro-hd a span{width:auto;height:20px;line-height: 20px;}
        .am-intro-hd a img{margin-left:5px;width: 7px;margin-bottom: 2px;}
        #subTypeList{padding:15px 5px 0px 5px;}
        .clear{clear: both; display: block; height: 0; overflow: hidden; visibility: hidden; width: 0;}
        #productDiv .type-left-button{border-top:none;}
        #productDiv .type-left-button:first-child{border-top:1px solid #dedbd5;}
        #productDiv .type-left-button:first-child.select{border-top:none;}

        .am-intro-title{
        	white-space:nowrap; 
			width:12em; 
			overflow:hidden;
        }
        
    </style>
</head>
<!-- Loading Screen -->


<link type="text/css" rel="stylesheet" href="./css/vic.css" />

<body data-ctrl=true class="white-back">
	<!-- <header data-am-widget="header" class="am-header am-header-default">
		<div class="am-header-left am-header-nav" onclick="history.go(-1);">
			<img class="am-header-icon-custom" src="./images/center/nav_bar_back.png"/><span>返回</span>
		</div>
	    <h1 class="am-header-title">分类</h1>
        <div class="am-header-right am-header-nav">
            <img class="am-header-icon-custom" src="./images/center/nav_home.png" />
        </div>
	</header>
    <div class="topDiv"></div> --><!-- 暂时隐藏头部导航栏 -->
    
    <div id="shopTypeContainerDiv">
        <div class="topDivSerch" >
            <div class="am-input-group" style="display:block;">
                <input id="tvKeyword" class="am-form-field search" type="text" placeholder="搜索" style="border-radius:3px;">
                <!--<span class="am-input-group-btn">
                    <button onclick="searchData(0);" class="title_serch" type="button" >搜索</button>
                </span>
				-->
            </div>
        </div>
        <div style="height:52px;"></div> <!-- 占据搜索框的位置-->
    	<div id="main-body">
            <div class="productDiv" id="productDiv">
                <div id="leftArea" class="am-intro-left am-u-sm-3">
					<?php if($brandsupply>0){//判断有无开启品牌供应商?>
                    <div class="type-left-button" typeid="-1">
                        <div>品牌分类</div>
                    </div>
                    <?php }?>
					<?php

						for($i=0;$i<sizeof($typearr);$i++){
						$typestr=explode('_',$typearr[$i]);
						
					?>
					<div class="type-left-button <?php if($i==0) echo "select";?>" typeid="<?php echo $typestr[0];?>" >
                        <div><?php echo $typestr[1];?></div>
                    </div>
					<?php }?>
				
                </div>
                <div id="rightArea" class="am-intro-right am-u-sm-9">
                    <div id="advertArea">
                        <div class="advert" id="type_adimg">
                            <!-- <img id="imgAdvert" class="wfull" src="./images/temp/vic/type_advert1.png" alt=""/> --><!-- 暂时隐藏 -->
                        </div>
                        <div data-am-widget="intro" class="am-intro am-cf am-intro-one">
                            <div class="am-intro-hd" id="type_parent">
                                <!--右边一级分类显示名字区域-->
                            </div>
                        </div>
                    </div> 
                    <div id="contentArea">
                        <ul id="subTypeList" data-am-widget="gallery" class="am-gallery am-avg-sm-3 am-gallery-default" data-am-gallery="{ pureview: false }">
                        </ul>
                    </div>   
                </div>
                <div class="clear"></div>
            </div>
        </div>
    </div>
<script type="text/javascript" src="./assets/js/jquery.min.js"></script> 	
    <!-- tabbar start -->
    <?php
	//底部菜单栏
	include_once("foot.php");
	?>
    <!-- tabbar end -->	
<script type="text/javascript">
    var winWidth = $(window).width();
    var winheight = $(window).height();
    var imgheight= $("#type_adimg").height();
    var customer_id='<?php echo $customer_id_en;?>';
	var owner_typeids="<?php echo $owner_typeids;?>";
	var new_baseurl="<?php echo $new_baseurl;?>";
	var brand_adimg="<?php echo $brand_adimg;?>"; 
	var brand_adurl="<?php echo $brand_adurl;?>";	
	var brandsupply="<?php echo $brandsupply;?>";
	var user_id=<?php echo $user_id;?>;

	$(function() {
         searchData(<?php echo $typefirstid;?>);
         
         adjustSize();  
	});
    
    $(window).resize(function() {
        adjustSize();
    });
    
    function adjustSize(){
    	$('#leftArea').height(winheight-105);
    	$('#rightArea').height(winheight-105);

    }
      
    function searchData(pt_id) { //点击分类
        var content = "";
        var content_parent="";
		var content_ad="";
		
		$("#type_parent").empty();
		$("#type_adimg").empty();
		$("#subTypeList").empty();
		$.ajax({
			type: "post",
			url: 'save_class_page.php',
			data: { pt_id: pt_id,customer_id: customer_id,owner_typeids: owner_typeids},
			success: function (result) {
				
				var Json = eval(result);

				for (var i = 0; i < Json.length; i++) {               			  
					content_parent +='<h3 class="am-intro-title" style="text-overflow:ellipsis">' + Json[i].gc_name + '</h3>';
					content_parent +='<a class="am-intro-more am-intro-more-top" href="list.php?customer_id='+customer_id+'&tid=' + Json[i].gc_id +'"><span>更多</span><img src="./images/vic/right_arrow.png"></a>';
					if(Json[i].type_adimg){
						content_ad +='<a href="'+ Json[i].type_adurl +'"><img id="imgAdvert" class="wfull" src="'+ new_baseurl+Json[i].type_adimg +'" alt=""/></a>';
					}
					var LJson = eval(Json[i].brandinfo);
				 
					for (var j = 0; j < LJson.length; j++) {
					
						content += '<li>';
						content += '    <div class="am-gallery-item">';
						content += '        <a href="list.php?customer_id=' + customer_id + '&tid=' + LJson[j].gb_id +'">';
						content += '            <img src="' + LJson[j].gb_logo.toString() + '" alt=""/>';					
						content += '        </a>';
						content += '    </div>';
						content += '</li>';
					}
					$("#subTypeList").html(content);
				}
				
				$("#type_parent").html(content_parent);
				$("#type_adimg").html(content_ad);
			}    
		});
			
    }
    
	function brandimg(){
		var content = "";
		var content_parent="";
		var content_ad="";
		$.ajax({
			type: "post",
			url: 'save_class_page_brand.php',
			data: {customer_id: customer_id},
			success: function (result) {
				var Json = eval(result);
				for (var i = 0; i < Json.length; i++) {               			  
					content += '<li>';
					content += '    <div class="am-gallery-item" >';
					content += '        <a href="list.php?customer_id='+customer_id+'&supply_id='+Json[i].user_id+'">';
					content += '            <img src="' + Json[i].brand_logo + '" alt=""/>';					
					content += '        </a>';
					content += '    </div>';
					content += '</li>';
				}
				$("#subTypeList").html(content);
				content_parent +='<h3 class="am-intro-title" style="text-overflow:ellipsis">品牌分类</h3>';
				//content_parent +='<a class="am-intro-more am-intro-more-top" href="list.php?customer_id='+customer_id+'"><span>更多</span><img src="./images/vic/right_arrow.png"></a>';
				$("#type_parent").html(content_parent);
				if(brand_adimg){
					content_ad +='<a href="'+brand_adurl+'"><img id="imgAdvert" class="wfull" src="'+ new_baseurl+brand_adimg +'" alt=""/></a>';
				}
				//$("#type_parent").css("display","none");
				$("#type_adimg").html(content_ad);

			}    
		});
		
		
		
	}
	
    $(".type-left-button").click(function(){
        $(".type-left-button").removeClass("select");
        $(this).addClass("select");
		typeid=$(this).attr("typeid");
		if(typeid>0){
			searchData($(this).attr("typeid"));
		}else{
			brandimg();
		}	
    });

    
    function goShop(shopID){
    	window.location = "wodedianpu.html";
    }
    $("#tvKeyword").click(function(){  //点击搜索栏跳转
		window.location.href="search.php?customer_id="+customer_id+"";
	})
</script>


<!--引入微信分享文件----start-->
<?php require('../common/share.php');?>
<!--引入微信分享文件----end-->
  
    <script type="text/javascript" src="./assets/js/amazeui.js"></script>
    <script type="text/javascript" src="./js/global.js"></script>
    <script type="text/javascript" src="./js/loading.js"></script>
    <script src="./js/jquery.ellipsis.js"></script>
    <script src="./js/jquery.ellipsis.unobtrusive.js"></script>
</body>
</html>