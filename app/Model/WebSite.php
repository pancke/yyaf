<?php

class Model_WebSite extends Model_Base
{

    const TABLE_NAME = 't_website';
    
    public static $iType = array(
    		1 => '网站基本信息',
    		2 => '广告主帮助中心',
    		3 => '媒体主帮助中心',
    		4 => '运营规则',
    		5 => '通知公告',
    		6 => '其他'
    );
}