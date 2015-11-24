<?php

/**
 * 广告添加、编辑、详情、支付等操作
 * Created by PhpStorm.
 * User: xiejinci
 * Date: 14/12/24
 * Time: 下午2:32
 */
class Controller_Index_Ad extends Controller_Index_Base
{

    protected $aCurrUser = null;

    public function actionBefore ()
    {
        parent::actionBefore();
        
        // 判断广告主是否已登录
        $this->aCurrUser = $this->getCurrUser(Model_User::TYPE_AD);
        if (empty($this->aCurrUser)) {
            return $this->redirect('/user/login/type/' . Model_User::TYPE_AD . '.html?ret=' . Util_Uri::getCurrUrl());
        }
    }

    /**
     * 添加推广
     */
    public function addAction ()
    {
        if ($this->isPost()) {
            $iAdID = (int) $this->getParam('id');
            $aAd = null;
            if ($iAdID > 0) {
                $aAd = Model_Ad::getDetail($iAdID);
                if ($aAd['iUserID'] != $this->aCurrUser['iUserID']) {
                    return $this->showMsg('不要乱改别人的数据', false);
                }
            }
            
            $aData = array();
            $aData['iUserID'] = $this->aCurrUser['iUserID'];
            $aData['iMediaType'] = (int) $this->getParam('iMediaType');
            $aData['iAdType'] = (int) $this->getParam('iAdType');
            $aData['sAdName'] = $this->getParam('sAdName');
            $aData['iPlanMinMoney'] = (int) $this->getParam('iPlanMinMoney');
            $aData['iPlanMaxMoney'] = (int) $this->getParam('iPlanMaxMoney');
            $aData['sCatID'] = (int) $this->getParam('sCatID');
            
            $aAdType = $this->getParam('aAdType');
            $aCityID = $this->getParam('aCityID');
            
            $aErr = array();
            if (! Util_Validate::isLength($aData['sAdName'], 2, 50)) {
                $aErr['sAdName'] = '请输入推广名称';
            }
            if ($aData['iPlanMinMoney'] > $aData['iPlanMaxMoney']) {
                $aErr['iPlanMoney'] = '后者数字必须大于前者数字';
            }
            /*
             * if (empty($aAdType)) { $aErr['aAdType'] = '请选择广告类型'; } if (empty($aCatID)) { $aErr['aCatID'] = '请选择媒体类别'; }
             */
            /*
             * if (empty($aCityID)) { $aErr['aCityID'] = '请选择城市/地区'; }
             */
            if (! empty($aCityID) && count($aCityID) > 3) {
                $aErr['aCityID'] = '最多选择3个城市/地区';
            }
            
            // 判断该自媒是否已经存在
            if (! empty($aData['sAdName'])) {
                $aOtherAd = Model_Ad::getAdByName($aData['iUserID'], $aData['sAdName'], $iAdID);
                if (! empty($aOtherAd) && $aOtherAd['iStatus'] != Model_Ad::STATUS_DELETE) {
                    $aErr['sAdName'] = '该推广计划已经存在！';
                }
            }
            
            if (! empty($aErr)) {
                return $this->showMsg($aErr, false);
            }
            
            /*
             * if (count($aAdType) == 2) { $aData['iAdType'] = 3; } else { $aData['iAdType'] = array_pop($aAdType); }
             */
            $aData['sCityID'] = empty($aCityID) ? '' : join(',', $aCityID);
            $aData['iStatus'] = Model_Ad::STATUS_WRITING;
            
            if (empty($aAd)) {
                $iAdID = Model_Ad::addData($aData);
            } else {
                $aData['iAdID'] = $aAd['iAdID'];
                Model_Ad::updData($aData);
            }
            
            Model_User::updData(array(
                'iUserID' => $this->aCurrUser['iUserID'],
                'iFirst' => 'iFirst + 1'
            ));
            
            if ($iAdID > 0) {
                return $this->showMsg($iAdID, true);
            } else {
                $aErr['sAdName'] = '添加失败，请稍后再试';
                return $this->showMsg($aErr, false);
            }
        } else {
            $iAdID = intval($this->getParam('id'));
            $aAd = null;
            if ($iAdID > 0) {
                $aAd = Model_Ad::getDetail($iAdID);
            }
            
            if (empty($aAd)) {
                $iType = intval($this->getParam('type', Model_Media::TYPE_WEIXIN));
            } else {
                $iType = $aAd['iMediaType'];
                $aAd['aCityID'] = explode(',', $aAd['sCityID']);
                $aAd['aCatID'] = explode(',', $aAd['sCatID']);
            }
            
            $aData = array();
            switch ($iType) {
                case Model_Media::TYPE_WEIXIN:
                    $aData['aCategory'] = Model_Domain::getOption(Model_Domain::TYPE_MEDIA_WEIXIN_CATEGORY);
                    // $aData['aTag'] = Model_Domain::getOption(Model_Domain::TYPE_MEDIA_WEIXIN_TAG);
                    break;
                case Model_Media::TYPE_FRIEND:
                    $aData['aCategory'] = Model_Domain::getOption(Model_Domain::TYPE_MEDIA_FRIEND_CATEGORY);
                    // $aData['aTag'] = Model_Domain::getOption(Model_Domain::TYPE_MEDIA_FRIEND_TAG);
                    break;
                case Model_Media::TYPE_WEIBO:
                    $aData['aCategory'] = Model_Domain::getOption(Model_Domain::TYPE_MEDIA_WEIBO_CATEGORY);
                    // $aData['aTag'] = Model_Domain::getOption(Model_Domain::TYPE_MEDIA_WEIBO_TAG);
                    break;
                case Model_Media::TYPE_NEWS:
                    $aData['aCategory'] = Model_Domain::getOption(Model_Domain::TYPE_MEDIA_NEWS_CATEGORY);
                    // $aData['aTag'] = Model_Domain::getOption(Model_Domain::TYPE_MEDIA_NEWS_TAG);
                    break;
            }
            $aData['aAdType'] = array(
                1 => '软广',
                2 => '硬广'
            );
            $aData['aCity'] = Model_City::getPairCitys(Model_City::TYPE_FRONT);
            
            $aType = Model_Media::$aType;
            unset($aType[Model_Media::TYPE_NEWS]);
            
            $this->assign('iType', $iType);
            $this->assign('aType', $aType);
            $this->assign('aData', $aData);
            $this->assign('aAd', $aAd);
            $this->assign('aUser', Model_User::getDetail($this->aCurrUser['iUserID']));

            $this->assign('sTopMenu', 'aadd');
            $this->setMeta('ad_add', array(
                'sTitle' => '添加推广计划'
            ));
        }
    }

    /**
     * 设置广告
     */
    public function add3Action ()
    {
        if ($this->isPost()) {
            $iAdID = (int) $this->getParam('iAdID', 0);
            $aAd = Model_Ad::getDetail($iAdID);
            if (empty($aAd) || $aAd['iUserID'] != $this->aCurrUser['iUserID']) {
                return $this->show404();
            }
            
            $aErr = array();
            $aData = array();
            $aData['iAdID'] = $iAdID;
            $aData['iAdPos'] = (int) $this->getParam('iAdPos');
            $aData['iPlanTime'] = strtotime($this->getParam('iPlanTime'));
            
            if ($aData['iPlanTime'] < strtotime('+2hour')) {
                $aErr['iPlanTime'] = '投放时间必须晚于当前时间2小时';
            }
            if ($aData['iPlanTime'] > strtotime('+7day')) {
                $aErr['iPlanTime'] = '投放时间必须小于7天';
            }
            
            switch ($aAd['iMediaType']) {
                case Model_Media::TYPE_WEIXIN:
                    $aData['sImportUrl'] = $this->getParam('sImportUrl', '');
                    $aData['sWordFile'] = $this->getParam('sWordFile', '');
                    $aData['sTitle'] = $this->getParam('sTitle');
                    $aData['sCoverImg'] = $this->getParam('sCoverImg');
                    $aData['iIsCover'] = (int)$this->getParam('iIsCover');
                    $aData['sAbstract'] = $this->getParam('sAbstract');
                    $aData['sContent'] = $this->getParam('sContent');
                    $aData['sOriginalUrl'] = $this->getParam('sOriginalUrl');
                    
                    if (! Util_Validate::isCLength($aData['sTitle'], 2, 50)) {
                        $aErr['sTitle'] = '请输入标题';
                    }
                    if ($aData['iIsCover'] && empty($aData['sCoverImg'])) {
                        $aErr['sCoverImg'] = '请上传封面图';
                    }
//                    if (empty($aData['sAbstract'])) {
//                        $aErr['sAbstract'] = '请输入摘要';
//                    }
                    if (! Util_Validate::isCLength($aData['sContent'], 20, 999999)) {
                        $aErr['sContent'] = '请输入内容至少20个字';
                    }
                    break;
                case Model_Media::TYPE_FRIEND:
                case Model_Media::TYPE_WEIBO:
                    $aData['sForwardUrl'] = $this->getParam('sForwardUrl', '');
                    $aData['sForwardText'] = $this->getParam('sForwardText', '');
                    $aForwardImg = $this->getParam('aForwardImg');
                    
                    if ($aData['iAdPos'] == 1) {
                        if (! Util_Validate::isAbsoluteUrl($aData['sForwardUrl'])) {
                            $aErr['sForwardUrl'] = '请输入正确的投放地址';
                        }
                    } else {
                        if (! empty($aData['sForwardUrl']) && ! Util_Validate::isAbsoluteUrl($aData['sForwardUrl'])) {
                            $aErr['sForwardUrl'] = '请输入正确的投放地址';
                        }
                    }
                    if (! Util_Validate::isCLength($aData['sForwardText'], 5, 200)) {
                        $aErr['sForwardText'] = '投放文字长度为5~200字之间';
                    }
                    
                    $aData['sForwardImg'] = array();
                    foreach ($aForwardImg as $sForwardImg) {
                        if (! empty($sForwardImg)) {
                            $aData['sForwardImg'][] = $sForwardImg;
                        }
                    }
                    if (empty($aData['sForwardImg'])) {
                        $aErr['sForwardImg'] = '请至少选择一张投放配图';
                        $aForwardImg = array();
                    }
                    $aData['sForwardImg'] = join(',', $aData['sForwardImg']);
                    break;
            }
            
            if (! empty($aErr)) {
                return $this->showMsg($aErr, false);
            }
            
            Model_Ad::setSetting($aAd, $aData);
            
            return $this->showMsg($aAd['iAdID'], true);
        } else {
            $iAdID = (int) $this->getParam('id', 0);
            $aAd = Model_Ad::getDetail($iAdID);
            if (empty($aAd) || $aAd['iUserID'] != $this->aCurrUser['iUserID']) {
                return $this->show404();
            }
            
            $sCookieKey = 'media_choose_' . $aAd['iMediaType'];
            $aChooseID = isset($_COOKIE[$sCookieKey]) ? explode(',', $_COOKIE[$sCookieKey]) : array();
            if (! empty($aChooseID)) {
                Model_Ad::updMedia($iAdID, $aChooseID, $this->aCurrUser['iUserID']);
            }
            setcookie($sCookieKey, null, 0, '/');
            
            $aSetting = Model_Ad::getSetting($aAd);
            if (! empty($aSetting) && isset($aSetting['sForwardImg'])) {
                $aSetting['aForwardImg'] = explode(',', $aSetting['sForwardImg']);
            }
            $this->assign('aTitle', Model_Media::$aPos[$aAd['iMediaType']]);
            $this->assign('aSetting', $aSetting);
            $this->assign('aAd', $aAd);
            
            $this->assign('sTopMenu', 'aadd');
            $this->setMeta('ad_add', array(
                'sTitle' => '添加推广计划 - 推广计划'
            ));
        }
    }

    /**
     * 导入内容
     */
    public function importAction ()
    {
        $sUrl = $this->getParam('url');
        if (empty($sUrl)) {
            return $this->showMsg('请输入正确的URL', false);
        }
        
        $sContent = file_get_contents($sUrl);
        if (empty($sContent)) {
            return $this->showMsg('内容抓取错误', false);
        }
        $sContent = str_replace(array(
            "\n",
            "\r"
        ), "", $sContent);
        
        $aData = array();
        if (preg_match('@<h2 class="rich_media_title">(.+?)</h2>@i', $sContent, $match)) {
            $aData['sTitle'] = trim($match[1]);
        } else {
            return $this->showMsg('没有匹配到标题', false);
        }
        if (preg_match('@var cover = "(http://[^"]+)"@i', $sContent, $match)) {
            $aData['sCover'] = $match[1];
        } else {
            // return $this->showMsg('没有匹配到封面图', false);
        }
        if (preg_match('@<div class="rich_media_content\s+" id="js_content">(.+?)</div>\s+<script@i', $sContent, $match)) {
            $aData['sContent'] = str_replace('data-src', 'src', trim($match[1]));
        } else {
            return $this->showMsg('没有匹配到内容', false);
        }
        
        return $this->showMsg($aData, true);
    }

    /**
     * 支付款项
     */
    public function add4Action ()
    {
        if ($this->isPost()) {
            $iAdID = $this->getParam('iAdID');
            $aChoose = $this->getParam('aChoose');
            $aAdPos = $this->getParam('aAdPos');
            $aAd = Model_Ad::getDetail($iAdID);
            if (empty($aAd) || $aAd['iUserID'] != $this->aCurrUser['iUserID']) {
                return $this->show404();
            }
            
            if (empty($aChoose)) {
                return $this->showMsg('请至少选择一个自媒体', false);
            }
            
            Model_AdMedia::updChoose($iAdID, $aChoose, $aAdPos);
            
            return $this->showMsg($iAdID, true);
        } else {
            $iAdID = intval($this->getParam('id'));
            $aAd = Model_Ad::getDetail($iAdID);
            if (empty($aAd) || $aAd['iUserID'] != $this->aCurrUser['iUserID']) {
                return $this->show404();
            }
            
            $aList = Model_AdMedia::getAll(array(
                'where' => array(
                    'iAdID' => $iAdID,
                    'iStatus !=' => 0
                )
            ));
            $iTotalMoney = $iTotalMedia = 0;
            foreach ($aList as $k => &$v) {
                $v['aMedia'] = Model_Media::getDetail($v['iMediaID']);
                if (empty($v['aMedia'])) {
                    unset($aList[$k]);
                }
                if ($v['iChoose']) {
                    $iTotalMoney += $v['iMoney'];
                    $iTotalMedia += 1;
                }
            }
            
            $this->assign('iTotalMoney', $iTotalMoney);
            $this->assign('iTotalMedia', $iTotalMedia);
            $this->assign('aList', $aList);
            $this->assign('aAd', $aAd);
            $this->assign('aStatus', Model_AdMedia::$aStatus);
            $this->assign('aPos', Model_Media::$aPos[$aAd['iMediaType']]);
            $this->assign('aType', Model_Media::$aType);
            $this->assign('sMediaType', Model_Media::$aType[$aAd['iMediaType']]);
            $this->assign('sTopMenu', 'aadd');
            
            $this->setMeta('ad_add', array(
                'sTitle' => '添加推广计划 - 支付款项'
            ));
        }
    }

    /**
     * 支付详情
     */
    public function wpayAction ()
    {
        $iAdID = intval($this->getParam('id'));
        $aAd = Model_Ad::getDetail($iAdID);
        if (empty($aAd) || $aAd['iUserID'] != $this->aCurrUser['iUserID']) {
            return $this->show404();
        }
        
        if ($aAd['iTotalMoney'] == 0) {
            return $this->show404('订单价格不能为0！');
        }
        
        $aUser = Model_User::getDetail($this->aCurrUser['iUserID']);
        
        $this->assign('aAd', $aAd);
        $this->assign('aUser', $aUser);
        $this->assign('sTopMenu', 'aadd');
        
        $this->setMeta('ad_add', array(
            'sTitle' => '添加推广计划 - 支付详情'
        ));
    }

    /**
     * 删除推广
     */
    public function delAction ()
    {
        $iAdID = intval($this->getParam('id'));
        $aAd = Model_Ad::getDetail($iAdID);
        
        if (empty($aAd) || $aAd['iUserID'] != $this->aCurrUser['iUserID']) {
            return $this->showMsg('数据异常', false);
        }
        
        if ($aAd['iPayStatus'] == 1) {
            return $this->showMsg('该推广计划已经支付，不允许删除', false);
        }
        
        Model_Ad::delData($iAdID);
        
        return $this->showMsg('删除成功', true);
    }
}
