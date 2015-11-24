<?php

class Controller_Admin_Mediaorder extends Controller_Admin_Base
{

    /**
     * 资源订单删除
     */
    public function delAction()
    {
        $iAdMediaID = intval($this->getParam('id'));
        $iRet = Model_AdMedia::delData($iAdMediaID);
        if ($iRet == 1) {
            return $this->showMsg('资源订单删除成功！', true);
        } else {
            return $this->showMsg('资源订单删除失败！', false);
        }
    }

    /**
     * 资源订单列表
     */
    public function listAction()
    {
        $iPage = intval($this->getParam('page'));
        $uWhere = array();
        $aParam = $this->getParams();
        $userid = '';
        if (! empty($aParam['sUserRealName'])) {
        	$uWhere['sRealName LIKE'] = '%'.$aParam['sUserRealName'].'%';
        	
        	$data_user = Model_User::getAll(array('where' => $uWhere));

        	$useridArr = array();
        	foreach ($data_user as $val) {
        		if($val['iUserID'] != '') {
        			$useridArr[] = $val['iUserID'];
        		}
        	}
        	if(count($useridArr) > 0){
        		$userid = implode(',' , array_unique($useridArr));
        	}
        	
        }
        
        $aWhere = array();
        if (! empty($aParam['iUserID'])) {
        	$aWhere['iUserID'] = $aParam['iUserID'];
        }
        if($userid != '') {
        	$aWhere['iUserID IN'] = $userid;
        }
        
        $iAdID = '';
        if (! empty($aParam['sAdName'])) {
        	$uWhere['sAdName LIKE'] = '%'.$aParam['sAdName'].'%';
        	 
        	$data_Ad = Model_Ad::getAll(array('where' => $uWhere));
        
        	$iAdIDArr = array();
        	foreach ($data_Ad as $val) {
        		if($val['iAdID'] != '') {
        			$iAdIDArr[] = $val['iAdID'];
        		}
        	}
        	if(count($iAdIDArr) > 0){
        		$iAdID = implode(',' , array_unique($iAdIDArr));
        	}
        	 
        }
        if (! empty($aParam['iAdID'])) {
        	$aWhere['iAdID'] = $aParam['iAdID'];
        }
        if($iAdID != '') {
        	$aWhere['iAdID IN'] = $iAdID;
        }
        if (! empty($aParam['iPayStatus']) && $aParam['iPayStatus'] != '') {
        	$aWhere['iPayStatus'] = $aParam['iPayStatus'];
        }
        if (! empty($aParam['iStatus']) && $aParam['iStatus'] != '') {
        	$aWhere['iStatus'] = $aParam['iStatus'];
        }
        
        $aList = Model_AdMedia::getList($aWhere, $iPage);
        
        $iAdIDArr = $useridArr = array();
        foreach ($aList['aList'] as $val) {
        	if($val['iUserID'] != '') {
        		$useridArr[] = $val['iUserID'];
        	}
        	if ($val['iAdID'] != '') {
        		$iAdIDArr[] = $val['iAdID'];
        	}
        }
        if (count($useridArr) > 0) {
        	$whereU = array();
        	$whereU['iUserID IN'] = implode(',' , array_unique($useridArr));
        	$userData = Model_User::getAll(array('where' => $whereU));

        	if (count($userData) > 0) {
        		$data = array();
        		foreach ($userData as $val) {
        			$row = array();
        			$row['iUserID'] = $val['iUserID'];
        			$row['sRealName'] = $val['sRealName'];
        			$data[$val['iUserID']] = $row;
        		}
        		$this->assign('aData', $data);
        	}        	
        }
        $data = array();
        if (count($iAdIDArr) > 0) {
        	$whereAd = array();
        	$whereAd['iAdID IN'] = implode(',' , array_unique($iAdIDArr));
        	$AdData = Model_Ad::getAll(array('where' => $whereAd));

        	if (count($AdData) > 0) {
        		
        		foreach ($AdData as $val) {
        			$row = array();
        			$row['iAdID'] = $val['iAdID'];
        			$row['sAdName'] = $val['sAdName'];
        			$row['iMediaType'] = $val['iMediaType'];
        			$data[$val['iAdID']] = $row;
        		}
        		$this->assign('AdData', $data);
        	}        	
        }
        foreach ($aList['aList'] as $key => $val) {
        	$iMediaType = '';
        	if (count($data) > 0 && isset($data[$val['iAdID']]['iMediaType']) && $data[$val['iAdID']]['iMediaType'] != '') {
        		$iMediaType = $data[$val['iAdID']]['iMediaType'];
        	}
        	
        	$aTitle = array(
        		Model_Media::TYPE_WEIXIN => array(
        			'1' => '单图文报价',
        			'2' => '第一条报价',
        			'3' => '第二条报价',
        			'4' => '其它位置价'
        		),
        		Model_Media::TYPE_FRIEND => array(
        			'1' => '转发报价',
        			'2' => '直发报价'
        		),
        		Model_Media::TYPE_WEIBO => array(
        			'1' => '转发报价',
        			'2' => '直发报价'
        		)
        	);
        	if ($iMediaType != '') {
        		$aList['aList'][$key]['iPos'] = $aTitle[$iMediaType][$val['iPos']];
        	}
        	
        	$sMediaName = '';
        	$row_Media = Model_Media::getDetail($val['iMediaID']);
        	if (isset($row_Media)) {
        		$sMediaName = $row_Media['sMediaName'];
        	}
        	$aList['aList'][$key]['iMediaID'] = $sMediaName;
        	
        	if ($val['iPayStatus'] == '0') {
        		$aList['aList'][$key]['iPayStatus'] = '未支付';
        	} elseif ($val['iPayStatus'] == '1') {
        		$aList['aList'][$key]['iPayStatus'] = '已支付';
        	}
        	
        	if ($val['iStatus'] == '1') {
        		$aList['aList'][$key]['iStatus'] = '等待接单';
        	} elseif ($val['iStatus'] == '2') {
        		$aList['aList'][$key]['iStatus'] = '等待执行';
        	} elseif ($val['iStatus'] == '3') {
        		$aList['aList'][$key]['iStatus'] = '执行中';
        	} elseif ($val['iStatus'] == '4') {
        		$aList['aList'][$key]['iStatus'] = '已完成';
        	} elseif ($val['iStatus'] == '5') {
        		$aList['aList'][$key]['iStatus'] = '拒绝接单';
        	}
        	
        }
        
        $this->assign('aList', $aList);
        $this->assign('aParam', $aParam);
    }

    /**
     * 资源订单修改
     */
    public function editAction()
    {
        if ($this->_request->isPost()) {
            $aAdMedia = $this->_checkData('update');
            if (empty($aAdMedia)) {
                return null;
            }
            $aAdMedia['iAutoID'] = intval($this->getParam('iAutoID'));
            $aOldAdMedia = Model_AdMedia::getDetail($aAdMedia['iAutoID']);
            if (empty($aOldAdMedia)) {
                return $this->showMsg('资源订单不存在！', false);
            }
            if (1 == Model_AdMedia::updData($aAdMedia)) {
                return $this->showMsg('资源订单更新成功！', true);
            } else {
                return $this->showMsg('资源订单更新失败！', false);
            }
        } else {
            $iAdMediaID = intval($this->getParam('id'));
            $aAdMedia = Model_AdMedia::getDetail($iAdMediaID);
            
            //广告
            $aAd = Model_Ad::getDetail($aAdMedia['iAdID']);
            $this->assign('aAd', $aAd);
            //资源
            $aMedia = Model_Media::getDetail($aAdMedia['iMediaID']);
            $this->assign('aMedia', $aMedia);
            //用户
            $aUser = Model_User::getDetail($aAdMedia['iUserID']);
            $this->assign('aUser', $aUser);
            //广告位
            $aTitle = array(
            	Model_Media::TYPE_WEIXIN => array(
            		'1' => '单图文报价',
            		'2' => '第一条报价',
            		'3' => '第二条报价',
            		'4' => '其它位置价'
            	),
            	Model_Media::TYPE_FRIEND => array(
            		'1' => '转发报价',
            		'2' => '直发报价'
            	),
            	Model_Media::TYPE_WEIBO => array(
            		'1' => '转发报价',
            		'2' => '直发报价'
            	)
            );
            if ($aAd['iMediaType'] != '') {
            	$iPos = $aTitle[$aAd['iMediaType']];
            	$this->assign('aPos', $iPos);
            }
            $this->assign('aAdMedia', $aAdMedia);
        }
    }

    /**
     * 增加资源订单
     */
    public function addAction()
    {
        if ($this->_request->isPost()) {
            $aAdMedia = $this->_checkData('add');
            if (empty($aAdMedia)) {
                return null;
            }
            if (Model_AdMedia::addData($aAdMedia) > 0) {
                return $this->showMsg('资源订单增加成功！', true);
            } else {
                return $this->showMsg('资源订单增加失败！', false);
            }
        }
    }
    
    /**
     * 导出
     */
    public function explodeAction(){
    	$uWhere = array();
    	$aParam = $this->getParams();
    	$userid = '';
    	if (! empty($aParam['sRealName'])) {
    		$uWhere['sRealName LIKE'] = '%'.$aParam['sRealName'].'%';
    		 
    		$data_user = Model_User::getAll(array('where' => $uWhere));
    	
    		$useridArr = array();
    		foreach ($data_user as $val) {
    			if($val['iUserID'] != '') {
    				$useridArr[] = $val['iUserID'];
    			}
    		}
    		if(count($useridArr) > 0){
    			$userid = implode(',' , array_unique($useridArr));
    		}
    		 
    	}
    	
    	$aWhere = array();
    	if (! empty($aParam['iUserID'])) {
    		$aWhere['iUserID'] = $aParam['iUserID'];
    	}
    	if($userid != '') {
    		$aWhere['iUserID IN'] = $userid;
    	}

    	if (! empty($aParam['iPayment']) && $aParam['iPayment'] != '') {
    		$aWhere['iPayment'] = $aParam['iPayment'];
    	}
    	if (! empty($aParam['iSource']) && $aParam['iSource'] != '') {
    		$aWhere['iSource'] = $aParam['iSource'];
    	}
    	if (! empty($aParam['sRealName']) && $aParam['sRealName'] != '') {
    		$aWhere['sRealName LIKE'] = '%'.$aParam['sRealName'].'%';
    	}
    	if (! empty($aParam['iPayType']) && $aParam['iPayType'] != '') {
    		$aWhere['iPayType'] = $aParam['iPayType'];
    	}
    	if (! empty($aParam['iPayStatus']) && $aParam['iPayStatus'] != '') {
    		$aWhere['iPayStatus'] = $aParam['iPayStatus'];
    	}
    	
    	$aList = Model_AdMedia::getAll(array('where' , $aWhere));
    	
    	header("Content-type:application/vnd.ms-excel");
    	header("Content-Disposition:filename=资源订单_".date('Y-m-d' , time()).".xls");
    	$str_explode = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'><html xmlns='http://www.w3.org/1999/xhtml'><head><meta http-equiv='Content-Type' content='text/html; charset=UTF-8' /><title>导出</title><style>td{text-align:center;font-size:12px;font-family:Arial, Helvetica, sans-serif;border:#1C7A80 1px solid;color:#152122;width:100px;}table,tr{border-style:none;}.title{background:#7DDCF0;color:#FFFFFF;font-weight:bold;}</style></head><body>";
    	
    	$str_explode .= '<table cellspacing="0" cellpadding="3" rules="rows" border="1" id="" style="border-style:None;width:100%;border-collapse:collapse;">
							<tr>
								<th scope="col">用户名称/th>
								<th scope="col">广告名称</th>
								<th scope="col">资源媒体名称</th>
								<th scope="col">广告位</th>
								<th scope="col">价格</th>
								<th scope="col">执行时间</th>
								<th scope="col">支付状态</th>
								<th scope="col">状态</th>
								<th scope="col">时间</th>
							</tr>';
    	
    	foreach ($aList as $key => $val) {
    		$aAd = Model_Ad::getDetail($val['iAdID']);
    		//资源
    		$aMedia = Model_Media::getDetail($val['iMediaID']);
    		$sMediaName = $aMedia['sMediaName'];
    		//用户
    		$aUser = Model_User::getDetail($val['iUserID']);
    		$sEmail = $aUser['sEmail'];
    		//广告位
    		$aTitle = array(
    			Model_Media::TYPE_WEIXIN => array(
    				'1' => '单图文报价',
    				'2' => '第一条报价',
    				'3' => '第二条报价',
    				'4' => '其它位置价'
    			),
    			Model_Media::TYPE_FRIEND => array(
    				'1' => '转发报价',
    				'2' => '直发报价'
    			),
    			Model_Media::TYPE_WEIBO => array(
    				'1' => '转发报价',
    				'2' => '直发报价'
    			)
    		);
    		$iPos = '';
    		if ($aAd['iMediaType'] != '') {
    			$iPos = $aTitle[$aAd['iMediaType']][$val['iPos']];
    		}
    		
    		//支付状态
    		$iPayStatusName = '';
    		if ($val['iPayStatus'] == '0') { 
    			$iPayStatusName = '未支付';
    		} elseif ($val['iPayStatus'] == '1') {
    			$iPayStatusName = '已支付';
    		}
    		$iMoney = $val['iMoney'];
    		$iPlanTime = '';
    		if ($val['iPlanTime'] != '' && $val['iPlanTime'] > 0) {
    			$iPlanTime = date('Y-m-d H:i' , $val['iPlanTime']);
    		}
    		//状态
    		$iStatusName = '';
    		if ($val['iStatus'] == '1') { 
    			$iStatusName = '等待接单';
    		} elseif ($val['iStatus'] == '2') {
    			$iStatusName = '等待执行';
    		} elseif ($val['iStatus'] == '3') {
    			$iStatusName = '执行中';
    		} elseif ($val['iStatus'] == '4') {
    			$iStatusName = '已完成';
    		} elseif ($val['iStatus'] == '5') {
    			$iStatusName = '拒绝接单';
    		}
    		$iCreateTime = date('Y-m-d H:i:s' , $val['iCreateTime']);
    		
    		$str_explode .= '<tr>
								<td align="left">'.$sEmail.'</td>
								<td align="left">'.$aAd['sAdName'].'</td>
								<td align="left">'.$sMediaName.'</td>
								<td align="left">'.$iPos.'</td>
								<td align="left">'.$iMoney.'</td>
								<td align="left">'.$iPlanTime.'</td>
								<td align="left">'.$iPayStatusName.'</td>
								<td align="left">'.$iStatusName.'</td>
								<td align="left">'.$iCreateTime.'</td>
							</tr>';
    	}
    	$str_explode .= '</table>';
    	
    	$str_explode .= "</body></html>";
    	
    	echo $str_explode;
    	
    }

    /**
     * 请求数据检测
     *
     * @return mixed
     */
    public function _checkData($sType = 'add')
    {
        $iAdID = $this->getParam('iAdID');
        $iUserID = $this->getParam('iUserID');
        $iMediaID = $this->getParam('iMediaID');
        $iPos = $this->getParam('iPos');
        $iMoney = $this->getParam('iMoney');
        $iPlanTime = $this->getParam('iPlanTime');
        if($iPlanTime != '') {
        	$iPlanTime = strtotime($iPlanTime);
        }
        $iPayStatus = $this->getParam('iPayStatus');
        $iStatus = $this->getParam('iStatus');
        $iUpdateTime = time();
        
        $aRow = array(
            'iAdID' => $iAdID,
            'iUserID' => $iUserID,
            'iMediaID' => $iMediaID,
            'iPos' => $iPos,
            'iMoney' => $iMoney,
            'iPlanTime' => $iPlanTime,
            'iPayStatus' => $iPayStatus,
            'iStatus' => $iStatus,
            'iUpdateTime' => $iUpdateTime
        );
        
        return $aRow;
    }
}