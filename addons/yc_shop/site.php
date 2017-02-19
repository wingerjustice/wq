<?php

/*
----------------------------------
*|  auther:  yc  yc@yuanxu.top
*|  website: yuanxu.top
---------------------------------------
*/
defined('IN_IA') or exit('Access Denied');
global $_W,$_GPC;

require_once 'core/inc/core.php';
require_once 'core/inc/define.php';
require_once 'core/inc/user.php';

class Yc_shopModuleSite extends Core {

	public function doWebCategory() {
		## 商城分类管理
        $this->_exec(__FUNCTION__,true);
	}

    public function doWebAdv() {
        ## 商城幻灯片管理
        $this->_exec(__FUNCTION__,true);
    }

    public function doMobileIndex() {
        //这个操作被定义用来呈现 功能封面.
        $this->_exec(__FUNCTION__,false);
    }
    public function doMobileTest() {
        //这个操作被定义用来呈现 功能封面.
        $this->_exec(__FUNCTION__,false);
    }


}