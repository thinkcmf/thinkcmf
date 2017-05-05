<?php
namespace app\api\controller;

use Think\Controller\RestController;

class UserController extends RestController {
    public function index(){
        $data = ['code' => 200, 'msg' => 'restful api'];
        $this->response($data,'json',200);
    }
    public function test(){
    	$username = '18310715463';
    	$new_sign = sha1(md5(date("YmdHi").'&%@$Xi768'.$username));
    	echo $new_sign;
    	echo '<br >';
    	echo sha1(md5(date("YmdHi").'&%@$Xi768'));
    	/*$params = urldecode(file_get_contents("php://input"));
    	$data = json_decode($params , true);
//     	var_dump($data);
    		foreach($data['img'] as $key=>$value){
    			var_dump($value);
	    		$fileext = pathinfo($value["name"], PATHINFO_EXTENSION);//文件后缀
	    		$new_name = "Uploads/attached/".time().$key.".".$fileext;
	    		$rs = copy($value["tmp_name"], $new_name);
	    		$image = new \Think\Image();
	    		$image->open($new_name);
	    		$imagestrmin = substr($new_name,0,-4).'min.'.$fileext;
	    		
	    		$width = $image->width(); // 返回图片的宽度
	    		$height = $image->height(); // 返回图片的高度
	    		$newwidth = 300;
	    		$pre = $newwidth/$width;
	    		$newheight = ceil($height*$pre);
	    		$image->thumb($newwidth, $newheight)->save($imagestrmin);//等比例裁剪
	    		$imgurl2 = $this->_upload_oss(array('name' => $new_name,'size' => 10240,'tmp_name' => $new_name));//上传oss
	    		$imgurl3 = $this->_upload_oss(array('name' => $new_name,'size' => 10240,'tmp_name' => $imagestrmin));//上传oss
	    		@unlink($new_name);@unlink($imagestrmin);//删除本地图片
    		}*/
    }
    
    public function checkUser(){
    	$signValue = I("get.signValue");
    	$phone = I("get.phone");
    	if(empty($signValue) || empty($phone)){
    		$data['code'] = '403';
    		$data['msg'] = '缺少参数';
    		$this->response($data , 'json' , 403);
    	}
    	if(checkSign($signValue)){
    		$user = M("User");
    		$id = $user->where("phone=$phone")->getField("id");
    		if($id){
    			$data['code'] = '403';
    			$data['msg'] = '用户已存在';
    			$this->response($data , 'json' , 403);
    		}else{
    			$data['code'] = '200';
    			$data['msg'] = '成功';
    			$this->response($data , 'json' , 200);
    		}
    	}else{
    		$data['code'] = '403';
    		$data['msg'] = '验签失败';
    		$this->response($data , 'json' , 403);
    	}
    	
    }//验证用户是否存在
    
    public function sendCode(){
    	$signValue = I("get.signValue");
    	$phone = I("get.phone");
    	if(empty($signValue) || empty($phone)){
    		$data['code'] = '403';
    		$data['msg'] = '缺少参数';
    		$data['yzm'] = '';
    		$this->response($data , 'json' , 403);
    	}
    	if(checkSign2($signValue, $phone)){
    		$code = mt_rand(100000, 999999);
//     		session('code' , $code);
			$gets = HttpBatchSendSM($phone,"【美术空间】您的验证码是：".$code.",来自美术空间。如需帮助请联系客服。");
   			if($gets == 0){
   				$data['code'] = 200;
   				$data['msg'] = '短信发送成功';
    			$data['yzm'] = "$code";
    			$this->response($data , 'json' , 200);
    		}else{
   				$data['code'] = 403;
    			$data['msg'] = '短信发送失败';
   				$data['yzm'] = '成功';
   				$this->response($data , 'json' , 403);
   			}
    	}else{
    		$data['code'] = '403';
    		$data['msg'] = '验签失败';
    		$data['yzm'] = '';
    		$this->response($data , 'json' , 403);
    	}
    }//发送验证码

	public function settoken(){
		$params = urldecode(file_get_contents("php://input"));
		if($params){
			$data = json_decode($params , true);
			if(empty($data['signValue']) || empty($data['uid']) || empty($data['token'])){
				$data2['code'] = '403';
				$data2['msg'] = '缺少参数';
				$data2['userid'] = 0;
				$data2['role'] = 0;
				$this->response($data2 , 'json' , 403);
			}else{
				if(checkSign($data['signValue'])) {
					$user = M('User');

					//踢下别的账户
					$user_rs = $user->where("token='$data[token]'")->field("id")->select();
					if($user_rs[0][id]){
						foreach($user_rs as $value){
							$user->where("id=$value[id]")->data(array("token"=>""))->save();
						}
					}
					$user->data(array("token"=>$data['token']))->where("id=".$data['uid'])->save();

					$data2['code'] = '200';
					$data2['msg'] = '成功';
					$this->response($data2 , 'json' , 200);
				}else{
					$data2['code'] = '403';
					$data2['msg'] = '验签失败';
					$data2['userid'] = 0;
					$data2['role'] = 0;
					$this->response($data2 , 'json' , 403);
				}
			}
		}else{
			$data2['code'] = '403';
			$data2['msg'] = '数据获取失败';
			$data2['userid'] = 0;
			$data2['role'] = 0;
			$this->response($data2 , 'json' , 403);
		}
	}
    
    public function addUser(){
    	$params = urldecode(file_get_contents("php://input"));
    	if($params){
	    	$data = json_decode($params , true);
	    	if(empty($data['signValue']) || empty($data['phone']) || empty($data['password'])){
				// 清哥让去掉    || empty($data['token'])
	    		$data2['code'] = '403';
	    		$data2['msg'] = '缺少参数';
	    		$data2['userid'] = 0;
	    		$data2['role'] = 0;
	    		$this->response($data2 , 'json' , 403);
	    	}
	    	if(checkSign($data['signValue'])){
	    		$user = M('User');
	    		$user->phone = $data['phone'];
	    		$user->password = $data['password'];
	    		$user->role = 1;
	    		$user->fid = $data['fid'];
	    		$user->token = empty($data['token']) ? '' : $data['token'];
	    		$user->ctime = time();
	    		$insertid = $user->add();
	    		if($insertid){
	    			addVoucher($insertid, 60, 7, 1);
	    			$data2['code'] = '200';
	    			$data2['msg'] = '成功';
	    			$data2['userid'] = $insertid;
	    			$data2['role'] = 1;
	    			$this->response($data2 , 'json' , 200);
	    		}else{
	    			$data2['code'] = '403';
	    			$data2['msg'] = '用户注册失败';
	    			$data2['userid'] = 0;
	    			$data2['role'] = 0;
	    			$this->response($data2 , 'json' , 403);
	    		}
	    	}else{
	    		$data2['code'] = '403';
	    		$data2['msg'] = '验签失败';
	    		$data2['userid'] = 0;
	    		$data2['role'] = 0;
	    		$this->response($data2 , 'json' , 403);
	    	}
    	}else{
    		$data2['code'] = '403';
    		$data2['msg'] = '数据获取失败';
    		$data2['userid'] = 0;
    		$data2['role'] = 0;
    		$this->response($data2 , 'json' , 403);
    	}
    }//注册用户
    
    public function login(){
    	$params = urldecode(file_get_contents("php://input"));
    	if($params){
    		$data = json_decode($params , true);
    		if(empty($data['signValue']) || empty($data['phone']) || empty($data['password'])){
				// 清哥让去掉    || empty($data['token'])
    			$data2['code'] = '403';
    			$data2['msg'] = '缺少参数';
    			$data['userid'] = 0;
    			$data2['role'] = 0;
    			$data2['token'] = '';
    			$this->response($data2 , 'json' , 403);
    		}
    		if(checkSign($data['signValue'])){
    			$user = M('User');
    			$info = $user->where("phone=".$data['phone'])->field("id, password,role,token")->find();
    			if($info){
    				if($info['password'] != $data['password']){
    					$data2['code'] = '403';
    					$data2['msg'] = '账户或密码不正确';
    					$data2['userid'] = 0;
    					$data2['role'] = 0;
    					$data2['token'] = '';
    					$this->response($data2 , 'json' , 403);
    				}else{
    					//踢下别的账户
						if(!empty($data[token])) {
							$user_rs = $user->where("token='$data[token]'")->field("id")->select();
							if ($user_rs[0][id]) {
								foreach ($user_rs as $value) {
									$user->where("id=$value[id]")->data(array("token" => ""))->save();
								}
							}
							$user->data(array("token" => $data['token']))->where("phone=" . $data['phone'])->save(); //记录token
						}
    					//$new_token = $user->where("phone=".$data['phone'])->getField("token");
    					$data2['code'] = '200';
    					$data2['msg'] = '成功';
    					$data2['userid'] = $info['id'];
    					$data2['role'] = $info['role'];
    					$data2['token'] = $info['token'];
    					$this->response($data2 , 'json' , 200);
    				}
    			}else{
    				$data2['code'] = '403';
    				$data2['msg'] = '用户不存在';
    				$data2['userid'] = 0;
    				$data2['role'] = 0;
    				$data2['token'] = '';
    				$this->response($data2 , 'json' , 403);
    			}
    		}else{
    			$data2['code'] = '403';
    			$data2['msg'] = '验签失败';
    			$data2['userid'] = 0;
    			$data2['role'] = 0;
    			$data2['token'] = '';
    			$this->response($data2 , 'json' , 403);
    		}
    	}else{
    		$data2['code'] = '403';
    		$data2['msg'] = '数据获取失败';
    		$data2['userid'] = 0;
    		$data2['role'] = 0;
    		$data2['token'] = '';
    		$this->response($data2 , 'json' , 403);
    	}
    	
    }//登录

	public function feedback(){
		$params = urldecode(file_get_contents("php://input"));
		if(!empty($params)){
			$array = json_decode($params , true);
			if(empty($array['signValue']) || empty($array['userid']) || empty($array['content'])){
				$data['code'] = '403';
				$data['msg'] = '缺少参数';
				$this->response($data , 'json' , 403);
			}
			if(checkSign($array['signValue'])){
				M('feedback')->data(array(
					"uid"	  =>	$array['userid'],
					"content" =>	$array['content'],
					"ctime"	  =>	time()
				))->add();
				$data['code'] = '200';
				$data['msg'] = '成功';
				$this->response($data , 'json' , 200);
			}else{
				$data['code'] = '403';
				$data['msg'] = '验签失败';
				$this->response($data , 'json' , 403);
			}
		}else{
			$data['code'] = '403';
			$data['msg'] = '数据获取失败';
			$this->response($data , 'json' , 403);
		}
	}//用户反馈接口

	public function report(){
		$params = urldecode(file_get_contents("php://input"));
		if(!empty($params)){
			$array = json_decode($params , true);
			if(empty($array['signValue']) || empty($array['uid']) || empty($array['juid']) || empty($array['content'])){
				$data['code'] = '403';
				$data['msg'] = '缺少参数';
				$this->response($data , 'json' , 403);
			}
			if(checkSign($array['signValue'])){
				M('report')->data(array(
					"uid"	  =>	$array['uid'],
					"type"	  =>    empty($array['type']) ? 0 : $array['type'],
					"juid"	  =>	$array['juid'],
					"content" =>	$array['content'],
					"ctime"	  =>	time()
				))->add();
				$data['code'] = '200';
				$data['msg'] = '成功';
				$this->response($data , 'json' , 200);
			}else{
				$data['code'] = '403';
				$data['msg'] = '验签失败';
				$this->response($data , 'json' , 403);
			}
		}else{
			$data['code'] = '403';
			$data['msg'] = '数据获取失败';
			$this->response($data , 'json' , 403);
		}
	}//用户举报接口

	public function authentication(){
		$params = urldecode(file_get_contents("php://input"));
		if(!empty($params)){
			$array = json_decode($params , true);
			if(empty($array['signValue']) || empty($array['userid']) || empty($array['name']) || empty($array['record']) || empty($array['certificatecode']) || empty($array['certificate']) || empty($array['rank']) || empty($array['image']) || empty($array['identity_front']) || empty($array['identity_opposite']) || empty($array['qprice'])){
				$data['code'] = '403';
				$data['msg'] = '缺少参数';
				$this->response($data , 'json' , 403);
			}
			if(checkSign($array['signValue'])){
				if($array['action'] == 'edit'){
					M("authentication")->data(array(
						"uid"				=>	$array['userid'],
						"name"				=>	$array['name'],
						"image"				=>	$array['image'],
						"rank"				=>	$array['rank'],
						"record"			=>	$array['record'],
						"certificate"		=>	$array['certificate'],
						"certificatecode"	=>	$array['certificatecode'],
						"identity_front"	=>	$array['identity_front'],
						"identity_opposite"	=>	$array['identity_opposite']
					))->where("uid=$array[userid]")->save();
				}else{
					M("authentication")->data(array(
						"uid"				=>	$array['userid'],
						"name"				=>	$array['name'],
						"image"				=>	$array['image'],
						"rank"				=>	$array['rank'],
						"record"			=>	$array['record'],
						"certificate"		=>	$array['certificate'],
						"certificatecode"	=>	$array['certificatecode'],
						"identity_front"	=>	$array['identity_front'],
						"identity_opposite"	=>	$array['identity_opposite'],
						"ctime"	 			=>	time()
					))->add();
				}
				M("user")->data(array("qprice"=>$array['qprice']))->where("id=$array[userid]")->save();
				$data['code'] = '200';
				$data['msg'] = '成功';
				$this->response($data , 'json' , 200);
			}else{
				$data['code'] = '403';
				$data['msg'] = '验签失败';
				$this->response($data , 'json' , 403);
			}
		}else{
			$data['code'] = '403';
			$data['msg'] = '数据获取失败';
			$this->response($data , 'json' , 403);
		}
	}//用户提交认证资料接口

	public function isLookHigh(){

	}//判断用户是否可以查看高清图片接口

    
    public function getUserInfo(){
    	$userid = I("get.userid");
    	$signValue = I("get.signValue");
    	if(empty($signValue) || empty($userid)){
    		$data['code'] = '403';
    		$data['msg'] = '缺少参数';
    		$data['userinfo'] = null;
    		$this->response($data, 'json' , 403);
    	}
    	if(checkSign($signValue)){
    		$user = M('User');
    		$authentication = M('Authentication');
    		$userinfo = $user->where("id=$userid")->field("role,username,phone,img as image,name,sex,birthday,address,sign,plrange,qnum,qprice,email,school,company,occupation,background,praise")->find();
    		$auth = $authentication->where("uid=$userid")->field("id, rank")->find();
    		if($userinfo){
    			if($auth['id']){
    				$userinfo['isauth'] = 1;
    			}else{
    				$userinfo['isauth'] = 0;
    			}
    			$userinfo['rank'] = $auth['rank'];
    			$userinfo['attention'] = attentionNum($userid);
    			$userinfo['fsnum'] = fensiNum($userid);
    			$userinfo['evaluate'] = evaluateNum($userid);
	    		$data['code'] = '200';
	    		$data['msg'] = '成功';
	    		$data['userinfo'] = $userinfo;
	    		$data['useropus'] = $this->_useropus($userid);
	    		$this->response($data, 'json', 200);
    		}else{
    			$data['code'] = '403';
    			$data['msg'] = '获取数据失败';
    			$data['userinfo'] = null;
    			$this->response($data, 'json' , 403);
    		}
    	}else{
    		$data['code'] = '403';
    		$data['msg'] = '验签失败';
    		$data['userinfo'] = null;
    		$this->response($data, 'json', 403);
    	}
    }//获得用户信息

	/*修改用户信息*/
    public function saveUserInfo(){
    	$params = urldecode(file_get_contents("php://input"));
//     	var_dump($params);
    	if($params){
    		$data = json_decode($params , true);
    		if(empty($data['signValue']) || empty($data['userid'])){
    			$data2['code'] = '403';
    			$data2['msg'] = '缺少参数';
    			$this->response($data2 , 'json' , 403);
    		}
    		if(checkSign($data['signValue'])){
//     	    if($data['signValue']){
    			$user = M('User');
    			if($data['username']){
    				$savedata['username'] = $data['username'];
    			}
    			if($data['image']){
//     				$oldimg = $user->where("phone='$data[phone]'")->getField('img');
//     				$img = uploadfile2($data['img'] , $oldimg, 'user');
    				$savedata['img'] = $data['image'];
    			}
    			if($data['sex']){ 
    				$savedata['sex'] = $data['sex'];
    			}
    			if($data['birthday']){
    				$savedata['birthday'] = $data['birthday'];
    			}
    			if($data['sign']){
    				$savedata['sign'] = $data['sign'];
    			}
    			if($data['name']){
    				$savedata['name'] = $data['name'];
    			}
    			if($data['address']){
    				$savedata['address'] = $data['address'];
    			}
    			if($data['password']){
    				$savedata['password'] = $data['password'];
    			}
    			if($data['plrange']){
    				$savedata['plrange'] = $data['plrange'];
    			}
    			if($data['qprice']){
    				$savedata['qprice'] = $data['qprice'];
    			}

				/*新加字段*/
				if($data['email']){
					$savedata['email'] = $data['email'];
				}
				if($data['school']){
					$savedata['school'] = $data['school'];
				}
				if($data['company']){
					$savedata['company'] = $data['company'];
				}
				if($data['occupation']){
					$savedata['occupation'] = $data['occupation'];
				}
				if($data['background']){
					$savedata['background'] = $data['background'];
				}

    			if($savedata){
    				$rs = $user->data($savedata)->where("id='$data[userid]'")->save();
    			}
    			/*if($data['image_list']){
    				foreach($data['image_list'] as $key=>$value){
    					$fileext = pathinfo($value["name"], PATHINFO_EXTENSION);//文件后缀
			    		$new_name = "Uploads/attached/".time().$key.".".$fileext;
			    		$rs = copy($value["tmp_name"], $new_name);
			    		$image = new \Think\Image();
			    		$image->open($new_name);
			    		$imagestrmin = substr($new_name,0,-4).'min.'.$fileext;
			    		
			    		$width = $image->width(); // 返回图片的宽度
			    		$height = $image->height(); // 返回图片的高度
			    		$newwidth = 300;
			    		$pre = $newwidth/$width;
			    		$newheight = ceil($height*$pre);
			    		$image->thumb($newwidth, $newheight)->save($imagestrmin);//等比例裁剪
			    		$imgurl2 = $this->_upload_oss(array('name' => $new_name,'size' => 10240,'tmp_name' => $new_name));//上传oss
			    		$imgurl3 = $this->_upload_oss(array('name' => $new_name,'size' => 10240,'tmp_name' => $imagestrmin));//上传oss
// 			    		echo $imgurl2.'<br />'.$imgurl3;
			    		$this->_img_list($imgurl2,$imgurl3, $data['userid']);//存入数据表
			    		@unlink($new_name);@unlink($imagestrmin);//删除本地图片
    				}
    			}*/
    			if($rs !== false){
    				$data2['code'] = '200';
    				$data2['msg'] = '成功';
    				$this->response($data2 , 'json' , 200);
    			}else{
    				$data2['code'] = '403';
    				$data2['msg'] = '数据更新失败';
    				$this->response($data2 , 'json' , 403);
    			}
    		}else{
    			$data2['code'] = '403';
    			$data2['msg'] = '验签失败';
    			$this->response($data2 , 'json' , 403);
    		}
    	}else{
    		$data2['code'] = '403';
    		$data2['msg'] = '数据获取失败';
    		$this->response($data2 , 'json' , 403);
    	}
    }
    
    public function attention(){
    	$params = urldecode(file_get_contents("php://input"));
    	if($params){
    		$data = json_decode($params , true);
    		if(empty($data['signValue']) || empty($data['userid']) || empty($data['auid'])){
    			$data2['code'] = '403';
    			$data2['msg'] = '缺少参数';
    			$this->response($data2 , 'json' , 403);
    		}
    		if(checkSign($data['signValue'])){
    			$attention = M('Attention');
    			$exist = $attention->where("uid=$data[userid] and auid=$data[auid]")->find();
    			if($exist){
    				$del_rs = $attention->where("uid=$data[userid] and auid=$data[auid]")->delete();
    				if($del_rs !== false){
    					$data2['code'] = '200';
    					$data2['msg'] = '成功';
    					$this->response($data2 , 'json' , 200);
    				}else{
	    				$data2['code'] = '403';
	    				$data2['msg'] = '取消关注失败';
	    				$this->response($data2 , 'json' , 403);
    				}
    			}else{
	    			$attention->uid = $data['userid'];
	    			$attention->auid = $data['auid'];
	    			$attention->ctime = time();
	    			$insertid = $attention->add();
	    			if($insertid){
	    				$data2['code'] = '200';
	    				$data2['msg'] = '成功';
	    				$this->response($data2 , 'json' , 200);
	    			}else{
	    				$data2['code'] = '403';
	    				$data2['msg'] = '关注粉丝失败';
	    				$this->response($data2 , 'json' , 403);
	    			}
    			}
    		}else{
    			$data2['code'] = '403';
    			$data2['msg'] = '验签失败';
    			$this->response($data2 , 'json' , 403);
    		}
    	}else{
    		$data2['code'] = '403';
    		$data2['msg'] = '数据获取失败';
    		$this->response($data2 , 'json' , 403);
    	}
    }//关注
    
    public function forgetPassword(){
    	$signValue = I("get.signValue");
    	$phone = I("get.phone");
    	if(empty($signValue) || empty($phone)){
    		$data['code'] = '403';
    		$data['msg'] = '缺少参数';
    		$this->response($data , 'json' , 403);
    	}
    	if(checkSign($signValue)){
    		$user = M('User');
    		$info = $user->where("phone=$phone")->getField("id");
    		if($info){
    			$data['code'] = '200';
    			$data['msg'] = '成功';
    			$this->response($data , 'json' , 200);
    		}else{
    			$data['code'] = '403';
    			$data['msg'] = '用户不存在';
    			$this->response($data , 'json' , 403);
    		}
    	}else{
    		$data['code'] = '403';
    		$data['msg'] = '验签失败';
    		$this->response($data , 'json' , 403);
    	}
    }//忘记密码，验证用户是否存在
    
    public function viewOther(){
    	$signValue = I("get.signValue");
    	$uid = I("get.userid"); //当前登录id
    	$ouid = I("get.ouid");  //查看用户id
    	if(empty($signValue) || empty($uid) || empty($ouid)){
    		$data['code'] = '403';
    		$data['msg'] = '缺少参数';
    		$data['userinfo'] = null;
    		$this->response($data , 'json' , 403);
    	}
    	if(checkSign($signValue)){
    		$user = M('User');
    		$userinfo = $user->join("a left join wl_authentication b on a.id=b.uid")->where("a.id=$ouid")->field("a.id, a.role, a.username, a.phone, a.name, a.sex, a.birthday, a.img as image, a.address, a.sign, a.plrange, b.rank, a.qprice,a.qnum,a.email,a.school,a.company,a.occupation,a.background,a.praise")->find();
			//var_dump($userinfo);exit;
    		//查看当前用户和老师的问题状态
    		if($userinfo){
	    		$userinfo['attention'] = attentionNum($ouid);
	    		$userinfo['fsnum'] = fensiNum($ouid);
	    		$userinfo['isAttention'] = isAttention($uid, $ouid);
	    		$userinfo['evaluate'] = evaluateNum($uid);
	    		$userinfo['status'] = $this->_viewQstatus($uid, $ouid);
				$userinfo['isPraise'] = isPraise($uid, $ouid);
	    		$data['code'] = '200';
	    		$data['msg'] = '成功';
	    		$data['userinfo'] = $userinfo;
	    		$data['useropus'] = $this->_useropus($ouid);
	    		$this->response($data , 'json', 200);
    		}else{
    			$data['code'] = '403';
    			$data['msg'] = '用户不存在';
    			$data['userinfo'] = null;
    			$this->response($data , 'json' , 403);
    		}
    	}else{
    		$data['code'] = '403';
    		$data['msg'] = '验签失败';
    		$data['userinfo'] = null;
    		$this->response($data , 'json' , 403);
    	}
    }//查看其他用户信息
    
    public function myAttention(){
    	$signValue = I("get.signValue");
    	$uid = I("get.userid");
    	$p = I("get.p" , 1);
    	if(empty($signValue) || empty($uid)){
    		$data['code'] = '403';
    		$data['msg'] = '缺少参数';
    		$data['attentionlist'] = array();
    		$data['is_next'] = 0;
    		$this->response($data , 'json' , 403);
    	}
    	if(checkSign($signValue)){
    		$pagesize = 10;
    		$start = ($p-1)*$pagesize;
	    	$attention = M('Attention');
	    	$count = $attention->where("uid=$uid")->count("id");
	    	$list = $attention->join("a left join wl_user b on a.auid=b.id")->where("a.uid=$uid")->field("b.id, b.role, b.username, b.name, b.img as image,b.phone,b.qprice")->limit($start, $pagesize)->select();
	    	foreach($list as $key=>$value){
	    		if(empty($list[$key]['name'])){
	    			$list[$key]['name'] = str_replace(substr($list[$key]['phone'], 4,3), '***', $list[$key]['phone']);
	    		}
	    		$list[$key]['record'] = M('Authentication')->where("uid=$value[id]")->getField("record");
	    	    unset($list[$key]['phone']);
	    	}
	    	$list = empty($list) ? array() : $list;
	    	$is_next = 0;
	    	if($count > $p*$pagesize){
	    		$is_next = 1;
	    	}
	    	$data['code'] = '200';
	    	$data['msg'] = '成功';
	    	$data['attentionlist'] = $list;
	    	$data['is_next'] = $is_next;
	    	$this->response($data , 'json' , 200);
    	}else{
    		$data['code'] = '403';
    		$data['msg'] = '验签失败';
    		$data['attentionlist'] = array();
    		$data['is_next'] = 0;
    		$this->response($data , 'json' , 403);
    	}
    	
    }//我的关注
    
    public function myCollect(){
    	$signValue = I("get.signValue");
    	$uid = I("get.userid");
    	$p = I("get.p" , 1);
    	if(empty($signValue) || empty($uid)){
    		$data['code'] = '403';
    		$data['msg'] = '缺少参数';
    		$data['imglist'] = array();
    		$data['is_next'] = 0;
    		$this->response($data, 'json', 403);
    	}
    	if(checkSign($signValue)){
    		$collection = M("Collection");
    		$pagesize = 20;
    		$start = ($p-1) * $pagesize;
    		$count = $collection->where("uid=$uid")->count("id");
    		$list = $collection->join("a left join wl_img_list b on a.iid=b.id left join wl_image_resource c on a.sid=c.id")->where("a.uid='$uid' and b.image_raw<>''")->field("c.id as sid, b.id as iid, c.title, c.enTitle as extTitle,b.image_raw,b.image_raw_size,b.image_raw_width,b.image_raw_height,b.image_cps,b.image_cps_width,b.image_cps_height,b.image_small,b.image_small_width,b.image_small_height, c.collection")->limit("$start, $pagesize")->select();
            foreach($list as $key=>$value){
				$list[$key]['image_raw_size'] = getIsize($list[$key]['image_raw_size']);
            }
//     		echo $collection->_sql();
    		$list = empty($list) ? array() : $list;
    	    $is_next = 0;
            if($count > $p*$pagesize){
            	$is_next = 1;
            }
    	    $data['code'] = '200';
    	    $data['msg'] = '成功';
    	    $data['imglist'] = $list;
    	    $data['is_next'] = $is_next;
    	    $this->response($data, 'json' , 200);
    	}else{
    		$data['code'] = '403';
    		$data['msg'] = '验签失败';
    		$data['imglist'] = array();
    		$data['is_next'] = 0;
    		$this->response($data, 'json' , 403);
    	}
    }//我的收藏
    
    public function _useropus($uid){
    	$user_opus = M('User_opus');
    	$list = $user_opus->where("uid=$uid")->field("id,raw_image,coy_image,image,image_width,image_height")->select();
		foreach($list as $key => $val){
			$list[$key]['coy_image'] = empty($val['coy_image']) ? $val['image'] : $val['coy_image'];
		}
    	return $list ? $list : array();
    }//用户作品墙
    
    public function accompany(){
    	$signValue = I("get.signValue");
    	$uid = I("get.userid");
    	$keyword = I("get.keyword");
    	if(empty($signValue)){
    		$data['code'] = '403';
    		$data['msg'] = '缺少参数';
    		$data['adimg'] = '';
    		$data['commend_list'] = array();
    		$data['user_list'] = array();
            $this->response($data, 'json', 403);
    	}
    	if(checkSign($signValue)){
    	    $where = '';
    		if($uid){
    			$where .= " and a.id<>$uid"; //不包含自己
    		}
    		if($keyword){
    			$where = " and (a.name like '%".$keyword."%' OR a.username like '%".$keyword."%' OR b.rank like '%".$keyword."%' OR b.record like '%".$keyword."%')";
    		}
    		$user = M("User");
    		$commend = $user->join("a left join wl_authentication b on a.id=b.uid")->where("a.isCommend=1 and a.role=2 and a.id<>$uid")->field("a.id, a.phone, a.name, a.img as image, b.rank, b.record,a.qprice")->group("a.id")->limit(20)->select();
    		if($commend){
	    		foreach($commend as $key=>$value){
	    			if(empty($commend[$key]['name'])){
    	    			$commend[$key]['name'] = str_replace(substr($commend[$key]['phone'], 4,3), '***', $commend[$key]['phone']);
    	    		}
    	    		unset($commend[$key]['phone']);
	    		}
    		}else{
    			$commend =  array();
    		}
    		$commend = empty($commend) ? array() : $commend;
    	    $list = $user->join("a left join wl_authentication b on a.id=b.uid")->where("a.role=2".$where)->field("a.id, a.phone, a.name, a.img as image, a.sign, b.rank, b.record, a.qnum,a.qprice")->group("a.id")->order("a.qnum desc")->limit(50)->select();
    	    if($list){
    	    	foreach($list as $key=>$value){
    	    		if(empty($list[$key]['name'])){
    	    			$list[$key]['name'] = str_replace(substr($list[$key]['phone'], 4,3), '***', $list[$key]['phone']);
    	    		}
    	    		$list[$key]['status'] = $this->_viewQstatus($uid, $list[$key]['id']);
    	    	    unset($list[$key]['phone']);
    	    	}
    	    }else{
    	    	$list = array();
    	    }
    	    $img = M("advert")->where("positionid=1")->field("image")->order("id desc")->limit("1")->select();
    	    $data['code'] = '200';
    	    $data['msg'] = '成功';
    	    $data['adimg'] = $img[0]['image'];
    	    $data['commend_list'] = $commend;
    	    $data['user_list'] = $list;
    	    $this->response($data, 'json', 200);
    	}else{
    		$data['code'] = '403';
    		$data['msg'] = '验签失败';
    		$data['adimg'] = '';
    		$data['commend_list'] = array();
    		$data['user_list'] = array();
    		$this->response($data, 'json', 403);
    	}
    }//陪你接口
    
    public function setPassword(){
    	$params = urldecode(file_get_contents("php://input"));
    	if($params){
    		$data = json_decode($params , true);
    		if(empty($data['signValue']) || empty($data['phone']) || empty($data['password'])){
    			$data2['code'] = '403';
    			$data2['msg'] = '缺少参数';
    			$this->response($data2, 'json', 403);
    		}
    		if(checkSign($data['signValue'])){
    			$user = M('User');
    			$uid = $user->where("phone=$data[phone]")->getField("id");
    			if($uid){
    				$save_data['password'] = $data['password'];
    				$rs = $user->where("id=$uid")->data($save_data)->save();
    				if($rs !== false){
	    				$data2['code'] = '200';
	    				$data2['msg'] = '成功';
	    				$this->response($data2, 'json', 200);
    				}else{
    					$data2['code'] = '403';
    					$data2['msg'] = '更新失败';
    					$this->response($data2, 'json', 403);
    				}
    			}else{
    				$data2['code'] = '403';
    				$data2['msg'] = '用户 不存在';
    				$this->response($data2, 'json', 403);
    			}
    		}else{
    			$data2['code'] = '403';
    			$data2['msg'] = '验签失败';
    			$this->response($data2, 'json', 403);
    		}
    	}else{
    		$data2['code'] = '403';
    		$data2['msg'] = '获取数据失败';
    		$this->response($data2, 'json', 403);
    	}
    }//重置密码
    
    public function _collectNum($uid){
    	$collection = M('Collection');
    	$count = $collection->where("uid=$uid")->count("id");
    	return $count ? $count : 0;
    }//收藏数量
    
    public function _subscribeNum($uid){
    	$video_subscribe = M('Video_subscribe');
    	$count = $video_subscribe->where("uid=$uid and expireTime>".time()." and isPay=1")->count("id");
    	return $count ? $count : 0;
    }//订阅数量
    
    public function _cacheNum($uid){
    	$image_cache = M('Image_cache');
    	$video_cache = M('Video_cache');
    	$count = $image_cache->where("uid=$uid")->count("id");
    	$count = $count ? $count : 0;
    	$count2 = $video_cache->where("uid=$uid")->count("id");
    	$count2 = $count2 ? $count2 : 0;
    	return $count+$count2;
    }//缓存数量
    
    public function me(){
    	$signValue = I("get.signValue");
    	$uid = I("get.userid");
    	if(empty($signValue) || empty($uid)){
    		$data['code'] = '403';
    		$data['msg'] = '缺少参数';
    		$data['userinfo'] = null;
    		$data['subscribe'] = 0;
    		$data['collect'] = 0;
    		$this->response($data, 'json', '403');
    	}
    	if(checkSign($signValue)){
    		$userinfo = M("User")->where("id=$uid")->field("id, role, username, name, phone, img as image")->find();
    		if($userinfo){
    			$rs = M("Authentication")->where("uid=$uid")->find();
    			$userinfo['status'] =0;
    			if($userinfo['role'] > 1){
    				$userinfo['status'] = 2;
    			}elseif($rs){
    				$userinfo['status'] = 1;
    			}
    		}else{
    			$userinfo = null;
    		}
    		$data['code'] = '200';
    		$data['msg'] = '成功';
    		$data['userinfo'] = $userinfo;
    		$data['subscribe'] = $this->_subscribeNum($uid);
    		$data['collect'] = $this->_collectNum($uid);
    		$this->response($data, 'json', '200');
    	}else{
    		$data['code'] = '403';
    		$data['msg'] = '验签失败';
    		$data['userinfo'] = null;
    		$data['subscribe'] = 0;
    		$data['collect'] = 0;
    		$this->response($data, 'json', '403');
    	}
    }//我
    
    public  function my_purse(){
    	$signValue = I("get.signValue");
    	$uid = I("get.userid");
    	if(empty($signValue) || empty($uid)){
    		$data['code'] = '403';
    		$data['msg'] = '缺少参数';
    		$data['account'] = 0;
    		$this->response($data, 'json', 403);
    	}
    	if(checkSign($signValue)){
    		$user = M('User');
    		$account = $user->where("id=$uid")->getField("account");
    		$account = $account ? $account : 0;
    		$data['code'] = '200';
    		$data['msg'] = '成功';
    		$data['account'] = $account;
    		$this->response($data, 'json', 200);
    	}else{
    		$data['code'] = '403';
    		$data['msg'] = '验签失败';
    		$data['account'] = 0;
    		$this->response($data, 'json', 403);
    	}
    }//我的钱包
    
    public function my_voucher(){
    	$signValue = I("get.signValue");
    	$uid = I("get.userid");
    	$p = I("get.p" , 1);
    	if(empty($signValue) || empty($uid)){
    		$data['code'] = '403';
    		$data['msg'] = '缺少参数';
    		$data['voucherlist'] = array();
    		$data['is_next'] = 0;
    		$this->response($data, 'json', 403);
    	}
    	if(checkSign($signValue)){
    		$voucher = M('Voucher');
    		$pagesize = 10;
    		$start = ($p-1)*$pagesize;
    		$count = $voucher->where("uid=$uid")->count("id");
    		$list = $voucher->join("a left join wl_voucher_ext b on a.typeid=b.id")->where("a.uid=$uid")->limit("$start, $pagesize")->field("a.id,a.voucher,a.starttime,a.expiretime,b.title, b.content,a.is_use")->order("ctime desc")->select();
    		foreach($list as $key=>$value){
    			$list[$key]['expire'] = date("Y.m.d" , $list[$key]['starttime']).'-'.date("Y.m.d" , $list[$key]['expiretime']);
                if($value['expiretime'] >= time()){
                	$list[$key]['is_expire'] = 0;
                }else{
                	$list[$key]['is_expire'] = 1;
                }
    			unset($list[$key]['starttime']);
    		    unset($list[$key]['expiretime']);
    		}
    		$is_next = 0;
    		if($count > $p*$pagesize){
    			$is_next = 1;
    		}
    		$list = empty($list) ? array() : $list;
    		$data['code'] = '200';
    		$data['msg'] = '成功';
    		$data['voucherlist'] = $list;
    		$data['is_next'] = $is_next;
    		$this->response($data, 'json', 200);
    	}else{
    		$data['code'] = '403';
    		$data['msg'] = '验签失败';
    		$data['voucherlist'] = array();
    		$data['is_next'] = 0;
    		$this->response($data, 'json', 403);
    	}
    }//我的优惠券
    
    public function charge_type(){
    	$signValue = I("get.signValue");
    	if(empty($signValue)){
    		$data['code'] = '403';
    		$data['msg'] = '缺少参数';
    		$data['type_list'] = array();
    		$this->response($data, 'json', 403);
    	}
    	if(checkSign($signValue)){
    		$charge_ext = M('Charge_ext');
    		$type_list = $charge_ext->select();
    		foreach($type_list as $key=>$value){
    			$type_list[$key]['account'] = $type_list[$key]['account'].'元';
    		}
    		$type_list = empty($type_list) ? array() : $type_list;
    		$data['code'] = '200';
    		$data['msg'] = '成功';
    		$data['type_list'] = $type_list;
    		$this->response($data, 'json', 200);
    	}else{
    		$data['code'] = '403';
    		$data['msg'] = '验签失败';
    		$data['type_list'] = array();
    		$this->response($data, 'json', 403);
    	}
    }//充值金额分类
    
    public function charge(){
    	$pay = new \Api\Controller\PayController();
    	$params = urldecode(file_get_contents("php://input"));
    	if($params){
    		$data = json_decode($params, true);
    		if(empty($data['signValue']) || empty($data['userid']) || empty($data['typeid']) || empty($data['payWay'])){
    			$data2['code'] = '403';
    			$data2['msg'] = '缺少参数';
    			$data2['payInfo'] = '';
    			$data2['orderid'] = '';
    			$this->response($data2, 'json', 403);
    		}
    		if(checkSign($data['signValue'])){
    			$charge_ext = M("Charge_ext");
    			$price = $charge_ext->where("id=$data[typeid]")->getField("account");
    			$orderid = ordercode("c");
    			$paydata['orderid'] = $orderid;
    			$paydata['goodname'] = '充值'.$price."元";
    			$paydata['body'] = '充值'.$price."元";
    			$paydata['price'] = $price;
    			$data['orderid'] = $orderid;
    			$data['price'] = $price;
    			if($data['payWay'] == 'alipay'){//支付宝
    				$data['intro'] = '支付宝';
    				$data['way'] = 1;
    				$insertid = $this->_addSubscribe($data);
    				if(!$insertid){
    					$data2['code'] = '403';
    					$data2['msg'] = '创建订单失败';
    					$data2['payInfo'] = '';
    					$data2['orderid'] = '';
    					$this->response($data2, 'json' , 403);
    				}
    				$new_str = $pay->alipay($paydata);
    				$data2['code'] = 201;
    				$data2['msg'] = '成功';
    				$data2['payInfo'] = $new_str;
    				$data2['orderid'] = "$orderid";
    				$this->response($data2, 'json', 201);
    			}elseif($data['payWay'] == 'wxpay'){//微信
    				$Return = $pay->wxpay($paydata);
    				if($Return == 'fail'){//如果失败
    					$data2['code'] = 403;
    					$data2['msg'] = '发起预支付失败';
    					$data2['payInfo'] = '';
    					$data2['orderid'] = '';
    					$this->response($data2, 'json', 403);
    				}else {
    					$wxpayservice_json = json_encode($Return);
    					$data['intro'] = $wxpayservice_json;
    					$data['way'] = 2;
    					$insertid2 = $this->_addSubscribe($data);
    					if (!empty($insertid2)) {//各字段正常 入库成功
    						$data2['code'] = 201;
    						$data2['msg'] = '成功';
    						$data2['payInfo'] = $Return;
    						$data2['orderid'] = "$orderid";
    						$this->response($data2, 'json', 201);
    					} else {
    						$data2['code'] = 403;
    						$data2['msg'] = '创建订单失败';//入库失败，请检查字段
    						$data2['payInfo'] = '';
    						$data2['orderid'] = '';
    						$this->response($data2, 'json', 403);
    					}
    				}
    			}
    		}else{
    			$data2['code'] = '403';
    			$data2['msg'] = '验签失败';
    			$data2['payInfo'] = '';
    			$data2['orderid'] = '';
    			$this->response($data2, 'json', 403);
    		}
    	}else{
    		$data2['code'] = '403';
    		$data2['msg'] = '获取数据失败';
    		$data2['payInfo'] = '';
    		$data2['orderid'] = '';
    		$this->response($data2, 'json', 403);
    	}
    }//充值
    
    public function _addSubscribe($data){
    	$charge = M('Charge');
    	$charge->orderid = $data['orderid'];
    	$charge->uid = $data['userid'];
    	$charge->typeid = $data['typeid'];
    	$charge->price = $data['price'];
    	$charge->payWay = $data['way'];
    	$charge->intro = $data['intro'];
    	$charge->ctime = time();
    	$insertid = $charge->add();
    	return $insertid;
    }//添加视频组订阅
    
    public function _upload_oss($file){   
    	$Oss = new \Think\Oss();
    	$parameter = array(
    			'maxSize'	=> 999145728,
    			'exts'		=> array('jpg', 'gif', 'png', 'jpeg'),
    			'filepath'	=> 'opus/'.date('Y-m',time()).'/'
    	);
    	$result = $Oss->upload($file,$parameter);
    	if($result['code'] == 1){
    		$imagesrc = $result['src'];
    	}else{
    		$imagesrc = '';
    		//$this->error($result['msg']); //错误
    	}
    	return replace_alip($imagesrc);
    }//上传图片
    
    public function _img_list($imgurl1,$imgurl2,$uid,$coy_image=''){
    	$user_opus = M('User_opus');
    	$rawimg = getimagesize($imgurl1);
    	$minimg = getimagesize($imgurl2);
    	$insertid = $user_opus->data(array(
    			"uid"	      =>	$uid,
    			"raw_image"   =>	$imgurl1,
				"coy_image"	  =>	$coy_image,
    			"image"       =>	$imgurl2,
    			"image_width" =>    $minimg[0],
    			"image_height"=>    $minimg[1],
    			"ctime"		  =>	time()
    	))->add();
    	return $insertid;
    }/*存入子图片列表*/
    
    public function delete_opusimg(){
        $signValue = I("get.signValue");
        $id = I("get.id");
        if(empty($signValue) || empty($id)){
        	$data['code'] = '403';
        	$data['msg'] = '缺少参数';
        	$this->response($data, 'json', 403);
        }
        if(checkSign($signValue)){
            $user_opus = M('User_opus');
            $rs = $user_opus->where("id=$id")->field("raw_image, coy_image, image")->find();
            $Oss = new \Think\Oss();
			if(!empty($rs['raw_image'])){
				$Oss->delete($rs['raw_image']);
			}
			if(!empty($rs['coy_image'])) {
				$Oss->delete($rs['coy_image']);
			}
			if(!empty($rs['image'])) {
				$Oss->delete($rs['image']);
			}

			$user_opus->where("id=$id")->delete();
            $data['code'] = '200';
            $data['msg'] = '成功';
            $this->response($data, 'json', 200);
        }else{
        	$data['code'] = '403';
        	$data['msg'] = '验签失败';
        	$this->response($data, 'json', 403);
        }
    }//删除照片墙
    public function upload(){
    	$signValue = I("post.signValue");
    	$userid = I("post.userid");
    	$file = $_FILES['profile_picture'];
    	$user_opus = M('User_opus');
    	if(empty($userid) || empty($signValue) || empty($file['name'])){
    		$data['code'] = '403';
    		$data['msg'] = '缺少参数';
    		$data['id'] = 0;
    		$data['raw_image'] = '';
    		$data['image'] = '';
    		$data['image_width'] = 0;
    		$data['image_height'] = 0;
    		$this->response($data,'json',403);
    	}
    	if(checkSign($signValue)){
//     		var_dump($signValue);
    		//图片处理开始
    		if($file["name"] == ""){
    			$imagestr = '';
    		}else{
    			$upload_path = './Uploads/attached/';
    			$upload = new \Think\Upload();// 实例化上传类
    			$upload->maxSize   =     999145728;// 设置附件上传大小
    			$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
    			$upload->rootPath  =     $upload_path; // 设置附件上传根目录
    			$upload->savePath  =     ''; // 设置附件上传（子）目录
    			$upload->autoSub   = false;
    			$upload->subName   = array('date', 'Y-m'); //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
    			$retuimg = $upload->upload();// 上传文件
    			if(!$retuimg) {// 上传错误提示错误信息
    				//$this->error($upload->getError());
    			}else{// 上传成功
    				$fileext = pathinfo($retuimg["profile_picture"]["savename"], PATHINFO_EXTENSION);//文件后缀
    				$imagestraw = $upload_path.$retuimg["profile_picture"]["savename"];
    				$imagestrmin2 = substr($imagestraw,0,-4).'coy.'.$fileext;
					$imagestrmin = substr($imagestraw,0,-4).'min.'.$fileext;
    
    				$image = new \Think\Image();
    				$image->open($imagestraw);
    
    				$width = $image->width(); // 返回图片的宽度
    				$height = $image->height(); // 返回图片的高度

					$newwidth2 = 800;
					$pre2 = $newwidth2/$width;
					$newheight2 = ceil($height*$pre2);
					$image->thumb($newwidth2, $newheight2)->save($imagestrmin2);//等比例裁剪 800

					$newwidth = 300;
					$pre = $newwidth/$width;
					$newheight = ceil($height*$pre);
					$image->thumb($newwidth, $newheight)->save($imagestrmin);//等比例裁剪 300
    					
    				$imgurl1 = $this->_upload_oss(array('name' => $retuimg["profile_picture"]["savename"],'size' => 10240,'tmp_name' => $imagestraw));//上传oss
    				$imgurl2 = $this->_upload_oss(array('name' => $retuimg["profile_picture"]["savename"],'size' => 10240,'tmp_name' => $imagestrmin));//上传oss
					$coy_image = $this->_upload_oss(array('name' => $retuimg["profile_picture"]["savename"],'size' => 10240,'tmp_name' => $imagestrmin2));//上传oss

    				$insertid = $this->_img_list($imgurl1,$imgurl2,$userid,$coy_image);//存入数据表
    				@unlink($imagestraw);@unlink($imagestrmin2);@unlink($imagestrmin);//删除本地图片
    			}
    		}
    		$data['code'] = '200';
    		$data['msg'] = '成功';
    		$data['id'] = $insertid;
    		$data['raw_image'] = $imgurl1;
    		$data['image'] = $imgurl2;
			$data['coy_image'] = $coy_image;
    		$data['image_width'] = $newwidth;
    		$data['image_height'] = $newheight;
    		$this->response($data,'json',200);
    	}else{
    		$data['code'] = '403';
    		$data['msg'] = '验签失败';
    		$data['id'] = 0;
    		$data['raw_image'] = '';
    		$data['image'] = '';
    		$data['image_width'] = 0;
    		$data['image_height'] = 0;
    		$this->response($data,'json',403);
    	}
    }
    
    public function upload_ios(){
    	$params = urldecode(file_get_contents("php://input"));
    	if($params){
    		$data = json_decode($params, true);
    		if(empty($data['signValue']) || empty($data['userid']) || empty($data['image'])){
    			$data2['code'] = '403';
    			$data2['msg'] = '缺少参数';
    			$data2['id'] = 0;
				$data2['raw_image'] = '';
				$data2['image'] = '';
				$data2['image_width'] = 0;
				$data2['image_height'] = 0;
    			$this->response($data2, 'json', 403);
    		}
    		if(checkSign($data['signValue'])){
//             if($data['signValue']){
    			$user_opus = M('User_opus');
//     			$img = base64_decode(str_replace(" ","+",$data['image']));
    			$img = base64_decode($data['image']);
    			$upload_path = './Uploads/attached/';
    			$pic_title = time();
    			$savename = $this->getName($rule, $pic_title); //存取文件名称
    			$filename = $savename.'.jpg';
//     			$pic_url = '/Uploads/attached/'.$savename.'.jpg';
    			$pic_url2 = './Uploads/attached/'.$savename.'.jpg';
    			$size = file_put_contents($pic_url2, $img);//返回的是字节数
				$imagestrmin = substr($pic_url2,0,-4).'min.jpg';
				$imagestrmin2 = substr($pic_url2,0,-4).'coy.jpg';
				
				$image = new \Think\Image();
				$image->open($pic_url2);

				$width = $image->width(); // 返回图片的宽度
				$height = $image->height(); // 返回图片的高度

				$newwidth2 = 800;
				$pre2 = $newwidth2/$width;
				$newheight2 = ceil($height*$pre2);
				$image->thumb($newwidth2, $newheight2)->save($imagestrmin2);//等比例裁剪 800

				$newwidth = 300;
				$pre = $newwidth/$width;
				$newheight = ceil($height*$pre);
				$image->thumb($newwidth, $newheight)->save($imagestrmin);//等比例裁剪
				
				$imgurl1 = $this->_upload_oss(array('name' => $filename,'size' => 10240,'tmp_name' => $pic_url2));//上传oss
				$imgurl2 = $this->_upload_oss(array('name' => $filename,'size' => 10240,'tmp_name' => $imagestrmin));//上传oss
				$coy_image = $this->_upload_oss(array('name' => $filename,'size' => 10240,'tmp_name' => $imagestrmin2));//上传oss

				$insertid = $this->_img_list($imgurl1,$imgurl2,$data['userid'],$coy_image);//存入数据表
				@unlink($pic_url2);@unlink($imagestrmin);//删除本地图片
				$data2['code'] = '200';
				$data2['msg'] = '成功';
				$data2['id'] = $insertid;
				$data2['raw_image'] = $imgurl1;
				$data['coy_image'] = $coy_image;
				$data2['image'] = $imgurl2;
				$data2['image_width'] = $newwidth;
				$data2['image_height'] = $newheight;
				$this->response($data2, 'json', 200);
    		}else{
    			$data2['code'] = '403';
    			$data2['msg'] = '验签失败';
    			$data2['id'] = 0;
				$data2['raw_image'] = '';
				$data2['image'] = '';
				$data2['image_width'] = 0;
				$data2['image_height'] = 0;
    			$this->response($data2, 'json', 403);
    		}
    	}else{
    		$data2['code'] = '403';
    		$data2['msg'] = '获取数据失败';
    		$data2['image'] = '';
    		$this->response($data2, 'json', 403);
    	}
    }//上传图片
    
    private function getName($rule, $filename){
    	$rule = $rule ? $rule : array('uniqid','');
    	$name = '';
    	if(is_array($rule)){ //数组规则
    		$func     = $rule[0];
    		$param    = (array)$rule[1];
    		foreach ($param as &$value) {
    			$value = str_replace('__FILE__', $filename, $value);
    		}
    		$name = call_user_func_array($func, $param);
    	} elseif (is_string($rule)){ //字符串规则
    		if(function_exists($rule)){
    			$name = call_user_func($rule);
    		} else {
    			$name = $rule;
    		}
    	}
    	return $name;
    }//定义图片名称
    
    public function user_auth_type(){
    	$signValue = I("get.signValue");
    	$type = I("get.type");
    	if(empty($signValue) || empty($type)){
    		$data['code'] = '403';
    		$data['msg'] = '缺少参数';
    		$data['type_list'] = array();
    		$this->response($data, 'json', 403);
    	}
    	if(checkSign($signValue)){
    		$type_list = array();
    		if($type == 1){
    			$record = M("Record");
    			$type_list = $record->select();
    		}elseif($type == 2){
    			$certificate_type = M("Certificate_type");
    			$type_list = $certificate_type->select();
    		}
    		$type_list = empty($type_list) ? array() : $type_list;
    		$data['code'] = '200';
    		$data['msg'] = '成功';
    		$data['type_list'] = $type_list;
    		$this->response($data, 'json', 200);
    	}else{
    		$data['code'] = '403';
    		$data['msg'] = '验签失败';
    		$data['type_list'] = array();
    		$this->response($data, 'json', 403);
    	}
    }//用户认证类型 接口
   
    public function view_authentication(){
    	$signValue = I("get.signValue");
    	$uid = I("get.userid");
    	if(empty($signValue) || empty($uid)){
    		$data['code'] = '403';
    		$data['msg'] = '缺少参数';
    		$data['info'] = null;
    		$this->response($data, 'json' , 403);
    	}
    	if(checkSign($signValue)){
    		$authentication = M('Authentication');
    		$info = $authentication->where("uid=$uid")->field("uid,name,image,rank,record,certificate,certificatecode,identity_front,identity_opposite")->find();
            if($info){
            	$qprice = M("User")->where("id=$uid")->getField("qprice");
            	$info['qprice'] = $qprice;
            }
    		$info = empty($info) ? null : $info;
//     	    if($info){
    	    	$data['code'] = '200';
    	    	$data['msg'] = '成功';
    	    	$data['info'] = $info;
    	    	$this->response($data, 'json', 200);
    	   /* }else{
    	    	$data['code'] = '403';
    	    	$data['msg'] = '认证不存在';
    	    	$data['info'] = null;
    	    	$this->response($data, 'json', 403);
    	    }*/
    	}else{
    		$data['code'] = '403';
    		$data['msg'] = '验签失败';
    		$data['info'] = null;
    		$this->response($data, 'json', 403);
    	}
    }//查看认证用户资料
    
    public function user_message(){
    	$signValue = I("get.signValue");
    	$uid = I("get.userid");
    	$id = I("get.id", 0);
    	if(empty($signValue) || empty($uid)){
    		$data['code'] = '403';
    		$data['msg'] = '缺少参数';
    		$data['list'] = array();
    		$data['is_next'] = 0;
    		$this->response($data, 'json', 403);
    	}
    	if(checkSign($signValue)){
    		$system_push = M("System_push");
    		$pagesize = 10;
    		if($id == 0){
    			$count = $system_push->where("uid=$uid")->count("id");
    			$list = $system_push->join("a left join wl_user b on a.uid=b.id")->where("a.uid=$uid")->field("a.id, a.title, a.content, a.ctime, b.img as image")->order("a.id desc")->limit($pagesize)->select(); 
    		}else{
    			$count = $system_push->where("uid=$uid and id<$id")->count("id");
    			$list = $system_push->join("a left join wl_user b on a.uid=b.id")->where("a.uid=$uid and a.id<$id")->field("a.id, a.title, a.content, a.ctime, b.img as image")->order("a.id desc")->limit($pagesize)->select();
    		}
//     		var_dump($system_push->_sql());
    		if($list){
    			foreach($list as $key=>$value){
    				$list[$key]['ctime'] = formatDate($list[$key]['ctime']);
    			}
    		}else{
    			$list = array();
    		}
    		$is_next = 0;
    		if($count > $pagesize){
    			$is_next = 1;
    		}
    		$data['code'] = '200';
    		$data['msg'] = '成功';
    		$data['list'] = $list;
    		$data['is_next'] = $is_next;
    		$this->response($data, 'json', 200);
    	}else{
    		$data['code'] = '403';
    		$data['msg'] = '验签失败';
    		$data['list'] = array();
    		$data['is_next'] = 0;
    		$this->response($data, 'json', 403);
    	}
    }//用户消息
    
    public function message_info(){
    	$signValue = I("get.signValue");
    	$id = I("get.id");
    	if(empty($signValue) || empty($id)){
    		$data['code'] = '403';
    		$data['msg'] = '缺少参数';
    		$data['info'] = null;
    		$this->response($data, 'json', 403);
    	}
    	if(checkSign($signValue)){
    		$system_push = M("System_push");
    		$info = $system_push->join("a left join wl_user b on a.uid=b.id")->where("a.id=$id")->field("a.title, a.content,a.ctime, b.name as uname, b.img as image")->find();
            if($info){
            	$info['ctime'] = formatDate($info['ctime']);
            }else{
            	$info = null;
            }
    	    $data['code'] = '200';
    	    $data['msg'] = '成功';
    	    $data['info'] = $info;
    	    $this->response($data, 'json', 200);
    	}else{
    		$data['code'] = '403';
    		$data['msg'] = '验签失败';
    		$data['info'] = null;
    		$this->response($data, 'json', 403);
    	}
    }//消息详情
    
    public function awards(){
        $signValue = I("get.signValue");
        $uid = I("get.userid");
        if(empty($signValue) || empty($uid)){
        	$data['code'] = '403';
        	$data['msg'] = '缺少参数';
        	$data['adimg'] = '';
        	$data['voucher'] = 0;
        	$data['award'] = 0;
        	$data['cash'] = 0;
        	$data['shareurl'] = '';
        	$this->response($data, 'json', 403);
        }
        if(checkSign($signValue)){
        	$url = 'http://www.meishuroom.com/User/register/uid/'.$uid;
        	$img = M("advert")->where("positionid=2")->field("image")->order("id desc")->limit("1")->select();
        	$imgs = M("advert")->where("positionid=3")->field("image")->order("id desc")->limit("1")->select();
            $cash = M("deal_record")->where("uid=$uid and payway=3 and `change`=1 and note like '%分销得到%'")->field("price")->select();
//         	echo M("deal_record")->_sql();
            $total = 0;
        	foreach($cash as $value){
        		$total += $value['price'];
        	}
            $data['code'] = '200';
            $data['msg'] = '成功';
            $data['adimg'] = $img[0]['image'];
        	$data['voucher'] = 100;
            $data['award'] = 60;
            $data['cash'] = $total;
            $data['title'] = subtitle('告诉你一个和全国名师学画画的好去处！', 40);
            $data['title_ext'] = subtitle('再送你一百块钱做盘缠！', 60);
            $data['content'] = '推荐有奖';
            $data['image'] = $imgs[0]['image'];
            $data['shareurl'] = $url;
            $this->response($data, 'json', 200);
        }else{
        	$data['code'] = '403';
        	$data['msg'] = '验签失败';
        	$data['adimg'] = '';
        	$data['voucher'] = 0;
        	$data['award'] = 0;
        	$data['cash'] = 0;
        	$data['shareurl'] = '';
        	$this->response($data, 'json', 403);
        }
    }//推荐有奖
    
    public function upgrade(){
    	$signValue = I("get.signValue");
    	if(empty($signValue)){
    		$data['code'] = '403';
    		$data['msg'] = '缺少参数';
    		$data['upgrade_info'] = null;
    		$this->response($data, 'json', 403); 
    	}
    	if(checkSign($signValue)){
    		$upgrade = M("Upgrade");
    	    $rs = $upgrade->field("version, version_intro, upgrade, file, is_update")->order("ctime desc")->limit("1")->select();
    	    if($rs[0]){
    	    	$new_file = explode("?", $rs[0]['file']);
    	    	if(count($new_file)>1){
    	    		$rs[0]['file'] = $new_file[0];
    	    	}
    	    	$upgrade_info = $rs[0];
    	    }else{
    	    	$upgrade_info = null;
    	    }
    	    $data['code'] = '200';
    	    $data['msg'] = '成功';
    	    $data['upgrade_info'] = $upgrade_info;
    	    $this->response($data, 'json', 200);
    	}else{
    		$data['code'] = '403';
    		$data['msg'] = '验签失败';
    		$data['upgrade_info'] = null;
    		$this->response($data, 'json', 403);
    	}
    }//升级包
    
    public function _viewQstatus($uid, $buid){
    	$question = M("Question");
    	$rs = $question->join("a left join wl_question_subscribe b on a.id=b.qid")->where("b.uid=$uid and b.quid=$buid and b.isPay=1")->order("a.id desc")->field("a.*")->limit(1)->select();
    	if($rs){
    		if($rs['is_deal'] == 1){
    			$status = 0;
    		}else{
    			$status = 1;
    		}
    	}else{
    		$status = 0;
    	}
    	return $status;
    }//查看用户和老师目前支付状态
    
    public function logout(){
    	$signValue = I("get.signValue");
    	$uid = I("get.userid");
    	if(empty($signValue) || empty($uid)){
    		$data['code'] = '403';
    		$data['msg'] = '缺少参数';
    		$this->response($data, 'json', 403);
    	}
    	if(checkSign($signValue)){
    		$user = M("User");
    		$rs = $user->where("id=$uid")->data(array("token"=>""))->save();
    		if($rs !== false){
    			$data['code'] = '200';
    			$data['msg'] = '成功';
    			$this->response($data, 'json', 200);
    		}else{
    			$data['code'] = '403';
    			$data['msg'] = '验签失败';
    			$this->response($data, 'json', 403);
    		}
    	}else{
    		$data['code'] = '403';
    		$data['msg'] = '验签失败';
    		$this->response($data, 'json', 403);
    	}
    }//退出登录

	/*用户自己二维码链接*/
	public function my_qrcode(){
		$signValue = I("get.signValue");
		$uid = I("get.uid");
		if(empty($signValue) || empty($uid)){
			$data['code'] = '403';
			$data['msg'] = '缺少参数';
			$this->response($data, 'json', 403);
		}else{
			if(checkSign($signValue)){
				$qrcode_url = 'http://www.meishuroom.com/User/register/uid/'.$uid.'?from=singlemessage&isappinstalled=1';
				$data['code_url'] = $qrcode_url;
				$data['code'] = '200';
				$data['msg'] = '成功';
				$this->response($data, 'json', 200);
			}else{
				$data['code'] = '403';
				$data['msg'] = '验签失败';
				$this->response($data, 'json', 403);
			}
		}
	}

	/*用户点赞用户，用户取消点赞*/
	public function praise(){
		$signValue = I("get.signValue");
		$uid = I("get.uid");//被点赞用户ID
		$duid = I("get.duid");//触发点赞按钮用户ID
		$type = I("get.type");//praise点赞，cancel取消点赞
		if(empty($signValue) || empty($uid) || empty($duid) || empty($type)){
			$data['code'] = '403';
			$data['msg'] = '缺少参数';
			$this->response($data, 'json', 403);
		}
		if(checkSign($signValue)){
			$u_praise = M('user')->where("id='$uid'")->getField('praise');//返回用户点赞数
			$w_praise = M('praise')->where("duid='$duid' and uid='$uid'")->find();//查询当前用户点赞情况
			if($type == 'praise'){//点赞
				if(empty($w_praise)) {//没有点过赞
					M('praise')->data(array(
						"uid" => $uid, "uid" => $uid, "duid" => $duid, "ctime" => time(),
					))->add();
					M('user')->where("id='$uid'")->setInc('praise');//用户点赞+1
					$data['praise'] = $u_praise + 1;
					$data['code'] = '200';
					$data['msg'] = '点赞成功';
					$this->response($data, 'json', 200);
				}else{//已经点过赞
					$data['praise'] = $u_praise;
					$data['code'] = '403';
					$data['msg'] = '不能重复点赞';
					$this->response($data, 'json', 403);
				}
			}elseif($type == 'cancel'){//取消点赞
				if(empty($w_praise)) {//没有点过赞
					$data['praise'] = 0;
					$data['code'] = '403';
					$data['msg'] = '并未点赞，或已取消点赞';
					$this->response($data, 'json', 403);
				}else {
					M('praise')->where("duid='$duid' and uid='$uid'")->delete();
					M('user')->where("id='$uid'")->setDec('praise');//用户点赞-1
					$data['praise'] = $u_praise - 1;
					$data['code'] = '200';
					$data['msg'] = '取消成功';
					$this->response($data, 'json', 200);
				}
			}
		}else{
			$data['code'] = '403';
			$data['msg'] = '验签失败';
			$this->response($data, 'json', 403);
		}
	}

	/*是否在IOS端开启支付*/
	public function ios_open_payment(){
		$version = I('get.version','1.1.1');
		if($version == '1.1.1'){
			$payment = 1;
		}else{
			$payment = 1;
		}
		$data['payment'] = $payment; //0关闭支付，1打开支付
		$data['code'] = '200';
		$data['msg'] = '成功';
		$this->response($data, 'json', 200);
	}
}