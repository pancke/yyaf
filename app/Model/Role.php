<?php

class Model_Role extends Model_Base
{

    const TABLE_NAME = 't_role';

    /**
     * 根据角色名取得角色信息
     * @param unknown $sRoleName
     * @return Ambigous <number, multitype:, mixed>
     */
    public static function getRoleByName ($sRoleName)
    {
        return self::getRow(array(
            'where' => array(
                'sRoleName' => $sRoleName
            )
        ));
    }
    
    /**
     * 取得所有角色的ID => Name数组
     * @return Ambigous <number, multitype:multitype:, multitype:unknown >
     */
    public static function getPairRoles ()
    {
        return self::getPair(array('where' => array('iStatus' => 1)), 'iRoleID', 'sRoleName');
    }
}