<?php

/**
 * 搜索条件数字段配置
 */
class Controller_Admin_Follower extends Controller_Admin_Base
{

    /**
     * 搜索条件除删
     */
    public function delAction()
    {
        $iFollowerID = intval($this->getParam('id'));
        $iRet = Model_Follower::delData($iFollowerID);
        if ($iRet == 1) {
            return $this->showMsg('搜索条件删除成功！', true);
        } else {
            return $this->showMsg('搜索条件删除失败！', false);
        }
    }

    /**
     * 搜索条件列表
     */
    public function listAction()
    {
        $iPage = intval($this->getParam('page'));
        $aWhere = array(
            'iStatus' => 1
        );
        
        
        
        $aList = Model_Follower::getList($aWhere, $iPage);
        $this->assign('aList', $aList);
    }

    /**
     * 搜索条件修改
     */
    public function editAction()
    {
        if ($this->_request->isPost()) {
        	//修改密码验证
        	$aCookie = Util_Cookie::get(Yaf_G::getConf('authkey', 'cookie'));
        	$iAdminID = $aCookie['iAdminID'];
        	$aUser = Model_Admin::getDetail($iAdminID);
        	$pwd = $this->getParam('pwd');
        	if (!isset($pwd) || $pwd == '') {
        		return $this->showMsg('修改密码不可以为空', false);
        	}
        	if ($aUser['sEditPassword'] != md5($pwd)) {
        		return $this->showMsg('修改密码不正确', false);
        	}
        	
            $aFollower = $this->_checkData('update');
            if (empty($aFollower)) {
                return null;
            }
            $aFollower['iAutoID'] = intval($this->getParam('iAutoID'));
            $aOldFollower = Model_Follower::getDetail($aFollower['iAutoID']);
            if (empty($aOldFollower)) {
                return $this->showMsg('搜索条件不存在！', false);
            }
            if (1 == Model_Follower::updData($aFollower)) {
                return $this->showMsg('搜索条件信息更新成功！', true);
            } else {
                return $this->showMsg('搜索条件信息更新失败！', false);
            }
        } else {
            $iFollowerID = intval($this->getParam('id'));
            $aFollower = Model_Follower::getDetail($iFollowerID);
            $this->assign('aFollower', $aFollower);
        }
    }

    /**
     * 增加搜索条件
     */
    public function addAction()
    {
        if ($this->_request->isPost()) {
            $aFollower = $this->_checkData('add');
            if (empty($aFollower)) {
                return null;
            }
            if (Model_Follower::addData($aFollower) > 0) {
                return $this->showMsg('搜索条件增加成功！', true);
            } else {
                return $this->showMsg('搜索条件增加失败！', false);
            }
        }
    }

    /**
     * 请求数据检测
     *
     * @return mixed
     */
    public function _checkData($sType = 'add')
    {
        $sTitle = $this->getParam('sTitle');
        $iMinPrice = $this->getParam('iMinPrice');
        $iMaxPrice = $this->getParam('iMaxPrice');
        $iUpdateTime = time();
        
        $aRow = array(
            'sTitle' => $sTitle,
            'iMinPrice' => $iMinPrice,
            'iMaxPrice' => $iMaxPrice,
            'iUpdateTime' => $iUpdateTime
        );
        
        return $aRow;
    }
    
}