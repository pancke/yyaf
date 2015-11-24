<?php

/**
* 支付宝在线支付
* User: xiejinci
* Date: 14/12/24
* Time: 下午2:32
*/
class Controller_Payment_Alipay extends Controller_Index_Base
{

    /**
     * 支付宝支付跳转
     */
    public function payAction ()
    {
        $orderid = $this->getParam('orderid');
        $subject = $this->getParam('subject');
        $body = $this->getParam('body');
        $total_fee = $this->getParam('total_fee');
        
        echo Payment_Alipay::makePay($orderid, $subject, $body, $total_fee);
        
        return false;
    }

    /**
     * 将回调数据记录到数据库中
     * @param unknown $aParam
     * @return Ambigous <int/false, last_insert_id, number>
     */
    private function logPay($aParam) 
    {
        $aData = array(
            'iPayType' => Model_Finance::TYPE_ALIPAY,
            'sPayAccount' => $aParam['buyer_email'],
            'sPayOrderID' => $aParam['trade_no'],
            'sMyOrderID' => $aParam['out_trade_no'],
            'sData' => json_encode($aParam, JSON_UNESCAPED_UNICODE),
            'iStatus' => 0
        );
        return Model_Pay::logPay($aData);
    }
    
    /**
     * 支付成功回写
     */
    public function returnAction ()
    {
        $aParam = $this->getParams();
        Yaf_Logger::debug('return: ' . json_encode($aParam, JSON_UNESCAPED_UNICODE) . "\n", 'alipay');

        $bRet = Payment_Alipay::callback($aParam);
                
        // $bRet = true;
        
        $iOrderID = $aParam['out_trade_no'];
        $iMoney = $aParam['total_fee'];
        if ($bRet) { // 支付成功
            $this->logPay($aParam);
            
            $aArg = array(
                'iPayType' => Model_Finance::TYPE_ALIPAY,
                'sPayAccount' => $aParam['buyer_email'],
                'sPayOrder' => $aParam['trade_no']
            );
            
            $iPayID = Model_Finance::pay($iOrderID, $iMoney, $aArg);
            $url = "/payment/pay/success/id/" . $iPayID . '.html';
        } else { // 支付失败
            $url = "/payment/pay/fail/id/" . $aParam['out_trade_no'] . '.html';
        }
        
        // $this->setViewScript('404.phtml');
        return $this->redirect($url);
    }

    /**
     * 支付宝
     */
    public function notifyAction ()
    {
        $aParam = $this->getParams();
        Yaf_Logger::debug('notify: ' . json_encode($aParam, JSON_UNESCAPED_UNICODE) . "\n", 'alipay');
        
        $bRet = Payment_Alipay::callback($aParam);
        
        $iOrderID = $aParam['out_trade_no'];
        $iMoney = $aParam['total_fee'];
        if ($bRet) { // 支付成功
            $this->logPay($aParam);
            
            $aArg = array(
                'iPayType' => Model_Finance::TYPE_ALIPAY,
                'sPayAccount' => $aParam['buyer_email'],
                'sPayOrder' => $aParam['trade_no']
            );
            
            Model_Finance::pay($iOrderID, $iMoney, $aArg);
            echo "success";
        } else { // 支付失败
            echo "fail";
        }
        
        return false;
    }
}