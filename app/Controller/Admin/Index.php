<?php

class Controller_Admin_Index extends Controller_Admin_Base
{
    protected $bCheckLogin = false;

    public function indexAction ()
    {
        $aCookie = Util_Cookie::get(Yaf_G::getConf('authkey', 'cookie'));
        $sUrl = '/admin/login';

        if ($aCookie) {
            $sUrl = '/admin/user/info';
        }
        return $this->redirect($sUrl);
    }

    public function loginAction ()
    {}

    public function logoutAction ()
    {
        Util_Cookie::delete(Yaf_G::getConf('authkey', 'cookie'));
        $this->redirect('/admin/login');
    }

    public function signinAction ()
    {
        $sAdminName = $this->getParam('username');
        $sPassword = $this->getParam('password');
        $bRemember = $this->getParam('remember');
        $aUser = Model_Admin::getAdminByName($sAdminName);
        if (empty($aUser)) {
            return $this->showMsg('帐号不存在！', false);
        }
        if ($aUser['iStatus'] == 0) {
            return $this->showMsg('帐号被禁用！', false);
        }
        if ($aUser['sPassword'] != md5(Yaf_G::getConf('cryptkey', 'cookie') . $sPassword)) {
            return $this->showMsg('密码不正确！', false);
        }
        $aCookie = array(
            'iAdminID' => $aUser['iAdminID'],
            'iCityID' => $aUser['iCityID'],
            'sAdminName' => $aUser['sAdminName'],
            'sRealName' => $aUser['sRealName']
        );
        if ($bRemember) {
            $expire = 86400 * 7;
        } else {
            $expire = 0;
        }
        Util_Cookie::set(Yaf_G::getConf('authkey', 'cookie'), $aCookie, $expire);

        $aPermissions = Model_Permission::getUserPermissions($aCookie['iAdminID']);

        $sUrl = '/admin/user/info';
        return $this->showMsg(['msg' => '登录成功！', 'sUrl' => $sUrl], true);
    }
    
    /**
     * 更换城市
     */
    public function changeAction()
    {
        // 当前用户
        $aCookie = Util_Cookie::get(Yaf_G::getConf('authkey', 'cookie'));
        if (empty($aCookie)) {
            return $this->redirect('/admin/login');
        }
        $this->aCurrUser = $aCookie;
        
        $iCityID = $this->getParam('id');
        $aCity = Model_City::getDetail($iCityID);
        if (empty($aCity) || $aCity['iBackendShow'] == 0 || $aCity['iStatus'] == 0) {
            return $this->showMsg('城市不存在或未开放！', false);
        }
        $aUser = Model_Admin::getDetail($this->aCurrUser['iAdminID']);
        $aCityID = explode(',', $aUser['sCityID']);
        if ($aUser['sCityID'] != '-1' && !in_array($iCityID, $aCityID)) {
            return $this->showMsg('您没有访问该城市的权限，请联系管理员！', false);
        }
        Util_Cookie::set('city', $iCityID);
        return $this->showMsg('城市切换成功!', true);
    }

    public function permissionAction ()
    {
        $aMenuList = Model_Menu::getMenus();

        $aCtrClass = array();
        $aMenuAction = array();
        foreach ($aMenuList as $aMenu) {
            if ($aMenu['bIsLeaf']) {
                $aRoute = Yaf_G::getRoute($aMenu['sUrl']);
                $aMenuAction[$aRoute['module'] . '_' . $aRoute['controller'] . '_' . $aRoute['action'] ] = $aMenu['sMenuName'];
                $aCtrClass[$aRoute['module'] . '_' . $aRoute['controller']] = array(
                    'iMenuID' => $aMenu['iMenuID'],
                    'sMenuName' => $aMenu['sMenuName'],
                    'sUrl' => $aMenu['sUrl']
                );
            }
        }

        $aPermission = array();
        foreach ($aCtrClass as $sCtrClass => $aMenu) {
            try {
                $sCtrClass = 'Controller_' . $sCtrClass;
                if (class_exists($sCtrClass)) {
                    $oCtr = new ReflectionClass($sCtrClass);
                    $aMethod = $oCtr->getMethods();
                    foreach ($aMethod as $oMethod) {
                        $sAction = $oMethod->getName();
                        if (substr($sAction, - 6) === 'Action') {
                            $sAction = substr($sAction, 0, -6);
                            $aRow = array($aMenu['iMenuID']);
                            $aRow[] = Yaf_G::routeToUrl($sCtrClass.'_'.$sAction);
                            $sDoc = $oMethod->getDocComment();
                            $matches = null;
                            if (preg_match('/\s+\*\s+(.+)/i', $sDoc, $matches)) {
                                $aRow[] = $matches[1];
                            } elseif (isset($aMenuAction[$sCtrClass.'_'.$sAction])) {
                                $aRow[] = $aMenuAction[$sCtrClass.'_'.$sAction];
                            } else {
                                $aRow[] = $aMenu['sMenuName'] . '::' . $sAction;
                            }
                            $aPermission[] = $aRow;
                        }
                    }
                }
            } catch (Exception $e) {
                $aPermission[] = array(
                    $aMenu['iMenuID'],
                    Yaf_G::getUrl($aMenu['sUrl']),
                    $aMenu['sMenuName']
                );
            }
        }
        $this->showMsg($aPermission, true);
    }
}