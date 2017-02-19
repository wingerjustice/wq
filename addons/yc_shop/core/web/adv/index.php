<?php
// +----------------------------------------------------------------------
// | WSHOTO [ 技术主导，服务至上，提供微信端解决方案 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2020 http://www.wshoto.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: yc <yc@yuanxu.top>
// +----------------------------------------------------------------------
## 商城幻灯片管理
global $_W,$_GPC;

load()->func('tpl');
#表名
$tbName = 'yc_shop_adv';

$op = empty($_GPC['op']) ?'display':$_GPC['op'] ;

# 基本数组
$adv = array(
    'uniacid'=>$_W['uniacid'],
    'status'=>'1'
);

# 默认展示已有幻灯片列表
if ($op== 'display'){
    $resAdv  = pdo_get($tbName,$adv);
    if (empty($resAdv)){
        message('幻灯片列表为空,请前去添加', $this->createWebUrl('adv', array('op'=>'add'))  , 'error');
    }

}

if ($op== 'add'){



}


include $this->template('adv');