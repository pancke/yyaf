<?php

class Controller_Admin_Crawlwxarticle extends Controller_Admin_Base
{

    /**
     * 抓取微信文章删除
     */
    public function delAction()
    {
        $iWeixinID = intval($this->getParam('id'));
        $iRet = Model_CrawlWeixinArticle::delData($iWeixinID);
        if ($iRet == 1) {
            return $this->showMsg('微信文章删除成功！', true);
        } else {
            return $this->showMsg('微信文章删除失败！', false);
        }
    }

    /**
     * 微信文章列表
     */
    public function listAction()
    {
        $iPage = intval($this->getParam('page'));
        $aWhere = array();
        
        $aParam = $this->getParams();
        if (! empty($aParam['iWeixinID'])) {
        	$aWhere['iWeixinID'] = $aParam['iWeixinID'];
        }
        if (! empty($aParam['sAccount'])) {
        	$aWhere['sAccount'] = $aParam['sAccount'];
        }
        if (! empty($aParam['sTitle'])) {
        	$aWhere['sTitle LIKE'] = '%'.$aParam['sTitle'].'%';
        }
        
        $aList = Model_CrawlWeixinArticle::getList($aWhere, $iPage);
        $this->assign('aList', $aList);
        $this->assign('aParam', $aParam);
    }

    /**
     * 微信文章修改
     */
    public function editAction()
    {
        if ($this->_request->isPost()) {
            $aWeiXin = $this->_checkData('update');
            if (empty($aWeiXin)) {
                return null;
            }
            $aWeiXin['iWeixinID'] = intval($this->getParam('iWeixinID'));
            $aOldWeiXin = Model_CrawlWeixinArticle::getDetail($aWeiXin['iWeixinID']);
            if (empty($aOldWeiXin)) {
                return $this->showMsg('微信文章不存在！', false);
            }
            if (1 == Model_CrawlWeixinArticle::updData($aWeiXin)) {
                return $this->showMsg('微信文章更新成功！', true);
            } else {
                return $this->showMsg('微信文章更新失败！', false);
            }
        } else {
            $iWeixinID = intval($this->getParam('id'));
            $aWeiXin = Model_CrawlWeixinArticle::getDetail($iWeixinID);
            $this->assign('aWeiXin', $aWeiXin);
        }
    }

    /**
     * 微信文章内容
     */
    public function addAction()
    {
        if ($this->_request->isPost()) {
            $aWeiXin = $this->_checkData('add');
            if (empty($aWeiXin)) {
                return null;
            }
            if (Model_CrawlWeixinArticle::addData($aWeiXin) > 0) {
                return $this->showMsg('微信文章增加成功！', true);
            } else {
                return $this->showMsg('微信文章增加失败！', false);
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
        $sAccount = $this->getParam('sAccount');
        $sName = $this->getParam('sName');
        $iAccountID = $this->getParam('iAccountID');
        $iReadTotal = $this->getParam('iReadTotal');
        $iPublishTimes = $this->getParam('iPublishTimes');
        $iPublishTotal = $this->getParam('iPublishTotal');
        $iReadMax = $this->getParam('iReadMax');
        $iReadAvg = $this->getParam('iReadAvg');
        $iPraiseTotal = $this->getParam('iPraiseTotal');
        $iWCI = $this->getParam('iWCI');
        $sFunInfo = $this->getParam('sFunInfo');
        $sAutoInfo = $this->getParam('sAutoInfo');
        $sTag = $this->getParam('sTag');
        $iTotalRank = $this->getParam('iTotalRank');
        $iCatRank = $this->getParam('iCatRank');
        $iHeadReadTotal = $this->getParam('iHeadReadTotal');
        $iHeadReadAvg = $this->getParam('iHeadReadAvg');
        $iPraiseAvg = $this->getParam('iPraiseAvg');
        $iPublish10w = $this->getParam('iPublish10w');
        $sRankdate = $this->getParam('sRankdate');
        $iUpdateTime = time();
        
        $aRow = array(
            'sAccount' => $sTitle,
            'sName' => $sContent,
            'iAccountID' => $sContent,
            'iReadTotal' => $sContent,
            'iPublishTimes' => $sContent,
            'iPublishTotal' => $sContent,
            'iReadMax' => $sContent,
            'iReadAvg' => $sContent,
            'iPraiseTotal' => $sContent,
            'iWCI' => $sContent,
            'sFunInfo' => $sContent,
            'sAutoInfo' => $sContent,
            'sTag' => $sContent,
            'iTotalRank' => $sContent,
            'iCatRank' => $sContent,
            'iHeadReadTotal' => $sContent,
            'iHeadReadAvg' => $sContent,
            'iPraiseAvg' => $sContent,
            'iPublish10w' => $sContent,
            'sRankdate' => $sContent,
            'iUpdateTime' => $iUpdateTime
        );
        
        return $aRow;
    }
}