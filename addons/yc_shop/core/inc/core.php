<?php
/*
----------------------------------
*|  auther:  yc  yc@yuanxu.top
*|  website: yuanxu.top
---------------------------------------
*/
# 重新定义 template() 方法



if (!defined('IN_IA')) {
    die( 'Access Denied' );
}

class Core extends WeModuleSite {
    public $footer = array();
    public $header = null;


    /**
     * 加载html 文件函数
     * @$filename 文件夹名字
     * @$file 文件名字 不填则默认index文件
    */
    public function template($filename){
        global $_W;
        $name = strtolower($this->modulename);
        $defineDir = dirname($this->__define);

        # 判断是否自定义路径
        $file = strpos($filename,'/') ? '': '/index' ;

        if (defined('IN_SYS')) {
            #定义插件 模版目录
            $source = $defineDir.'/template/web/'.$filename.$file.'.html';
            $compile = IA_ROOT . "/data/tpl/web/{$_W['template']}/{$name}/{$filename}.tpl.php";

        } else {
            #定义插件 模版目录
            $source = $defineDir.'/template/mobile/'.$filename.$file.'.html';
            $compile = IA_ROOT . "/data/tpl/app/{$_W['template']}/{$name}/{$filename}.tpl.php";
        }

        if (!is_file($source)) {
            die( "Error: template source '{$filename}' is not exist!" );
        }

        if (DEVELOPMENT || !is_file($compile) || filemtime($source) > filemtime($compile)) {
            template_compile($source, $compile, true);
        }

        return $compile;
    }

    public function _exec($do,$web = true, $default='' ){
        global $_GPC;

        $default = empty($file) ? 'index' : $default;

        $do = strtolower(substr($do, $web ? 5 : 8));
        $p = trim($_GPC['p']);
        empty( $p ) && ( $p = $default );
        if ($web) {
            $file = IA_ROOT . "/addons/yc_shop/core/web/" . $do . "/" . $p . ".php";
        } else {
            $file = IA_ROOT . "/addons/yc_shop/core/mobile/" . $do . "/" . $p . ".php";
        }
        if (!is_file($file)) {
            message("未找到 控制器文件 {$do}::{$p} : {$file}");
        }
        include $file;
        die;
    }





}