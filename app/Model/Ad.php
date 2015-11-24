<?php

class Model_Ad extends Model_Base
{

    const TABLE_NAME = 't_ad';

    const STATUS_DELETE = 0; // 已删除
    const STATUS_WAIT_APPROVE = 1; // 待审核
    const STATUS_APPROVE_OK = 2; // 审核通过
    const STATUS_APPROVE_NO = 3; // 审核未通过
    const STATUS_FINISHED = 4; // 已完成
    const STATUS_WRITING = 5; // 填写中
    public static $aOrderStatus = array(
        0 => '已删除',
        1 => '待审核',
        2 => '审核通过',
        3 => '审核未通过',
        4 => '已完成'
    );

    public static $aPayStatus = array(
        0 => '未支付',
        1 => '已支付'
    );

    /**
     * 根据名字获取自媒体
     *
     * @param unknown $iAdType            
     * @param unknown $sAdName            
     * @param number $iAdID            
     * @return Ambigous <number, multitype:, mixed>
     */
    public static function getAdByName ($iUserID, $sAdName, $iAdID = 0)
    {
        $aWhere = array(
            'iUserID' => $iUserID,
            'sAdName' => $sAdName
        );
        
        if ($iAdID > 0) {
            $aWhere['iAdID !='] = $iAdID;
        }
        
        return self::getRow(array(
            'where' => $aWhere
        ));
    }

    /**
     * 更新自媒体分类
     *
     * @param unknown $iAdID            
     * @param unknown $aNewMediaID            
     */
    public static function updMedia ($iAdID, $aNewMediaID, $iUserID)
    {
        $aOldMediaID = Model_AdMedia::getPair(array(
            'where' => array(
                'iAdID' => $iAdID,
                'iStatus' => 1
            )
        ), 'iAutoID', 'iMediaID');
        
        $aNewMediaID = array_flip($aNewMediaID);
        $aOldMediaID = array_flip($aOldMediaID);
        foreach ($aOldMediaID as $iMediaID => $iAutoID) {
            if (isset($aNewMediaID[$iMediaID])) {
                continue;
            }
            Model_AdMedia::realDelData($iAutoID);
        }
        
        foreach ($aNewMediaID as $iMediaID => $iAutoID) {
            if (isset($aOldMediaID[$iMediaID])) {
                continue;
            }
            $aMedia = Model_Media::getDetail($iMediaID);
            Model_AdMedia::addData(array(
                'iAdID' => $iAdID,
                'iMediaID' => $iMediaID,
                'iAUserID' => $iUserID,
                'iMUserID' => $aMedia['iUserID'],
                'iPos' => 1,
                'iMoney' => 0,
                'iPlanTime' => 0,
                'iStatus' => 1
            ));
        }
    }

    /**
     * 取得Model
     */
    public static function getSettingModel ($iMediaType)
    {
        $sModel = '';
        switch ($iMediaType) {
            case Model_Media::TYPE_WEIXIN:
                $sModel = 'Model_AdWeixin';
                break;
            case Model_Media::TYPE_FRIEND:
                $sModel = 'Model_AdFriend';
                break;
            case Model_Media::TYPE_WEIBO:
                $sModel = 'Model_AdWeibo';
                break;
            case Model_Media::TYPE_NEWS:
                $sModel = 'Model_AdNews';
                break;
        }
        
        return $sModel;
    }

    /**
     * 取得设置详情
     *
     * @param unknown $aAd            
     */
    public static function getSetting ($aAd)
    {
        if (empty($aAd)) {
            return null;
        }
        
        $sModel = self::getSettingModel($aAd['iMediaType']);
        
        return $sModel::getDetail($aAd['iAdID']);
    }

    /**
     * 更新设置详情
     *
     * @param unknown $aAd            
     * @param unknown $aData            
     */
    public static function setSetting ($aAd, $aData)
    {
        $sModel = self::getSettingModel($aAd['iMediaType']);
        
        $aSetting = self::getSetting($aAd);
        if (empty($aSetting)) {
            $sModel::addData($aData);
        } else {
            $sModel::updData($aData);
        }
        
        Model_Ad::updData(array(
            'iPlanTime' => $aData['iPlanTime'],
            'iAdID' => $aAd['iAdID']
        ));
        self::query('UPDATE t_ad_media SET iPlanTime="' . $aData['iPlanTime'] . '",iAdPos=' . $aData['iAdPos'] . ' WHERE iAdID=' . $aAd['iAdID']);
        self::query('UPDATE t_ad_media am,t_media m SET am.iMoney=m.iPrice' . $aData['iAdPos'] . ' WHERE am.iAdID=' . $aAd['iAdID'] . ' AND am.iMediaID=m.iMediaID');
        
        // 修改状态为待审核
        self::updData(array(
            'iAdID' => $aAd['iAdID'],
            'iStatus' => self::STATUS_WAIT_APPROVE
        ));
    }

    /**
     * 取得昨天订单数
     *
     * @return Ambigous <multitype:multitype: Ambigous <> , multitype:, array/string, int/false, number, unknown, unknown>
     */
    public static function getYesterdayAdCnt ()
    {
        $iToday = strtotime('today');
        return self::query('SELECT COUNT(*) FROM t_ad WHERE iStatus=1 AND iCreateTime>=' . ($iToday - 86400) . ' AND iCreateTime<' . $iToday, 'one');
    }

    /**
     * 取得昨天的投放客户数
     *
     * @return Ambigous <multitype:multitype: Ambigous <> , multitype:, array/string, int/false, number, unknown, unknown>
     */
    public static function getYesterdayUserCnt ()
    {
        $iToday = strtotime('today');
        return self::query('SELECT COUNT(DISTINCT iUserID) FROM t_ad WHERE iStatus=1 AND iCreateTime>=' . ($iToday - 86400) . ' AND iCreateTime<' . $iToday, 'one');
    }
}