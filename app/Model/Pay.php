<?php

class Model_Pay extends Model_Base
{

    const TABLE_NAME = 't_pay';
    
    
    public static function logPay($aData) 
    {
        $iAutoID = (int)self::query('SELECT iAutoID from t_pay WHERE sPayOrderID="' . $aData['sPayOrderID'] . '" AND iPayType=' . $aData['iPayType'], 'one');
        if ($iAutoID == 0) {
            $iAutoID = self::addData($aData);
        }
        
        return iAutoID;
    }
}