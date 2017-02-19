<?php
// +----------------------------------------------------------------------
// | WSHOTO [ 技术主导，服务至上，提供微信端解决方案 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2020 http://www.wshoto.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: yc <yc@yuanxu.top>
// +----------------------------------------------------------------------
/**
 * 自定义函数定义文件
 *
*/
# 插件名称
$Mname = $_W['current_module']['name'];

define('DS', DIRECTORY_SEPARATOR);
define('IA_ADDONS', IA_ROOT . DS .'addons');
define('IA_FRAMEWORK', IA_ROOT . DS .'framework');
define('IA_Mname', IA_ADDONS . DS . $Mname);

define('IA_YC_STATIC', $_W['siteroot'] . 'addons/'.$Mname.'/static');
define('FRA',IA_YC_STATIC. DS.'framework7');
define('IA_YC_STATIC_COMMON', IA_YC_STATIC . '/common');
define('IA_YC_STATIC_CSS', IA_YC_STATIC . '/css');
define('IA_YC_STATIC_JS', IA_YC_STATIC . '/js');
define('IA_YC_STATIC_IMAGES', IA_YC_STATIC . '/images');