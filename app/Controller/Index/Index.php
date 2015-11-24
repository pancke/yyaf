<?php

/**
 * 提供网站首页和广告详情功能
 * Created by PhpStorm.
 * User: xiejinci
 * Date: 14/12/24
 * Time: 下午2:32
 */
class Controller_Index_Index extends Controller_Index_Base
{

    /**
     * 首页
     */
    public function indexAction ()
    {
        $this->assign('aAUser', $this->getCurrUser(Model_User::TYPE_AD));
        $this->assign('aMUser', $this->getCurrUser(Model_User::TYPE_MEDIA));
        $this->assign('aBanner', Model_Banner::getAll(array(
            'where' => array(
                'iStatus' => 1
            ),
            'limit' => 5,
            'order' => 'rank asc'
        )));
        $this->assign('aCricle', Model_WorkMedia::getAllData());
        $this->assign('aCase', Model_HomeCase::getAllData());
        $this->assign('aManager', Model_HomeManager::getAllData());
        $this->assign('sTopMenu', 'home');
        
        $this->setMeta('home_page', array(
        ));
    }
    
    /**
     * 推广详情
     */
    public function adAction ()
    {
        $iAdID = (int) $this->getParam('id', 0);
        $aAd = Model_Ad::getDetail($iAdID);
        if (empty($aAd)) {
            return $this->show404();
        }
        
        $aSetting = Model_Ad::getSetting($aAd);
        if (! empty($aSetting) && isset($aSetting['sForwardImg'])) {
            $aSetting['aForwardImg'] = explode(',', $aSetting['sForwardImg']);
        }
        
        $aList = Model_AdMedia::getAll(array(
            'iAdID' => $iAdID,
            'iChoose' => 1,
            'iStatus >' => 0
        ));
        foreach ($aList as $k => &$aRow) {
            $aRow['aAd'] = Model_Ad::getDetail($aRow['iAdID']);
            $aRow['aMedia'] = Model_Media::getDetail($aRow['iMediaID']);
        }

        $this->assign('aStatus', Model_AdMedia::$aStatus);
        $this->assign('aTitle', Model_Media::$aPos[$aAd['iMediaType']]);
        $this->assign('aSetting', $aSetting);
        $this->assign('aAd', $aAd);
        $this->assign('aList', $aList);
        $this->assign('iType', $this->getParam('type', 1));
        $this->setMeta('ad_add', array(
            'sTitle' => '添加推广计划 - 支付详情'
        ));
    }
    
    public function svnAction ()
    {
        $path = realpath(APP_PATH . '/../');
        $out = null;
        exec('cd /data/wwwroot/51wom_online;/usr/bin/svn up', $out);
        var_dump($out);
        
        return false;
    }
}