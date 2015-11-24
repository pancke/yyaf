<?php

/**
 * 自媒体管理
 */
class Controller_Admin_Media extends Controller_Admin_Base
{

    /**
     * 自媒体删除
     */
    public function delAction()
    {
        $iMediaID = intval($this->getParam('id'));
        $iRet = Model_Media::delData($iMediaID);
        if ($iRet == 1) {
            return $this->showMsg('自媒体删除成功！', true);
        } else {
            return $this->showMsg('自媒体删除失败！', false);
        }
    }

    /**
     * 自媒体列表
     */
    public function listAction()
    {
        $iPage = intval($this->getParam('page'));
        $aWhere = array();
        
        $aParam = $this->getParams();
        if (! empty($aParam['iUserID'])) {
        	$aWhere['iUserID'] = $aParam['iUserID'];
        }
        if (! empty($aParam['iMediaType'])) {
        	$aWhere['iMediaType'] = $aParam['iMediaType'];
        }
        if (! empty($aParam['sMediaName'])) {
        	$aWhere['sMediaName LIKE'] = '%'.$aParam['sMediaName'].'%';
        }
        if (! empty($aParam['iStatus'])) {
        	$aWhere['iStatus'] = $aParam['iStatus'];
        }else{
        	$aWhere['iStatus IN'] = '1,2,3';
        }
        if (! empty($aParam['iPut'])) {
            $aWhere['iPut'] = $aParam['iPut'];
        }else{
            $aWhere['iPut IN'] = '0,1';
        }
        
        $aList = Model_Media::getList($aWhere, $iPage,'iStatus desc ,iMediaID desc');
        
        $useridArr = array();
        foreach ($aList['aList'] as $key => $val){
        	if($val['iUserID'] > 0){
        		$useridArr[] = $val['iUserID'];
        	}
        }
        
        if(count($useridArr) > 0){
        	$sUserid 	= '';
        	$useridArr 	= array_unique($useridArr);
        	$sUserid 	= implode(',' , $useridArr);
        	$sUserid   	= trim($sUserid , ',');
        	$userData = Model_User::getAll(array('where' => array('iUserID IN' => $sUserid)));

        	if(count($userData) > 0){
        		$data = array();
        		foreach ($userData as $key => $value){
        			$data[$value['iUserID']] = array('iUserID' => $value['iUserID'] , 'sEmail' => $value['sEmail']);
        		}
        		
        		$this->assign('userData', $data);
        	}        	
        }
        
        
        $this->assign('aList', $aList);
        $this->assign('MediaType' , Model_Media::$aType);
        $this->assign('aParam', $aParam);
    }

    /**
     * 自媒体修改
     */
    public function editAction()
    {
        if ($this->_request->isPost()) {
            $aMedia = $this->_checkData('update');
            if (empty($aMedia)) {
                return null;
            }
            //标签
            $sTagID = $aMedia['sTagID'];
            unset($aMedia['sTagID']);
            //所属类目
            $sCategoryID = $aMedia['sCategoryID'];
            unset($aMedia['sCategoryID']);
            //行业圈子
            $sCricleID = $aMedia['sCricleID'];
            unset($aMedia['sCricleID']);
            //城市
            $sCityID = $aMedia['sCityID'];
            unset($aMedia['sCityID']);
            
            $aMedia['iMediaID'] = intval($this->getParam('iMediaID'));
            $aOldCity = Model_Media::getDetail($aMedia['iMediaID']);
            if (empty($aOldCity)) {
                return $this->showMsg('自媒体不存在！', false);
            }
            if (1 == Model_Media::updData($aMedia)) {
            	//标签
            	if($sTagID != ''){
            		$sTagID = explode(',' , $sTagID);
            		$data_MediaTag = Model_MediaTag::getAll(array('where' => array('iMediaID' => $aMedia['iMediaID'] , 'iStatus' => 1)));


            		foreach ($data_MediaTag as $val){
            			$val['iStatus'] = 0;
            			Model_MediaTag::updData($val);
            		}

            		$add = array();
            		$add['iMediaID'] = $aMedia['iMediaID'];
            		$add['iStatus'] = 1;
            		$add['iCreateTime'] = time();
            		$add['iUpdateTime'] = time();
            		foreach ($sTagID as $val){
            			$add['iTagID'] = $val;
            			Model_MediaTag::addData($add);
            		}
            	}
            	//所属类目
            	if($sCategoryID != ''){
            		$sCategoryID = explode(',' , $sCategoryID);
            		$data_MediaCategory = Model_MediaCategory::getAll(array('where' => array('iMediaID' => $aMedia['iMediaID'] , 'iStatus' => 1)));
            		foreach ($data_MediaCategory as $val){
            			$val['iStatus'] = 0;
            			Model_MediaCategory::updData($val);
            		}
            		$add = array();
            		$add['iMediaID'] = $aMedia['iMediaID'];
            		$add['iStatus'] = 1;
            		$add['iCreateTime'] = time();
            		$add['iUpdateTime'] = time();
            		foreach ($sCategoryID as $val){
            		$add['iCategoryID'] = $val;
            			Model_MediaCategory::addData($add);
            		}
            	
            	}
            	//行业圈子
            	if($sCricleID != ''){
            		$sCricleID = explode(',' , $sCricleID);
            		$data_MediaCricle = Model_MediaCricle::getAll(array('where' => array('iMediaID' => $aMedia['iMediaID'] , 'iStatus' => 1)));
            		foreach ($data_MediaCricle as $val){
            			$val['iStatus'] = 0;
            			Model_MediaCricle::updData($val);
            		}
            		$add = array();
            		$add['iMediaID'] = $aMedia['iMediaID'];
            		$add['iStatus'] = 1;
            		$add['iCreateTime'] = time();
            		$add['iUpdateTime'] = time();
            		foreach ($sCricleID as $val){
            			$add['iCricleID'] = $val;
            			Model_MediaCricle::addData($add);
            		}
            	
            	}
            	//城市
            	if($sCityID != ''){
            		$sCityID = explode(',' , $sCityID);
            		$data_MediaCity = Model_MediaCity::getAll(array('where' => array('iMediaID' => $aMedia['iMediaID'] , 'iStatus' => 1)));
            		foreach ($data_MediaCity as $val){
            			$val['iStatus'] = 0;
            			Model_MediaCity::updData($val);
            		}
            		$add = array();
            		$add['iMediaID'] = $aMedia['iMediaID'];
            		$add['iCityID'] = $val;
            		$add['iStatus'] = 1;
            		$add['iCreateTime'] = time();
            		$add['iUpdateTime'] = time();
            		foreach ($sCityID as $val){
            			$add['iCityID'] = $val;
            			Model_MediaCity::addData($add);
            		}
            	
            	}
            	 $this->assign('add',$add);
                return $this->showMsg('自媒体信息更新成功！', true);
            } else {
                return $this->showMsg('自媒体信息更新失败！', false);
            }
        } else {
            $iMediaID = intval($this->getParam('id'));
            $aMedia = Model_Media::getDetail($iMediaID);
            
            //属性
            if($aMedia['iMediaType'] == Model_Media::TYPE_WEIXIN){// 微信公众号
            	$this->assign('aAttribute', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_WEIXIN_ATTRIBUTE));
            }else if($aMedia['iMediaType'] == Model_Media::TYPE_FRIEND){// 微信朋友圈
            	$this->assign('aAttribute', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_FRIEND_ATTRIBUTE));
            }else if($aMedia['iMediaType'] == Model_Media::TYPE_WEIBO){// 新浪微博
            	$this->assign('aAttribute', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_WEIBO_ATTRIBUTE));
            }else if($aMedia['iMediaType'] == Model_Media::TYPE_NEWS){// 新闻&论坛
            	$this->assign('aAttribute', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_NEWS_ATTRIBUTE));
            }
            //分类
            if($aMedia['iMediaType'] == Model_Media::TYPE_WEIXIN){// 微信公众号
            	$this->assign('aTypeInfo', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_WEIXIN_CATEGORY));
            }else if($aMedia['iMediaType'] == Model_Media::TYPE_FRIEND){// 微信朋友圈
            	$this->assign('aTypeInfo', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_FRIEND_CATEGORY));
            }else if($aMedia['iMediaType'] == Model_Media::TYPE_WEIBO){// 新浪微博
            	$this->assign('aTypeInfo', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_WEIBO_CATEGORY));
            }else if($aMedia['iMediaType'] == Model_Media::TYPE_NEWS){// 新闻&论坛
            	$this->assign('aTypeInfo', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_NEWS_CATEGORY));
            }
            //合作等级
            if($aMedia['iMediaType'] == Model_Media::TYPE_WEIXIN){// 微信公众号
            	$this->assign('aCooperateLevel', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_WEIXIN_COOPERATELEVEL));
            }else if($aMedia['iMediaType'] == Model_Media::TYPE_FRIEND){// 微信朋友圈
            	$this->assign('aCooperateLevel', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_FRIEND_COOPERATELEVEL));
            }else if($aMedia['iMediaType'] == Model_Media::TYPE_WEIBO){// 新浪微博
            	$this->assign('aCooperateLevel', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_WEIBO_COOPERATELEVEL));
            }else if($aMedia['iMediaType'] == Model_Media::TYPE_NEWS){// 新闻&论坛
            	$this->assign('aCooperateLevel', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_NEWS_COOPERATELEVEL));
            }
            //行业圈子
            if($aMedia['iMediaType'] == Model_Media::TYPE_WEIXIN){// 微信公众号
            	$this->assign('aIndustryCircle', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_WEIXIN_INDUSTRY));
            }else if($aMedia['iMediaType'] == Model_Media::TYPE_FRIEND){// 微信朋友圈
            	$this->assign('aIndustryCircle', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_FRIEND_INDUSTRY));
            }else if($aMedia['iMediaType'] == Model_Media::TYPE_WEIBO){// 新浪微博
            	$this->assign('aIndustryCircle', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_WEIBO_INDUSTRY));
            }else if($aMedia['iMediaType'] == Model_Media::TYPE_NEWS){// 新闻&论坛
            	$this->assign('aIndustryCircle', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_NEWS_INDUSTRY));
            }
            //认证
            if($aMedia['iMediaType'] == Model_Media::TYPE_WEIXIN){// 微信公众号
            	$this->assign('aVerifyState', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_WEIXIN_VERIFY));
            }else if($aMedia['iMediaType'] == Model_Media::TYPE_WEIBO){// 新浪微博
            	$this->assign('aVerifyState', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_WEIBO_VERIFY));
            }
            //标签
            if($aMedia['iMediaType'] == Model_Media::TYPE_WEIXIN){// 微信公众号
            	$this->assign('aTag', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_WEIXIN_TAG));
            }else if($aMedia['iMediaType'] == Model_Media::TYPE_FRIEND){// 微信朋友圈
            	$this->assign('aTag', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_FRIEND_TAG));
            }else if($aMedia['iMediaType'] == Model_Media::TYPE_WEIBO){// 新浪微博
            	$this->assign('aTag', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_WEIBO_TAG));
            }else if($aMedia['iMediaType'] == Model_Media::TYPE_NEWS){// 新闻&论坛
            	$this->assign('aTag', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_NEWS_TAG));
            }
            
            $aUser = Model_User::getDetail($aMedia['iUserID']);
            
            //分类
            $dataCategory = Model_MediaCategory::getAll(array('where' => array('iMediaID' => $aMedia['iMediaID'] , 'iStatus' => 1)));
            $aCategoryID = array();
            foreach ($dataCategory as $key => $val){
            	$aCategoryID[] = $val['iCategoryID'];
            }
            if(count($aCategoryID) > 0){
            	$aMedia['aCategoryID'] = array_unique($aCategoryID);
            }
            //标签
            $dataTag = Model_MediaTag::getAll(array('where' => array('iMediaID' => $aMedia['iMediaID'] , 'iStatus' => 1)));
            $aTagID = array();
            foreach ($dataTag as $key => $val){
            	$aTagID[] = $val['iTagID'];
            }
            if(count($aTagID) > 0){
            	$aMedia['iTagID'] = array_unique($aTagID);
            }
            //城市
            $dataCity = Model_MediaCity::getAll(array('where' => array('iMediaID' => $aMedia['iMediaID'] , 'iStatus' => 1)));
            $aCityID = array();
            foreach ($dataCity as $key => $val){
            	$aCityID[] = $val['iCityID'];
            }
            if(count($aCityID) > 0){
            	$aMedia['aCityID'] = array_unique($aCityID);
            }
            //价格
//             $dataPrice = Model_MediaPrice::getAll(array('where' => array('iMediaID' => $aMedia['iMediaID'] , 'iStatus' => 1)));
//             if(count($dataPrice) > 0){
//             	$aMedia['dataPrice'] = $dataPrice;
//             }
            //圈子
            $dataCricle = Model_MediaCricle::getAll(array('where' => array('iMediaID' => $aMedia['iMediaID'] , 'iStatus' => 1)));
            $aCricleID = array();
            foreach ($dataCricle as $key => $val){
            	$aCricleID[] = $val['iCricleID'];
            }
            if(count($aCricleID) > 0){
            	$aMedia['aCricleID'] = array_unique($aCricleID);
            }
            
            $aMedia['aTypeInfo'] = explode(',', $aMedia['sTypeInfo']);
            $aMedia['aCooperateLevelInfo'] = explode(',', $aMedia['sCooperateLevelInfo']);
            
            $this->assign('aMedia', $aMedia);
            $this->assign('aUser', $aUser);
        }
        $this->assign('MediaType' , Model_Media::$aType);
        
        $aRecommend = Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_RECOMMEND);
        $this->assign('aRecommend', $aRecommend);
        //后台管理用户
        $adminData = Model_Admin::getAll(array('where' => array('iStatus' => 1)));
        $this->assign('adminData', $adminData);
        //城市
        $this->assign('aCity', Model_City::getPairCitys());
    }

    /**
     * 增加自媒体
     */
    public function addAction()
    {
        if ($this->_request->isPost()) {
            $aMedia = $this->_checkData('add');
            if (empty($aMedia)) {
                return null;
            }
//            if (Model_Media::getCityByName($aMedia['sCityName'])) {
//                return $this->showMsg('自媒体已经存在！', false);
//            }
            //标签
            $sTagID = $aMedia['sTagID'];
            unset($aMedia['sTagID']);
            //所属类目
            $sCategoryID = $aMedia['sCategoryID'];
            unset($aMedia['sCategoryID']);
            //行业圈子
            $sCricleID = $aMedia['sCricleID'];
            unset($aMedia['sCricleID']);
            //城市
            $sCityID = $aMedia['sCityID'];
            unset($aMedia['sCityID']);
            
            if (Model_Media::addData($aMedia) > 0) {
            	//标签
            	if($sTagID != ''){
            		$sTagID = explode(',' , $sTagID);
            		$add = array();
            		$add['iMediaID'] = $aMedia['iMediaID'];
            		$add['iStatus'] = 1;
            		$add['iCreateTime'] = time();
            		$add['iUpdateTime'] = time();
            		foreach ($sTagID as $val){
            			$add['iTagID'] = $val;
            			Model_MediaTag::addData($add);
            		}
            	
            	}
            	//所属类目
            	if($sCategoryID != ''){
            		$sCategoryID = explode(',' , $sCategoryID);
            		$add = array();
            		$add['iMediaID'] = $aMedia['iMediaID'];
            		$add['iStatus'] = 1;
            		$add['iCreateTime'] = time();
            		$add['iUpdateTime'] = time();
            		foreach ($sCategoryID as $val){
            			$add['iCategoryID'] = $val;
            			Model_MediaCategory::addData($add);
            		}
            		 
            	}
            	//行业圈子
            	if($sCricleID != ''){
            		$sCricleID = explode(',' , $sCricleID);
            		$add = array();
            		$add['iMediaID'] = $aMedia['iMediaID'];
            		$add['iStatus'] = 1;
            		$add['iCreateTime'] = time();
            		$add['iUpdateTime'] = time();
            		foreach ($sCricleID as $val){
            			$add['iCricleID'] = $val;
            			Model_MediaCricle::addData($add);
            		}
            		 
            	}
            	//城市
            	if($sCityID != ''){
            		$sCityID = explode(',' , $sCityID);
            		$add = array();
            		$add['iMediaID'] = $aMedia['iMediaID'];
            		$add['iStatus'] = 1;
            		$add['iCreateTime'] = time();
            		$add['iUpdateTime'] = time();           		
            		foreach ($sCityID as $val){
            			$add['iCityID'] = $val;
            			Model_MediaCity::addData($add);
            		}
            		 
            	}
            	
                return $this->showMsg('自媒体增加成功！', true);
            } else {
                return $this->showMsg('自媒体增加失败！', false);
            }
        }
        $iMediaType = $this->getParam('iMediaType');
        $this->assign('iMediaType',$iMediaType);
        //属性
        if($iMediaType == Model_Media::TYPE_WEIXIN){// 微信公众号
        	$this->assign('aAttribute', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_WEIXIN_ATTRIBUTE));
        }else if($iMediaType == Model_Media::TYPE_FRIEND){// 微信朋友圈
        	$this->assign('aAttribute', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_FRIEND_ATTRIBUTE));
        }else if($iMediaType == Model_Media::TYPE_WEIBO){// 新浪微博
        	$this->assign('aAttribute', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_WEIBO_ATTRIBUTE));
        }else if($iMediaType == Model_Media::TYPE_NEWS){// 新闻&论坛
        	$this->assign('aAttribute', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_NEWS_ATTRIBUTE));
        }
        //分类
        if($iMediaType == Model_Media::TYPE_WEIXIN){// 微信公众号
        	$this->assign('aTypeInfo', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_WEIXIN_CATEGORY));
        }else if($iMediaType == Model_Media::TYPE_FRIEND){// 微信朋友圈
        	$this->assign('aTypeInfo', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_FRIEND_CATEGORY));
        }else if($iMediaType == Model_Media::TYPE_WEIBO){// 新浪微博
        	$this->assign('aTypeInfo', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_WEIBO_CATEGORY));
        }else if($iMediaType == Model_Media::TYPE_NEWS){// 新闻&论坛
        	$this->assign('aTypeInfo', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_NEWS_CATEGORY));
        }
        //合作等级
        if($iMediaType == Model_Media::TYPE_WEIXIN){// 微信公众号
        	$this->assign('aCooperateLevel', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_WEIXIN_COOPERATELEVEL));
        }else if($iMediaType == Model_Media::TYPE_FRIEND){// 微信朋友圈
        	$this->assign('aCooperateLevel', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_FRIEND_COOPERATELEVEL));
        }else if($iMediaType == Model_Media::TYPE_WEIBO){// 新浪微博
        	$this->assign('aCooperateLevel', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_WEIBO_COOPERATELEVEL));
        }else if($iMediaType == Model_Media::TYPE_NEWS){// 新闻&论坛
        	$this->assign('aCooperateLevel', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_NEWS_COOPERATELEVEL));
        }
        //行业圈子
        if($iMediaType == Model_Media::TYPE_WEIXIN){// 微信公众号
        	$this->assign('aIndustryCircle', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_WEIXIN_INDUSTRY));
        }else if($iMediaType == Model_Media::TYPE_FRIEND){// 微信朋友圈
        	$this->assign('aIndustryCircle', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_FRIEND_INDUSTRY));
        }else if($iMediaType == Model_Media::TYPE_WEIBO){// 新浪微博
        	$this->assign('aIndustryCircle', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_WEIBO_INDUSTRY));
        }else if($iMediaType == Model_Media::TYPE_NEWS){// 新闻&论坛
        	$this->assign('aIndustryCircle', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_NEWS_INDUSTRY));
        }
        //认证
        if($iMediaType == Model_Media::TYPE_WEIXIN){// 微信公众号
        	$this->assign('aVerifyState', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_WEIXIN_VERIFY));
        }else if($iMediaType == Model_Media::TYPE_WEIBO){// 新浪微博
        	$this->assign('aVerifyState', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_WEIBO_VERIFY));
        }
        //标签
        if($iMediaType == Model_Media::TYPE_WEIXIN){// 微信公众号
        	$this->assign('aTag', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_WEIXIN_TAG));
        }else if($iMediaType == Model_Media::TYPE_FRIEND){// 微信朋友圈
        	$this->assign('aTag', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_FRIEND_TAG));
        }else if($iMediaType == Model_Media::TYPE_WEIBO){// 新浪微博
        	$this->assign('aTag', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_WEIBO_TAG));
        }else if($iMediaType == Model_Media::TYPE_NEWS){// 新闻&论坛
        	$this->assign('aTag', Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_NEWS_TAG));
        }
        //资源推荐
        $aRecommend = Model_Domain::getPairDomain(Model_Domain::TYPE_MEDIA_RECOMMEND);
        $this->assign('aRecommend', $aRecommend);
        //后台管理用户
        $adminData = Model_Admin::getAll(array('where' => array('iStatus' => 1)));
        $this->assign('adminData', $adminData);
        //城市
        $this->assign('aCity', Model_City::getPairCitys());
    }

    /**
     * 更换自媒体
     */
    public function changeAction()
    {
        $iCityID = $this->getParam('id');
        $aCity = Model_Media::getDetail($iCityID);
        if (empty($aCity) || $aCity['iBackendShow'] == 0 || $aCity['iStatus'] == 0) {
            return $this->showMsg('自媒体不存在或未开放！', false);
        }
        $aUser = Model_Admin::getDetail($this->aCurrUser['iAdminID']);
        $aCityID = explode(',', $aUser['sCityID']);
        if ($aUser['sCityID'] != '-1' && !in_array($iCityID, $aCityID)) {
            return $this->showMsg('您没有访问该自媒体的权限，请联系管理员！', false);
        }
        Util_Cookie::set('city', $iCityID);
        return $this->showMsg('自媒体切换成功!', true);
    }
    
    /**
     * 导出资源数据
     */
    public function explodeAction() {
    	$iPage = intval($this->getParam('page'));
    	$aWhere = array();
    	
    	$aParam = $this->getParams();
    	if (! empty($aParam['iUserID'])) {
    		$aWhere['iUserID'] = $aParam['iUserID'];
    	}
    	if (! empty($aParam['iMediaType'])) {
    		$aWhere['iMediaType'] = $aParam['iMediaType'];
    	}
    	if (! empty($aParam['sMediaName'])) {
    		$aWhere['sMediaName LIKE'] = '%'.$aParam['sMediaName'].'%';
    	}
    	if (! empty($aParam['iStatus'])) {
    		$aWhere['iStatus'] = $aParam['iStatus'];
    	}else{
    		$aWhere['iStatus IN'] = '1,2';
    	}
    	
    	$aList = Model_Media::getAll(array('where' => $aWhere));
    	
    	header("Content-type:application/vnd.ms-excel");
    	header("Content-Disposition:filename=资源_".date('Y-m-d' , time()).".xls");
    	$str_explode = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'><html xmlns='http://www.w3.org/1999/xhtml'><head><meta http-equiv='Content-Type' content='text/html; charset=UTF-8' /><title>导出</title><style>td{text-align:center;font-size:12px;font-family:Arial, Helvetica, sans-serif;border:#1C7A80 1px solid;color:#152122;width:100px;}table,tr{border-style:none;}.title{background:#7DDCF0;color:#FFFFFF;font-weight:bold;}</style></head><body>";
    	
    	$str_explode .= '<table cellspacing="0" cellpadding="3" rules="rows" border="1" id="" style="border-style:None;width:100%;border-collapse:collapse;">
							<tr>
								<th scope="col">归属帐号</th>
								<th scope="col">媒体类型</th>
								<th scope="col">帐号名称</th>
								<th scope="col">公众号名</th>
								<th scope="col">粉丝数量</th>
								<th scope="col">粉丝截图</th>
								<th scope="col">头像</th>
								<th scope="col">二维码</th>
								<th scope="col">报价1</th>
								<th scope="col">报价2</th>
								<th scope="col">报价3</th>
								<th scope="col">报价4</th>
								<th scope="col">阅读量</th>
								<th scope="col">评分</th>
								<th scope="col">推荐级别</th>
								<th scope="col">属性</th>
								<th scope="col">微信链接地址</th>
								<th scope="col">认证状态</th>
								<th scope="col">简介</th>
								<th scope="col">用户所属类目</th>
								<th scope="col">合作等级</th>
								<th scope="col">认证信息介绍</th>
								<th scope="col">点击量</th>
								<th scope="col">被选择执行的数量</th>
								<th scope="col">文章发布数量</th>
								<th scope="col">文章点赞平均数</th>
								<th scope="col">分类</th>
								<th scope="col">城市</th>
								<th scope="col">圈子</th>
								<th scope="col">标签</th>
							</tr>';
    	foreach ($aList as $key => $val){
    		$id = $val['iMediaID'];
    		$row = Model_User::getDetail($val['iUserID']);
    		$sRealName = ((isset($row) && $row['sRealName'] != '') ? $row['sRealName'] : '');
    		$iMediaType = '';
    		if($val['iMediaType'] == '1') {
    			$iMediaType = '微信';
    		} elseif ($val['iMediaType'] == '2') {
    			$iMediaType = '微博';
    		} elseif ($val['iMediaType'] == '3') {
    			$iMediaType = '朋友圈';
    		} elseif ($val['iMediaType'] == '4') {
    			$iMediaType = '新闻';
    		}
    		$sMediaName = $val['sMediaName'];
    		$sOpenName = $val['sOpenName'];
    		$iFollowerNum = $val['iFollowerNum'];
    		$sFollowerImg = Util_Uri::getDFSViewURL($val['sFollowerImg'], 130, 130);
    		$sAvatar = Util_Uri::getDFSViewURL($val['sAvatar'], 130, 130);
    		$sQRCode = Util_Uri::getDFSViewURL($val['sQRCode'], 130, 130);
    		$iPrice5 = $val['iPrice5'];
    		$iPrice6= $val['iPrice6'];
    		$iPrice7 = $val['iPrice7'];
    		$iPrice8 = $val['iPrice8'];
    		$iReadNum = $val['iReadNum'];
    		$iScore = $val['iScore'];
    		$iRecommendLevel = $val['iRecommendLevel'];
    		$iPersonCharge = $val['iPersonCharge'];
    		$iAuditperson = $val['iAuditperson'];

    		$rowattr = Model_Domain::getDetail($val['iAttribute']);
    		$iAttribute = (isset($rowattr['sName']) ? $rowattr['sName'] : '');//属性
    		
    		$sWxLink = $val['sWxLink'];
    		
    		$rowver = Model_Domain::getDetail($val['iVerifyState']);
    		$iVerifyState = (isset($rowver['sName']) ? $rowver['sName'] : '');//认证
    		
    		$sIntroduction = $val['sIntroduction'];
    		$sTypeInfo = $val['sTypeInfo'];
    		
    		$sCooperateLevelInfo = '';
    		if($val['sCooperateLevelInfo'] != '') {
	    		$rowlevel = Model_Domain::getAll(array('where' => array('iAutoID IN' => $val['sCooperateLevelInfo'])));//合作等级
	    		if(isset($rowlevel) && count($rowlevel) > 0) {
	    			foreach ($rowlevel as $lrow) {
	    				$sCooperateLevelInfo .= ' '.$lrow['sName'];
	    			}
	    		}
    		}
    		
    		$sCertifiedText = $val['sCertifiedText'];
    		$iClickNumber = $val['iClickNumber'];
    		$iChoiceNumber = $val['iChoiceNumber'];
    		$iArticleNumber = $val['iArticleNumber'];
    		$iZambiaNumber = $val['iZambiaNumber'];
    		//分类
    		$data_cat = Model_MediaCategory::getAll(array('where' => array('iMediaID' => $val['iMediaID'] , 'iStatus' => 1)));
    		$aCategory = '';
    		if(isset($data_cat) && count($data_cat) > 0){
    			$iAutoID = array();
    			foreach ($data_cat as $val_cat){
    				$iAutoID[] = $val_cat['iCategoryID'];
    			}
    			if(count($iAutoID) > 0){
    				$rowcat = Model_Domain::getAll(array('where' => array('iAutoID IN' => implode(',',array_unique($iAutoID)) , 'iStatus' => '1')));
    				if(isset($rowcat) && count($rowcat) > 0){
    					foreach ($rowcat as $vcat){
    						$aCategory .= ','.$vcat['sName'];
    					}
    				}
    			}
    		}
    		//城市
    		$data_city = Model_MediaCity::getAll(array('where' => array('iMediaID' => $val['iMediaID'] , 'iStatus' => 1)));
    		$aCity = '';
    		if(isset($data_city) && count($data_city) > 0){
    			$iAutoID = array();
    			foreach ($data_city as $val_City){
    				$iAutoID[] = $val_City['iCityID'];
    			}
    			if(count($iAutoID) > 0){
    				$rowCity = Model_Domain::getAll(array('where' => array('iAutoID IN' => implode(',',array_unique($iAutoID)) , 'iStatus' => '1')));
    				if(isset($rowCity) && count($rowCity) > 0){
    					foreach ($rowCity as $vCity){
    						$aCity .= ','.$vCity['sName'];
    					}
    				}
    			}
    		}
    		//圈子
    		$data_cricle = Model_MediaCricle::getAll(array('where' => array('iMediaID' => $val['iMediaID'] , 'iStatus' => 1)));
    		$aCricle = '';
    		if(isset($data_cricle) && count($data_cricle) > 0){
    			$iAutoID = array();
    			foreach ($data_cricle as $val_Cricle){
    				$iAutoID[] = $val_Cricle['iCricleID'];
    			}
    			if(count($iAutoID) > 0){
    				$rowCricle = Model_Domain::getAll(array('where' => array('iAutoID IN' => implode(',',array_unique($iAutoID)) , 'iStatus' => '1')));
    				if(isset($rowCricle) && count($rowCricle) > 0){
    					foreach ($rowCricle as $vCricle){
    						$aCricle .= ','.$vCricle['sName'];
    					}
    				}
    			}
    		}
    		//标签
    		$data_tag = Model_MediaTag::getAll(array('where' => array('iMediaID' => $val['iMediaID'] , 'iStatus' => 1)));
    		$aTag = '';
    		if(isset($data_tag) && count($data_tag) > 0){
    			$iAutoID = array();
    			foreach ($data_tag as $val_Tag){
    				$iAutoID[] = $val_Tag['iTagID'];
    			}
    			if(count($iAutoID) > 0){
    				$rowTag = Model_Domain::getAll(array('where' => array('iAutoID IN' => implode(',',array_unique($iAutoID)) , 'iStatus' => '1')));
    				if(isset($rowTag) && count($rowTag) > 0){
    					foreach ($rowTag as $vTag){
    						$aTag .= ','.$vTag['sName'];
    					}
    				}
    			}
    		}
    		
    		$iCreateTime = date('Y-m-d H:i:s' , $val['iCreateTime']);
    		
    		$str_explode .= '<tr>
								<td align="left">'.$sRealName.'</td>
								<td align="left">'.$iMediaType.'</td>
								<td align="left">'.$sMediaName.'</td>
								<td align="left">'.$sOpenName.'</td>
								<td align="left">'.$iFollowerNum.'</td>
								<td align="left"><img src="'.$sFollowerImg.'" /></td>
								<td align="left"><img src="'.$sAvatar.'" /></td>
								<td align="left"><img src="'.$sQRCode.'" /></td>
								<td align="left">'.$iPrice5.'</td>
								<td align="left">'.$iPrice6.'</td>
								<td align="left">'.$iPrice7.'</td>
								<td align="left">'.$iPrice8.'</td>
								<td align="left">'.$iReadNum.'</td>
								<td align="left">'.$iScore.'</td>
								<td align="left">'.$iRecommendLevel.'</td>
								<td align="left">'.$iAttribute.'</td>
								<td align="left">'.$sWxLink.'</td>
								<td align="left">'.$iVerifyState.'</td>
								<td align="left">'.$sIntroduction.'</td>
								<td align="left">'.$sTypeInfo.'</td>
								<td align="left">'.$sCooperateLevelInfo.'</td>
								<td align="left">'.$sCertifiedText.'</td>
								<td align="left">'.$iClickNumber.'</td>
								<td align="left">'.$iChoiceNumber.'</td>
								<td align="left">'.$iArticleNumber.'</td>
								<td align="left">'.$iZambiaNumber.'</td>
								<td align="left">'.$aCategory.'</td>
								<td align="left">'.$aCity.'</td>
								<td align="left">'.$aCricle.'</td>
								<td align="left">'.$aTag.'</td>
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
        $sMediaName = $this->getParam('sMediaName');
        $sOpenName = $this->getParam('sOpenName');
        $iFollowerNum = $this->getParam('iFollowerNum');
        $sFollowerImg = $this->getParam('sFollowerImg');
        $sAvatar = $this->getParam('sAvatar');
        $sQRCode = $this->getParam('sQRCode');
        $iPrice1 = $this->getParam('iPrice1');
        $iPrice2 = $this->getParam('iPrice2');
        $iPrice3 = $this->getParam('iPrice3');
        $iPrice4 = $this->getParam('iPrice4');
        $iReadNum = $this->getParam('iReadNum');
        $iRecommendLevel = $this->getParam('iRecommendLevel');
        $iPersonCharge = $this->getParam('iPersonCharge');
        $iAuditperson = $this->getParam('iAuditperson');
        $iAttribute = $this->getParam('iAttribute');
        $sWxLink = $this->getParam('sWxLink');
        $iVerifyState = $this->getParam('iVerifyState');
        $sCertifiedText = $this->getParam('sCertifiedText');
        $sIntroduction = $this->getParam('sIntroduction');
        $iPrice5 = $this->getParam('iPrice5');
        $iPrice6 = $this->getParam('iPrice6');
        $iPrice7 = $this->getParam('iPrice7');
        $iPrice8 = $this->getParam('iPrice8');
        //标签
        $sTagID = $this->getParam('sTagID');
        if (empty($sTagID)){
        	$sTagID = '';
        }else{
        	$sTagID = join(',', $sTagID);
        }
        //分类
        $sCategoryID = $this->getParam('sCategoryID');
        if (empty($sCategoryID)){
        	$sCategoryID = '';
        }else{
        	$sCategoryID = join(',', $sCategoryID);
        }
        //合作等级
        $sCooperateLevelInfo = $this->getParam('sCooperateLevelInfo');
        if (empty($sCooperateLevelInfo)){
        	$sCooperateLevelInfo = '';
        }else{
        	$sCooperateLevelInfo = join(',', $sCooperateLevelInfo);
        }
        //圈子
        $sCricleID = $this->getParam('sCricleID');
        if (empty($sCricleID)){
        	$sCricleID = '';
        }else{
        	$sCricleID = join(',', $sCricleID);
        }
        //城市
        $sCityID = $this->getParam('sCityID');
        if (empty($sCityID)){
        	$sCityID = '';
        }else{
        	$sCityID = join(',', $sCityID);
        }
        
        $iClickNumber = $this->getParam('iClickNumber');
        $iExplain = $this->getParam('iExplain');
        $iRate = $this->getParam('iRate');
        $iChoiceNumber = $this->getParam('iChoiceNumber');
        $iArticleNumber = $this->getParam('iArticleNumber');
        $iZambiaNumber = $this->getParam('iZambiaNumber');
        $iRecommend = $this->getParam('iRecommend');
        if($iRecommend == '1') {
        	$iRecommend = '0';
        }else{
        	if ($iRecommend == 2) {
        		$iRecommend = time();
        	}
        }
        $iStatus = $this->getParam('iStatus');
        $iPut = $this->getParam('iPut');
        $iUpdateTime = time();

        $aRow = array(
		    'sMediaName' => $sMediaName,
		    'sOpenName' => $sOpenName,
		    'iFollowerNum' => $iFollowerNum,
		    'sFollowerImg' => $sFollowerImg,
		    'sAvatar' => $sAvatar,
		    'sQRCode' => $sQRCode,
		    'iPrice1' => $iPrice1,
		    'iPrice2' => $iPrice2,
		    'iPrice3' => $iPrice3,
		    'iPrice4' => $iPrice4,
		    'iReadNum' => $iReadNum,
		    'iRecommendLevel' => $iRecommendLevel,
		    'iPersonCharge' => $iPersonCharge,
		    'iAuditperson' => $iAuditperson,
		    'iAttribute' => $iAttribute,
		    'sWxLink' => $sWxLink,
		    'iVerifyState' => $iVerifyState,
		    'sCertifiedText' => $sCertifiedText,
		    'sIntroduction' => $sIntroduction,
		    'sTagID' => $sTagID,
		    'sCategoryID' => $sCategoryID,
		    'sCooperateLevelInfo' => $sCooperateLevelInfo,
		    'sCricleID' => $sCricleID,
		    'sCityID' => $sCityID,
		    'iClickNumber' => $iClickNumber,
		    'iChoiceNumber' => $iChoiceNumber,
		    'iArticleNumber' => $iArticleNumber,
		    'iZambiaNumber' => $iZambiaNumber,
		    'iRecommend' => $iRecommend,
		    'iStatus' => $iStatus,
        	'iUpdateTime' => $iUpdateTime,
            'iPrice5' => $iPrice5,
            'iPrice6' => $iPrice6,
            'iPrice7' => $iPrice7,
            'iPrice8' => $iPrice8,
            'iPut' => $iPut,
            'iExplain' => $iExplain,
            'iRate' => $iRate,
		);
        
        return $aRow;
    }
}