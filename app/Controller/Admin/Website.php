<?php

class Controller_Admin_Website extends Controller_Admin_Base
{

    /**
     * 网站内容删除
     */
    public function delAction()
    {
        $iBadwordID = intval($this->getParam('id'));
        $iRet = Model_WebSite::delData($iBadwordID);
        if ($iRet == 1) {
            return $this->showMsg('网站内容删除成功！', true);
        } else {
            return $this->showMsg('网站内容删除失败！', false);
        }
    }

    /**
     * 网站内容列表
     */
    public function listAction()
    {
        $iPage = intval($this->getParam('page'));
        $aWhere = array(
            'iStatus' => 1
        );
        
        $aParam = $this->getParams();
        if (! empty($aParam['sTitle'])) {
        	$aWhere['sTitle LIKE'] = '%'.$aParam['sTitle'].'%';
        }
        if (! empty($aParam['iType']) && $aParam['iType'] != '') {
        	$aWhere['iType'] = $aParam['iType'];
        }
        if (! empty($aParam['iParentID']) && $aParam['iParentID'] != '') {
        	$aWhere['iParentID'] = $aParam['iParentID'];
        }
        
        $aList = Model_WebSite::getList($aWhere, $iPage);
        foreach ($aList['aList'] as $key => $val) {
        	if ($val['iParentID'] > 0) {
        		$row = Model_WebSite::getDetail($val['iParentID']);
        		if ($row) {
        			$aList['aList'][$key]['sParentTitle'] = $row['sTitle'];
        		} else {
        			$aList['aList'][$key]['sParentTitle'] = '顶级';
        		}
        		
        	} else{
        		$aList['aList'][$key]['sParentTitle'] = '顶级';
        	}
        }
        $this->assign('aList', $aList);
        $this->assign('aParam', $aParam);
        $this->assign('iType' , Model_WebSite::$iType);
        $parentWebsite = Model_WebSite::getList(array('iParentID' => 0 , 'iStatus' => 1), $iPage);
        $this->assign('parentWebsite', $parentWebsite['aList']);
    }

    /**
     * 网站内容修改
     */
    public function editAction()
    {
        if ($this->_request->isPost()) {
            $aWebSite = $this->_checkData('update');
            if (empty($aWebSite)) {
                return null;
            }
            $aWebSite['iWebSiteID'] = intval($this->getParam('iWebSiteID'));
            $aOldWebSite = Model_WebSite::getDetail($aWebSite['iWebSiteID']);
            if (empty($aOldWebSite)) {
                return $this->showMsg('网站内容不存在！', false);
            }
            if (1 == Model_WebSite::updData($aWebSite)) {
                return $this->showMsg('网站内容信息更新成功！', true);
            } else {
                return $this->showMsg('网站内容信息更新失败！', false);
            }
        } else {
            $iWebSiteID = intval($this->getParam('id'));
            $aWebSite = Model_WebSite::getDetail($iWebSiteID);
            $this->assign('aWebSite', $aWebSite);
             
            $parentWebsite = Model_WebSite::getList(array('iParentID' => 0 , 'iStatus' => 1), $iPage);
            $this->assign('parentWebsite', $parentWebsite['aList']);
        	$this->assign('iType' , Model_WebSite::$iType);
        }
    }

    /**
     * 增加网站内容
     */
    public function addAction()
    {
        if ($this->_request->isPost()) {
            $aWebSite = $this->_checkData('add');
            if (empty($aWebSite)) {
                return null;
            }
            if (Model_WebSite::addData($aWebSite) > 0) {
                return $this->showMsg('网站内容增加成功！', true);
            } else {
                return $this->showMsg('网站内容增加失败！', false);
            }
        }
        $parentWebsite = Model_WebSite::getList(array('iParentID' => 0 , 'iStatus' => 1), $iPage);
        $this->assign('parentWebsite', $parentWebsite['aList']);
        $this->assign('iType' , Model_WebSite::$iType);
    }

    /**
     * 请求数据检测
     *
     * @return mixed
     */
    public function _checkData($sType = 'add')
    {
        $iType = $this->getParam('iType');
        $iParentID = $this->getParam('iParentID');
        $sPage = $this->getParam('sPage');
        $sTitle = $this->getParam('sTitle');
        $skeywords = $this->getParam('skeywords');
        $sdescription = $this->getParam('sdescription');
        $sContent = $this->getParam('sContent');
        $iUpdateTime = time();
        
        $aRow = array(
            'iType' => $iType,
            'iParentID' => $iParentID,
            'sPage' => $sPage,
            'sTitle' => $sTitle,
            'skeywords' => $skeywords,
            'sdescription' => $sdescription,
            'sContent' => $sContent,
            'iUpdateTime' => $iUpdateTime
        );
        
        return $aRow;
    }
}