<?php

class Model_HomeCase extends Model_Base
{

    const TABLE_NAME = 't_home_case';
    
    public static $wtype = array(
    		'1' => '快消案例',
    		'2' => 'O2O案例',
    		'3' => '母婴案例',
    		'4' => '汽车案例',
    		'5' => '旅游案例',
    		'6' => 'IT科技案例',
    		'7' => '金融财经案例',
    		'8' => '本地生活案例',
    		'9' => '综合案例',
    		'10' => '奢侈品',
    		'9' => '教育',
    );
    
    /**
     * 取得首页圈子所有的配置
     * @return multitype:Ambigous <multitype:multitype: Ambigous <> , multitype:, array/string, int/false, number, unknown, unknown>
     */
    public static function getAllData()
    {
        $aData = array();
        foreach (self::$wtype as $k => $v) {
            $aData[$v] = self::query("SELECT * FROM t_home_case WHERE iType=$k AND iStatus=1", 'row');
        }
        
        return $aData;
    } 
}