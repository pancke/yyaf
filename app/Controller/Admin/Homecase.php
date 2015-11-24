<?php

class Controller_Admin_Homecase extends Controller_Admin_Base
{
	
    /**
     * 首页实例删除
     */
    public function delAction()
    {
        $iHomeCaseID = intval($this->getParam('id'));
        $iRet = Model_HomeCase::delData($iHomeCaseID);
        if ($iRet == 1) {
            return $this->showMsg('首页实例删除成功！', true);
        } else {
            return $this->showMsg('首页实例删除失败！', false);
        }
    }

    /**
     * 首页实例列表
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
        
        $aList = Model_HomeCase::getList($aWhere, $iPage);
        foreach ($aList['aList'] as $key => $val) {
        	if ($val['iType'] > 0) {
        		if(isset($this->wtype[$val['iType']])) {
        			$aList['aList'][$key]['iTypeTitle'] = $this->wtype[$val['iType']];
        		} else {
        			$aList['aList'][$key]['iTypeTitle'] = '';
        		}
        	} else {
        		$aList['aList'][$key]['iTypeTitle'] = '';
        	}
        }
        $this->assign('aList', $aList);
        $this->assign('aParam', $aParam);
        $this->assign('wtype', Model_HomeCase::$wtype);
    }

    /**
     * 首页实例修改
     */
    public function editAction()
    {
        if ($this->_request->isPost()) {
            $aHomeCase = $this->_checkData('update');
            if (empty($aHomeCase)) {
                return null;
            }
            $aHomeCase['iAutoID'] = intval($this->getParam('iAutoID'));
            $aOldWebSite = Model_HomeCase::getDetail($aHomeCase['iAutoID']);
            if (empty($aOldWebSite)) {
                return $this->showMsg('首页实例不存在！', false);
            }
            if (1 == Model_HomeCase::updData($aHomeCase)) {
                return $this->showMsg('首页实例信息更新成功！', true);
            } else {
                return $this->showMsg('首页实例信息更新失败！', false);
            }
        } else {
            $iHomeCaseID = intval($this->getParam('id'));
            $aHomeCase = Model_HomeCase::getDetail($iHomeCaseID);
            $this->assign('aHomeCase', $aHomeCase);
            $this->assign('wtype', Model_HomeCase::$wtype);
             
        }
    }

    /**
     * 增加首页实例
     */
    public function addAction()
    {
        if ($this->_request->isPost()) {
            $aHomeCase = $this->_checkData('add');
            if (empty($aHomeCase)) {
                return null;
            }
            if (Model_HomeCase::addData($aHomeCase) > 0) {
                return $this->showMsg('首页实例增加成功！', true);
            } else {
                return $this->showMsg('首页实例增加失败！', false);
            }
        }
        $this->assign('wtype', Model_HomeCase::$wtype);
    }

    /**
     * 请求数据检测
     *
     * @return mixed
     */
    public function _checkData($sType = 'add')
    {
        $iType = $this->getParam('iType');
        $sTitle = $this->getParam('sTitle');
        $sImage = $this->getParam('sImage');
        $sUrl = $this->getParam('sUrl');
        $sDesc = $this->getParam('sDesc');
        $iUserNum = $this->getParam('iUserNum');
        $iReadNum = $this->getParam('iReadNum');
        $iZanNum = $this->getParam('iZanNum');
        $iAvgZan = $this->getParam('iAvgZan');
        $sFollowerNum1 = $this->getParam('sFollowerNum1');
        $sMediaName1 = $this->getParam('sMediaName1');
        $sOpenName1 = $this->getParam('sOpenName1');
        $sMediaUrl1 = $this->getParam('sMediaUrl1');
        $sOpenName2 = $this->getParam('sOpenName2');
        $sMediaImage2 = $this->getParam('sMediaImage2');
        $sMediaName2 = $this->getParam('sMediaName2');
        $sMediaUrl2 = $this->getParam('sMediaUrl2');
        $sOpenName3 = $this->getParam('sOpenName3');
        $sMediaImage3 = $this->getParam('sMediaImage3');
        $sMediaName3 = $this->getParam('sMediaName3');
        $sMediaUrl3 = $this->getParam('sMediaUrl3');
        $sMediaImage1 = $this->getParam('sMediaImage1');
        $sFollowerNum2 = $this->getParam('sFollowerNum2');
        $sFollowerNum3 = $this->getParam('sFollowerNum3');
        $iUpdateTime = time();
        
        $aRow = array(
            'iType' => $iType,
            'sTitle' => $sTitle,
            'sImage' => $sImage,
            'sUrl' => $sUrl,
            'sDesc' => $sDesc,
            'iUpdateTime' => $iUpdateTime,
            'iUserNum'   =>  $iUserNum,
            'iReadNum'  =>$iReadNum,
            'iAvgZan'  =>$iAvgZan,
            'sFollowerNum1'  =>$sFollowerNum1,
            'sMediaName1'  =>$sMediaName1,
            'sOpenName1'  =>$sOpenName1,
            'iZanNum'  =>$iZanNum,
            'sMediaUrl1'  =>$sMediaUrl1,
            'sOpenName2'  =>$sOpenName2,
            'sMediaImage2'  =>$sMediaImage2,
            'sMediaName2'  =>$sMediaName2,
            'sMediaUrl2'  =>$sMediaUrl2,
            'sMediaImage1'  =>$sMediaImage1,
            'sFollowerNum2'  =>$sFollowerNum2,
            'sFollowerNum3'  =>$sFollowerNum3,
            'sOpenName3'  =>$sOpenName3,
            'sMediaImage3'  =>$sMediaImage3,
            'sMediaName3'  =>$sMediaName3,
            'sMediaUrl3'  =>$sMediaUrl3,


        );
        
        return $aRow;
    }
}