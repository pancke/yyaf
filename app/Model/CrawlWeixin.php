<?php

class Model_CrawlWeixin extends Model_Base
{

    const TABLE_NAME = 't_crawl_weixin';

    /**
     * 通过名称获取微信号
     * @param unknown $sAccount
     * @return Ambigous <number, multitype:, mixed>
     */
    public static function getWeixinByAccount ($sAccount)
    {
        return self::getRow(array(
            'where' => array(
                'sAccount' => $sAccount
            )
        ));
    }
}