<?php

/**
 * 价格字段配置
 */
class Controller_Admin_Price extends Controller_Admin_Base
{

    /**
     * 价格配置段除删
     */
    public function delAction()
    {
        $iPriceID = intval($this->getParam('id'));
        $iRet = Model_Price::delData($iPriceID);
        if ($iRet == 1) {
            return $this->showMsg('价格配置段删除成功！', true);
        } else {
            return $this->showMsg('价格配置段删除失败！', false);
        }
    }

    /**
     * 价格配置段列表
     */
    public function listAction()
    {
        $iPage = intval($this->getParam('page'));
        $aWhere = array(
            'iStatus' => 1
        );       
        
        $aList = Model_Price::getList($aWhere, $iPage);
        $this->assign('aList', $aList);
    }

    /**
     * 价格配置段修改
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
        	 
            $aPrice = $this->_checkData('update');
            if (empty($aPrice)) {
                return null;
            }
            $aPrice['iAutoID'] = intval($this->getParam('iAutoID'));
            $aOldPrice = Model_Price::getDetail($aPrice['iAutoID']);
            if (empty($aOldPrice)) {
                return $this->showMsg('价格配置段不存在！', false);
            }
            if (1 == Model_Price::updData($aPrice)) {
                return $this->showMsg('价格配置段信息更新成功！', true);
            } else {
                return $this->showMsg('价格配置段信息更新失败！', false);
            }
        } else {
            $iPriceID = intval($this->getParam('id'));
            $aPrice = Model_Price::getDetail($iPriceID);
            $this->assign('aPrice', $aPrice);
        }
    }

    /**
     * 增加价格配置段
     */
    public function addAction()
    {
        if ($this->_request->isPost()) {
            $aPrice = $this->_checkData('add');
            if (empty($aPrice)) {
                return null;
            }
            if (Model_Price::addData($aPrice) > 0) {
                return $this->showMsg('价格配置段增加成功！', true);
            } else {
                return $this->showMsg('价格配置段增加失败！', false);
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