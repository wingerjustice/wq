<?php

/*
----------------------------------
 *|  yc_expressage模块订阅器
 *|  auther:  yc  yc@yuanxu.top
 *|  website: yuanxu.top
---------------------------------------
 */

defined('IN_IA') or exit('Access Denied');

class Yc_expressageModuleReceiver extends WeModuleReceiver {
	public function receive() {
		$type = $this->message['type'];
		//这里定义此模块进行消息订阅时的, 消息到达以后的具体处理过程, 请查看文档来编写你的代码
	}
}