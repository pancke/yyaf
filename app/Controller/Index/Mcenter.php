<?php

/**
 * 自媒体 - 个人中心的各种操作
 * Created by PhpStorm.
 * User: xiejinci
 * Date: 14/12/24
 * Time: 下午2:32
 */
class Controller_Index_Mcenter extends Controller_Index_Base
{

    /**
     * 当前用户
     *
     * @var unknown
     */
    protected $aCurrUser;

    /**
     * 执行Action前执行
     *
     * @see Yaf_Controller::actionBefore()
     */
    public function actionBefore ()
    {
        parent::actionBefore();
        
        // 判断广告主是否已登录
        $this->aCurrUser = $this->getCurrUser(Model_User::TYPE_MEDIA);
        if (empty($this->aCurrUser)) {
            return $this->redirect('/user/login/type/' . Model_User::TYPE_MEDIA . '.html?ret=' . Util_Uri::getCurrUrl());
        }
        
        $this->assign('isMCenter', 1);
    }

    /**
     * 订单中心
     */
    public function indexAction ()
    {
        $iStatus = max(2, intval($this->getParam('type')));
        $iPage = max(1, intval($this->getParam('page')));
        
        $aData = Model_AdMedia::getList(array(
            'iMUserID' => $this->aCurrUser['iUserID'],
            'iChoose' => 1,
            'iStatus' => $iStatus
        ), $iPage, 'iUpdateTime DESC');
        
        foreach ($aData['aList'] as $k => &$aRow) {
            $aRow['aAd'] = Model_Ad::getDetail($aRow['iAdID']);
            $aRow['aMedia'] = Model_Media::getDetail($aRow['iMediaID']);
        }
        
        $aUser = Model_User::getDetail($this->aCurrUser['iUserID']);
        $this->assign('aUser', $aUser);
        $this->assign('aData', $aData);
        $this->assign('aType', Model_Media::$aType);
        $this->assign('aOrderStatus', Model_Ad::$aOrderStatus);
        $this->assign('iType', $iStatus);
		
        $this->assign('aStatus', Model_AdMedia::$aStatus);
        $this->setMeta('mcenter_page', array(
            'sTitle' => '自媒体中心 - 订单中心'
        ));
    }

    /**
     * 订单详情
     */
   /* public function daxqAction ()
    {
        $iStatus = max(2, intval($this->getParam('type')));
        $iPage = max(1, intval($this->getParam('page')));
        $iMediaID = (int) $this->getParam('id');
        $aData = Model_AdMedia::getList(array(
            'iMediaID' => $iMediaID
        ), $iPage);


        $this->assign('aData', $aData);
        $this->setMeta('mcenter_page', array(
            'sTitle' => '自媒体中心 -订单详情'
        ));
    }*/

    /**
     * 是否接单
     */
    public function doneAction ()
    {
        $iStatus = intval($this->getParam('status'));
        $iAutoID = intval($this->getParam('id'));
        
        $aAdMedia = Model_AdMedia::getDetail($iAutoID);
        if (empty($aAdMedia)) {
            return $this->showMsg('数据出错了', false);
        }
        
        if ($aAdMedia['iStatus'] != Model_AdMedia::STATUS_RECEIVE) {
            return $this->showMsg('你已经处理过了', false);
        }
        
        $aAd = Model_Ad::getDetail($aAdMedia['iAdID']);
        if ($aAd['iStatus'] != Model_Ad::STATUS_APPROVE_OK) {
            return $this->showMsg('该推广还没有审核通过', false);
        }
        
        $iStatus = $iStatus == 1 ? Model_AdMedia::STATUS_SUBMIT_PREVIEW : Model_AdMedia::STATUS_CANCEL;
        
        Model_AdMedia::begin();
        Model_AdMedia::updStatus($iAutoID, $iStatus);
        if (Model_AdMedia::STATUS_CANCEL == $iStatus) {
            Model_Finance::updMoney($aAdMedia['iAUserID'], array(
                'iPayment' => Model_Finance::PAYMENT_IN,
                'iSource' => Model_Finance::SOURCE_REFUSE_AD,
                'iPayType' => Model_Finance::TYPE_NO,
                'iMoney' => $aAdMedia['iMoney']
            ));
        }
        Model_AdMedia::commit();
        
        if (Model_AdMedia::STATUS_SUBMIT_PREVIEW == $iStatus) {
            // 邮件通知
            $sTitle = Model_Kv::getValue('media_receive_order_ok_email_title');
            $sContent = Model_Kv::getValue('media_receive_order_ok_email_content');
            
            // 短信通知
            $iTempID = Util_Common::getConf(4, 'aSmsTempID');
            $aUser = Model_User::getDetail($aAdMedia['iAUserID']);
            $aMedia = Model_Media::getDetail($aAdMedia['iMediaID']);
            Util_Tools::sendMail($aUser['sEmail'], $sTitle, $sContent, array(
                $aMedia['sMediaName']
            ));
            Util_Sms::sendTemplateSms($aUser['sMobile'], array(
                $aMedia['sMediaName']
            ), $iTempID);
            // echo $sTitle, "\n", $sContent, "\n", $aUser['sEmail'], $aUser['sMobile'], $iTempID;
        } else {}
        
        return $this->showMsg('操作成功', true);
    }


    /**
     * 进行投放
     */
    public function onlineAction ()
    {
        $iAutoID = intval($this->getParam('id'));
        
        $aAdMedia = Model_AdMedia::getDetail($iAutoID);
        if (empty($aAdMedia)) {
            return $this->show404('订单不存在!');
        }
        
        if ($aAdMedia['iStatus'] != Model_AdMedia::STATUS_PUSH) {
            return $this->show404('此订单您已经处理过了');
        }

        if ($this->isPost()) {
            $sOnlineUrl = $this->getParam('sOnlineUrl');
            $aErr = array();
            if (! Util_Validate::isAbsoluteUrl($sOnlineUrl)) {
                $aErr['sOnlineUrl'] = '请输入正确的推广地址';
            }
            
            if (! empty($aErr)) {
                return $this->showMsg($aErr, false);
            }
            
            
            $aAd = Model_Ad::getDetail($aAdMedia['iAdID']);
            Model_AdMedia::updData(array(
                'iAutoID' => $iAutoID,
                'sOnlineUrl' => $sOnlineUrl,
                'iStatus' => Model_AdMedia::STATUS_SUBMIT_EFFECT
            ));
            
            // 邮件通知
            $sTitle = Model_Kv::getValue('media_runing_email_title');
            $sContent = Model_Kv::getValue('media_runing_email_content');
            
            // 短信通知
            $iTempID = Util_Common::getConf(10, 'aSmsTempID');
            $aUser = Model_User::getDetail($aAdMedia['iAUserID']);
            $aMedia = Model_Media::getDetail($aAdMedia['iMediaID']);
            Util_Tools::sendMail($aUser['sEmail'], $sTitle, $sContent, array(
                $aMedia['sMediaName']
            ));
            Util_Sms::sendTemplateSms($aUser['sMobile'], array(
                $aMedia['sMediaName']
            ), $iTempID);
            // echo $sTitle, "\n", $sContent, "\n", $aUser['sEmail'], $aUser['sMobile'], $iTempID;
            return $this->showMsg('操作成功', true);
        } else {            
            $this->assign('aAdMedia', $aAdMedia);
            $this->setMeta('mcenter_page', array(
                'sTitle' => '自媒体中心 - 提交上线地址'
            ));
        }
    }
    /**
     * 自媒体审核失败
     */
    public function istatusAction()
    {
        $iMediaID = (int) $this->getParam('id');
        $iPage = max(1, intval($this->getParam('page')));
        $aData = Model_Media::getList(array(
            'iMediaID' => $iMediaID
        ),$iPage);
        $this->assign('aData', $aData);
        $this->assign('iMediaID', $iMediaID);

        $this->setMeta('mcenter_page', array(
            'sTitle' => '审核失败'
        ));
    }
    /**
     * 提交预览
     */
    public function previewAction ()
    {
        $iAutoID = (int) $this->getParam('id');
        $aAdMedia = Model_AdMedia::getDetail($iAutoID);
        if (empty($aAdMedia)) {
            return $this->show404();
        }
        if ($aAdMedia['iStatus'] != Model_AdMedia::STATUS_SUBMIT_PREVIEW) {
            return $this->show404();
        }
        
        if ($this->isPost()) {
            $sPreviewUrl = $this->getParam('sPreviewUrl');
            $aErr = array();
            if (! Util_Validate::isAbsoluteUrl($sPreviewUrl)) {
                $aErr['sPreviewUrl'] = '请输入正确的推广地址';
            }
            
            if (! empty($aErr)) {
                return $this->showMsg($aErr, false);
            }
            
            Model_AdMedia::updData(array(
                'iAutoID' => $iAutoID,
                'sPreviewUrl' => $sPreviewUrl,
                'iStatus' => Model_AdMedia::STATUS_CONFIRM_PREVIEW
            ));
            
            // 邮件通知
            $sTitle = Model_Kv::getValue('media_submit_preview_email_title');
            $sContent = Model_Kv::getValue('media_sumit_preview_email_content');
            
            // 短信通知
            $iTempID = Util_Common::getConf(5, 'aSmsTempID');
            $aUser = Model_User::getDetail($aAdMedia['iAUserID']);
            $aMedia = Model_Media::getDetail($aAdMedia['iMediaID']);
            Util_Tools::sendMail($aUser['sEmail'], $sTitle, $sContent, array(
                $aMedia['sMediaName']
            ));
            Util_Sms::sendTemplateSms($aUser['sMobile'], array(
                $aMedia['sMediaName']
            ), $iTempID);
            
            return $this->showMsg('预览地址提交成功', true);
        } else {
            $this->assign('aAdMedia', $aAdMedia);
            $this->setMeta('mcenter_page', array(
                'sTitle' => '自媒体中心 - 提交预览'
            ));
        }
    }

    /**
     * 提交效果
     * 
     * @return boolean
     */
    public function effectAction ()
    {
        $iAutoID = (int) $this->getParam('id');
        $aAdMedia = Model_AdMedia::getDetail($iAutoID);
        if (empty($aAdMedia)) {
            return $this->show404();
        }
        if ($aAdMedia['iStatus'] != Model_AdMedia::STATUS_SUBMIT_EFFECT) {
            return $this->show404();
        }
        
        if ($this->isPost()) {
            $sEffectImg = $this->getParam('sEffectImg');
            $aErr = array();
            if (empty($sEffectImg)) {
                $aErr['sEffectImg'] = '请上传效果图';
            }
            
            if (! empty($aErr)) {
                return $this->showMsg($aErr, false);
            }
            
            Model_AdMedia::updData(array(
                'iAutoID' => $iAutoID,
                'sEffectImg' => $sEffectImg,
                'iStatus' => Model_AdMedia::STATUS_CONFIRM_EFFECT
            ));
            
            // 邮件通知
            $sTitle = Model_Kv::getValue('media_runing_email_title');
            $sContent = Model_Kv::getValue('media_runing_email_content');
            
            // 短信通知
            $iTempID = Util_Common::getConf(7, 'aSmsTempID');
            $aUser = Model_User::getDetail($aAdMedia['iAUserID']);
            $aMedia = Model_Media::getDetail($aAdMedia['iMediaID']);
            Util_Tools::sendMail($aUser['sEmail'], $sTitle, $sContent, array(
                $aMedia['sMediaName']
            ));
            Util_Sms::sendTemplateSms($aUser['sMobile'], array(
                $aMedia['sMediaName']
            ), $iTempID);
            
            return $this->showMsg('执行效果图提交成功', true);
        } else {
            $this->assign('aAdMedia', $aAdMedia);
            $this->setMeta('mcenter_page', array(
                'sTitle' => '自媒体中心 - 提交效果图'
            ));
        }
    }

    /**
     * 财务中心
     */
    public function financeAction ()
    {
        $iType = (int) $this->getParam('type');
        $iPage = max(1, intval($this->getParam('page')));
        $aWhere = array(
            'iUserID' => $this->aCurrUser['iUserID'],
            'iStatus' => 1,
			'iPut' => 1
        );
        if ($iType > 0 && $iType < 3) {
            $aWhere['iPayment'] = $iType;
        } 
        $aData = Model_Finance::getList($aWhere, $iPage, 'iAutoID DESC');
        $this->assign('aData', $aData);
        $this->assign('aUser', Model_User::getDetail($this->aCurrUser['iUserID']));
        $this->assign('aSource', Model_Finance::getSources());
        $this->assign('aPayment', Model_Finance::getPayments());
        $this->assign('iType', $iType);
        
        $this->setMeta('mcenter_page', array(
            'sTitle' => '自媒体中心 - 财务中心'
        ));
    }

    /**
     * 消息中心
     */
    public function newsAction ()
    {
        $iType = max(1, (int) $this->getParam('type'));
        $iPage = max(1, intval($this->getParam('page')));

        $aData = Model_Media::getList(array(
            'iStatus > ' => 0,
            'iUserID' => $this->aCurrUser['iUserID'],

        ), $iPage);

        $this->assign('aData', $aData);
        $this->assign('iType', $iType);
        $this->setMeta('mcenter_page', array(
            'sTitle' => '自媒体中心 - 消息中心'
        ));
    }


    /**
     * 消息详情
     */
   public function usernewsAction ()
    {
        $iPage = max(1, intval($this->getParam('page')));
        $iMediaID = (int) $this->getParam('id');

        $aData = Model_Media::getList(array(
            'iStatus > ' => 0,
            'iMediaID' => $iMediaID
        ), $iPage);

        $this->assign('aData', $aData);
        $this->assign('iType', $iPage);
        $this->setMeta('mcenter_page', array(
            'sTitle' => '自媒体中心 - 消息详情'
        ));
    }

    /**
     * 账号中心
     */
    public function accountAction ()
    {
        $iType = max(1, (int) $this->getParam('type'));
        $iPage = max(1, intval($this->getParam('page')));
        
        $aData = Model_Media::getList(array(
            'iStatus > ' => 0,
            'iUserID' => $this->aCurrUser['iUserID'],
            'iMediaType' => $iType
        ), $iPage);
        
        $this->assign('aData', $aData);
        $this->assign('iType', $iType);
        
        $this->setMeta('mcenter_page', array(
            'sTitle' => '自媒体中心 - 财号中心'
        ));
    }

      /**
     * 提现申请
     */
    public function cashoutAction ()
    {
        if ($this->isPost()) {
            $aUser = Model_User::getDetail($this->aCurrUser['iUserID']);
            
            $aParam = $this->getParams();
            $aParam['iPayMoney'] = (int) $this->getParam('iPayMoney');
            $aParam['iPayType'] = (int) $this->getParam('iPayType');
            
            $aErr = array();
            if (empty($aParam['sRealName'])) {
                $aErr['sRealName'] = '请输入申请人';
            }
            if (empty($aParam['iPayMoney']) || intval($aParam['iPayMoney']) < 1) {
                $aErr['iPayMoney'] = '请输入正确的提现金额';
            }
            if (empty($aParam['sPayPassword']) || $aUser['sPayPass'] != Model_User::makePassword($aParam['sPayPassword'])) {
                $aErr['sPayPass'] = '支付密码错误';
            }
            if ($aParam['iPayMoney'] > $aUser['iMoney']) {
                $aErr['iPayMoney'] = '可提现的余额不足';
            }
            
            if ($aParam['iPayType'] == 1) {
                if (empty($aParam['sPayAccount']) || strlen($aParam['sPayAccount']) < 5) {
                    $aErr['sPayAccount'] = '请输入正确的支付账号';
                }

                $aParam['sOpenName'] = $aParam['sOpenName1'];
                if (empty($aParam['sOpenName']) || ! Util_Validate::isCLength($aParam['sOpenName'], 2, 20)) {
                    $aErr['sOpenName'] = '请输入正确的支付宝姓名';
                }
            } else {
                if (empty($aParam['sOpenName']) || ! Util_Validate::isCLength($aParam['sOpenName'], 2, 20)) {
                    $aErr['sOpenName'] = '请输入正确的开户姓名';
                }
                if (empty($aParam['sBankName']) || ! Util_Validate::isCLength($aParam['sBankName'], 4, 50)) {
                    $aErr['sBankName'] = '请输入正确的开户银行';
                }
                if (empty($aParam['sBankAccount']) || ! Util_Validate::isLength($aParam['sBankAccount'], 10, 30)) {
                    $aErr['sBankAccount'] = '请输入正确的开户银行';
                }
            }
            if (! empty($aErr)) {
                return $this->showMsg($aErr, false);
            }
            
            $aRow = array(
                'iUserID' => $aUser['iUserID'],
                'iPayment' => Model_Finance::PAYMENT_OUT,
                'iSource' => Model_Finance::SOURCE_CASH_OUT,
                'sRealName' => $aParam['sRealName'],
                'iPayType' => (int) $aParam['iPayType'],
                'iPayMoney' => $aParam['iPayMoney'],
                'iUserMoney' => $aUser['iMoney'] - $aParam['iPayMoney'],
                'sOpenName' => $aParam['iPayType'] == 1 ? '' : $aParam['sOpenName'],
                'sBankName' => $aParam['iPayType'] == 1 ? '' : $aParam['sBankName'],
                'sPayAccount' => $aParam['iPayType'] == 1 ? $aParam['sPayAccount'] : $aParam['sBankAccount'],
                'iPayStatus' => 0,
                'sPayOrder' => '',
                'sRemark' => ''
            );
            Model_User::begin();
            Model_Finance::addData($aRow);
            Model_User::updData(array(
                'iUserID' => $aUser['iUserID'],
                'iMoney' => 'iMoney - ' . $aParam['iPayMoney']
            ));
            Model_User::commit();
            $sTitle = Model_Kv::getValue('user_tixian_email_title');
            $sContent = Model_Kv::getValue('user_tixian_email_content');
            $email='viven@51wom.com';
            Util_Mail::send($email, $sTitle, $sContent,array($aUser['sEmail'],$aParam['iPayMoney']));
            return $this->showMsg('提现申请成功', true);
        } else {
            $aType = Model_Domain::getOption(Model_Domain::TYPE_USER_CASTOUT);
            $aUser = Model_User::getDetail($this->aCurrUser['iUserID']);
            if (empty($aUser['sPayPass'])) {
                return $this->redirect('/mcenter/chgpaypwd.html?ret=' . Util_Uri::getCurrUrl());
            }

            $this->assign('aType', $aType);
            $this->assign('aUser', $aUser);
            
            $this->setMeta('mcenter_page', array(
                'sTitle' => '自媒体中心 - 申请提现'
            ));
        }
    }

    /**
     * 申请提现成功
     */
    public function cashokAction ()
    {
        $this->setMeta('mcenter_page', array(
            'sTitle' => '自媒体中心 - 申请成功'
        ));
    }

    /**
     * 企业资料
     */
    public function coinfoAction ()
    {
        if ($this->isPost()) {
            $aParam = $this->getParams();
            $aParam['iUserID'] = $this->aCurrUser['iUserID'];
            $aUser = Model_User::getDetail($aParam['iUserID']);
            
            $aErr = array();
            if (! Util_Validate::isCLength($aParam['sCoName'], 2, 50)) {
                $aErr['sCoName'] = '公司名称长度为2-50个汉字';
            }
            if (empty($aParam['aCoIndustry']) || count($aParam['aCoIndustry']) > 3) {
                $aErr['sCoIndustry'] = '请选择1~3个行业';
            }
            if (! Util_Validate::isCLength($aParam['sCoAddress'], 5, 100)) {
                $aErr['sCoAddress'] = '公司地址长度为5-50个汉字';
            }
            if (! Util_Validate::isAbsoluteUrl($aParam['sCoWebsite'])) {
                $aErr['sCoWebsite'] = '公司网址格式不正确';
            }
            if (! Util_Validate::isCLength($aParam['sCoDesc'], 2, 200)) {
                $aErr['sCoDesc'] = '公司介绍长度为2-500个汉字';
            }
            
            if (! empty($aErr)) {
                return $this->showMsg($aErr, false);
            }
            
            $aParam['sCoIndustry'] = join(',', $aParam['aCoIndustry']);
            Model_User::updData($aParam);
            return $this->showMsg('企业资料修改成功！', true);
        } else {
            $aUser = Model_user::getDetail($this->aCurrUser['iUserID']);
            $aUser['aCoIndustry'] = explode(',', $aUser['sCoIndustry']);
            $aIndustry = Model_Domain::getOption(Model_Domain::TYPE_CO_INDUSTRY);
            
            $this->assign('aUser', $aUser);
            $this->assign('aIndustry', $aIndustry);
            $this->assign('iTabID', 1);
            
            $this->setMeta('mcenter_page', array(
                'sTitle' => '自媒体中心 - 企业资料'
            ));
        }
    }

    /**
     * 个人信息
     */
    public function userinfoAction ()
    {
        if ($this->isPost()) {
            $aParam = $this->getParams();
            $aParam['iUserID'] = $this->aCurrUser['iUserID'];
            $aUser = Model_User::getDetail($aParam['iUserID']);
            
            $aErr = array();
//             if (empty($aParam['sEmail']) || ! Util_Validate::isEmail($aParam['sEmail'])) {
//                 $aErr['sEmail'] = '邮箱格式不正确';
//             }
            if (empty($aParam['sMobile']) || ! Util_Validate::isMobile($aParam['sMobile'])) {
                $aErr['sMobile'] = '手机号码格式不正确';
            }
            if ($aUser['sEmail'] != $aParam['sEmail'] && Model_User::getUserByEmail($aParam['sEmail'], $aParam['iType'], $aParam['iUserID'])) {
                $aErr['sEmail'] = '该邮箱已经被注册了';
            }
            if ($aUser['sMobile'] != $aParam['sMobile'] && Model_User::getUserByMobile($aParam['sMobile'], $aParam['iType'], $aParam['iUserID'])) {
                $aErr['sMobile'] = '该手机号码已经被注册了';
            }
            if (! Util_Validate::isCLength($aParam['sRealName'], 2, 5)) {
                $aErr['sRealName'] = '姓名长度为2-5个汉字!';
            }
            if (! Util_Validate::isLength($aParam['sWeixin'], 4, 50)) {
                $aErr['sWeixin'] = '请输入正确的微信号';
            }
            if (! Util_Validate::isQQ($aParam['sQQ'])) {
                $aErr['sQQ'] = 'QQ号码输入不正确';
            }
            
            if (! empty($aErr)) {
                return $this->showMsg($aErr, false);
            }
            
            Model_User::updData($aParam);
            return $this->showMsg('个人信息修改成功！', true);
        } else {
            $aUser = Model_user::getDetail($this->aCurrUser['iUserID']);
            
            $this->assign('aUser', $aUser);
            $this->assign('iTabID', 2);
            
            $this->setMeta('mcenter_page', array(
                'sTitle' => '自媒体中心 - 个人资料'
            ));
        }
    }

    /**
     * 修改密码
     */
    public function chgpaypwdAction ()
    {
        if ($this->isPost()) {
            $aParam = $this->getParams();
            $aErr = array();
            if (empty($aParam['sNewPassword']) || ! Util_Validate::isLength($aParam['sNewPassword'], 6, 12)) {
                $aErr['sNewPassword'] = '支付密码长度为6-12个字符';
            }
            if ($aParam['sNewPassword'] != $aParam['sRePassword']) {
                $aErr['sRePassword'] = '支付密码两次输入不一致';
            }
            
            $aUser = Model_User::getDetail($this->aCurrUser['iUserID']);
            if (Model_User::makePassword($aParam['sOldPassword']) != $aUser['sPassword']) {
                $aErr['sOldPassword'] = '登录密码输入错误 ';
            }
            
            if (! empty($aErr)) {
                return $this->showMsg($aErr, false);
            }
            
            $sNewPassword = Model_User::makePassword($aParam['sNewPassword']);
            Model_User::updData(array(
                'sPayPass' => $sNewPassword,
                'iUserID' => $aUser['iUserID']
            ));
            return $this->showMsg('支付密码修改成功！', true);
        } else {
            $this->assign('iTabID', 4);
            $ret = $this->getParam('ret', '');
            $ret = empty($ret) ? $this->getRequest()->getHttpReferer() : $ret;
            $this->assign('ret', $ret);
            $this->setMeta('mcenter_page', array(
                'sTitle' => '自媒体中心 - 修改支付密码'
            ));
        }
    }

    /**
     * 修改密码
     */
    public function chgpwdAction ()
    {
        if ($this->isPost()) {
            $aParam = $this->getParams();
            $aErr = array();
            if (empty($aParam['sNewPassword']) || ! Util_Validate::isLength($aParam['sNewPassword'], 6, 12)) {
                $aErr['sNewPassword'] = '登录密码长度为6-12个字符';
            }
            if ($aParam['sNewPassword'] != $aParam['sRePassword']) {
                $aErr['sRePassword'] = '登录密码两次输入不一致';
            }
            
            $aUser = Model_User::getDetail($this->aCurrUser['iUserID']);
            if (Model_User::makePassword($aParam['sOldPassword']) != $aUser['sPassword']) {
                $aErr['sOldPassword'] = '原登录密码输入错误 ';
            }
            
            if (! empty($aErr)) {
                return $this->showMsg($aErr, false);
            }
            
            $sNewPassword = Model_User::makePassword($aParam['sNewPassword']);
            Model_User::updData(array(
                'sPassword' => $sNewPassword,
                'iUserID' => $aUser['iUserID']
            ));
            return $this->showMsg('登录密码修改成功！', true);
        } else {
            $this->assign('iTabID', 3);
            $this->setMeta('mcenter_page', array(
                'sTitle' => '自媒体中心 - 修改登录密码'
            ));
        }
    }

    /**
     * 公众号认证
     */
    public function verifyaccountAction ()
    {
        $iType = max(1, (int) $this->getParam('type'));
        $iPage = max(1, intval($this->getParam('page')));
        
        $aData = Model_CrawlWeixin::getList(array(
        ), $iPage);
        
        $this->assign('aData', $aData);
        $this->assign('iType', $iType);
        
        $this->setMeta('mcenter_page', array(
            'sTitle' => '自媒体中心 - 公众号认证'
        ));
    }
}