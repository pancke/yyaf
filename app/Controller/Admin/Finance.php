<?php

class Controller_Admin_Finance extends Controller_Admin_Base
{

    /**
     * 财务删除
     */
    public function delAction()
    {
        $iFinanceID = intval($this->getParam('id'));
        $iRet = Model_Finance::delData($iFinanceID);
        if ($iRet == 1) {
            return $this->showMsg('财务删除成功！', true);
        } else {
            return $this->showMsg('财务删除失败！', false);
        }
    }

    /**
     * 财务列表
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
        if ($aParam['iPayStatus'] != '') {
        	$aWhere['iPayStatus'] = $aParam['iPayStatus'];
        }
        
        $aList = Model_Finance::getList($aWhere, $iPage,'iCreateTime desc');
        
        $useridArr = array();
        foreach ($aList['aList'] as $val) {
        	if($val['iUserID'] != '') {
        		$useridArr[] = $val['iUserID'];
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
        
        $this->assign('aList', $aList);
        $this->assign('aParam', $aParam);
    }

    /**
     * 财务修改
     */
    public function editAction()
    {
        if ($this->_request->isPost()) {
            $aFinance = $this->_checkData('update');
            if (empty($aFinance)) {
                return null;
            }
            $aFinance['iAutoID'] = intval($this->getParam('iAutoID'));
            $aOldFinance = Model_Finance::getDetail($aFinance['iAutoID']);
            if (empty($aOldFinance)) {
                return $this->showMsg('财务不存在！', false);
            }
            if (1 == Model_Finance::updData($aFinance)) {
                return $this->showMsg('财务更新成功！', true);
            } else {
                return $this->showMsg('财务更新失败！', false);
            }
        } else {
            $iFinanceID = intval($this->getParam('id'));
            $aFinance = Model_Finance::getDetail($iFinanceID);
            
            $aUser = Model_User::getDetail($aFinance['iUserID']);
            $this->assign('aUser', $aUser);
            $this->assign('aFinance', $aFinance);
        }
    }

    /**
     * 增加财务
     */
    public function addAction()
    {
        if ($this->_request->isPost()) {
            $aFinance = $this->_checkData('add');
            if (empty($aFinance)) {
                return null;
            }
            if (Model_Finance::addData($aFinance) > 0) {
                return $this->showMsg('财务增加成功！', true);
            } else {
                return $this->showMsg('财务增加失败！', false);
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
    	
    	$aList = Model_Finance::getAll(array('where' , $aWhere));
    	
    	header("Content-type:application/vnd.ms-excel");
    	header("Content-Disposition:filename=财务_".date('Y-m-d' , time()).".xls");
    	$str_explode = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'><html xmlns='http://www.w3.org/1999/xhtml'><head><meta http-equiv='Content-Type' content='text/html; charset=UTF-8' /><title>导出</title><style>td{text-align:center;font-size:12px;font-family:Arial, Helvetica, sans-serif;border:#1C7A80 1px solid;color:#152122;width:100px;}table,tr{border-style:none;}.title{background:#7DDCF0;color:#FFFFFF;font-weight:bold;}</style></head><body>";
    	
    	$str_explode .= '<table cellspacing="0" cellpadding="3" rules="rows" border="1" id="" style="border-style:None;width:100%;border-collapse:collapse;">
							<tr>
								<th scope="col">用户名称/th>
								<th scope="col">收支情况</th>
								<th scope="col">事件来源</th>
								<th scope="col">申请人</th>
								<th scope="col">支付类型</th>
								<th scope="col">本次金额</th>
								<th scope="col">用户余额</th>
								<th scope="col">银行开户姓名</th>
								<th scope="col">开户银行</th>
								<th scope="col">支付帐号</th>
								<th scope="col">支付状态</th>
								<th scope="col">支付流水号</th>
								<th scope="col">备注</th>
								<th scope="col">时间</th>
							</tr>';
    	
    	foreach ($aList as $key => $val) {
    		$row = Model_User::getDetail($val['iUserID']);
    		$sUserRealName = ((isset($row) && $row['sRealName'] != '') ? $row['sRealName'] : '');
    		
    		$iPayment = ((isset($val['iPayment']) && $val['iPayment'] == '1') ? '收入' : '支出');
    		
    		$iSource = '';
    		if ($val['iSource'] == '1') {
    			$iSource = '自主充值';
    		} elseif ($val['iSource'] == '2') {
    			$iSource = '付款充值';
    		} elseif ($val['iSource'] == '3') {
    			$iSource = '拒单退款';
    		} elseif ($val['iSource'] == '4') {
    			$iSource = '取现';
    		} elseif ($val['iSource'] == '5') {
    			$iSource = '广告费用';
    		}
    		
    		$sRealName = $val['sRealName'];
    		
    		$iPayType = '';
    		if ($val['iPayType'] == '1') {
    			$iPayType = '支付宝';
    		} elseif ($val['iPayType'] == '2') {
    			$iPayType = '微信';
    		} elseif ($val['iPayType'] == '3') {
    			$iPayType = '银行卡';
    		}
    		
    		$iPayMoney = $val['iPayMoney'];
    		$iUserMoney = $val['iUserMoney'];
    		$sOpenName = $val['sOpenName'];
    		$sBankName = $val['sBankName'];
    		$sPayAccount = $val['sPayAccount'];
    		
    		$iPayStatus = ((isset($val['iPayStatus']) && $val['iPayStatus'] == '0' ? '未支付' : '已支付'));
    		
    		$sPayOrder = $val['sPayOrder'];
    		$sRemark = $val['sRemark'];
    		$iCreateTime = date('Y-m-d H:i:s' , $val['iCreateTime']);
    		
    		$str_explode .= '<tr>
								<td align="left">'.$sUserRealName.'</td>
								<td align="left">'.$iPayment.'</td>
								<td align="left">'.$iSource.'</td>
								<td align="left">'.$sRealName.'</td>
								<td align="left">'.$iPayType.'</td>
								<td align="left">'.$iPayMoney.'</td>
								<td align="left">'.$iUserMoney.'</td>
								<td align="left">'.$sOpenName.'</td>
								<td align="left">'.$sBankName.'</td>
								<td align="left">'.$sPayAccount.'</td>
								<td align="left">'.$iPayStatus.'</td>
								<td align="left">'.$sPayOrder.'</td>
								<td align="left">'.$sRemark.'</td>
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
        $iPayment = $this->getParam('iPayment');
        $iSource = $this->getParam('iSource');
        $sRealName = $this->getParam('sRealName');
        $iPayType = $this->getParam('iPayType');
        $iPayMoney = $this->getParam('iPayMoney');
        $iUserMoney = $this->getParam('iUserMoney');
        $sOpenName = $this->getParam('sOpenName');
        $sBankName = $this->getParam('sBankName');
        $sPayAccount = $this->getParam('sPayAccount');
        $iPayStatus = $this->getParam('iPayStatus');
        $sPayOrder = $this->getParam('sPayOrder');
        $sRemark = $this->getParam('sRemark');
        
        $iUpdateTime = time();
        
        $aRow = array(
            'iPayment' => $iPayment,
            'iSource' => $iSource,
            'sRealName' => $sRealName,
            'iPayType' => $iPayType,
            'iPayMoney' => $iPayMoney,
            'iUserMoney' => $iUserMoney,
            'sOpenName' => $sOpenName,
            'sBankName' => $sBankName,
            'sPayAccount' => $sPayAccount,
            'iPayStatus' => $iPayStatus,
            'sPayOrder' => $sPayOrder,
            'sRemark' => $sRemark,
            'iUpdateTime' => $iUpdateTime
        );
        
        return $aRow;
    }
}