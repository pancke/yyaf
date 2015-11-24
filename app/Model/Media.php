<?php

class Model_Media extends Model_Base
{

    const TABLE_NAME = 't_media';

    const TYPE_WEIXIN = 1; // 微信公众号
    const TYPE_FRIEND = 2; // 微信朋友圈
    const TYPE_WEIBO = 3; // 新浪微博
    const TYPE_NEWS = 4; // 新闻&论坛
    public static $aType = array(
        1 => '微信公众号',
        // 2 => '微信朋友圈',
        3 => '新浪微博',
        // 4 => '新闻&论坛'
    );
    
    /**
     * 
     * @var unknown
     */
    public static $aPos = array(
        self::TYPE_WEIXIN => array(
            '1' => '单图文',
            '2' => '多图文第一条',
            '3' => '多图文第二条',
            '4' => '多图文第3~N条'
        ),
        self::TYPE_FRIEND => array(
            '1' => '转发报价',
            '2' => '直发报价'
        ),
        self::TYPE_WEIBO => array(
            '1' => '转发报价',
            '2' => '直发报价'
        )
    );

    /**
     * 取得自媒体的分类名称
     * @param unknown $iMediaID
     * @return string
     */
    public static function getCategoryNames($iMediaID)
    {
        $aName = self::query("SELECT d.sName FROM t_domain d, t_media_category c WHERE c.iMediaID=$iMediaID AND c.iCategoryID=d.iAutoID AND c.iStatus=1", 'col');
        
        return empty($aName) ? '' : join(',', $aName);
    }
    
    /**
     * 取得自媒体的标签名称
     * @param unknown $iMediaID
     * @return string
     */
    public static function getTagNames($iMediaID)
    {
        $aName = self::query("SELECT d.sName FROM t_domain d, t_media_tag c WHERE c.iMediaID=$iMediaID AND c.iTagID=d.iAutoID AND c.iStatus=1", 'col');
        
        return empty($aName) ? '' : join(',', $aName);
    }

    /**
     * 取得自媒体的城市名称
     * @param unknown $iMediaID
     * @return string
     */
    public static function getCityNames($iMediaID)
    {
        $aName = self::query("SELECT d.sCityName FROM t_city d, t_media_city c WHERE c.iMediaID=$iMediaID AND c.iCityID=d.iCityID AND c.iStatus=1", 'col');
    
        return empty($aName) ? '' : join(',', $aName);
    }
    
    /**
     * 取得用户所有自媒体ID
     * @param unknown $iUserID
     * @return Ambigous <number, multitype:multitype:, multitype:unknown >
     */
    public static function getUserMediaID ($iUserID)
    {
        return self::getCol(array(
            'iStatus' => 1,
            'iUserID' => $iUserID
        ), 'iMediaID');
    }

    /**
     * 搜索自媒体
     * 
     * @param unknown $aParam            
     * @param string $sOrder            
     * @param number $iPageSize            
     * @return Ambigous <NULL, boolean, string, multitype:multitype: Ambigous <> , multitype:, array/string, int/false, number, unknown, unknown>
     */
    public static function search ($aParam, $sOrder = '', $iPageSize = 20)
    {
        $aRule = array();
        $aTable = array(
            't_media m'
        );
        $aWhere = array(
            'm.iStatus=1',
            'm.iPut=1',
            'm.iMediaType=' . intval($aParam['type'])
        );
        if (! empty($aParam['name'])) {
            $aWhere[] = '(m.sMediaName LIKE "%'.$aParam['name'].'%" OR m.sOpenName LIKE "%'.$aParam['name'].'%")';
        }
        if (! empty($aParam['catid'])) {
            $aTable[] = 't_media_category c';
            $aWhere[] = 'c.iMediaID=m.iMediaID';
            $aWhere[] = 'c.iCategoryID=' . intval($aParam['catid']);
            //$aWhere[] = 'c.iCategoryID IN(' . join(',', $aParam['catid']) . ')';
        }
        if (! empty($aParam['price'])) {
            $aPrice = Model_Price::parsePrice($aParam['price']);
            $aWhere[] = '(m.iPrice1>=' . $aPrice[0] . ' AND m.iPrice1<' . $aPrice[1] . ' OR m.iPrice2>=' . $aPrice[0] . ' AND m.iPrice2<' . $aPrice[1] . ')';
        }
        if (! empty($aParam['follower'])) {
            $aFollower = explode('~', $aParam['follower']);
            $aWhere[] = '(m.iFollowerNum>=' . $aFollower[0] . ' AND m.iFollowerNum<' . $aFollower[1] . ')';
        }
        if (! empty($aParam['city'])) {
            $aTable[] = 't_media_city ct';
            $aWhere[] = 'ct.iMediaID=m.iMediaID';
            $aWhere[] = 'ct.iCityID IN(' . join(',', $aParam['city']) . ')';
        }
        if (! empty($aParam['readnum0'])) {
            $aWhere[] = 'm.iReadAvgNum >=' . intval($aParam['readnum0']);
        }
        if (! empty($aParam['readnum1'])) {
            $aWhere[] = 'm.iReadAvgNum <=' . intval($aParam['readnum1']);
        }
        if (! empty($aParam['score'])) {
            $aWhere[] = 'm.iScore >=' . intval($aParam['score']);
        }
        if (! empty($aParam['tag'])) {
            $aTable[] = 't_media_tag t';
            $aWhere[] = 't.iMediaID=m.iMediaID';
            $aWhere[] = 't.iTagID IN(' . join(',', $aParam['tag']) . ')';
        }
        /*
        if (! empty($aParam['aCriceID'])) {
            $aTable[] = 't_media_cricle cr';
            $aWhere[] = 'cr.iMediaID=m.iMediaID';
            $aWhere[] = 'cr.iCricleID IN(' . join(',', $aParam['aCriceID']) . ')';
        }
        */
        
        $iPage = max(intval($aParam['page']), 1);
        $sLimit = ' LIMIT ' . (($iPage - 1) * $iPageSize) . ',' . $iPageSize;
        $sOrder = empty($sOrder) ? '' : ' ORDER BY ' . $sOrder;
        
        $sSQL = 'SELECT m.* FROM ' . join(',', $aTable) . ' WHERE ' . join(' AND ', $aWhere) . ' GROUP BY m.iMediaID' . $sOrder . $sLimit;
        $aRet['aList'] = self::query($sSQL);
        if ($iPage == 1 && count($aRet['aList']) < $iPageSize) {
            $aRet['iTotal'] = count($aRet['aList']);
            $aRet['aPager'] = null;
        } else {
            unset($aParam['limit'], $aParam['order']);
            $sSQL = 'SELECT COUNT(DISTINCT m.iMediaID) FROM ' . join(',', $aTable) . ' WHERE ' . join(' AND ', $aWhere);
            $aRet['iTotal'] = self::query($sSQL, 'one');
            $sUrl = Util_Common::getUrl();
            $aRet['aPager'] = Util_Page::getPage($aRet['iTotal'], $iPage, $iPageSize, $sUrl, $aParam);
        }
        
        return $aRet;
    }

    /**
     * 根据名字获取自媒体
     *
     * @param unknown $iMediaType            
     * @param unknown $sMediaName            
     * @param number $iMediaID            
     * @return Ambigous <number, multitype:, mixed>
     */
    public static function getMediaByName ($iMediaType, $sMediaName, $iMediaID = 0)
    {
        $aWhere = array(
            'iMediaType' => $iMediaType,
            'sMediaName' => $sMediaName
        );
        
        if ($iMediaID > 0) {
            $aWhere['iMediaID !='] = $iMediaID;
        }
        
        return self::getRow(array(
            'where' => $aWhere
        ));
    }
    
    /**
     * 根据名字获取自媒体
     *
     * @param unknown $iMediaType
     * @param unknown $sMediaName
     * @param number $iMediaID
     * @return Ambigous <number, multitype:, mixed>
     */
    public static function getMediaByOpenName ($iMediaType, $sOpenName, $iMediaID = 0)
    {
        $aWhere = array(
            'iMediaType' => $iMediaType,
            'sOpenName' => $sOpenName
        );
    
        if ($iMediaID > 0) {
            $aWhere['iMediaID !='] = $iMediaID;
        }
    
        return self::getRow(array(
            'where' => $aWhere
        ));
    }

    /**
     * 取得完整的自媒体信息
     *
     * @param unknown $iPKID            
     */
    public static function getFullDetail ($iMediaID)
    {
        $aData = self::getDetail($iMediaID);
        if (empty($aData)) {
            return $aData;
        }
        
        $aData['aCatID'] = Model_MediaCategory::getPair(array(
            'where' => array(
                'iMediaID' => $iMediaID,
                'iStatus' => 1
            )
        ), 'iAutoID', 'iCategoryID');
        
        $aData['aCityID'] = Model_MediaCity::getPair(array(
            'where' => array(
                'iMediaID' => $iMediaID,
                'iStatus' => 1
            )
        ), 'iAutoID', 'iCityID');
        
        $aData['aTagID'] = Model_MediaTag::getPair(array(
            'where' => array(
                'iMediaID' => $iMediaID,
                'iStatus' => 1
            )
        ), 'iAutoID', 'iTagID');
        
        return $aData;
    }

    /**
     * 更新自媒体分类
     *
     * @param unknown $iMediaID            
     * @param unknown $aNewCatID            
     */
    public static function updCategory ($iMediaID, $aNewCatID)
    {
        $aOldCatID = Model_MediaCategory::getPair(array(
            'where' => array(
                'iMediaID' => $iMediaID,
                'iStatus' => 1
            )
        ), 'iAutoID', 'iCategoryID');
        
        $aNewCatID = array_flip($aNewCatID);
        $aOldCatID = array_flip($aOldCatID);
        foreach ($aOldCatID as $iCatID => $iAutoID) {
            if (isset($aNewCatID[$iCatID])) {
                continue;
            }
            Model_MediaCategory::realDelData($iAutoID);
        }
        
        foreach ($aNewCatID as $iCatID => $iAutoID) {
            if (isset($aOldCatID[$iCatID])) {
                continue;
            }
            Model_MediaCategory::addData(array(
                'iMediaID' => $iMediaID,
                'iCategoryID' => $iCatID
            ));
        }
    }

    /**
     * 更新自媒体归属城市
     *
     * @param unknown $iMediaID            
     * @param unknown $aNewCityID            
     */
    public static function updCity ($iMediaID, $aNewCityID)
    {
        $aOldCityID = Model_MediaCity::getPair(array(
            'where' => array(
                'iMediaID' => $iMediaID,
                'iStatus' => 1
            )
        ), 'iAutoID', 'iCityID');
        
        $aNewCityID = array_flip($aNewCityID);
        $aOldCityID = array_flip($aOldCityID);
        foreach ($aOldCityID as $iCityID => $iAutoID) {
            if (isset($aNewCityID[$iCityID])) {
                continue;
            }
            Model_MediaCity::realDelData($iAutoID);
        }
        
        foreach ($aNewCityID as $iCityID => $iAutoID) {
            if (isset($aOldCityID[$iCityID])) {
                continue;
            }
            Model_MediaCity::addData(array(
                'iMediaID' => $iMediaID,
                'iCityID' => $iCityID
            ));
        }
    }

    /**
     * 更新自媒体标签
     *
     * @param unknown $iMediaID            
     * @param unknown $aTag            
     */
    public static function updTag ($iMediaID, $aNewTagID)
    {
        $aOldTagID = Model_MediaTag::getPair(array(
            'where' => array(
                'iMediaID' => $iMediaID,
                'iStatus' => 1
            )
        ), 'iAutoID', 'iTagID');
        
        $aNewTagID = array_flip($aNewTagID);
        $aOldTagID = array_flip($aOldTagID);
        foreach ($aOldTagID as $iTagID => $iAutoID) {
            if (isset($aNewTagID[$iTagID])) {
                continue;
            }
            Model_MediaTag::realDelData($iAutoID);
        }
        
        foreach ($aNewTagID as $iTagID => $iAutoID) {
            if (isset($aOldTagID[$iTagID])) {
                continue;
            }
            Model_MediaTag::addData(array(
                'iMediaID' => $iMediaID,
                'iTagID' => $iTagID
            ));
        }
    }
}
