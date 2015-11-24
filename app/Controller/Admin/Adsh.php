<?php

class Controller_Admin_Adsh extends Controller_Admin_Base
{

    /**
     * 广告删除
     */
    public function delAction()
    {
        $iAdID = intval($this->getParam('id'));
        $iRet = Model_Ad::delData($iAdID);
        if ($iRet == 1) {
            return $this->showMsg('广告删除成功！', true);
        } else {
            return $this->showMsg('广告删除失败！', false);
        }
    }

    /**
     * 广告列表
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
        if (! empty($aParam['sAdName']) && $aParam['sAdName'] != '') {
        	$aWhere['sAdName LIKE'] = '%'.$aParam['sAdName'].'%';
        }
        if (! empty($aParam['iMediaType']) && $aParam['iMediaType'] != '') {
        	$aWhere['iMediaType'] = $aParam['iMediaType'];
        }
        if (! empty($aParam['iAdType']) && $aParam['iAdType'] != '') {
        	$aWhere['iAdType'] = $aParam['iAdType'];
        }
        if (! empty($aParam['iPayStatus']) && $aParam['iPayStatus'] != '') {
        	$aWhere['iPayStatus'] = $aParam['iPayStatus'];
        }
        if (! empty($aParam['iStatus']) && $aParam['iStatus'] != '') {
        	$aWhere['iStatus'] = $aParam['iStatus'];
        }else{
        	$aWhere['iStatus IN'] = '1,2,3,4,5';
        }
        
        $aList = Model_Ad::getList($aWhere, $iPage,'iStatus asc,iCreateTime desc,iPayStatus asc');
        
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
        
        foreach ($aList['aList'] as $key => $val) {

        	//媒体类型
			$aList['aList'][$key]['iMoney']=Model_AdMedia::getDetail($val['iAdID']) ['iMoney'] ;
        	if ($val['iMediaType'] == '1') {
        		$aList['aList'][$key]['iMediaType'] = '公众号';
        	} elseif($val['iMediaType'] == '2') {
        		$aList['aList'][$key]['iMediaType'] = '朋友圈';       		
        	} elseif($val['iMediaType'] == '3') {
        		$aList['aList'][$key]['iMediaType'] = '新浪微博';        		
        	} elseif($val['iMediaType'] == '4') {
        		$aList['aList'][$key]['iMediaType'] = '新闻论坛';        		
        	}
        	//广告类型
        	if ($val['iAdType'] == '1') {
        		$aList['aList'][$key]['iAdType'] = '硬广';
        	} elseif($val['iAdType'] == '2') {
        		$aList['aList'][$key]['iAdType'] = '软广';
        	} elseif($val['iAdType'] == '3') {
        		$aList['aList'][$key]['iAdType'] = '全部';
        	}
        	//支付状态
        	if ($val['iPayStatus'] == '0') {
        		$aList['aList'][$key]['iPayStatus'] = '未付款';
        	} elseif($val['iPayStatus'] == '1') {
        		$aList['aList'][$key]['iPayStatus'] = '已付款';
        	}
        	//状态
        	if ($val['iStatus'] == '1') {
        		$aList['aList'][$key]['iStatus'] = '待审核';
        	} elseif($val['iStatus'] == '2') {
        		$aList['aList'][$key]['iStatus'] = '审核通过';
        	} elseif($val['iStatus'] == '3') {
        		$aList['aList'][$key]['iStatus'] = '审核未通过';
        	} elseif($val['iStatus'] == '4') {
        		$aList['aList'][$key]['iStatus'] = '完成';
        	} elseif($val['iStatus'] == '5') {
        		$aList['aList'][$key]['iStatus'] = '未填写完成';
        	}
        	//城市
        	if ($val['sCityID'] != '') {
        		$data_City = Model_City::getAll(array('where' => array('iCityID IN' => $val['sCityID'])));
        		$sCityID = '';
        		if (count($data_City) > 0) {
	        		foreach ($data_City as $val_City) {
	        			$sCityID .= ','.$val_City['sCityName'];
	        		}
        		}
        		$aList['aList'][$key]['sCityID'] = $sCityID;
        	}
        	//媒体分类
        	if ($val['sCatID'] != '') {
        		$data_Cat = Model_Domain::getAll(array('where' => array('iAutoID IN' => $val['sCatID'])));
        		$sCatID = '';
        		if (count($data_Cat) > 0) {
	        		foreach ($data_Cat as $val_City) {
	        			$sCatID .= ','.$val_City['sName'];
	        		}
        		}
        		$aList['aList'][$key]['sCatID'] = $sCatID;
        	}
        }
        
        $this->assign('aList', $aList);
        $this->assign('aParam', $aParam);
    }
    
    /**
     * 广告信息
     */
    public function showAction()
    {
    	$iAdID = intval($this->getParam('id'));
    	$aAd = Model_Ad::getDetail($iAdID);
		
    	//类别
    	$aCat = '';
    	if ($aAd['sCatID'] != '') {
    		$rowCat = Model_Domain::getAll(array('where' => array('iAutoID IN' => $aAd['sCatID'])));
    		if (count($rowCat) > 0) {
    			foreach ($rowCat as $val) {
    				if ($val['sName'] != '') {
    					$aCat .= ','.$val['sName'];
    				}
    			}
    		}
    	}
    	$this->assign('aCat', $aCat);
    	//城市
    	$aCityName = '';
    	if ($aAd['sCityID'] != '') {
    		$rowCity = Model_City::getAll(array('where' => array('iCityID IN' => $aAd['sCityID'])));
    		if (count($rowCity) > 0) {
    			foreach ($rowCity as $val) {
    				if ($val['sName'] != '') {
    					$aCityName .= ','.$val['sCityName'];
    				}
    			}
    		}
    	}
    	$this->assign('aCityName', $aCityName);
    			
    	$iType = $aAd['iMediaType'];
    	$iTypeName = Model_Media::$aType[$aAd['iMediaType']];
    	$this->assign('iTypeName', $iTypeName);
    	
    	if ($aAd['iAdType'] == '1') {
    		$this->assign('iAdType', '软广');
    	} elseif ($aAd['iAdType'] == '2') {
    		$this->assign('iAdType', '硬广');
    	} elseif ($aAd['iAdType'] == '3') {
    		$this->assign('iAdType', '全部');
    	}
    	
    	if ($aAd['iStatus'] == '1') {
    		$this->assign('iStatus', '待审核');
    	} elseif ($aAd['iStatus'] == '2') {
    		$this->assign('iStatus', '审核通过');
    	} elseif ($aAd['iStatus'] == '3') {
    		$this->assign('iStatus', '审核未通过');
    	} elseif ($aAd['iStatus'] == '4') {
    		$this->assign('iStatus', '完成');
    	} elseif ($aAd['iStatus'] == '5') {
    		$this->assign('iStatus', '未填写完成');
    	}
    	
        $aUser = Model_User::getDetail($aAd['iUserID']);
    	$this->assign('aUser', $aUser);
    	$this->assign('aAd', $aAd);
    	
    	//资源广告内容
    	if ($aAd['iMediaType'] == Model_Media::TYPE_WEIXIN) {
    		$rowAd = Model_AdWeixin::getDetail($aAd['iAdID']);
    		$this->assign('type_weixin', Model_Media::TYPE_WEIXIN);
    		$iPosID = $rowAd['iAdPos'];
    	} elseif ($aAd['iMediaType'] == Model_Media::TYPE_FRIEND) {
    		$rowAd = Model_AdFriend::getDetail($aAd['iAdID']);
    		$this->assign('type_friend', Model_Media::TYPE_FRIEND);
    		$iPosID = $rowAd['iAdPos'];
    	} elseif ($aAd['iMediaType'] == Model_Media::TYPE_WEIBO) {
    		$rowAd = Model_AdWeibo::getDetail($aAd['iAdID']);
    		$this->assign('type_weibo', Model_Media::TYPE_WEIBO);
    		$iPosID = $rowAd['iAdPos'];
    	} elseif ($aAd['iMediaType'] == Model_Media::TYPE_NEWS) {
    		$rowAd = Model_AdNews::getDetail($aAd['iAdID']);
    		$this->assign('type_news', Model_Media::TYPE_NEWS);
    		$iPosID = $rowAd['iAdPos'];
    	}
    	
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
    		$iPos = $aTitle[$aAd['iMediaType']][$iPosID];
    		$this->assign('aPos', $iPos);
    	}
    	
    	$this->assign('rowAd', $rowAd);
    	
    	
    	$aAdMedia = Model_AdMedia::getAll(array('where' => array('iAdID' => $aAd['iAdID'])));
    	
    	foreach ($aAdMedia as $key => $val) {
    		//资源
    		$aMedia = Model_Media::getDetail($val['iMediaID']);
    		$aAdMedia[$key]['sMediaName'] = $aMedia['sMediaName'];
    		//用户
    		$aUser = Model_User::getDetail($val['iUserID']);
    		$aAdMedia[$key]['sEmail'] = $aUser['sEmail'];
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
    			$iPos = $aTitle[$aAd['iMediaType']][$val['iPos']];
    			$aAdMedia[$key]['iPosName'] = $iPos;
    		}
    		
    		//支付状态
    		if ($val['iPayStatus'] == '0') { 
    			$aAdMedia[$key]['iPayStatusName'] = '未支付';
    		} elseif ($val['iPayStatus'] == '1') {
    			$aAdMedia[$key]['iPayStatusName'] = '已支付';
    		}
    		
    		//状态
    		if ($val['iStatus'] == '1') { 
    			$aAdMedia[$key]['iStatusName'] = '等待接单';
    		} elseif ($val['iStatus'] == '2') {
    			$aAdMedia[$key]['iStatusName'] = '等待执行';
    		} elseif ($val['iStatus'] == '3') {
    			$aAdMedia[$key]['iStatusName'] = '执行中';
    		} elseif ($val['iStatus'] == '4') {
    			$aAdMedia[$key]['iStatusName'] = '已完成';
    		} elseif ($val['iStatus'] == '5') {
    			$aAdMedia[$key]['iStatusName'] = '拒绝接单';
    		}

    	}
    	
    	
    	$this->assign('aAdMedia', $aAdMedia);
    }
    
    /**
     * 导出
     */
    public function adexplodeAction() {
		$iAdID = intval($this->getParam('id'));
    	$aAd = Model_Ad::getDetail($iAdID);
		
    	//类别
    	$aCat = '';
    	if ($aAd['sCatID'] != '') {
    		$rowCat = Model_Domain::getAll(array('where' => array('iAutoID IN' => $aAd['sCatID'])));
    		if (count($rowCat) > 0) {
    			foreach ($rowCat as $val) {
    				if ($val['sName'] != '') {
    					$aCat .= ','.$val['sName'];
    				}
    			}
    		}
    	}
    	$this->assign('aCat', $aCat);
    	//城市
    	$aCityName = '';
    	if ($aAd['sCityID'] != '') {
    		$rowCity = Model_City::getAll(array('where' => array('iCityID IN' => $aAd['sCityID'])));
    		if (count($rowCity) > 0) {
    			foreach ($rowCity as $val) {
    				if ($val['sName'] != '') {
    					$aCityName .= ','.$val['sCityName'];
    				}
    			}
    		}
    	}
    	$this->assign('aCityName', $aCityName);
    			
    	$iType = $aAd['iMediaType'];
    	$iTypeName = Model_Media::$aType[$aAd['iMediaType']];
    	$this->assign('iTypeName', $iTypeName);
    	
    	if ($aAd['iAdType'] == '1') {
    		$this->assign('iAdType', '软广');
    	} elseif ($aAd['iAdType'] == '2') {
    		$this->assign('iAdType', '硬广');
    	} elseif ($aAd['iAdType'] == '3') {
    		$this->assign('iAdType', '全部');
    	}
    	
    	if ($aAd['iStatus'] == '1') {
    		$this->assign('iStatus', '待审核');
    	} elseif ($aAd['iStatus'] == '2') {
    		$this->assign('iStatus', '审核通过');
    	} elseif ($aAd['iStatus'] == '3') {
    		$this->assign('iStatus', '审核未通过');
    	} elseif ($aAd['iStatus'] == '4') {
    		$this->assign('iStatus', '完成');
    	} elseif ($aAd['iStatus'] == '5') {
    		$this->assign('iStatus', '未填写完成');
    	}
    	
        $aUser = Model_User::getDetail($aAd['iUserID']);
    	$this->assign('aUser', $aUser);
    	$this->assign('aAd', $aAd);
    	
    	//资源广告内容
    	if ($aAd['iMediaType'] == Model_Media::TYPE_WEIXIN) {
    		$rowAd = Model_AdWeixin::getDetail($aAd['iAdID']);
    		$this->assign('type_weixin', Model_Media::TYPE_WEIXIN);
    		$iPosID = $rowAd['iAdPos'];
    	} elseif ($aAd['iMediaType'] == Model_Media::TYPE_FRIEND) {
    		$rowAd = Model_AdFriend::getDetail($aAd['iAdID']);
    		$this->assign('type_friend', Model_Media::TYPE_FRIEND);
    		$iPosID = $rowAd['iAdPos'];
    	} elseif ($aAd['iMediaType'] == Model_Media::TYPE_WEIBO) {
    		$rowAd = Model_AdWeibo::getDetail($aAd['iAdID']);
    		$this->assign('type_weibo', Model_Media::TYPE_WEIBO);
    		$iPosID = $rowAd['iAdPos'];
    	} elseif ($aAd['iMediaType'] == Model_Media::TYPE_NEWS) {
    		$rowAd = Model_AdNews::getDetail($aAd['iAdID']);
    		$this->assign('type_news', Model_Media::TYPE_NEWS);
    		$iPosID = $rowAd['iAdPos'];
    	}
    	
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
    		$iPos = $aTitle[$aAd['iMediaType']][$iPosID];
    		$this->assign('aPos', $iPos);
    	}
    	
    	$this->assign('rowAd', $rowAd);
    	
    	
    	$aAdMedia = Model_AdMedia::getAll(array('where' => array('iAdID' => $aAd['iAdID'])));
    	
    	foreach ($aAdMedia as $key => $val) {
    		//资源
    		$aMedia = Model_Media::getDetail($val['iMediaID']);
    		$aAdMedia[$key]['sMediaName'] = $aMedia['sMediaName'];
    		//用户
    		$aUser = Model_User::getDetail($val['iUserID']);
    		$aAdMedia[$key]['sEmail'] = $aUser['sEmail'];
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
    			$iPos = $aTitle[$aAd['iMediaType']][$val['iPos']];
    			$aAdMedia[$key]['iPosName'] = $iPos;
    		}
    		
    		//支付状态
    		if ($val['iPayStatus'] == '0') { 
    			$aAdMedia[$key]['iPayStatusName'] = '未支付';
    		} elseif ($val['iPayStatus'] == '1') {
    			$aAdMedia[$key]['iPayStatusName'] = '已支付';
    		}
    		
    		//状态
    		if ($val['iStatus'] == '1') { 
    			$aAdMedia[$key]['iStatusName'] = '等待接单';
    		} elseif ($val['iStatus'] == '2') {
    			$aAdMedia[$key]['iStatusName'] = '等待执行';
    		} elseif ($val['iStatus'] == '3') {
    			$aAdMedia[$key]['iStatusName'] = '执行中';
    		} elseif ($val['iStatus'] == '4') {
    			$aAdMedia[$key]['iStatusName'] = '已完成';
    		} elseif ($val['iStatus'] == '5') {
    			$aAdMedia[$key]['iStatusName'] = '拒绝接单';
    		}

    	}   	
    	
    	$this->assign('aAdMedia', $aAdMedia);
		
		header("Content-type:application/vnd.ms-excel");
    	header("Content-Disposition:filename=资源订单_".date('Y-m-d' , time()).".xls");
    	$str_explode = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'><html xmlns='http://www.w3.org/1999/xhtml'><head><meta http-equiv='Content-Type' content='text/html; charset=UTF-8' /><title>导出</title><style>td{text-align:center;font-size:12px;font-family:Arial, Helvetica, sans-serif;border:#1C7A80 1px solid;color:#152122;width:100px;}table,tr{border-style:none;}.title{background:#7DDCF0;color:#FFFFFF;font-weight:bold;}</style></head><body>";
    	
    	$str_explode .= '<table cellspacing="0" cellpadding="3" rules="rows" border="1" id="" style="border-style:None;width:100%;border-collapse:collapse;">
							<tr>
								<td>用户名称</td>
								<td>'.$aUser['sRealName'].'</td>
							</tr>
							<tr>
								<td>广告名称</td>
								<td>'.(isset($aAd['sAdName'])?$aAd['sAdName']:'').'</td>
							</tr>
							<tr>
								<td>最小投放预算</td>
								<td>'.(isset($aAd['iPlanMinMoney'])?$aAd['iPlanMinMoney']:'').'</td>
							</tr>
							<tr>
								<td>最大投放预算</td>
								<td>'.(isset($aAd['iPlanMaxMoney'])?$aAd['iPlanMaxMoney']:'').'</td>
							</tr>
							<tr>
								<td>投放时间</td>
								<td>'.(isset($aAd['iPlanTime'])?$aAd['iPlanTime']:'').'</td>
							</tr>
							<tr>
								<td>媒体类型</td>
								<td>'.$iTypeName.'</td>
							</tr>
							<tr>
								<td>广告类型</td>
								<td>'.$iAdType.'</td>
							</tr>
							<tr>
								<td>媒体分类</td>
								<td>'.$aCat.'</td>
							</tr>
							<tr>
								<td>所在城市</td>
								<td>'.$aCityName.'</td>
							</tr>
							<tr>
								<td>总价</td>
								<td>'.(isset($aAd['iTotalMoney'])?$aAd['iTotalMoney']:'').'</td>
							</tr>
							<tr>
								<td>支付类型</td>
								<td>'.(isset($aAd['iPayStatus'])&&$aAd['iPayStatus']=='0' ? '未付款' : '已付款').'</td>
							</tr>';
    		if ($aAd['iMediaType'] == Model_Media::TYPE_WEIXIN) {
			$str_explode .= '<tr>
								<td>广告位</td>
								<td>'.$aPos.'</td>
							</tr>
							<tr>
								<td>显示时间</td>
								<td>'.date('Y-m-d H:i:s' , $rowAd['iShowTime']).'</td>
							</tr>
							<tr>
								<td>导入URL</td>
								<td>'.$rowAd['sImportUrl'].'</td>
							</tr>
							<tr>
								<td>上传Word</td>
								<td>'.$rowAd['sWordFile'].'</td>
							</tr>
							<tr>
								<td>标题</td>
								<td>'.$rowAd['sTitle'].'</td>
							</tr>
							<tr>
								<td>作者</td>
								<td>'.$rowAd['sAuthor'].'</td>
							</tr>
							<tr>
								<td>封面图片</td>
								<td>'.(isset($rowAd['sCoverImg'])?Util_Uri::getDFSViewURL($rowAd['sCoverImg'], 130, 130):'').'</td>
							</tr>
							<tr>
								<td>摘要</td>
								<td>'.$rowAd['sAbstract'].'</td>
							</tr>
							<tr>
								<td>内容</td>
								<td>'.$rowAd['sContent'].'</td>
							</tr>
							<tr>
								<td>原链接</td>
								<td>'.$rowAd['sOriginalUrl'].'</td>
							</tr>';
			} elseif ($aAd['iMediaType'] == Model_Media::TYPE_FRIEND) {
				$str_explode .= '<tr>
								<td>投放形式</td>
								<td>'.$aPos.'</td>
							</tr>
							<tr>
								<td>转发链接</td>
								<td>'.$rowAd['sForwardUrl'].'</td>
							</tr>
							<tr>
								<td>转发文字</td>
								<td>'.$rowAd['sForwardText'].'</td>
							</tr>
							<tr>
								<td>转发配图</td>
								<td>';
									if ($rowAd['sForwardImg'] != '') {
										$arr = explode(',' , $rowAd['sForwardImg']);
										if (count($arr) > 0) {
											foreach ($arr as $v) {
												$str_explode .= '<img src="'.Util_Uri::getDFSViewURL($v, 130, 130).'" width="130" />';
											}
										}else{
											$str_explode .= '<img src="'.Util_Uri::getDFSViewURL($rowAd['sOriginalUrl'], 130, 130).'" width="130" />';
										}
									}
								$str_explode .= '</td>
							</tr>';
		} elseif ($aAd['iMediaType'] == Model_Media::TYPE_WEIBO) {
			$str_explode .= '<tr>
								<td>投放形式</td>
								<td>'.$aPos.'</td>
							</tr>
							<tr>
								<td>转发链接</td>
								<td>'.$rowAd['sForwardUrl'].'</td>
							</tr>
							<tr>
								<td>转发文字</td>
								<td>'.$rowAd['sForwardText'].'</td>
							</tr>
							<tr>
								<td>转发配图</td>
								<td>';
								if ($rowAd['sForwardImg'] != '') {
									$arr = explode(',' , $rowAd['sForwardImg']);
									if (count($arr) > 0) {
										foreach ($arr as $v) {
											$str_explode .= '<img src="'.Util_Uri::getDFSViewURL($v, 130, 130).'" width="130" />';
										}
									}else{
										$str_explode .= '<img src="'.Util_Uri::getDFSViewURL($rowAd['sForwardImg'], 130, 130).'" width="130" />';
									}
								}
								$str_explode .= '</td>
							</tr>';
		} elseif ($aAd['iMediaType'] == Model_Media::TYPE_NEWS) {
			$str_explode .= '<tr>
								<td>投放形式</td>
								<td>'.$aPos.'</td>
							</tr>
							<tr>
								<td>标题</td>
								<td>'.$rowAd['sTitle'].'</td>
							</tr>
							<tr>
								<td>内容</td>
								<td>'.$rowAd['sContent'].'</td>
							</tr>';
		}
			$str_explode .= '<tr>
								<td>状态</td>
								<td>'.$iStatus.'</td>
							</tr>
							<tr>
								<td>资源列表</td>
								<td>
									<table cellspacing="0" cellpadding="3" rules="rows" border="1" id="" style="border-style:None;width:100%;border-collapse:collapse;">
										<tr>
											<td style="border-right: 1px solid #000000;border-bottom: 1px solid #000000;">#</td>
											<td style="border-right: 1px solid #000000;border-bottom: 1px solid #000000;">用户名称</td>
											<td style="border-right: 1px solid #000000;border-bottom: 1px solid #000000;">媒体名称</td>
											<td style="border-right: 1px solid #000000;border-bottom: 1px solid #000000;">广告位</td>
											<td style="border-right: 1px solid #000000;border-bottom: 1px solid #000000;">价格</td>
											<td style="border-right: 1px solid #000000;border-bottom: 1px solid #000000;">执行时间</td>
											<td style="border-right: 1px solid #000000;border-bottom: 1px solid #000000;">支付状态</td>
											<td style="border-bottom: 1px solid #000000;">状态</td>
										</tr>';
										foreach ($aAdMedia as $keyAdMedia => $valAdMedia) {
											$iAutoID = $valAdMedia['iAutoID'];
											$sEmail = $valAdMedia['sEmail'];
											$sMediaName = $valAdMedia['sMediaName'];
											$iPosName = $valAdMedia['iPosName'];
											$iMoney = $valAdMedia['iMoney'];
											$iPlanTime = '';
											if ($valAdMedia['iPlanTime'] != '' && $valAdMedia['iPlanTime'] > 0){
												$iPlanTime = date('Y-m-d' , $valAdMedia['iPlanTime']);
											}
											$iPayStatusName = $valAdMedia['iPayStatusName'];
											$iStatusName = $valAdMedia['iStatusName'];
											$str_explode .= '<tr>
														<td style="border-right: 1px solid #000000;border-bottom: 1px solid #000000;">'.$iAutoID.'</td>
														<td style="border-right: 1px solid #000000;border-bottom: 1px solid #000000;">'.$sEmail.'</td>
														<td style="border-right: 1px solid #000000;border-bottom: 1px solid #000000;">'.$sMediaName.'</td>
														<td style="border-right: 1px solid #000000;border-bottom: 1px solid #000000;">'.$iPosName.'</td>
														<td style="border-right: 1px solid #000000;border-bottom: 1px solid #000000;">'.$iMoney.'</td>
														<td style="border-right: 1px solid #000000;border-bottom: 1px solid #000000;">'.$iPlanTime.'</td>
														<td style="border-right: 1px solid #000000;border-bottom: 1px solid #000000;">'.$iPayStatusName.'</td>
														<td style="border-bottom: 1px solid #000000;">'.$iStatusName.'</td>
													</tr>';
										}
					$str_explode .= '</table>
								</td>
							</tr>
							<tr>
								<td>更新时间</td>
								<td>'.date('Y-m-d H:i:s' , $aAd['iUpdateTime']).'</td>
							</tr>
							<tr>
								<td>创建时间</td>
								<td>'.date('Y-m-d H:i:s' , $aAd['iCreateTime']).'</td>
							</tr>';
		$str_explode .= '</table>';
    	
    	$str_explode .= "</body></html>";
    	
    	echo $str_explode;
	}

    /**
     * 广告修改
     */
    public function editAction()
    {
        if ($this->_request->isPost()) {
            $aAd = $this->_checkData('update');
            if (empty($aAd)) {
                return null;
            }
            $aAd['iAdID'] = intval($this->getParam('iAdID'));
            $aOldAd = Model_Ad::getDetail($aAd['iAdID']);
            if (empty($aOldAd)) {
                return $this->showMsg('广告不存在！', false);
            }
            if (1 == Model_Ad::updData($aAd)) {
            	if ($aOldAd['iStatus'] == Model_Ad::STATUS_WAIT_APPROVE ||$aOldAd['iStatus'] == Model_Ad:: STATUS_APPROVE_NO&& $aAd['iStatus'] == Model_Ad::STATUS_APPROVE_OK) {
            		$aMediaList = Model_AdMedia::getMediaByAd($aAd['iAdID'], Model_AdMedia::STATUS_CHECK, 1);
        			foreach ($aMediaList as $aMedia) {
        			    Model_AdMedia::updStatus($aMedia['iAutoID'], Model_AdMedia::STATUS_RECEIVE);
        			}

            		// 邮件通知
            		$sTitle = Model_Kv::getValue('media_receive_order_email_title');
            		$sContent = Model_Kv::getValue('media_receive_order_email_content');

            		// 短信通知
            		$iTempID = Util_Common::getConf(3, 'aSmsTempID');
            		foreach ($aMediaList as $aMedia) {
                		$aUser = Model_User::getDetail($aMedia['iMUserID']);
						Util_Mail::send($aUser['sEmail'], $sTitle, $sContent, array($aMedia['iMoney']));
                		Util_Sms::sendTemplateSms($aUser['sMobile'], array($aMedia['iMoney']), $iTempID);
                		// echo $sTitle, "\n", $sContent, "\n", $aUser['sEmail'], $aUser['sMobile'], $iTempID;
            		}
            	}
                return $this->showMsg('广告更新成功！', true);
            } else {
                return $this->showMsg('广告更新失败！', false);
            }
        } else {
            $iAdID = intval($this->getParam('id'));
            $aAd = Model_Ad::getDetail($iAdID);
			$aAd['iMoney']=Model_AdMedia::getDetail($aAd['iAdID']) ['iMoney'] ;
            $aAd['sCatID'] = ((isset($aAd['sCatID']) && sCatID != '') ? explode(',' , $aAd['sCatID']) : '');
            $aAd['sCityID'] = ((isset($aAd['sCityID']) && sCatID != '') ? explode(',' , $aAd['sCityID']) : '');
            
            $iType = $aAd['iMediaType'];
            $aCategory = array();
            switch ($iType) {
            	case Model_Media::TYPE_WEIXIN:
            		$aCategory = Model_Domain::getOption(Model_Domain::TYPE_MEDIA_WEIXIN_CATEGORY);
            		break;
            	case Model_Media::TYPE_FRIEND:
            		$aCategory = Model_Domain::getOption(Model_Domain::TYPE_MEDIA_FRIEND_CATEGORY);
            		break;
            	case Model_Media::TYPE_WEIBO:
            		$aCategory = Model_Domain::getOption(Model_Domain::TYPE_MEDIA_WEIBO_CATEGORY);
            		break;
            	case Model_Media::TYPE_NEWS:
            		$aCategory = Model_Domain::getOption(Model_Domain::TYPE_MEDIA_NEWS_CATEGORY);
            		break;
            }

            $aCity = Model_City::getPairCitys(Model_City::TYPE_FRONT);

            $aUser = Model_User::getDetail($aAd['iUserID']);
            $this->assign('aUser', $aUser);
            $this->assign('aAd', $aAd);
            $this->assign('aCategory', $aCategory);
            $this->assign('aCity', $aCity);
        }
    }

    /**
     * 增加广告
     */
    public function addAction()
    {
        if ($this->_request->isPost()) {
            $aAd = $this->_checkData('add');
            if (empty($aAd)) {
                return null;
            }
            if (Model_Ad::addData($aAd) > 0) {
                return $this->showMsg('广告增加成功！', true);
            } else {
                return $this->showMsg('广告增加失败！', false);
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
        if (! empty($aParam['sAdName']) && $aParam['sAdName'] != '') {
        	$aWhere['sAdName LIKE'] = '%'.$aParam['sAdName'].'%';
        }
        if (! empty($aParam['iMediaType']) && $aParam['iMediaType'] != '') {
        	$aWhere['iMediaType'] = $aParam['iMediaType'];
        }
        if (! empty($aParam['iAdType']) && $aParam['iAdType'] != '') {
        	$aWhere['iAdType'] = $aParam['iAdType'];
        }
        if (! empty($aParam['iPayStatus']) && $aParam['iPayStatus'] != '') {
        	$aWhere['iPayStatus'] = $aParam['iPayStatus'];
        }
        if (! empty($aParam['iStatus']) && $aParam['iStatus'] != '') {
        	$aWhere['iStatus'] = $aParam['iStatus'];
        }else{
        	$aWhere['iStatus IN'] = '1,2,3,4,5';
        }
        
    	$aList = Model_Ad::getAll(array('where' , $aWhere));
    	
    	foreach ($aList['aList'] as $key => $val) {
    		//媒体类型
    		if ($val['iMediaType'] == '1') {
    			$aList['aList'][$key]['iMediaType'] = '公众号';
    		} elseif($val['iMediaType'] == '2') {
    			$aList['aList'][$key]['iMediaType'] = '朋友圈';
    		} elseif($val['iMediaType'] == '3') {
    			$aList['aList'][$key]['iMediaType'] = '新浪微博';
    		} elseif($val['iMediaType'] == '4') {
    			$aList['aList'][$key]['iMediaType'] = '新闻论坛';
    		}
    		//广告类型
    		if ($val['iAdType'] == '1') {
    			$aList['aList'][$key]['iAdType'] = '硬广';
    		} elseif($val['iAdType'] == '2') {
    			$aList['aList'][$key]['iAdType'] = '软广';
    		} elseif($val['iAdType'] == '3') {
    			$aList['aList'][$key]['iAdType'] = '全部';
    		}
    		//支付状态
    		if ($val['iPayStatus'] == '0') {
    			$aList['aList'][$key]['iPayStatus'] = '未付款';
    		} elseif($val['iPayStatus'] == '1') {
    			$aList['aList'][$key]['iPayStatus'] = '已付款';
    		}
    		//状态
    		if ($val['iStatus'] == '1') {
    			$aList['aList'][$key]['iStatus'] = '待审核';
    		} elseif($val['iStatus'] == '2') {
    			$aList['aList'][$key]['iStatus'] = '审核通过';
    		} elseif($val['iStatus'] == '3') {
    			$aList['aList'][$key]['iStatus'] = '审核未通过';
    		} elseif($val['iStatus'] == '4') {
    			$aList['aList'][$key]['iStatus'] = '完成';
    		} elseif($val['iStatus'] == '5') {
    			$aList['aList'][$key]['iStatus'] = '全未填写完成';
    		}
    		//城市
    		if ($val['sCityID'] != '') {
    			$data_City = Model_City::getAll(array('where' => array('iCityID IN' => $val['sCityID'])));
    			$sCityID = '';
    			if (count($data_City) > 0) {
    				foreach ($data_City as $val_City) {
    					$sCityID .= ','.$val_City['sCityName'];
    				}
    			}
    			$aList['aList'][$key]['sCityID'] = $sCityID;
    		}
    		//媒体分类
    		if ($val['sCatID'] != '') {
    			$data_Cat = Model_Domain::getAll(array('where' => array('iAutoID IN' => $val['sCatID'])));
    			$sCatID = '';
    			if (count($data_Cat) > 0) {
    				foreach ($data_Cat as $val_City) {
    					$sCatID .= ','.$val_City['sName'];
    				}
    			}
    			$aList['aList'][$key]['sCatID'] = $sCatID;
    		}
    		
    		//资源广告内容
    		$rowAd = array();
    		if ($val['iMediaType'] == Model_Media::TYPE_WEIXIN) {
    			$rowAd = Model_AdWeixin::getDetail($val['iAdID']);
    			$iPosID = $rowAd['iAdPos'];
    		} elseif ($val['iMediaType'] == Model_Media::TYPE_FRIEND) {
    			$rowAd = Model_AdFriend::getDetail($val['iAdID']);
    			$iPosID = $rowAd['iAdPos'];
    		} elseif ($val['iMediaType'] == Model_Media::TYPE_WEIBO) {
    			$rowAd = Model_AdWeibo::getDetail($val['iAdID']);
    			$iPosID = $rowAd['iAdPos'];
    		} elseif ($val['iMediaType'] == Model_Media::TYPE_NEWS) {
    			$rowAd = Model_AdNews::getDetail($val['iAdID']);
    			$iPosID = $rowAd['iAdPos'];
    		}
    		$aList['aList'][$key]['Media'] = $rowAd;
    		 
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
    		if ($val['iMediaType'] != '') {
    			$iPos = $aTitle[$val['iMediaType']][$iPosID];
    			$aList['aList'][$key]['sPos'] = $iPos;
    		}
    	}
    	
    	header("Content-type:application/vnd.ms-excel");
    	header("Content-Disposition:filename=广告_".date('Y-m-d' , time()).".xls");
    	$str_explode = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'><html xmlns='http://www.w3.org/1999/xhtml'><head><meta http-equiv='Content-Type' content='text/html; charset=UTF-8' /><title>导出</title><style>td{text-align:center;font-size:12px;font-family:Arial, Helvetica, sans-serif;border:#1C7A80 1px solid;color:#152122;width:100px;}table,tr{border-style:none;}.title{background:#7DDCF0;color:#FFFFFF;font-weight:bold;}</style></head><body>";
    	
    	$str_explode .= '<table cellspacing="0" cellpadding="3" rules="rows" border="1" id="" style="border-style:None;width:100%;border-collapse:collapse;">
							<tr>
								<th scope="col">用户名称/th>
								<th scope="col">广告名称</th>
								<th scope="col">最小投放预算</th>
								<th scope="col">最大投放预算</th>
								<th scope="col">投放时间</th>
								<th scope="col">媒体类型</th>
								<th scope="col">广告类型</th>
								<th scope="col">媒体分类</th>
								<th scope="col">所在城市</th>
								<th scope="col">总价</th>
								<th scope="col">支付状态</th>
								<th scope="col">状态</th>
								<th scope="col">时间</th>
							</tr>';
    	
    	foreach ($aList as $key => $val) {
    		$row = Model_User::getDetail($val['iUserID']);
    		$sUserRealName = ((isset($row) && $row['sRealName'] != '') ? $row['sRealName'] : '');
    		
    		$iCreateTime = date('Y-m-d H:i:s' , $val['iCreateTime']);
    		$iPlanTime = ((isset($val['iPlanTime']) && $val['iPlanTime'] != '') ? date('Y-m-d H:i:s' , $val['iPlanTime']) : '');
    		
    		$str_explode .= '<tr>
								<td align="left">'.$sUserRealName.'</td>
								<td align="left">'.$val['sAdName'].'</td>
								<td align="left">'.$val['iPlanMinMoney'].'</td>
								<td align="left">'.$val['iPlanMaxMoney'].'</td>
								<td align="left">'.$val['iPlanTime'].'</td>
								<td align="left">'.$val['iMediaType'].'</td>
								<td align="left">'.$val['iAdType'].'</td>
								<td align="left">'.$val['sCatID'].'</td>
								<td align="left">'.$val['sCityID'].'</td>
								<td align="left">'.$val['iTotalMoney'].'</td>
								<td align="left">'.$val['iPayStatus'].'</td>
								<td align="left">'.$val['iStatus'].'</td>
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
        $iStatus = $this->getParam('iStatus');
        $iExplain = $this->getParam('iExplain');

        $iUpdateTime = time();
        
        $aRow = array(
            'iStatus' => $iStatus,
            'iUpdateTime' => $iUpdateTime ,
            'iExplain' => $iExplain ,
        );
        
        return $aRow;
    }
}