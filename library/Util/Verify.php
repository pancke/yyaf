<?php
/**
 * 验证码
 * @author len
 *
 */

class Util_Verify
{
    const TYPE_AD_IMAGE = 1;
    const TYPE_MEDIA_IMAGE = 2;
    const TYPE_FORGET_IMAGE = 3;
    
    const TYPE_SMS_FORGET = 11;    //忘记密码
    
    /**
     * 获取图片验证码
     * @param unknown $iType
     * @return string
     */
    public static function makeImageCode ($iType)
    {
        $sKey = self::getImageKey($iType);
        $sRand = Util_Tools::passwdGen(4);
        Util_Cookie::set($sKey, $sRand, 1800);
        return Util_Image::createIdentifyCodeImage(120, 50, $sRand);
    }
    
    /**
     * 取得验证码Cookie名
     *
     * @param int $iType
     * @return string
     */
    protected static function getImageKey ($iType)
    {
        $sGuid = Util_Cookie::get('guid');
        return $sGuid . '_' . $iType;
    }
    
    /**
     * 检测验证码是否正确
     * @param unknown $iType
     * @param unknown $sCode
     * @return bool
     */
    public static function checkImageCode($iType, $sCode)
    {
        $sKey = self::getImageKey($iType);
        $sSaveCode = Util_Cookie::get($sKey);
        Util_Cookie::delete($sKey);
        return strtoupper($sCode) == strtoupper($sSaveCode);
    }
    
    /**
     * 取得验证码Cookie名
     *
     * @param int $iType
     * @return string
     */
    protected static function getSmsKey ($iType)
    {
        $sGuid = Util_Cookie::get('guid');
        return $sGuid . 's_' . $iType;
    }
    
    /**
     * 发送手机验证码
     * @param unknown $sMobile
     * @param unknown $iType
     */
    public static function makeSMSCode ($sMobile, $iType)
    {
        $sKey = self::getSmsKey($iType);
        $sRand = Util_Tools::passwdGen(4, Util_Tools::FLAG_NUMERIC);
        Util_Cookie::set($sKey, $sRand, 1800);
        
        $iTempID = Util_Common::getConf($iType, 'aSmsTempID');
        
        return Sms_CCP::sendTemplateSMS($sMobile, array($sRand, 10), $iTempID);
    }
    
    /**
     * 检验验证码
     * @param unknown $sMobile
     * @param unknown $iType
     * @param unknown $sCode
     */
    public static function checkSMSCode ($sMobile, $iType, $sCode)
    {
        $sKey = self::getSmsKey($iType);
        $sSaveCode = Util_Cookie::get($sKey);
        Util_Cookie::delete($sKey);
        return strtoupper($sCode) == strtoupper($sSaveCode);
    }
}