<?php

/**
 * Controller基类，做一些基础事情(GUID,当前用户)
 *  
 *  User: pancke@qq.com
 *  Date: 2015-10-27
 *  Time: 上午9:28:18
 */
class Controller_Index_Base extends Yaf_Controller
{

    /**
     * 访问者GUID
     * @var unknown
     */
    protected $sVistorGuid = '';
    
    /**
     * 当前登录用户，通过checkLogin赋值
     * @var unknown
     */
    protected $aCurrUser = null;

    /**
     * 检测是否登录
     */
    public function checkLogin($iType)
    {
        $this->getCurrUser($iType);
        if (empty($this->aCurrUser)) {
            return $this->redirect('/user/login/type/' . $iType . '.html?ret=' . Util_Uri::getCurrUrl());
        }
        
        return true;
    }
    
    /**
     * 取得广告主登录信息
     */
    public function getCurrUser ($iType, $bLog = true)
    {
        $sKey = Model_User::getUserType($iType);
        $this->aCurrUser = Util_Cookie::get(Yaf_G::getConf($sKey, 'cookie'));
        if ($bLog && ! empty($this->aCurrUser)) {
            Model_ActionLog::setUser($this->aCurrUser['sEmail']);
        }
        
        return $this->aCurrUser;
    }

    /**
     * 执行Action前执行
     *
     * @see Yaf_Controller::actionBefore()
     */
    public function actionBefore ()
    {
        Model_ActionLog::setType(Model_ActionLog::TYPE_FRONT);
        
        $this->_frame = 'frame.phtml';
        
        // 访问者的GUID
        $this->sVistorGuid = Util_Cookie::get('guid');
        if (empty($this->sVistorGuid)) {
            $this->sVistorGuid = Util_Guid::get('-');
            Util_Cookie::set('guid', $this->sVistorGuid, 86400 * 365);
        }

        $this->assign('sStaticRoot', 'http://' . Yaf_G::getConf('static', 'domain'));
    }

    /**
     * 执行Action后的操作
     *
     * @see Yaf_Controller::actionAfter()
     */
    public function actionAfter ()
    {
        if ($this->autoRender() == true) {
            $aDebug = Util_Common::getDebugData();
            if ($aDebug) {
                $this->assign('__showDebugInfo__', 'showDebugInfo(' . json_encode($aDebug) . ');');
            }
            
            $this->assign('_iMediaTotal', Model_Media::getCnt(array('where' => array('iStatus' => 1))));
            $this->assign('_iYAdTotal', Model_Ad::getYesterdayAdCnt());
            $this->assign('_iYUserTotal', Model_Ad::getYesterdayUserCnt());
            
            if (empty($this->aCurrUser)) {
                $this->aCurrUser = $this->getCurrUser(Model_User::TYPE_AD, false);
                if (empty($this->aCurrUser)) {
                    $this->aCurrUser = $this->getCurrUser(Model_User::TYPE_MEDIA, false);
                }
            }
            $this->assign('_aCurrUser', $this->aCurrUser);
        } else {}
    }
    
    /**
     * 设置meta
     * @param unknown $sPage
     * @param unknown $aData
     */
    protected function setMeta($sPage, $aData) 
    {
        $aMeta = Model_Meta::getDetail($sPage);
        if (empty($aMeta)) {
            $aMeta = array(
                'sTitle' => '',
                'sDescription' => '',
                'sKeyword' => '',
            );
        } else {
            $aSearch = array();
            $aReplace = array();
            foreach ($aData as $k => $v) {
                $aSearch[] = '{' . $k . '}';
                $aReplace[] = $v;
            }
            foreach ($aMeta as $k => $v) {
                $aMeta[$k] = str_replace($aSearch, $aReplace, $v);
            }
        }
        
        $this->assign("aMeta", $aMeta);
    }
}