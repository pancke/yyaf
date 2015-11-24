<?php

class Model_HomeManager extends Model_Base
{

    const TABLE_NAME = 't_home_manager';
    
    /**
     * 取得首页圈子所有的配置
     * @return multitype:Ambigous <multitype:multitype: Ambigous <> , multitype:, array/string, int/false, number, unknown, unknown>
     */
    public static function getAllData()
    {
        return self::query("SELECT * FROM t_home_manager WHERE iStatus=1");
    } 
}