<?php
/**
 * 基础配置管理
 */
class Controller_Admin_Domain extends Controller_Admin_Base
{

    /**
     * 基础配置删除
     */
    public function delAction()
    {
        $iAutoID = intval($this->getParam('id'));
        $iRet = Model_Domain::delData($iAutoID);
        if ($iRet == 1) {
            return $this->showMsg('基础配置删除成功！', true);
        } else {
            return $this->showMsg('基础配置删除失败！', false);
        }
    }

    /**
     * 基础配置列表
     */
    public function listAction()
    {
        $iPage = intval($this->getParam('page'));
        $aWhere = array(
            'iStatus' => 1
        );
        $aParam = $this->getParams();
        if (!empty($aParam['iType'])) {
        	$aWhere['iType'] = $aParam['iType'];
        }
        if (! empty($aParam['sName'])) {
        	$aWhere['sName LIKE'] = '%'.$aParam['sName'].'%';
        }
        $aList = Model_Domain::getList($aWhere, $iPage);
        $this->assign('aList', $aList);
        $this->assign('iType', Model_Domain::$iType);
        $this->assign('aParam', $aParam);
    }

    /**
     * 基础配置修改
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
        	
            $aDomain = $this->_checkData('update');
            if (empty($aDomain)) {
                return null;
            }
            $aDomain['iAutoID'] = intval($this->getParam('iAutoID'));
            $aOldDomain = Model_Domain::getDetail($aDomain['iAutoID']);
            if (empty($aOldDomain)) {
                return $this->showMsg('基础配置不存在！', false);
            }
            if ($aOldDomain['sName'] != $aDomain['sName']) {
               	if (Model_Domain::getRow(array('where' => array('sName' => $aDomain['sName'] , 'iType' => $aDomain['iType'] , 'iStatus' => 1)))) {
                    return $this->showMsg('基础配置已经存在！', false);
                }
            }
            if (1 == Model_Domain::updData($aDomain)) {
                return $this->showMsg('基础配置信息更新成功！', true);
            } else {
                return $this->showMsg('基础配置信息更新失败！', false);
            }
        } else {
            $iAutoID = intval($this->getParam('id'));
            $aDomain = Model_Domain::getDetail($iAutoID);
            $this->assign('aDomain', $aDomain);
        }
        $this->assign('iType', Model_Domain::$iType);
    }

    /**
     * 增加基础配置
     */
    public function addAction()
    {
        if ($this->_request->isPost()) {
            $aDomain = $this->_checkData('add');
            if (empty($aDomain)) {
                return null;
            }
            if (Model_Domain::getRow(array('where' => array('sName' => $aDomain['sName'] , 'iType' => $aDomain['iType'] , 'iStatus' => 1)))) {
                return $this->showMsg('基础配置已经存在！', false);
            }
            if (Model_Domain::addData($aDomain) > 0) {
                return $this->showMsg('基础配置增加成功！', true);
            } else {
                return $this->showMsg('基础配置增加失败！', false);
            }
        }
        $this->assign('iType', Model_Domain::$iType);
    }

    /**
     * 请求数据检测
     *
     * @return mixed
     */
    public function _checkData($sType = 'add')
    {
        $sName = $this->getParam('sName');
        $iOrder = $this->getParam('iOrder');
        $iType = $this->getParam('iType');
        $iUpdateTim = time();
        
        $aRow = array(
            'sName' => $sName,
            'iOrder' => $iOrder,
            'iType' => $iType,
        	'iUpdateTim' => $iUpdateTim
        );
        
        return $aRow;
    }
}