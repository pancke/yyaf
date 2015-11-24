<?php

/**
 * 支付回调
 */
class Controller_Payment_Weixin extends Controller_Index_Base
{
    public function payAction ()
    {
        $orderid = $this->getParam('orderid');
        $subject = $this->getParam('subject');
        $body = $this->getParam('body');
        $total_fee = $this->getParam('total_fee');
        $sQRUrl = Payment_Weixin::qrcode($orderid, $subject, $total_fee, $body);
        
        $this->assign('sQRUrl', $sQRUrl);
        $this->assign('aParam', $this->getParams());
    }

    /**
     * 将回调数据记录到数据库中
     * @param unknown $aParam
     * @return Ambigous <int/false, last_insert_id, number>
     */
    private function logPay($aParam)
    {
        $aData = array(
            'iPayType' => Model_Finance::TYPE_WEIXIN,
            'sPayAccount' => $aParam['openid'],
            'sPayOrderID' => $aParam['transaction_id'],
            'sMyOrderID' => $aParam['attach'],
            'sData' => json_encode($aParam, JSON_UNESCAPED_UNICODE),
            'iStatus' => 0
        );
        return Model_Pay::logPay($aData);
    }
    
    /**
     * 微信异步调用
     */
    public function notifyAction ()
    {
        Payment_Weixin::notify(array($this, 'callback'));
        return false;
    }
    
    public function callback($data)
    {
        $this->logPay($data);
        
        $iMoney = $data['total_fee'] / 100;
        $sOrderID = $data['attach'];
        $aArg = array(
            'iPayType' => Model_Finance::TYPE_WEIXIN,
            'sPayAccount' => $data['openid'],
            'sPayOrder' => $data['transaction_id'],
        );
        
        return Model_Finance::pay($sOrderID, $iMoney, $aArg);
    }
}