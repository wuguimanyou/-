<?php
header("Content-type: text/html; charset=utf-8"); //svn
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
require('../proxy_info.php');
require('../common/utility_msg.php');
require('../common/utility_fun.php');

mysql_query("SET NAMES UTF8");

$username=-1;
$password=-1;
$weixin_fromuser=-1;//用户标识
$weixin_headimgurl="";//用户头像
$status=-1; //返回的状态
$phone="";//电话号码
$op="";//操作，1为登陆，2为修改密码
$str->url="";
$urltype=-1; //跳转参数
if(!empty($_POST["op"])){
	$op = $configutil->splash_new($_POST["op"]);
}
if(!empty($_GET["urltype"])){
	$urltype = $configutil->splash_new($_GET["urltype"]);
}
if(!empty($_GET["customer_id"])){ //判断页面传过来的customer_id值
	$customer_id = $configutil->splash_new($_GET["customer_id"]);
	if(sql_check($customer_id)){
		$status=-2;
		$str->status=$status;
		echo json_encode($str);	
		return;
	}
	$customer_id=passport_decrypt((string)$customer_id);  //解密customer_id
}

if($customer_id<0){ //假如获取不了customer_id，就提示找不到商家
	$status=-3;
	$str->status=$status;
	echo json_encode($str);	
	return;
}

switch($op){
	case "login": //登陆操作
		if(!empty($_POST["username"])){
			$username = $configutil->splash_new($_POST["username"]);
		}
		if(!empty($_POST["password"])){
			$password = $configutil->splash_new($_POST["password"]);
		}
		if(sql_check($username) or sql_check($password)){
			$status=-2;
			$str->status=$status;
			echo json_encode($str);	
			return;
		}
		$password_en=md5($password);//MD5加密密码
		$query="select user_id,customer_id from system_user_t where isvalid=true and account=".$username." and password='".$password_en."' and customer_id=".$customer_id." limit 0,1";
		$result=mysql_query($query) or die ("login query faild" .mysql_error());
		while($row=mysql_fetch_object($result)){
			$user_id=$row->user_id;
			$customer_id=$row->customer_id;
			if(0<$user_id){ //当查询到用户才去查询fromuser
				$opid_query="select weixin_fromuser,weixin_headimgurl from weixin_users where isvalid=true and id=".$user_id." limit 0,1";
				$opid_result=mysql_query($opid_query) or die ("opid_query faild" .mysql_error());
				while($row=mysql_fetch_object($opid_result)){
					$weixin_fromuser=$row->weixin_fromuser;
					$weixin_headimgurl=$row->weixin_headimgurl;
				}
				$_SESSION["user_id_".$customer_id]		=$user_id;
				$_SESSION["myfromuser_".$customer_id]	=$weixin_fromuser;
				$_SESSION["fromuser_".$customer_id]		=$weixin_fromuser;
				$_SESSION["is_bind_".$customer_id]		=1;//已经注册
				setcookie("login_headimgurl",$weixin_headimgurl, time()+604800);//设置用户头像COOKIE
				setcookie("login_username",$username, time()+604800);//设置用户登录账号
				setcookie("login_password",$password, time()+604800);//设置用户登录密码
				$status=1;//登陆成功状态
				//$str->url="../common_shop/jiushop/index.php?customer_id=".passport_encrypt($customer_id)."";
				if(empty($_SESSION["nurl_".$customer_id])){
					if($urltype){
						switch ($urltype){
							//自行添加
							case 20://线下商城
								$str->url="../city_area/shop/index.php?customer_id=".passport_encrypt($customer_id)."";
								break;
							default://线上商城首页
								$str->url="../common_shop/jiushop/index.php?customer_id=".passport_encrypt($customer_id)."";
								break;
						}
						
					}else{ //没有session 以及type就跳转首页
						$str->url="../common_shop/jiushop/index.php?customer_id=".passport_encrypt($customer_id)."";
					}
				}else{//优先跳转session
					$str->url=$_SESSION["nurl_".$customer_id];
				}
			}
		}

		$str->status=$status;

		echo json_encode($str);
		break;
	
	case "send"://发送验证码
		
		if(!empty($_POST["phone"])){
			$mobile = $configutil->splash_new($_POST["phone"]);
		}
		
		session_start();//开启缓存
		$_SESSION['time'.$mobile] = date("Y-m-d H:i:s");
		
		srand((double)microtime()*1000000);//create a random number feed.	
		$ychar="0,1,2,3,4,5,6,7,8,9";
		$list=explode(",",$ychar);
		for($i=0;$i<6;$i++){
			$randnum=rand(0,9); 
			$authnum.=$list[$randnum]; //生成的验证码
		}

		$result = "";

		if(!empty($mobile)){
			if($mobile) { 
				$_SESSION['phone'] = $mobile;
				$_SESSION['msg_mcode_'.$mobile] = $authnum; 
			
				$mcode = $_SESSION['msg_mcode_'.$mobile]; 

				$acount=0;
				$query="select acount from sms_settings where isvalid=true and customer_id=".$customer_id;
				//echo $query;
				$result = mysql_query($query) or die('Query failed: ' . mysql_error());
				while ($row = mysql_fetch_object($result)) {
					$acount = $row->acount;
				}
					if( $acount>0){
						$shop_name = '微商城';
						$query = "select name from weixin_commonshops where isvalid=true and customer_id=".$customer_id."";
						$result=mysql_query($query)or die('Query failed'.mysql_error());
						while($row=mysql_fetch_object($result)){
							$shop_name = $row->name;
						}
						$content="【".$shop_name."】用户正在修改密码，验证码是：".$mcode.".如非本人操作请联系商家";
						
						$commUtil=new publicmessage_Utlity;
						
						if($commUtil->remindMsgNum($customer_id,$mobile,$content)){
							$result="短信发送成功";
						}else{
							$result="短信发送失败";
						}

					} //调试不成功自行查询数据库

			} 

		}
		
		$out = json_encode($result);
		echo $out;
		break;	
	
	case "edit"://修改密码
		
		if(!empty($_POST["phone"])){ //电话号码
			$mobile = $configutil->splash_new($_POST["phone"]);
		}
		if(!empty($_POST["yzm"])){	//验证码
			$yzm = $configutil->splash_new($_POST["yzm"]);
		}
		if(!empty($_POST["password"])){	//密码
			$password = $configutil->splash_new($_POST["password"]);
		}
		
		if((strtotime($_SESSION['time'.$mobile])+180)<time()) {//将获取的缓存时间转换成时间戳加上180秒后与当前时间比较，小于当前时间即为过期
		
			$_SESSION['time'.$mobile] = '';		//清空
			$arr = array('code' => 10003, 'msg' => '验证码已过期！');
			echo json_encode($arr,JSON_UNESCAPED_UNICODE);
			exit;
		}

		if(!isset($_SESSION['msg_mcode_'.$mobile])){
			$arr = array('code' => 10001, 'msg' => '验证码已过期');
			echo json_encode($arr,JSON_UNESCAPED_UNICODE);
			exit;
		}
		
		if($_SESSION['msg_mcode_'.$mobile] != $yzm){
			$arr = array('code' => 10004, 'msg' => '验证码错误');
			echo json_encode($arr,JSON_UNESCAPED_UNICODE);
			exit;
		}
		//----当验证码成功
		if($_SESSION['msg_mcode_'.$mobile] == $yzm){
			if(sql_check($username) or sql_check($password)){ //判断非法参数
				$arr = array('code' => 10009, 'msg' => '非法参数');
				echo json_encode($arr,JSON_UNESCAPED_UNICODE);
				exit;
			}
			
			$password=md5($password);
			
			$update_password="update system_user_t set password='".$password."' where isvalid=true and account=".$mobile." and customer_id=".$customer_id."";
			mysql_query($update_password) or die ("update_password faild " .mysql_error());
			$count = mysql_affected_rows();
			if($count>0){
				$arr = array('code' => 10010, 'msg' => '密码修改成功，请重新登录');
			}else{
				$arr = array('code' => 10011, 'msg' => '密码修改失败');
			}
			
			echo json_encode($arr,JSON_UNESCAPED_UNICODE);
					
		}
		break;

		case "edit_paypassword":
		
			if(!empty($_POST["phone"])){ //电话号码
				$mobile = $configutil->splash_new($_POST["phone"]);
			}

			$query = "SELECT user_id FROM system_user_t WHERE isvalid=true AND customer_id=$customer_id AND account = $mobile LIMIT 1";
			$result= mysql_query($query) or die ("query faild 230" .mysql_error());
			while( $row = mysql_fetch_object($result) ){
				$user_id = $row->user_id;
			}

			if(!empty($_POST["yzm"])){	//验证码
				$yzm = $configutil->splash_new($_POST["yzm"]);
			}
			if(!empty($_POST["password"])){	//密码
				$password = $configutil->splash_new($_POST["password"]);
			}
			
//			if((strtotime($_SESSION['time'.$mobile])+180)<time()) {//将获取的缓存时间转换成时间戳加上180秒后与当前时间比较，小于当前时间即为过期
//
//				$_SESSION['time'.$mobile] = '';		//清空
//				$arr = array('code' => 10003, 'msg' => '验证码已过期！');
//				echo json_encode($arr,JSON_UNESCAPED_UNICODE);
//				exit;
//			}
            // by chen 2017-07-24 start
//			if(!isset($_SESSION['msg_mcode_'.$mobile])){
//				$arr = array('code' => 10001, 'msg' => '验证码已过期');
//				echo json_encode($arr,JSON_UNESCAPED_UNICODE);
//				exit;
//			}
//
//			if($_SESSION['msg_mcode_'.$mobile] != $yzm){
//				$arr = array('code' => 10004, 'msg' => '验证码错误');
//				echo json_encode($arr,JSON_UNESCAPED_UNICODE);
//				exit;
//			}
            // by chen 2017-07-24 start
			//----当验证码成功
//			if($_SESSION['msg_mcode_'.$mobile] == $yzm){
//				if(sql_check($username) or sql_check($password)){ //判断非法参数
//					$arr = array('code' => 10009, 'msg' => '非法参数');
//					echo json_encode($arr,JSON_UNESCAPED_UNICODE);
//					exit;
//				}
				
				$password=md5($password);
				
				$update_password="update user_paypassword set paypassword='".$password."' where isvalid=true and user_id=".$user_id." and customer_id=".$customer_id."";
				mysql_query($update_password) or die ("update_password faild " .mysql_error());
				$count = mysql_affected_rows();
				if($count>0){
					$arr = array('code' => 10010, 'msg' => '支付密码修改成功');
				}else{
					$arr = array('code' => 10011, 'msg' => '支付密码修改失败');
				}
				
				echo json_encode($arr,JSON_UNESCAPED_UNICODE);
						
//			}
		break;

		case "send_paypw_msg":

		if(!empty($_POST["phone"])){
			$mobile = $configutil->splash_new($_POST["phone"]);
		}
		// $mobile = '';
		// $query = "SELECT account FROM system_user_t WHERE isvalid=true AND user_id=$user_id AND customer_id = $customer_id LIMIT 1";
		// $result= mysql_query($query) or die('Query failed285: ' . mysql_error());
		// while( $row = mysql_fetch_object($result) ){
		// 	$mobile = $row->account;
		// }
		
		session_start();//开启缓存
		$_SESSION['time'.$mobile] = date("Y-m-d H:i:s");
		
		srand((double)microtime()*1000000);//create a random number feed.	
		$ychar="0,1,2,3,4,5,6,7,8,9";
		$list=explode(",",$ychar);
		for($i=0;$i<6;$i++){
			$randnum=rand(0,9); 
			$authnum.=$list[$randnum]; //生成的验证码
		}

		$result = "";

		if(!empty($mobile)){
			if($mobile) { 
				$_SESSION['phone'] = $mobile;
				$_SESSION['msg_mcode_'.$mobile] = $authnum; 
			
				$mcode = $_SESSION['msg_mcode_'.$mobile]; 

				$acount=0;
				$query="select acount from sms_settings where isvalid=true and customer_id=".$customer_id;
				//echo $query;
				$result = mysql_query($query) or die('Query failed 313 : ' . mysql_error());
				while ($row = mysql_fetch_object($result)) {
					$acount = $row->acount;
				}
					if( $acount>0){
						$shop_name = '微商城';
						$query = "select name from weixin_commonshops where isvalid=true and customer_id=".$customer_id."";
						$result=mysql_query($query)or die('Query failed 320 '.mysql_error());
						while($row=mysql_fetch_object($result)){
							$shop_name = $row->name;
						}
						$content="【".$shop_name."】用户正在修改支付密码，验证码是：".$mcode.".如非本人操作请联系商家";
						
						$commUtil=new publicmessage_Utlity;
						
						if($commUtil->remindMsgNum($customer_id,$mobile,$content)){
							$result="短信发送成功";
						}else{
							$result="短信发送失败";
						}

					} //调试不成功自行查询数据库

			} 

		}
		
		$out = json_encode($result);
		echo $out;

		break;
}

mysql_close($link);
	

?>