<?php

class Model_Finance extends Model_Base
{

    const TABLE_NAME = 't_finance';

    const PAYMENT_IN = 1; // 收入
    const PAYMENT_OUT = 2; // 支出
                           
    // 定义事由来源
    const SOURCE_SELF_CASH_IN = 1; // 自主充值
    const SOURCE_AD_CASH_IN = 2; // 付款充值
    const SOURCE_REFUSE_AD = 3; // 拒单退款
    const SOURCE_CASH_OUT = 4; // 提现
    const SOURCE_AD_COST = 5; // 广告费用
    const SOURCE_AD_INCOME = 6; // 广告收入
                              
    // 支付类型
    const TYPE_NO = 0; // 无
    const TYPE_ALIPAY = 1; // 支付宝
    const TYPE_WEIXIN = 2; // 微信
    const TYPE_BANK = 3; // 银行卡
                         
    // 订单类型
    const ORDER_AD = 1;

    const ORDER_SELF = 2;

    /**
     * 取得事由来源
     *
     * @return multitype:string
     */
    public static function getTypes ()
    {
        return array(
            self::TYPE_NO => '无',
            self::TYPE_ALIPAY => '支付宝',
            self::TYPE_WEIXIN => '微信',
            self::TYPE_BANK => '银行卡'
        );
    }

    /**
     * 获取payment类型
     */
    public static function getPayments ()
    {
        return array(
            self::PAYMENT_IN => '收入',
            self::PAYMENT_OUT => '支出'
        );
    }

    /**
     * 取得事由来源
     *
     * @return multitype:string
     */
    public static function getSources ()
    {
        return array(
            self::SOURCE_SELF_CASH_IN => '自主充值',
            self::SOURCE_AD_CASH_IN => '付款充值',
            self::SOURCE_REFUSE_AD => '拒单退款',
            self::SOURCE_CASH_OUT => '提现',
            self::SOURCE_AD_COST => '广告费用',
            self::SOURCE_AD_INCOME => '广告收入'
        );
    }

    /**
     * 取得我的订单
     * 
     * @param unknown $sMyOrder            
     */
    public static function getMyOrder ($sMyOrder)
    {
        if (empty($sMyOrder)) {
            return null;
        }
        
        return self::getRow(array(
            'where' => array(
                'sMyOrder' => $sMyOrder
            )
        ));
    }

    /**
     * 更新用户余额
     * 
     * @param unknown $aUser            
     * @param unknown $aParam            
     */
    public static function updMoney ($aUser, $aParam, $iPayID = 0)
    {
        if (Db_Orm::getCommitCnt() == 0) {
            throw new Exception('处理钱一定要用事务处理！');
            return false;
        }
        
        if ($aParam['iMoney'] == 0) {
            return 1;
        }
        if (is_array($aUser)) {
            $iUserID = $aUser['iUserID'];
        } else {
            $iUserID = (int) $aUser;
        }
        
        $aUser = Model_User::getDetail($iUserID);
        
        // 增加之前余额判断
        $iRet = Model_User::query('UPDATE t_user SET iMoney=iMoney+' . $aParam['iMoney'] . ' WHERE iUserID=' . $iUserID . ' AND iMoney=' . $aUser['iMoney']);
        if ($iRet != 1) {
            Model_Base::rollback();
            throw new Exception('处理钱一定要用事务处理！');
            return false;
        }
        
        if ($iPayID == 0) {
            $aRow = array(
                'iUserID' => $iUserID,
                'iPayment' => $aParam['iPayment'],
                'iSource' => $aParam['iSource'],
                'sReaName' => isset($aParam['sReaName']) ? $aParam['sReaName'] : '',
                'iPayType' => isset($aParam['iPayType']) ? $aParam['iPayType'] : 0,
                'iPayMoney' => $aParam['iMoney'],
                'iUserMoney' => $aUser['iMoney'] + $aParam['iMoney'],
                'sOpenName' => isset($aParam['sOpenName']) ? $aParam['sOpenName'] : '',
                'sBankName' => isset($aParam['sBankName']) ? $aParam['sBankName'] : '',
                'sPayAccount' => isset($aParam['sPayAccount']) ? $aParam['sPayAccount'] : '',
                'iPayStatus' => isset($aParam['iPayStatus']) ? $aParam['iPayStatus'] : 1,
                'sPayOrder' => isset($aParam['sPayOrder']) ? $aParam['sPayOrder'] : '',
                'sMyOrder' => isset($aParam['sMyOrder']) ? $aParam['sMyOrder'] : '',
                'sRemark' => isset($aParam['sRemark']) ? $aParam['sRemark'] : ''
            );
            $iPayID = self::addData($aRow);
        } else {
            $aRow = array(
                'iAutoID' => $iPayID,
                'iUserMoney' => $aUser['iMoney'] + $aParam['iMoney'],
                'iPayStatus' => isset($aParam['iPayStatus']) ? $aParam['iPayStatus'] : 1,
                'sPayOrder' => isset($aParam['sPayOrder']) ? $aParam['sPayOrder'] : '',
                'sMyOrder' => isset($aParam['sMyOrder']) ? $aParam['sMyOrder'] : '',
                'sRemark' => isset($aParam['sRemark']) ? $aParam['sRemark'] : ''
            );
            self::updData($aRow);
        }
        
        return $iPayID;
    }

    /**
     * 广告付款
     * 
     * @param unknown $aUser            
     * @param unknown $aAd            
     * @param unknown $iMoney            
     */
    public static function payAd ($aUser, $aAd, $iPayMoney = 0, $aArg = array())
    {
        self::begin();
        if ($iPayMoney > 0) {
            // 充值金额
            $aRow = array(
                'iPayment' => self::PAYMENT_IN,
                'iSource' => self::SOURCE_AD_CASH_IN,
                'iMoney' => $iPayMoney
            );
            $aRow = array_merge($aRow, $aArg);
            self::updMoney($aUser, $aRow);
        }
        
        // 扣款金额
        $iPayID = self::updMoney($aUser, array(
            'iPayment' => self::PAYMENT_OUT,
            'iSource' => self::SOURCE_AD_COST,
            'iMoney' => $aAd['iTotalMoney'] * -1
        ));
        
        // 更新广告支付状态
        $iRet = Model_Ad::query('UPDATE t_ad SET iPayStatus=1,iPayID="'.$iPayID.'" WHERE iAdID=' . $aAd['iAdID'] . ' AND iPayStatus=0');
        if ($iRet == 0) {
            self::rollback();
            return false;
        }

        // 更新自媒体状态为待接单
        self::query('UPDATE t_ad_media SET iStatus=' . Model_AdMedia::STATUS_CHECK . ' WHERE iChoose=1 AND iAdID=' . $aAd['iAdID']);
        
        self::commit();
        
        return $iPayID;
    }

    /**
     * 充值
     * 
     * @param unknown $sType            
     * @param unknown $sOrderID            
     * @param unknown $iMoney            
     * @param unknown $aArg            
     */
    public static function pay ($sOrderID, $iPayMoney, $aArg)
    {
        Model_Finance::begin();
        $aArg['sMyOrder'] = $sOrderID;
        $sType = $sOrderID[0];
        $iOrderID = substr($sOrderID, 1);
        if ($sType == self::ORDER_AD) { // 广告
            $aAd = Model_Ad::getDetail($iOrderID);
            if ($aAd && $aAd['iPayStatus'] == 0) {
                $aUser = Model_User::getDetail($aAd['iUserID']);
                if (ENV_SCENE == 'dev') {
                    $iPayMoney = $aAd['iTotalMoney'] - $aUser['iMoney'];
                }
                $iPayID = self::payAd($aUser, $aAd, $iPayMoney, $aArg);
            } elseif ($aAd && $aAd['iPayStatus'] == 1) {
                $iPayID = $aAd['iPayID'];
            } else {
                $iPayID = 0;
            }
        } else {
            $aArg['iPayment'] = Model_Finance::PAYMENT_IN;
            $aArg['iSource'] = Model_Finance::SOURCE_SELF_CASH_IN;
            $aFinance = Model_Finance::getDetail($iOrderID);
            if ($aFinance && $aFinance['iPayStatus'] == 0) {
                $aUser = Model_User::getDetail($aFinance['iUserID']);
                if (ENV_SCENE == 'dev') {
                    $iPayMoney = $aFinance['iPayMoney'];
                }
                $aArg['iMoney'] = $iPayMoney;
                $iPayID = self::updMoney($aUser, $aArg, $iOrderID);
            } else {
                $iPayID = $iOrderID;
            }
        }
        Model_Finance::commit();
        
        return $iPayID;
    }
}