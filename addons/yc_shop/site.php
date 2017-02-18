<?php

/*
----------------------------------
*|  auther:  yc  yc@yuanxu.top
*|  website: yuanxu.top
---------------------------------------
*/
defined('IN_IA') or exit('Access Denied');

require_once 'core/inc/core.php';
require_once 'core/inc/user.php';


class Yc_shopModuleSite extends Core {

	public function doWebIndex() {
		//这个操作被定义用来呈现 规则列表
        $this->_exec(__FUNCTION__,true);
	}

    public function doMobileIndex() {
        //这个操作被定义用来呈现 功能封面.
        $this->_exec(__FUNCTION__,false);
    }


}