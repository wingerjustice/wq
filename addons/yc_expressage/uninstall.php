<?php
/*
----------------------------------
*|  auther:  yc  yc@yuanxu.top
*|  website: yuanxu.top
---------------------------------------
*/

global $_W;
$sql = "
DROP TABLE IF EXISTS `ims_yc_expressage_api`;
DROP TABLE IF EXISTS `ims_yc_expressage_user`;
";
pdo_query($sql);