<?php

/**
 * 提供自媒体的添加、媒体中心功能
 * Created by PhpStorm.
 * User: xiejinci
 * Date: 14/12/24
 * Time: 下午2:32
 */
class Controller_Index_Media extends Controller_Index_Base
{

    /**
     * 自媒体中心
     */
    public function indexAction ()
    {
        $aParam = $this->getParams();
        $aParam['type'] = isset($aParam['type']) ? intval($aParam['type']) : Model_Media::TYPE_WEIXIN;
        $iAdID = (int) $this->getParam('id');
        $aAd = null;
        if ($iAdID > 0) {
            $aAd = Model_Ad::getDetail($iAdID);
        }
        if (! empty($aAd)) {
            $aParam['type'] = $aAd['iMediaType'];
            $sReferer = $this->getRequest()->getHttpReferer();
            if (strpos($sReferer, '/ad/add') > 0) {
                if (! empty($aAd['sCatID'])) {
                    $aParam['catid'] = intval($aAd['sCatID']);
                }
                if (! empty($aAd['sCityID'])) {
                    $aParam['city'] = explode(',', $aAd['sCityID']);
                }
            }
        }
        
        $aData = Model_Media::search($aParam);
        foreach ($aData['aList'] as &$aRow) {
            $aRow['sCatName'] = Model_Media::getCategoryNames($aRow['iMediaID']);
        }
        
        switch ($aParam['type']) {
            case Model_Media::TYPE_WEIXIN:
                $aData['aType'] = Model_Domain::getOption(Model_Domain::TYPE_MEDIA_WEIXIN_CATEGORY);
                $aData['aTag'] = Model_Domain::getOption(Model_Domain::TYPE_MEDIA_WEIXIN_TAG);
                break;
            case Model_Media::TYPE_FRIEND:
                $aData['aType'] = Model_Domain::getOption(Model_Domain::TYPE_MEDIA_FRIEND_CATEGORY);
                $aData['aTag'] = Model_Domain::getOption(Model_Domain::TYPE_MEDIA_FRIEND_TAG);
                break;
            case Model_Media::TYPE_WEIBO:
                $aData['aType'] = Model_Domain::getOption(Model_Domain::TYPE_MEDIA_WEIBO_CATEGORY);
                $aData['aTag'] = Model_Domain::getOption(Model_Domain::TYPE_MEDIA_WEIBO_TAG);
                break;
            case Model_Media::TYPE_NEWS:
                $aData['aType'] = Model_Domain::getOption(Model_Domain::TYPE_MEDIA_NEWS_CATEGORY);
                $aData['aTag'] = Model_Domain::getOption(Model_Domain::TYPE_MEDIA_NEWS_TAG);
                break;
        }
        $aData['aPrice'] = Model_Price::getOption();
        $aData['aFollower'] = Model_Follower::getOption();
        $aData['aCity'] = Model_City::getPairCitys(Model_City::TYPE_FRONT);
        $aData['aLevel'] = Model_Domain::getOption(Model_Domain::TYPE_MEDIA_RECOMMEND);
        $aData['aAd'] = $aAd;
        
        if (! empty($aData['aAd'])) {
            $aData['aChooseID'] = Model_AdMedia::getCol(array(
                'where' => array(
                    'iStatus >' => 0,
                    'iAdID' => $iAdID
                )
            ), 'iMediaID');
        }
        if (empty($aData['aChooseID'])) {
            $sCookieKey = 'media_choose_' . $aParam['type'];
            $aData['aChooseID'] = ! empty($_COOKIE[$sCookieKey]) ? explode(',', $_COOKIE[$sCookieKey]) : array();
        }
        $aData['aChoose'] = array();
        foreach ($aData['aChooseID'] as $k => $v) {
            $aMedia = Model_Media::getDetail($v);
            if (empty($aMedia)) {
                continue;
            }
            $aData['aChoose'][] = $aMedia;
        }
        
        $this->assign('aData', $aData);
        $this->assign('aParam', $aParam);
        $this->assign('sTopMenu', empty($aAd) ? 'media' : 'aadd');
        
        $this->setMeta('media_center', array(
            'sTitle' => '媒体中心'
        ));
    }

    /**
     * 进行认证
     */
    public function verifyAction ()
    {
        if ($this->isPost()) {
            $iMediaID = (int) $this->getParam('id');
            $aMedia = Model_Media::getDetail($iMediaID);
            if (empty($aMedia)) {
                return $this->show404('微信公众号未找到！');
            }
            
            $sVerifyUrl = $this->getParam('url');
            if (strpos($sVerifyUrl, 'http://mp.weixin.qq.com/') !== 0) {
                return $this->showMsg('图文消息地址错误', false);
            }
            $content = file_get_contents($sVerifyUrl);
            if (strpos($content, 'id="post-user">' . $aMedia['sMediaName'] . '</a>') === FALSE) {
                return $this->showMsg('公众帐号不匹配', false);
            }
            if (strpos($content, $aMedia['sVerifyCode']) === FALSE) {
                return $this->showMsg('验证码没有匹配到', false);
            }
            
            $aData = array(
                'iMediaID' => $aMedia['iMediaID'],
                'iVerifyState' => 1
            );
            Model_Media::updData($aData);
            
            return $this->showMsg('恭喜你，验证成功！', true);
        }
    }

    /**
     * 自媒体上下架
     */
    public function iputAction()
    {
        $iMediaID = (int) $this->getParam('id');
        $iput = (int) $this->getParam('iput');
        $aMedia = Model_Media::getDetail($iMediaID);
       if($iput==2) {
        $aData = array(
            'iMediaID' => $aMedia['iMediaID'],
            'iPut' => 1
        );
       }else{
           $aData = array(
               'iMediaID' => $aMedia['iMediaID'],
               'iPut' => 2
           );
       }
        $ask=$iput==1?'下架成功':'上架成功';
        if(Model_Media::updData($aData)){
            ?>
            <script>
                alert('修改成功');
                location.href='http://www.51wom.com/mcenter/account/type/1.html';
            </script>
            <?php
        } else{
            ?>
            <script>
                alert('修改失败');
                location.href='http://www.51wom.com/mcenter/account/type/1.html';
            </script>
<?php
        }



    }



    /**
     * 自媒体详情
     */
    public function detailAction ()
    {
        $iMediaID = max(1, intval($this->getParam('id')));
        $iPage = intval($this->getParam('page'));
        $aMedia = Model_Media::getDetail($iMediaID);
        if (empty($aMedia)) {
            return $this->show404('微信公众号未找到！');
        }
        $aMedia['sTags'] = Model_Media::getTagNames($aMedia['iMediaID']);
        
        $aWeixin = Model_CrawlWeixin::getWeixinByAccount($aMedia['sOpenName']);
        if (empty($aWeixin)) {
            return $this->show404('微信公众号信息未匹配到！');
        }
        
        $bTop10 = (bool) $this->getParam('top10');
        $aWhere = array(
            'iWeixinID' => $aWeixin['iWeixinID'],
            'iStatus' => 1
        );
        if ($bTop10) {
            $aData = Model_CrawlWeixinArticle::getList($aWhere, 1, 'iReadNum DESC', 10);
            $aData['aPager'] = null;
        } else {
            $iPage = max(1, $this->getParam('page'));
            $aData = Model_CrawlWeixinArticle::getList($aWhere, $iPage, 'iPublishTime DESC', 10);
        }
        
        $this->assign('bTop10', $bTop10);
        $this->assign('aMedia', $aMedia);
        $this->assign('aWeixin', $aWeixin);
        $this->assign('aData', $aData);
        $this->assign('sTopMenu', 'media');
        
        $this->setMeta('media_detail', array(
            'sTitle' => '媒体详情'
        ));
    }

    /**
     * 检测是否存在
     */
    public function checkAction ()
    {
        $iMediaType = (int) $this->getParam('iMediaType');
        $sField = $this->getParam('field');
        $sValue = $this->getParam('value');
        
        $aErr['sOpenName'] = '该自媒体名已经存在！';
        $aErr['sMediaName'] = '该自媒体账号已经存在！';
        if (! isset($aErr[$sField])) {
            $this->showMsg('操作错误!', false);
        }
        
        $iCnt = Model_Media::getCnt(array(
            $sField => $sValue,
            'iMediaType' => $iMediaType,
            'iStatus !=' => 0
        ));
        
        if ($iCnt > 0) {
            return $this->showMsg($aErr[$sField], false);
        } else {
            return $this->showMsg('OK', true);
        }
    }

    /**
     * 添加自媒体
     */
    public function addAction ()
    {
        if ($this->isPost()) {
            $aUser = $this->getCurrUser(Model_User::TYPE_MEDIA);
            if (empty($aUser)) {
                return $this->showMsg('请先登录！', false);
            }
            
            $iMediaID = (int) $this->getParam('iMediaID');
            $aMedia = null;
            if ($iMediaID > 0) {
                $aMedia = Model_Media::getDetail($iMediaID);
                if ($aMedia['iUserID'] != $aUser['iUserID']) {
                    return $this->showMsg('不要乱改别人的数据', false);
                }
            }
            
            $aData = array();
            $aData['iUserID'] = $aUser['iUserID'];
            $aData['iMediaType'] = (int) $this->getParam('iMediaType');
            $aData['sMediaName'] = $this->getParam('sMediaName');
            $aData['sOpenName'] = $this->getParam('sOpenName');
            $aData['iFollowerNum'] = (int) $this->getParam('iFollowerNum');
            $aData['sFollowerImg'] = $this->getParam('sFollowerImg');
            $aData['sAvatar'] = $this->getParam('sAvatar');
            $aData['sQRCode'] = $this->getParam('sQRCode');
            
            $aErr = array();
            switch ($aData['iMediaType']) {
                case Model_Media::TYPE_WEIXIN:
                    if ($aData['iFollowerNum'] == 0) {
                        $aErr['iFollowerNum'] = '请输入丝粉数量';
                    }
                    if (! Util_Validate::isLength($aData['sMediaName'], 2, 50)) {
                        $aErr['sMediaName'] = '请输入微信名称';
                    }
                    if (! preg_match('/[a-z][0-9a-z_\-]{5,19}/i', $aData['sOpenName'])) {
                        $aErr['sOpenName'] = '请输入正确的微信帐号';
                    }
                    if (empty($aData['sFollowerImg'])) {
                        $aErr['sFollowerImg'] = '请上传粉丝数截图';
                    }
                    if (empty($aData['sAvatar'])) {
                        $aErr['sAvatar'] = '请上传微信头像';
                    }
                    if (empty($aData['sQRCode'])) {
                        $aErr['sQRCode'] = '请上传二维码';
                    }
                    
                    $aOtherMedia = Model_Media::getMediaByOpenName($aData['iMediaType'], $aData['sOpenName'], $iMediaID);
                    if (! empty($aOtherMedia)) {
                        $aErr['sOpenName'] = '该自媒体已经存在！';
                    }
                    break;
                case Model_Media::TYPE_FRIEND:
                    if (empty($aData['sMediaName'])) {
                        $aErr['sMediaName'] = '请输入账号名称';
                    }
                    if ($aData['iFollowerNum'] == 0) {
                        $aErr['iFollowerNum'] = '请输入好友数量';
                    }
                    if (empty($aData['sFollowerImg'])) {
                        $aErr['sFollowerImg'] = '请上传好友数截图';
                    }
                    if (empty($aData['sAvatar'])) {
                        $aErr['sAvatar'] = '请上传你微信头像';
                    }
                    // 判断该自媒是否已经存在
                    $aOtherMedia = Model_Media::getMediaByName($aData['iMediaType'], $aData['sMediaName'], $iMediaID);
                    if (! empty($aOtherMedia)) {
                        $aErr['sMediaName'] = '该微信帐号已经存在！';
                    }
                    break;
                case Model_Media::TYPE_WEIBO:
                    if (empty($aData['sMediaName'])) {
                        $aErr['sMediaName'] = '请输入账号名称';
                    }
                    if ($aData['iFollowerNum'] == 0) {
                        $aErr['iFollowerNum'] = '请输入好友数量';
                    }
                    if (empty($aData['sFollowerImg'])) {
                        $aErr['sFollowerImg'] = '请上传好友数截图';
                    }
                    if (empty($aData['sAvatar'])) {
                        $aErr['sAvatar'] = '请上传你微博头像';
                    }
                    $aData['sUrl'] = $this->getParam('sUrl');
                    if (! Util_Validate::isAbsoluteUrl($aData['sUrl'])) {
                        $aErr['sUrl'] = '请输入正确的微博地址';
                    }
                    // 判断该自媒是否已经存在
                    $aOtherMedia = Model_Media::getMediaByName($aData['iMediaType'], $aData['sMediaName'], $iMediaID);
                    if (! empty($aOtherMedia)) {
                        $aErr['sMediaName'] = '该微博帐号已经存在！';
                    }
                    break;
            }
            
            $aCatID = $this->getParam('aCatID');
            $aCityID = $this->getParam('aCityID');
            $aTagID = $this->getParam('aTagID');
            
//             if (empty($aCatID)) {
//                 $aErr['aCatID'] = '请选择媒体类别';
//             }
//             if (! empty($aCatID) && count($aCatID) > 3) {
//                 $aErr['aCatID'] = '最多选择3个媒体类别';
//             }
            if (empty($aTagID)) {
                $aErr['aTagID'] = '请选择媒体标签';
            }
            if (! empty($aTagID) && count($aTagID) > 6) {
                $aErr['aTagID'] = '最多选择6个媒体标签';
            }
            if (empty($aCityID)) {
                $aErr['aCityID'] = '请选择城市/地区';
            }
            if (! empty($aCityID) && count($aCityID) > 3) {
                $aErr['aCityID'] = '最多选择3个城市/地区';
            }
            
            if (! empty($aErr)) {
                return $this->showMsg($aErr, false);
            }
            
            if (empty($aMedia)) {
                $aData['iStatus'] = 3;
                $aData['sVerifyCode'] = strtoupper(Util_Tools::passwdGen(32));
                $iMediaID = Model_Media::addData($aData);
            } else {
                $aData['iStatus'] = 2;
                $aData['iMediaID'] = $aMedia['iMediaID'];
                Model_Media::updData($aData);
            }
            
            Model_User::updData(array(
                'iUserID' => $this->aCurrUser['iUserID'],
                'iFirst' => 'iFirst + 1'
            ));
            
            if ($iMediaID > 0) {
                //Model_Media::updCategory($iMediaID, $aCatID);
                Model_Media::updCity($iMediaID, $aCityID);
                Model_Media::updTag($iMediaID, $aTagID);
                
                return $this->showMsg($iMediaID, true);
            } else {
                $aErr['sMediaName'] = '添加失败，请稍后再试';
                return $this->showMsg($aErr, false);
            }
        } else {
            $aUser = $this->getCurrUser(Model_User::TYPE_MEDIA);
            if (empty($aUser)) {
                return $this->redirect('/user/login/type/' . Model_User::TYPE_MEDIA . '.html?ret=' . Util_Uri::getCurrUrl());
            }
            
            $iMediaID = intval($this->getParam('id'));
            $aMedia = null;
            if ($iMediaID > 0) {
                $aMedia = Model_Media::getFullDetail($iMediaID);
            }
            
            if (empty($aMedia)) {
                $iType = intval($this->getParam('type', Model_Media::TYPE_WEIXIN));
            } else {
                $iType = $aMedia['iMediaType'];
            }
            
            $aData = array();
            switch ($iType) {
                case Model_Media::TYPE_WEIXIN:
                    $aData['aCategory'] = Model_Domain::getOption(Model_Domain::TYPE_MEDIA_WEIXIN_CATEGORY);
                    $aData['aTag'] = Model_Domain::getOption(Model_Domain::TYPE_MEDIA_WEIXIN_TAG);
                    break;
                case Model_Media::TYPE_FRIEND:
                    $aData['aCategory'] = Model_Domain::getOption(Model_Domain::TYPE_MEDIA_FRIEND_CATEGORY);
                    $aData['aTag'] = Model_Domain::getOption(Model_Domain::TYPE_MEDIA_FRIEND_TAG);
                    break;
                case Model_Media::TYPE_WEIBO:
                    $aData['aCategory'] = Model_Domain::getOption(Model_Domain::TYPE_MEDIA_WEIBO_CATEGORY);
                    $aData['aTag'] = Model_Domain::getOption(Model_Domain::TYPE_MEDIA_WEIBO_TAG);
                    break;
                case Model_Media::TYPE_NEWS:
                    $aData['aCategory'] = Model_Domain::getOption(Model_Domain::TYPE_MEDIA_NEWS_CATEGORY);
                    $aData['aTag'] = Model_Domain::getOption(Model_Domain::TYPE_MEDIA_NEWS_TAG);
                    break;
            }
            $aData['aCity'] = Model_City::getPairCitys(Model_City::TYPE_FRONT);
            // array_unshift($aData['aCategory'], '不限');
            // array_unshift($aData['aCity'], '不限');
            // array_unshift($aData['aTag'], '不限');
            
            $aType = Model_Media::$aType;
            unset($aType[Model_Media::TYPE_NEWS]);
            $this->assign('iType', $iType);
            $this->assign('aType', $aType);
            $this->assign('aData', $aData);
            $this->assign('aMedia', $aMedia);
            $this->assign('aUser', Model_User::getDetail($this->aCurrUser['iUserID']));
            $this->assign('sTopMenu', 'madd');
            
            $sRandCode = Util_Tools::passwdGen(8, Util_Tools::FLAG_ALPHANUMERIC);
            Util_Cookie::set('media_verify_code', $sRandCode);
            $this->assign('sRandCode', $sRandCode);
            
            $this->setMeta('media_add', array(
                'sTitle' => '添加媒体'
            ));
        }
    }

    /**
     * 填写报价
     */
    public function add2Action ()
    {
        if ($this->isPost()) {
            $aUser = $this->getCurrUser(Model_User::TYPE_MEDIA);
            if (empty($aUser)) {
                return $this->showMsg('请先登录！', false);
            }
            
            $iMediaID = (int) $this->getParam('iMediaID');
            $aMedia = Model_Media::getDetail($iMediaID);
            if (empty($aMedia)) {
                return $this->showMsg('数据导常', false);
            }
            if ($aMedia['iUserID'] != $aUser['iUserID']) {
                return $this->showMsg('不要乱改别人的数据', false);
            }
            
            $aData = array(
                'iMediaID' => $iMediaID,
                'iPrice1' => intval($this->getParam('iPrice1', 0)),
                'iPrice2' => intval($this->getParam('iPrice2', 0)),
                'iPrice3' => intval($this->getParam('iPrice3', 0)),
                'iPrice4' => intval($this->getParam('iPrice4', 0)),
                'iPrice5' => intval($this->getParam('iPrice1', 0)),
                'iPrice6' => intval($this->getParam('iPrice2', 0)),
                'iPrice7' => intval($this->getParam('iPrice3', 0)),
                'iPrice8' => intval($this->getParam('iPrice4', 0))
            );
            
            $aErr = array();
            if (! Util_Validate::isUnsignedInt($aData['iPrice1'])) {
                $aErr['iPrice1'] = '请输入正确的报价';
            }
            if (! Util_Validate::isUnsignedInt($aData['iPrice2'])) {
                $aErr['iPrice2'] = '请输入正确的报价';
            }
            if (! Util_Validate::isUnsignedInt($aData['iPrice3'])) {
                $aErr['iPrice3'] = '请输入正确的报价';
            }
            if (! Util_Validate::isUnsignedInt($aData['iPrice4'])) {
                $aErr['iPrice4'] = '请输入正确的报价';
            }
            if ($aData['iPrice1'] + $aData['iPrice2'] + $aData['iPrice3'] + $aData['iPrice4'] == 0) {
                $aErr['iPrice1'] = '请至少输入一个报价';
            }
            if (! empty($aErr)) {
                return $this->showMsg($aErr, false);
            }

            $aData['iStatus'] = 2;
            Model_Media::updData($aData);
            
            return $this->showMsg($iMediaID, true);
        } else {
            $aUser = $this->getCurrUser(Model_User::TYPE_MEDIA);
            if (empty($aUser)) {
                return $this->redirect('/user/login/type/' . Model_User::TYPE_MEDIA . '?ret=' . Util_Uri::getCurrUrl());
            }
            
            $iMediaID = (int) $this->getParam('id');
            if (empty($iMediaID)) {
                return $this->show404();
            }
            
            $aMedia = Model_Media::getDetail($iMediaID);
            if (empty($aMedia) || $aMedia['iUserID'] != $aUser['iUserID']) {
                return $this->show404();
            }
            
            $aTitle = array(
                Model_Media::TYPE_WEIXIN => array(
                    'iPrice1' => '单图文报价',
                    'iPrice2' => '多图文第一条报价',
                    'iPrice3' => '多图文第二条报价',
                    'iPrice4' => '多图文第三条报价'
                ),
                Model_Media::TYPE_FRIEND => array(
                    'iPrice1' => '转发报价',
                    'iPrice2' => '直发报价'
                ),
                Model_Media::TYPE_WEIBO => array(
                    'iPrice1' => '转发报价',
                    'iPrice2' => '直发报价'
                )
            );
            
            $this->assign('aTitle', $aTitle);
            $this->assign('aMedia', $aMedia);
            $this->assign('sTopMenu', 'madd');
            
            $this->setMeta('media_add', array(
                'sTitle' => '添加媒体 - 填写报价'
            ));
        }
    }

    /**
     * 发布成功
     */
    public function addokAction ()
    {
        $iMediaID = (int) $this->getParam('id');
        $aMedia = Model_Media::getDetail($iMediaID);

        if (empty($iMediaID)) {
            return $this->show404();
        }
        

        if (empty($aMedia)) {
            return $this->show404($aMedia['']);
        }

        $aUser=Model_User::getDetail($aMedia['iUserID']) ;
        $title=Model_Kv::getValue('media_go_email_title');
        $content = Model_Kv::getValue('media_go_email_content');
        $eamil1='key@51wom.com';
        $eamil2='david@51wom.com';
        Util_Mail::send($eamil1, $title, $content, array($aUser['sEmail'],$aMedia['sMediaName']));
        Util_Mail::send($eamil2,$title,$content,array($aUser['sEmail'],$aMedia['sMediaName']));

        $aType = Model_Media::$aType;
        $this->assign('sType', $aType[$aMedia['iMediaType']]);
        $this->assign('aMedia', $aMedia);
        $this->assign('sTopMenu', 'madd');

        $this->setMeta('media_add', array(
            'sTitle' => '添加媒体 - 发布成功'
        ));

    }

    /**
     * 导出自媒体
     */
    public function exportAction ()
    {
        $iType = $this->getParam('type', 1);
        $sCookieKey = 'media_choose_' . $iType;
        $aData['aChooseID'] = ! empty($_COOKIE[$sCookieKey]) ? explode(',', $_COOKIE[$sCookieKey]) : array();
        $aData['aChoose'] = array();
        foreach ($aData['aChooseID'] as $k => $v) {
            $aRow = Model_Media::getDetail($v);
            $aRow['sTags'] = Model_Media::getTagNames($v['iMediaID']);
            $aRow['sCitys'] = Model_Media::getCityNames($v['iMediaID']);
            $aData['aChoose'][] = $aRow;
        }
        
        $aTitle = array(
            Model_Media::TYPE_WEIXIN => array(
                'sMediaName' => '帐号名称',
                'sOpenName' => '公众号',
                'iFollowerNum' => '粉丝量',
                'iPrice1' => '单图文价格',
                'iPrice2' => '第一条价格',
                'iPrice3' => '第二条价格',
                'iPrice4' => '其它位置价格',
                'iReadNum' => '阅读数',
                'sIntroduction' => '简介',
                'sTags' => '标签',
                'sCertifiedText' => '认证',
                'sCitys' => '地区'
            ),
            Model_Media::TYPE_FRIEND => array(
                'sMediaName' => '微信号',
                'iFollowerNum' => '好友数',
                'iPrice1' => '直发价格',
                'iPrice2' => '转发价格'
            ),
            Model_Media::TYPE_WEIBO => array(
                'sMediaName' => '微博名',
                'iFollowerNum' => '好友数',
                'iPrice1' => '直发价格',
                'iPrice2' => '转发价格'
            )
        );
        
        Util_File::exportCsv('自媒体表-' . date('Ymd') . '.csv', $aData['aChoose'], $aTitle[$iType]);
        
        return false;
    }
}