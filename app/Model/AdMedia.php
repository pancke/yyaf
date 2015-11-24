<?php

class Model_AdMedia extends Model_Base
{

    const TABLE_NAME = 't_ad_media';

    const STATUS_DELETE = 0; // 已删除
    const STATUS_PAY = 1; // 待付款
    const STATUS_CHECK = 9; // 待审核
    const STATUS_RECEIVE = 2; // 待接单
    const STATUS_SUBMIT_PREVIEW = 3; // 待提交预览
    const STATUS_CONFIRM_PREVIEW = 4; // 待内容确认
    const STATUS_PUSH = 5; // 待投放
    const STATUS_SUBMIT_EFFECT = 6; // 待提交效果
    const STATUS_CONFIRM_EFFECT = 7; // 待确认效果
    const STATUS_FINISHED = 8; // 已完成
    const STATUS_CANCEL = 11; // 拒绝接单
    public static $aStatus = array(
        0 => '已删除',
        1 => '待付款',
        9 => '待审核',
        2 => '待接单',
        3 => '待提交预览',
        4 => '待内容确认',
        5 => '待投放',
        6 => '待提交效果',
        7 => '待确认效果',
        8 => '已完成',
        11 => '已拒单'
    );

    /**
     * 取得媒体列表
     *
     * @param $iAdID $iAdID            
     * @param int $iChoose
     *            (-1=全部)
     * @param int $iStatus
     *            (-1=全部)
     */
    public static function getMediaByAd ($iAdID, $iStatus = -1, $iChoose = 1)
    {
        $aWhere = array(
            'iAdID' => $iAdID
        );
        if ($iChoose != - 1) {
            $aWhere['iChoose'] = $iChoose;
        }
        if ($iStatus == - 1) {
            $aWhere['iStatus >'] = 0;
        }
        
        return self::getAll(array(
            'where' => $aWhere
        ));
    }

    /**
     * 更新选择
     *
     * @param unknown $iAdID            
     * @param unknown $aChoose            
     */
    public static function updChoose ($iAdID, $aChoose, $aAdPos)
    {
        self::query('UPDATE t_ad_media SET iChoose=0 WHERE iAdID=' . $iAdID);
        // self::query('UPDATE t_ad_media SET iChoose=1 WHERE iAutoID IN(' . join(',', $aChoose) . ')');
        foreach ($aChoose as $iAutoID) {
            if (! isset($aAdPos[$iAutoID])) {
                continue;
            }
            $aAdMedia = self::getDetail($iAutoID);
            $aMedia = Model_Media::getDetail($aAdMedia['iMediaID']);
            self::updData(array(
                'iAutoID' => $iAutoID,
                'iChoose' => 1,
                'iAdPos' => $aAdPos[$iAutoID],
                'iMoney' => $aMedia['iPrice' . $aAdPos[$iAutoID]]                
            ));
        }
        self::query('UPDATE t_ad SET iTotalMoney=(SELECT SUM(iMoney) FROM t_ad_media WHERE iAdID=' . $iAdID . ' AND iChoose=1 AND iStatus>0) WHERE iAdID=' . $iAdID);
    }
}