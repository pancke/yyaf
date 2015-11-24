<?php

/**
 * 提供CMS内容
 * Created by PhpStorm.
 * User: xiejinci
 * Date: 14/12/24
 * Time: 下午2:32
 */
class Controller_Index_Help extends Controller_Index_Base
{

    /**
     * 用户中心
     */
    public function indexAction ()
    {
        $sType = $this->getParam('type');
        if (intval($sType) > 0) {
            $aData = Model_WebSite::getDetail((int) $sType);
        } else {
            $aData = Model_WebSite::getRow(array(
                'where' => array(
                    'sPage' => $sType
                )
            ));
        }
        if (empty($aData)) {
            return $this->show404('内容不存在!');
        }
        
        $this->assign('aData', $aData);
        $this->setMeta('help_page', $aData);
        $this->assign('sTopMenu', 'help');
        
        /**
         *  aQuestion  常见问题解答
         */
        $this->assign('aQuestion', Model_WebSite::getAll(array(
            'iStatus' => 1,
            'iType' => 2
        )));
        /**
         *  aOperate  平台运营规则
         */
        $this->assign('aOperate', Model_WebSite::getAll(array(
            'iStatus' => 1,
            'iType' => 3
        )));
        /**
         *  aMCenter  广告主
         */
        $this->assign('aMCenter', Model_WebSite::getAll(array(
            'iStatus' => 1,
            'iType' => 5
        )));
        /**
         *  aPCenter  自媒体
         */
        $this->assign('aPCenter', Model_WebSite::getAll(array(
            'iStatus' => 1,
            'iType' => 7
        )));
        /**
         *  aNotice  通知公告
         */
        $this->assign('aNotice', Model_WebSite::getAll(array(
            'iStatus' => 1,
            'iType' => 8
        )));
        /**
         *  aOther  其他
         */
        $this->assign('aOther', Model_WebSite::getAll(array(
            'iStatus' => 1,
            'iType' => 9
        )));
    }

    /**
     * 案例中心
     */
    public function caseAction ()
    {
        $sType = 'case';
        if (intval($sType) > 0) {
            $aData = Model_WebSite::getDetail((int) $sType);
        } else {
            $aData = Model_WebSite::getRow(array(
                'where' => array(
                    'sPage' => $sType
                )
            ));
        }
        if (empty($aData)) {
            return $this->show404('内容不存在!');
        }
        
        $this->assign('aData', $aData);
        $this->setMeta('help_page', $aData);
        $this->assign('sTopMenu', 'case');
    }

    /**
     * 不要frame的协议说明
     */
    public function noneAction ()
    {
        $this->indexAction();
        $this->setFrame('none.phtml');
    }
}