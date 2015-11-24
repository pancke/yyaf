<?php

class Controller_Admin_Crawlproxy extends Controller_Admin_Base
{

    
    /**
     * 代理列表
     */
    public function listAction()
    {
        $iPage = intval($this->getParam('page'));
        $aWhere = array();
        
        $aParam = $this->getParams();
        if (! empty($aParam['sProxy'])) {
        	$aWhere['sProxy LIKE'] = '%'.$aParam['sProxy'].'%';
        }
        if ($aParam['iStatus'] != '') {
        	$aWhere['iStatus'] = $aParam['iStatus'];
        }

        $aList = Model_CrawlProxy::getList($aWhere, $iPage);
        $this->assign('aList', $aList);
        $this->assign('aParam', $aParam);
    }
    
    /**
     * 代理修改
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
    		 
    		
    		$aProxy = $this->_checkData('update');
    		if (empty($aProxy)) {
    			return null;
    		}
    		$aProxy['iProxyID'] = intval($this->getParam('iProxyID'));
    		$aOldProxy = Model_CrawlProxy::getDetail($aProxy['iProxyID']);
    		if(!$aOldProxy){
    			return $this->showMsg('代理信息更新失败！', false);
    		}
    		if (1 == Model_CrawlProxy::updData($aProxy)) {
    			return $this->showMsg('代理信息更新成功！', true);
    		} else {
    			return $this->showMsg('代理信息更新失败！', false);
    		}
    	} else {
    		$iProxyID = intval($this->getParam('id'));
    		$aProxy = Model_CrawlProxy::getDetail($iProxyID);
    		$this->assign('aProxy', $aProxy);
    	}
    }
    
    /**
     * 增加代理
     */
    public function addAction()
    {
    	if ($this->_request->isPost()) {
    		$aProxy = $this->_checkData('add');
    		if (empty($aProxy)) {
    			return null;
    		}
    		if (Model_CrawlProxy::addData($aProxy) > 0) {
    			return $this->showMsg('代理信息增加成功！', true);
    		} else {
    			return $this->showMsg('代理信息增加失败！', false);
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
    	$sProxy = $this->getParam('sProxy');
    	$iStatus = $this->getParam('iStatus');
    	$iFailNum = $this->getParam('iFailNum');
    	$iBanNum = $this->getParam('iBanNum');
    	$iUpdateTime = time();
    
    	$aRow = array(
    			'sProxy' => $sProxy,
    			'iStatus' => $iStatus,
    			'iFailNum' => $iFailNum,
    			'iBanNum' => $iBanNum,
    			'iUpdateTime' => $iUpdateTime
    	);
    
    	return $aRow;
    }

}