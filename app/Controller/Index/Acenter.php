<?php

/**
 * 广告主 - 个人中心的各种操作
 * Created by PhpStorm.
 * User: xiejinci
 * Date: 14/12/24
 * Time: 下午2:32
 */
class Controller_Index_Acenter extends Controller_Index_Base
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
        
        $this->assign('isACenter', 1);
    }

    /**
     * 派单中心
     */
    public function indexAction ()
    {
        $iPage = max(intval($this->getParam('page')), 1);
        $aData = Model_Ad::getList(array(
            'iUserID' => $this->aCurrUser['iUserID'],
            'iStatus >' => 0
        ), $iPage, 'iAdID DESC');
        
        // 计算复盖粉丝
        foreach ($aData['aList'] as &$aRow) {
            $aRow['iFollowNum'] = Model_Ad::query('SELECT SUM(iFollowerNum) FROM t_ad_media a, t_media m WHERE a.iMediaID=m.iMediaID AND a.iAdID=' . $aRow['iAdID'], 'one');
        }
        
        $this->assign('aData', $aData);
        $this->assign('aOrderStatus', Model_Ad::$aOrderStatus);
        $this->assign('aMediaType', Model_Media::$aType);
        
        $this->setMeta('common_page', array(
            'sTitle' => '广告主中心 - 派单中心'
        ));
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
            'iStatus' => 1
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
        
        $this->setMeta('common_page', array(
            'sTitle' => '广告主中心 - 财务中心'
        ));
    }
    /**
     * 审核失败
     */
    public function istatusAction()
    {
        $iAdID = $this->getParam('id');
        $iPage = max(1, intval($this->getParam('page')));
        $aData = Model_Ad::getList(array(
            'iAdID' => $iAdID
        ),$iPage);
        $this->assign('aData', $aData);
        $this->assign('iAdID', $iAdID);

        $this->setMeta('mcenter_page', array(
            'sTitle' => '审核失败'
        ));
    }

    /**
     * 导出流水
     */
    public function exportAction ()
    {
        $iType = (int) $this->getParam('type');
        $iPage = max(1, intval($this->getParam('page')));
        $aWhere = array(
            'iUserID' => $this->aCurrUser['iUserID'],
            'iStatus' => 1
        );
        if ($iType > 0 && $iType < 3) {
            $aWhere['iPayment'] = $iType;
        }
        $aList = Model_Finance::getAll(array(
            'where' => $aWhere,
            'order' => 'iAutoID DESC'
        ));
        
        $aSource = Model_Finance::getSources();
        $aPayment = Model_Finance::getPayments();
        $aData = array();
        foreach ($aList as $v) {
            $aData[] = array(
                'iAutoID' => $v['iAutoID'],
                'iTime' => date('Y-m-d H:i:s', $v['iCreateTime']),
                'sType' => $aPayment[$v['iPayment']],
                'iPayMoney' => $v['iPayMoney'],
                'iUserMoney' => $v['iUserMoney'],
                'sSource' => $aSource[$v['iSource']],
                'sRemark' => $v['sRemark']
            );
        }
        $aTitle = array(
            'iAutoID' => '订单编号',
            'iTime' => '时间',
            'sType' => '分类',
            'iPayMoney' => '金额',
            'iUserMoney' => '当前余额',
            'sSource' => '账单明细',
            'sRemark' => '备注'
        );
        Util_File::exportCsv('财务流水-' . date('Ymd') . '.csv', $aData, $aTitle);
        return false;
    }

    /**
     * 报告中心
     */
    public function reportAction ()
    {}

    /**
     * 详细报告
     */
    public function reportdetailAction ()
    {}

    /**
     * 自主充值
     */
    public function cashinAction ()
    {
        $aUser = Model_User::getDetail($this->aCurrUser['iUserID']);
        $this->assign('aUser', $aUser);
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
            
            return $this->showMsg('提现申请成功', true);
        } else {
            $aType = Model_Domain::getOption(Model_Domain::TYPE_USER_CASTOUT);
            $aUser = Model_User::getDetail($this->aCurrUser['iUserID']);
            if (empty($aUser['sPayPass'])) {
                return $this->redirect('/acenter/chgpaypwd.html?ret=' . Util_Uri::getCurrUrl());
            }
            
            $this->assign('aType', $aType);
            $this->assign('aUser', $aUser);
            
            $this->setMeta('common_page', array(
                'sTitle' => '广告主中心 - 申请提现'
            ));
        }
    }

    /**
     * 申请提现成功
     */
    public function cashokAction ()
    {
        $this->setMeta('common_page', array(
            'sTitle' => '广告主中心 - 申请成功'
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
            
            $this->setMeta('common_page', array(
                'sTitle' => '广告主中心 - 企业资料'
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
            
            $this->setMeta('common_page', array(
                'sTitle' => '广告主中心 - 个人信息'
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
            $this->setMeta('common_page', array(
                'sTitle' => '广告主中心 - 修改支付密码'
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
            
            $this->setMeta('common_page', array(
                'sTitle' => '广告主中心 - 修改登录密码'
            ));
        }
    }

    /**
     * 订单处理中心
     */
    public function mediaAction ()
    {
        $iStatus = max(2, intval($this->getParam('type', 1)));
        $iPage = max(1, intval($this->getParam('page')));
        
        $aData = Model_AdMedia::getList(array(
            'iAUserID' => $this->aCurrUser['iUserID'],
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
        
        $aStatus = Model_AdMedia::$aStatus;
        unset($aStatus[0]);
        $this->assign('aStatus', $aStatus);
        
        $this->setMeta('common_page', array(
            'sTitle' => '广告主中心 - 订单处理'
        ));
    }

    /**
     * 确认内容
     * 
     * @return boolean
     */
    public function checkpreviewAction ()
    {
        $iAutoID = intval($this->getParam('id'));
        
        $aAdMedia = Model_AdMedia::getDetail($iAutoID);
        if (empty($aAdMedia)) {
            return $this->showMsg('数据出错了', false);
        }
        
        if ($aAdMedia['iStatus'] != Model_AdMedia::STATUS_CONFIRM_PREVIEW) {
            return $this->showMsg('你已经处理过了', false);
        }
        
        $aAd = Model_Ad::getDetail($aAdMedia['iAdID']);
        
        Model_AdMedia::updStatus($iAutoID, Model_AdMedia::STATUS_PUSH);
        // 邮件通知
        $sTitle = Model_Kv::getValue('ad_approve_preview_email_title');
        $sContent = Model_Kv::getValue('ad_approve_preview_email_content');
        
        // 短信通知
        $iTempID = Util_Common::getConf(6, 'aSmsTempID');
        $aUser = Model_User::getDetail($aAdMedia['iMUserID']);
        $aMedia = Model_Media::getDetail($aAdMedia['iMediaID']);
        Util_Mail::send($aUser['sEmail'], $sTitle, $sContent, array(
            $aMedia['sEmail']
        ));
        Util_Sms::sendTemplateSms($aUser['sMobile'], array(
            $aMedia['sEmail']
        ), $iTempID);
        // echo $sTitle, "\n", $sContent, "\n", $aUser['sEmail'], $aUser['sMobile'], $iTempID;
        
        return $this->showMsg('操作成功', true);
    }

    /**
     * 投放完成(结算)
     * 
     * @return boolean
     */
    public function finishAction ()
    {
        $iAutoID = intval($this->getParam('id'));
        
        $aAdMedia = Model_AdMedia::getDetail($iAutoID);
        if (empty($aAdMedia)) {
            return $this->showMsg('数据出错了', false);
        }
        
        if ($aAdMedia['iStatus'] != Model_AdMedia::STATUS_CONFIRM_EFFECT) {
            return $this->showMsg('你已经处理过了', false);
        }
        
        $aAd = Model_Ad::getDetail($aAdMedia['iAdID']);
        
        Model_AdMedia::begin();
        Model_AdMedia::updStatus($iAutoID, Model_AdMedia::STATUS_FINISHED);
        Model_Finance::updMoney($aAdMedia['iMUserID'], array(
            'iPayment' => Model_Finance::PAYMENT_IN,
            'iSource' => Model_Finance::SOURCE_AD_CASH_IN,
            'iPayType' => Model_Finance::TYPE_NO,
            'iMoney' => $aAdMedia['iMoney']
        ));
        Model_AdMedia::commit();
        
        // 邮件通知
        $sTitle = Model_Kv::getValue('ad_approve_preview_email_title');
        $sContent = Model_Kv::getValue('ad_approve_preview_email_content');
        
        // 短信通知
        $iTempID = Util_Common::getConf(6, 'aSmsTempID');
        $aUser = Model_User::getDetail($aAdMedia['iMUserID']);
        $aMedia = Model_Media::getDetail($aAdMedia['iMediaID']);
        Util_Mail::send($aUser['sEmail'], $sTitle, $sContent, array(
            $aMedia['sEmail']
        ));
        Util_Sms::sendTemplateSms($aUser['sMobile'], array(
            $aMedia['sEmail']
        ), $iTempID);
        // echo $sTitle, "\n", $sContent, "\n", $aUser['sEmail'], $aUser['sMobile'], $iTempID;
        
        return $this->showMsg('操作成功', true);
    }
}