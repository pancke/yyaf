<?php

class Model_WorkMedia extends Model_Base
{

    const TABLE_NAME = 't_workmedia';
    
    public static $wtype = array(
    		'1' => '时尚圈(1500+)',
    		'2' => '母婴圈(1300+)',
    		'3' => '汽车圈（800+)',
    		'4' => '旅游户外圈(2000+)',
    		'5' => '本地生活圈(1600+)',
    		'6' => 'IT科技圈(750+)',
    		'7' => '金融财经圈(900+)',
    		'8' => '房产家居圈(1200+)'
    );
    
    /**
     * 取得首页圈子所有的配置
     * @return multitype:Ambigous <multitype:multitype: Ambigous <> , multitype:, array/string, int/false, number, unknown, unknown>
     */
    public static function getAllData()
    {
        $aData = array();
        foreach (self::$wtype as $k => $v) {
            $aData[$v] = self::query("SELECT * FROM t_workmedia WHERE wtype=$k AND iStatus=1 LIMIT 6");
        }
    
        return $aData;
    }
}