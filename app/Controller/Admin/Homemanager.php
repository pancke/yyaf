<?php

class Controller_Admin_Homemanager extends Controller_Admin_Base
{

    /**
     * 首页经理删除
     */
    public function delAction()
    {
        $iHomeManagerID = intval($this->getParam('id'));
        $iRet = Model_HomeManager::delData($iHomeManagerID);
        if ($iRet == 1) {
            return $this->showMsg('首页经理删除成功！', true);
        } else {
            return $this->showMsg('首页经理删除失败！', false);
        }
    }

    /**
     * 首页经理列表
     */
    public function listAction()
    {
        $iPage = intval($this->getParam('page'));
        $aWhere = array(
            'iStatus' => 1
        );
        
        $aParam = $this->getParams();
        if (! empty($aParam['sName'])) {
        	$aWhere['sName LIKE'] = '%'.$aParam['sName'].'%';
        }
        
        $aList = Model_HomeManager::getList($aWhere, $iPage);

        $this->assign('aList', $aList);
        $this->assign('aParam', $aParam);
    }

    /**
     * 首页经理修改
     */
    public function editAction()
    {
        if ($this->_request->isPost()) {
            $aHomeManager = $this->_checkData('update');
            if (empty($aHomeManager)) {
                return null;
            }
            $aHomeManager['iAutoID'] = intval($this->getParam('iAutoID'));
            $aOldHomeManager = Model_HomeManager::getDetail($aHomeManager['iAutoID']);
            if (empty($aOldHomeManager)) {
                return $this->showMsg('首页经理不存在！', false);
            }
            if (1 == Model_HomeManager::updData($aHomeManager)) {
                return $this->showMsg('首页经理信息更新成功！', true);
            } else {
                return $this->showMsg('首页经理信息更新失败！', false);
            }
        } else {
            $iHomeManagerID = intval($this->getParam('id'));
            $aHomeManager = Model_HomeManager::getDetail($iHomeManagerID);
            $this->assign('aHomeManager', $aHomeManager);
             
        }
    }

    /**
     * 增加首页经理
     */
    public function addAction()
    {
        if ($this->_request->isPost()) {
            $aHomeManager = $this->_checkData('add');
            if (empty($aHomeManager)) {
                return null;
            }
            if (Model_HomeManager::addData($aHomeManager) > 0) {
                return $this->showMsg('首页经理增加成功！', true);
            } else {
                return $this->showMsg('首页经理增加失败！', false);
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
        $sName = $this->getParam('sName');
        $sImage = $this->getParam('sImage');
        $sUrl = $this->getParam('sUrl');
        $sDesc = $this->getParam('sDesc');
        $iUpdateTime = time();
        
        $aRow = array(
            'sName' => $sName,
            'sImage' => $sImage,
            'sUrl' => $sUrl,
            'sDesc' => $sDesc,
            'iUpdateTime' => $iUpdateTime
        );
        
        return $aRow;
    }
}