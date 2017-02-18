<?php

/*
----------------------------------
*|  auther:  yc  yc@yuanxu.top
*|  website: yuanxu.top
---------------------------------------
*/
defined('IN_IA') or exit('Access Denied');
require_once  'core/core.php';
require_once  'core/user.php';

class Yc_expressageModuleSite extends Core {
	public $tableApi = 'yc_expressage_api';
	public $tableUser = 'yc_expressage_user';

	# web端 查询快递
	public function doWebShow1(){
		/**
		 * 查询逻辑梳理
		 *   传入快递单号 kid
		 *     if 根据 uniacid openid kid 查询数据库中是否存在 该数据
		 *          true  if state == 3 (是否快递已签收)
		 *                  true  直接返回数据库中的数据
		 *                  false 用 kid kcode 调用查询API 查询数据.返回结果 update
		 *          false (不存在该数据)
		 *              调用 快递匹配 api,快递查询api 进行查询.查询成功后,把数据插入user表
		 */

		# 查询快递
		# 用户查询快递
        global $_W,$_GPC;
        $uniacid = $_W['uniacid'];
        $uid = $_W['uid'];
        load()->func('tpl');

        if(checksubmit('check') || !empty($_GPC['kid']) ) {
            # 获取用户填写的 快递单号
            $kid = trim($_GPC['kid'],'');
            if (empty( $kid ) ){
                message('快递单号不能为空', $this->createWebUrl('show1', array() )  , 'error');
            }
            $date = array(
                'uniacid' => $_W['uniacid'],
                'openid' => 'admin',
                'kid' => $kid
            );
            # 查询是否存在 该数据
            $res = pdo_get($this->tableUser,$date);
            if (empty( $res )){
                # 数据库返回结果为空,发送两个api请求
                # 获取api参数
                $api = pdo_get($this->tableApi, array('uniacid' => $uniacid), array('EBusinessID', 'key'));
                if (empty($api)){
                    message('快递api参数错误,请联系管理员', $this->createWebUrl('show1', array() )  , 'error');
                }
                # 用快递名字.换取快递代码
                require_once "core/mobile/check.php";
                $check = new check();
                $result = $check->goGit($kid,$api['EBusinessID'],$api['key']);
                $Code = $result['Shippers'][0];

                # 查询快递信息
                require_once "core/mobile/api.php";
                $goGit = new express();
                $rescod = $goGit->goGit($Code['ShipperCode'],$kid,$api['EBusinessID'],$api['key']);

                if (empty($rescod['Success']) ){
                    message('快递单号输入有误', $this->createWebUrl('show1', array() )  , 'error');
                }
                # 数据库倒序
                $res['Traces'] = array_reverse($rescod['Traces']);
                # 把数据转为json格式 存储到数据库
                $content = json_encode($res['Traces']);

                # 把第一次查询的数据 存入数据库
                $date['kname'] = $Code['ShipperName'];
                $date['kcode'] = $Code['ShipperCode'];
                $date['state'] = $rescod['State'];
                $date['content'] = $content;
                $date['createtime'] = time();
                $result =  pdo_insert($this->tableUser,$date);
                if (empty( $result )){
                    message('添加数据失败,请联系管理员', $this->createWebUrl('show1', array() )  , 'error');
                }
                # 组成html页面显示数据
                $state = $this->state($rescod['State']);
                $show = array(
                    'kname' => $Code['ShipperName'],
                    'kid' =>$kid,
                    'state' =>$state
                );
                include $this->template('show');
                die();

            }else if ($res['state']==3){
                $res['state']='已签收';
                $show = array(
                    'kname' => $res['kname'],
                    'kid' => $res['kid'],
                    'state' =>$res['state']
                );

                #json 转数组
                $res['Traces'] = json_decode($res['content'],true);

                include $this->template('web/show');
                die();
            }else {
                # 未签收,用已知的数据查询快递状态 api请求api物流,更新数据
                # 获取api参数
                $api = pdo_get($this->tableApi, array( 'uniacid' => $uniacid ), array( 'EBusinessID', 'key' ));
                if (empty( $api )) {
                    message('快递api参数错误,请联系管理员', $this->createWebUrl('show1', array() )  , 'error');
                }

                # 查询快递信息
                require_once "core/mobile/api.php";
                $goGit = new express();
                $rescod = $goGit->goGit($res['kcode'], $kid, $api['EBusinessID'], $api['key']);

                # 判断查询是否成功
                if (empty( $rescod['Success'] )) {
                    message('快递单号输入有误', $this->createWebUrl('show1', array() )  , 'error');
                }

                # 把快递信息进行倒序
                $res['Traces'] = array_reverse($rescod['Traces']);

                # 转为json
                $content = json_encode($res['Traces']);

                # 把最新的数据  插入数据库
                if (empty( $rescod['State']) || $rescod['State'] == '2'){
                    $newdate = array();
                }else{
                    $newdate = array(
                        'state' => $rescod['State'],
                        'content' => $content
                    );
                    $result = pdo_update($this->tableUser, $newdate, $date);

                    if (empty( $result )) {
                        message('更新数据失败,请联系管理员', $this->createWebUrl('show1', array() )  , 'error');
                    }
                }

                # 组成html页面显示数据
                $state = $this->state($rescod['State']);
                $show = array(
                    'kname' => $res['kname'],
                    'kid' =>$kid,
                    'state' =>$state
                );

                include $this->template('web/show');
                die();
            }
        }

		# 显示快递名字
		include $this->template('web/index');
	}

	# 设置快递api 参数
	public function doWebapi() {
		global $_W,$_GPC;
		load()->func('tpl');
		$uniacid = $_W['uniacid'];
		$uid = $_W['uid'];
		//查询数据库已存在的参数
		$api = pdo_get($this->tableApi, array('uniacid' => $uniacid), array('EBusinessID', 'key'));
		//修改api参数
		if (checksubmit('submit')) {
			$EBusinessID = $_GPC['EBusinessID'];
			$key = $_GPC['key'];
			$data = array(
				'uid'=>$uid,
				'uniacid'=>$uniacid,
				'EBusinessID' => $EBusinessID,
				'key'=> $key
			);
			//插入数据  表明 数据
			//如果不存在 insert ,否则 updata
			if (empty( $api )){
				$result = pdo_insert($this->tableApi, $data);
			}else{
				$result = pdo_update($this->tableApi, $data, array('uniacid' => $uniacid));
			}
			//判断是否插入成功
			if (empty($result)) {
				message('修改失败', $this->createWebUrl('api', array() )  , 'error');
			}
			message('修改成功', $this->createWebUrl('api', array() )  , 'success');
		}
        include $this->template('web/api');
	}

	# 用户查询快递
	public function doMobilecover1() {

		global $_W,$_GPC;
		$uniacid = $_W['uniacid'];
		$uid = $_W['uid'];
		load()->func('tpl');
        #检测是否是微信登陆
        checkMobile();

		if(checksubmit('check') || !empty($_GPC['kid']) ) {
			# 获取用户填写的 快递单号
			$kid = trim($_GPC['kid'],'');
			if (empty( $kid ) ){
				message('快递单号不能为空', $this->createMobileUrl('cover1', array() )  , 'error');
			}
			$date = array(
				'uniacid' => $_W['uniacid'],
				'openid' => $_W['openid'],
				'kid' => $kid
			);
			# 查询是否存在 该数据
			$res = pdo_get($this->tableUser,$date);
			if (empty( $res )){
				# 数据库返回结果为空,发送两个api请求
				# 获取api参数
				$api = pdo_get($this->tableApi, array('uniacid' => $uniacid), array('EBusinessID', 'key'));
				if (empty($api)){
					message('快递api参数错误,请联系管理员', $this->createWebUrl('show1', array() )  , 'error');
				}
				# 用快递名字.换取快递代码
				require_once "core/mobile/check.php";
				$check = new check();
				$result = $check->goGit($kid,$api['EBusinessID'],$api['key']);
				$Code = $result['Shippers'][0];

				# 查询快递信息
				require_once "core/mobile/api.php";
				$goGit = new express();
				$rescod = $goGit->goGit($Code['ShipperCode'],$kid,$api['EBusinessID'],$api['key']);

				if (empty($rescod['Success']) ){
					message('快递单号输入有误', $this->createMobileUrl('cover1', array() )  , 'error');
				}
				# 数据库倒序
				$res['Traces'] = array_reverse($rescod['Traces']);
				# 把数据转为json格式 存储到数据库
				$content = json_encode($res['Traces']);

				# 把第一次查询的数据 存入数据库
				$date['kname'] = $Code['ShipperName'];
				$date['kcode'] = $Code['ShipperCode'];
				$date['state'] = $rescod['State'];
				$date['content'] = $content;
				$date['createtime'] = time();
				$result =  pdo_insert($this->tableUser,$date);
				if (empty( $result )){
					message('添加数据失败,请联系管理员', $this->createMobileUrl('cover1', array() )  , 'error');
				}
				# 组成html页面显示数据
				$state = $this->state($rescod['State']);
				$show = array(
					'kname' => $Code['ShipperName'],
					'kid' =>$kid,
					'state' =>$state
				);
				include $this->template('show');
				die();

			}else if ($res['state']==3){
				$res['state']='已签收';
				$show = array(
					'kname' => $res['kname'],
					'kid' => $res['kid'],
					'state' =>$res['state']
				);

				#json 转数组
				$res['Traces'] = json_decode($res['content'],true);

				include $this->template('show');
				die();
			}else {
				# 未签收,用已知的数据查询快递状态 api请求api物流,更新数据
				# 获取api参数
				$api = pdo_get($this->tableApi, array( 'uniacid' => $uniacid ), array( 'EBusinessID', 'key' ));
				if (empty( $api )) {
					message('快递api参数错误,请联系管理员', $this->createMobileUrl('cover1', array()), 'error');
				}

				# 查询快递信息
				require_once "core/mobile/api.php";
				$goGit = new express();
				$rescod = $goGit->goGit($res['kcode'], $kid, $api['EBusinessID'], $api['key']);

				# 判断查询是否成功
				if (empty( $rescod['Success'] )) {
					message('快递单号输入有误', $this->createMobileUrl('cover1', array()), 'error');
				}

				# 把快递信息进行倒序
				$res['Traces'] = array_reverse($rescod['Traces']);

				# 转为json
				$content = json_encode($res['Traces']);

				# 把最新的数据  插入数据库
				if (empty( $rescod['State']) || $rescod['State'] == '2'){
					$newdate = array();
				}else{
					$newdate = array(
						'state' => $rescod['State'],
						'content' => $content
					);
					$result = pdo_update($this->tableUser, $newdate, $date);

					if (empty( $result )) {
						message('更新数据失败,请联系管理员', $this->createMobileUrl('cover1', array()), 'error');
					}
				}

				# 组成html页面显示数据
				$state = $this->state($rescod['State']);
				$show = array(
					'kname' => $res['kname'],
					'kid' =>$kid,
					'state' =>$state
				);

				include $this->template('show');
				die();
			}
		}

		# 显示快递名字
		include $this->template('index');
	}

	#扫一扫查询快递
	public function doMobileSweep() {

		global $_W,$_GPC;
		$uniacid = $_W['uniacid'];
		$uid = $_W['uid'];
		load()->func('tpl');
        #检测是否是微信登陆
        checkMobile();

		# 显示快递名字
		include $this->template('sweep');
	}

	# 通过扫一扫查快递
	public function doMobilegit(){
		global $_W,$_GPC;
		$uniacid = $_W['uniacid'];
		$uid = $_W['uid'];
		load()->func('tpl');
        #检测是否是微信登陆
        checkMobile();


		$res = explode(',',$_POST['code']);
		$kid = trim(intval($res[1]));

		if ($kid == false){
			$res['status'] = -1;
			$res['mag'] = '这不是快递单号';
			return json_encode($res);

		}
		# 查询的参数
		$date = array(
			'uniacid' => $_W['uniacid'],
			'openid' => $_W['openid'],
			'kid' => $kid
		);
		# 查询是否存在 该数据
		$res = pdo_get($this->tableUser,$date);

		if (empty( $res )){
			# 数据库返回结果为空,发送两个api请求
			# 获取api参数
			$api = pdo_get($this->tableApi, array('uniacid' => $uniacid), array('EBusinessID', 'key'));
			if (empty($api)){
				$res['status'] = -2;
				$res['mag'] = '获取API参数失败';
				return json_encode($res);
			}
			# 用快递名字.换取快递代码
			require_once "core/mobile/check.php";
			$check = new check();
			$result = $check->goGit($kid,$api['EBusinessID'],$api['key']);
			$Code = $result['Shippers'][0];

			# 查询快递信息
			require_once "core/mobile/api.php";
			$goGit = new express();
			$rescod = $goGit->goGit($Code['ShipperCode'],$kid,$api['EBusinessID'],$api['key']);

			if (empty($rescod['Success']) ){
				$res['status'] = -3;
				$res['mag'] = '快递查询失败';
				return json_encode($res);
			}
			# 数据库倒序
			$res['Traces'] = array_reverse($rescod['Traces']);
			# 把数据转为json格式 存储到数据库
			$content = json_encode($res['Traces']);

			# 把第一次查询的数据 存入数据库
			$date['kname'] = $Code['ShipperName'];
			$date['kcode'] = $Code['ShipperCode'];
			$date['state'] = $rescod['State'];
			$date['content'] = $content;
			$date['createtime'] = time();

			$result =  pdo_insert($this->tableUser,$date);
			if (empty( $result )){
				$res['status'] = -3;
				$res['mag'] = '添加数据失败,请联系管理员';
				return json_encode($res);

			}
			# 组成html页面显示数据
			$state = $this->state($rescod['State']);

			$res['state'] = $state;
			$res['kid'] = $kid;
			$res['kname'] = $Code['ShipperName'];
			$res['status'] = 1;
			return json_encode($res);

		}else if ($res['state']==3){
			$res['state']='已签收';
			#json 转数组
			$res['Traces'] = json_decode($res['content'],true);

			$res['status'] = 1;
			return json_encode($res);
		}else {
			# 未签收,用已知的数据查询快递状态 api请求api物流,更新数据

			# 获取api参数
			$api = pdo_get($this->tableApi, array( 'uniacid' => $uniacid ), array( 'EBusinessID', 'key' ));
			if (empty( $api )) {
				$res['status'] = -1;
				$res['mag'] = '获取API参数失败';
				return json_encode($res);
			}

			# 查询快递信息
			require_once "core/mobile/api.php";
			$goGit = new express();
			$rescod = $goGit->goGit($res['kcode'], $kid, $api['EBusinessID'], $api['key']);

			# 判断查询是否成功
			if (empty( $rescod['Success'] )) {
				$res['status'] = -3;
				$res['mag'] = '快递单号输入有误';
				return json_encode($res);
			}

			# 把快递信息进行倒序
			$res['Traces'] = array_reverse($rescod['Traces']);

			# 转为json
			$content = json_encode($res['Traces']);

			# 把最新的数据  插入数据库
			if (empty( $rescod['State'] )){
				$newdate = array();
			}else{
				$newdate = array(
					'state' => $rescod['State'],
					'content' => $content
				);
				$result = pdo_update($this->tableUser, $newdate, $date);
				if (empty( $result )) {
					$res['status'] = -3;
					$res['mag'] = '更新数据失败,请联系管理员';
					return json_encode($res);
				}
			}

			# 组成html页面显示数据
			$state = $this->state($rescod['State']);

			$res['state'] = $state;
			$res['status'] = 1;
			return json_encode($res);
		}
	}

	# 个人中心
	public function doMobileMember(){
		global $_W,$_GPC;
		load()->func('tpl');
        #检测是否是微信登陆
		checkMobile();
		$tableFans = 'mc_mapping_fans';
        $tableMc = 'mc_members';
        $tableUser = 'yc_expressage_user';

        $check = array(
            'openid' => $_W['openid'],
            'uniacid'=>$_W['uniacid']
        );

		$member = pdo_get($tableFans,$check,array('nickname','uid'));
        $member['images'] = pdo_get($tableMc,array('uid'=>$member['uid']),array('avatar'));//$res['avatar']
        $kcode = pdo_getall($tableUser,$check);

        # 如果用户名或者 头像为空 设置默认头像

        if (empty($member['nickname'])){
            $member['nickname'] = '去吧皮卡丘';
        }
        if (empty($member['images']['avatar'])){
            $member['images']['avatar'] = 'http://yuanxu.top/images/empty.jpg';
        }

        # 统计有几条数据
        $count = count($kcode);


		include $this->template('member');
	}

	public function doMobiletest(){
		global $_W;


		include $this->template('test');
	}
	# 匹配快递状态
	public function state($state){
		switch ($state){
			case '2':
				$state = '在途中';
				break;
			case '3':
				$state = '已签收';
				break;
			case '4':
				$state = '问题件';
				break;
			default:
				$state = '暂未查询到快递信息';
		}
		return $state;
	}

}