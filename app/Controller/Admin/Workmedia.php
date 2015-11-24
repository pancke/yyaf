<?php

class Controller_Admin_Workmedia extends Controller_Admin_Base
{

    /**
     * 资源圈子删除
     */
    public function delAction()
    {
        $iWorkMediaID = intval($this->getParam('id'));
        $iRet = Model_WorkMedia::delData($iWorkMediaID);
        if ($iRet == 1) {
            return $this->showMsg('资源圈子删除成功！', true);
        } else {
            return $this->showMsg('资源圈子删除失败！', false);
        }
    }

    /**
     * 资源圈子列表
     */
    public function listAction()
    {
        $iPage = intval($this->getParam('page'));
        $aWhere = array(
            'iStatus' => 1
        );
        
        $aParam = $this->getParams();
        if (! empty($aParam['title'])) {
        	$aWhere['title LIKE'] = '%'.$aParam['title'].'%';
        }
        if (! empty($aParam['wtype'])) {
        	$aWhere['wtype'] = $aParam['wtype'];
        }
        
        $aList = Model_WorkMedia::getList($aWhere, $iPage);
        $this->assign('aList', $aList);
        $this->assign('aParam', $aParam);
        $this->assign('wtype', Model_WorkMedia::$wtype);
    }

    /**
     * 资源圈子修改
     */
    public function editAction()
    {
        if ($this->_request->isPost()) {
            $aWorkMedia = $this->_checkData('update');
            if (empty($aWorkMedia)) {
                return null;
            }
            $aWorkMedia['iAutoID'] = intval($this->getParam('iAutoID'));
            $aOldWorkMedia = Model_WorkMedia::getDetail($aWorkMedia['iAutoID']);
            if (empty($aOldWorkMedia)) {
                return $this->showMsg('资源圈子不存在！', false);
            }
            if (1 == Model_WorkMedia::updData($aWorkMedia)) {
                return $this->showMsg('资源圈子信息更新成功！', true);
            } else {
                return $this->showMsg('资源圈子信息更新失败！', false);
            }
        } else {
            $iWorkMediaID = intval($this->getParam('id'));
            $aWorkMedia = Model_WorkMedia::getDetail($iWorkMediaID);
            $this->assign('aWorkMedia', $aWorkMedia);
        	$this->assign('wtype', Model_WorkMedia::$wtype);
        }
    }

    /**
     * 增加资源圈子
     */
    public function addAction()
    {
        if ($this->_request->isPost()) {
            $aWorkMedia = $this->_checkData('add');
            if (empty($aWorkMedia)) {
                return null;
            }
            $aWorkMedia['iStatus'] = 1;
            $aWorkMedia['iCreateTime'] = time();
            if (Model_WorkMedia::addData($aWorkMedia) > 0) {
                return $this->showMsg('资源圈子增加成功！', true);
            } else {
                return $this->showMsg('资源圈子增加失败！', false);
            }
        }
        $this->assign('wtype', Model_WorkMedia::$wtype);
    }

    /**
     * 请求数据检测
     *
     * @return mixed
     */
    public function _checkData($sType = 'add')
    {
        $wtype = $this->getParam('wtype');
        $title = $this->getParam('title');
        $imgurl = $this->getParam('imgurl');
        $link = $this->getParam('link');
        $rank = $this->getParam('rank');
        $subscribe = $this->getParam('subscribe');
        $readnum = $this->getParam('readnum');
        $introduce = $this->getParam('introduce');
        $iID = $this->getParam('iID');
        $iUpdateTime = time();


        $aRow = array(
            'wtype' => $wtype,
            'title' => $title,
            'imgurl' => $imgurl,
            'link' => $link,
            'rank' => $rank,
            'subscribe' => $subscribe,
            'readnum' => $readnum,
            'introduce' => $introduce,
            'iUpdateTime' => $iUpdateTime,
            'iID' => $iID,
        );
        
        return $aRow;
    }
}