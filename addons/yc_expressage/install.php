<?php
/*
----------------------------------
*|  auther:  yc  yc@yuanxu.top
*|  website: yuanxu.top
---------------------------------------
*/
$sql = "

DROP TABLE IF EXISTS `ims_yc_expressage_api`;
CREATE TABLE `ims_yc_expressage_api` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT '0' COMMENT '公众号id',
  `uid` int(11) DEFAULT NULL COMMENT '管理员id',
  `EBusinessID` varchar(255) DEFAULT '' COMMENT '快递api ID',
  `key` varchar(255) DEFAULT '' COMMENT '快递api KEY',
  PRIMARY KEY (`id`),
  KEY `idx_uniacid` (`uniacid`),
  KEY `idx_EBusinessID` (`EBusinessID`),
  KEY `idx_key` (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ims_yc_expressage_user`;
CREATE TABLE `ims_yc_expressage_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `uniacid` int(11) DEFAULT NULL COMMENT '公众号id',
  `uid` int(11) DEFAULT NULL COMMENT '管理员id',
  `openid` varchar(255) DEFAULT NULL COMMENT '用户openid',
  `kname` varchar(255) DEFAULT NULL COMMENT '快递名称',
  `kid` varchar(255) DEFAULT NULL COMMENT '快递编号',
  `kcode` varchar(255) DEFAULT NULL COMMENT '快递代码',
  `state` varchar(255) DEFAULT NULL COMMENT '快递状态',
  `content` text(0)  COMMENT '快递内容',
  `createtime` varchar(255) DEFAULT NULL COMMENT '查询时间',
  PRIMARY KEY (`id`),
  KEY `index_uniacid` (`uniacid`),
  KEY `index_openid` (`openid`),
  KEY `index_kname` (`kname`),
  KEY `index_kid` (`kid`),
  KEY `index_status` (`state`),
  KEY `index_createtime` (`createtime`),
  KEY `index_kcode` (`kcode`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

";

pdo_query($sql);