<?php

class Controller_Admin_City extends Controller_Admin_Base
{

    /**
     * 城市删除
     */
    public function delAction()
    {
        $iCityID = intval($this->getParam('id'));
        $iRet = Model_City::delData($iCityID);
        if ($iRet == 1) {
            return $this->showMsg('城市删除成功！', true);
        } else {
            return $this->showMsg('城市删除失败！', false);
        }
    }

    /**
     * 城市列表
     */
    public function listAction()
    {
    	$aCookie = Util_Cookie::get(Yaf_G::getConf('authkey', 'cookie'));
        $iPage = intval($this->getParam('page'));
        $aWhere = array(
            'iStatus' => 1
        );
        $aList = Model_City::getList($aWhere, $iPage);
        $this->assign('aList', $aList);
    }

    /**
     * 城市修改
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
        	
            $aCity = $this->_checkData('update');
            if (empty($aCity)) {
                return null;
            }
            $aCity['iCityID'] = intval($this->getParam('iCityID'));
            $aOldCity = Model_City::getDetail($aCity['iCityID']);
            if (empty($aOldCity)) {
                return $this->showMsg('城市不存在！', false);
            }
            if ($aOldCity['sCityName'] != $aCity['sCityName']) {
                if (Model_City::getCityByName($aCity['sCityName'])) {
                    return $this->showMsg('城市已经存在！', false);
                }
            }
            if (1 == Model_City::updData($aCity)) {
                return $this->showMsg('城市信息更新成功！', true);
            } else {
                return $this->showMsg('城市信息更新失败！', false);
            }
        } else {
            $iCityID = intval($this->getParam('id'));
            $aCity = Model_City::getDetail($iCityID);
            $this->assign('aCity', $aCity);
        }
    }

    /**
     * 增加城市
     */
    public function addAction()
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
        	 
        	
            $aCity = $this->_checkData('add');
            if (empty($aCity)) {
                return null;
            }
            if (Model_City::getCityByName($aCity['sCityName'])) {
                return $this->showMsg('城市已经存在！', false);
            }
            if (Model_City::addData($aCity) > 0) {
                return $this->showMsg('城市增加成功！', true);
            } else {
                return $this->showMsg('城市增加失败！', false);
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
        $sCityName = $this->getParam('sCityName');
        $sCityCode = $this->getParam('sCityCode');
        $iFrontShow = $this->getParam('iFrontShow');
        $iBackendShow = $this->getParam('iBackendShow');

        if (!Util_Validate::isLength($sCityName, 2, 20)) {
            return $this->showMsg('城市名称长度范围为2到20个字！', false);
        }
        if (!Util_Validate::isLength($sCityCode, 2, 50)) {
            return $this->showMsg('城市代码长度范围为2到50个字母！', false);
        }
        if (!Util_Validate::isRange($iFrontShow, 0, 1)) {
            return $this->showMsg('城市前台是否启用输入不合法！', false);
        }
        if (!Util_Validate::isRange($iBackendShow, 0, 1)) {
            return $this->showMsg('城市后台是否启用输入不合法！', false);
        }
        
        $aRow = array(
            'sCityName' => $sCityName,
            'sCityCode' => $sCityCode,
            'iFrontShow' => $iFrontShow,
            'iBackendShow' => $iBackendShow
        );
        
        return $aRow;
    }
}