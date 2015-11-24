<?php

class Model_CrawlWeixinArticle extends Model_Base
{

    const TABLE_NAME = 't_crawl_weixin_article';
    
    /**
     * 取得最近30篇阅读数
     * @param unknown $iWeixinID
     * @return Ambigous <multitype:multitype: Ambigous <> , multitype:, array/string, int/false, number, unknown, unknown>
     */
    public static function getAvgReadNum($iWeixinID) 
    {
        return self::query('SELECT AVG(iReadNum) FROM ' . self::TABLE_NAME . ' WHERE iWeixinID=' . $iWeixinID . ' AND iPublishTime>=' . strtotime('-30day') . ' ORDER BY iReadNum DESC LIMIT 30', 'one');
    }
}