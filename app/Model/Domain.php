<?php

/**
 * 
 * @author xiejinci
 *
 */
class Model_Domain extends Model_Base
{

    const TABLE_NAME = 't_domain';

    const TYPE_CO_INDUSTRY = 11;        //行业
    const TYPE_MEDIA_WEIXIN_TAG = 1;    //微信标签
    const TYPE_MEDIA_FRIEND_TAG = 4;    //朋友圈标签
    const TYPE_MEDIA_WEIBO_TAG = 5;     //微博标签
    const TYPE_MEDIA_NEWS_TAG = 6;      //论坛标签
    const TYPE_MEDIA_WEIXIN_CATEGORY = 2;    //微信分类
    const TYPE_MEDIA_FRIEND_CATEGORY = 7;    //朋友圈分类
    const TYPE_MEDIA_WEIBO_CATEGORY = 8;    //微博分类
    const TYPE_MEDIA_NEWS_CATEGORY = 9;    //新闻分类
    const TYPE_MEDIA_CIRCLE = 10;        //自媒体圈子
    const TYPE_MEDIA_RECOMMEND = 3;    //推荐
    const TYPE_MEDIA_WEIXIN_ATTRIBUTE = 12;	//微信属性
    const TYPE_MEDIA_WEIBO_ATTRIBUTE = 13;	//微博属性
    const TYPE_MEDIA_FRIEND_ATTRIBUTE = 14;	//朋友圈属性
    const TYPE_MEDIA_NEWS_ATTRIBUTE = 15;	//新闻论坛属性
    const TYPE_MEDIA_WEIXIN_COOPERATELEVEL = 16;	//微信合作等级
    const TYPE_MEDIA_WEIBO_COOPERATELEVEL = 17;	//微博合作等级
    const TYPE_MEDIA_FRIEND_COOPERATELEVEL = 18;	//朋友圈合作等级
    const TYPE_MEDIA_NEWS_COOPERATELEVEL = 19;	//新闻论坛合作等级
    const TYPE_MEDIA_WEIXIN_INDUSTRY = 20;	//微信行业圈
    const TYPE_MEDIA_WEIBO_INDUSTRY = 21;	//微博行业圈
    const TYPE_MEDIA_FRIEND_INDUSTRY = 22;	//朋友圈行业圈
    const TYPE_MEDIA_NEWS_INDUSTRY = 23;	//新闻论坛行业圈
    const TYPE_MEDIA_WEIXIN_VERIFY = 24;	//微信认证
    const TYPE_MEDIA_WEIBO_VERIFY = 25;	//微博认证
    const TYPE_USER_CASTOUT = 26;     //提现帐号类型
    
    public static $iType = array(
    		'1' => '公众号标签',
    		'2' => '公众号分类',
    		'3' => '推荐级别',
    		'4' => '朋友圈标签',
    		'5' => '新浪微博标签',
    		'6' => '新闻论坛标签',
    		'7' => '朋友圈分类',
    		'8' => '新浪微博分类',
    		'9' => '新闻论坛分类',
    		'10' => '自媒体圈子',
    		'11' => '行业',
    		'12' => '微信属性',
    		'13' => '微博属性',
    		'14' => '朋友圈属性',
    		'15' => '新闻论坛属性',
    		'16' => '微信合作等级',
    		'17' => '微博合作等级',
    		'18' => '朋友圈合作等级',
    		'19' => '新闻论坛合作等级',
    		'20' => '微信行业圈',
    		'21' => '微博行业圈',
    		'22' => '朋友圈行业圈',
    		'23' => '新闻论坛行业圈',
    		'24' => '微信认证',
    		'25' => '微博认证',
            '26' => '提现账号类型'
    );

    /**
     * 根据类型生成select的option
     * @param unknown $iType
     * @param number $iParentID
     * @param unknown $iCatID
     */
    public static function makeOption ($iType, $iCurrID = 0, $iParentID = 0)
    {
        $sOption = '';
        $aOption = self::getOption($iType, $iParentID);
        foreach ($aOption as $k => $v) {
            $sOption .= "<option value='$k' ".($k == $iCurrID?'selected':'').">$v</option>";
        }
        
        return $sOption;
    }
    
    /**
     * 取得Option
     * @param int $iType
     * @param int $iParentID
     * @return array
     */
    public static function getOption ($iType, $iParentID = 0)
    {
        $aWhere = array(
            'iType' => $iType,
            'iStatus' => 1
        );
        if ($iParentID > 0) {
            $aWhere['iParentID'] = $iParentID;
        }
        
        return self::getPair(array(
            'where' => $aWhere,
            'order' => 'iOrder,iAutoID'
        ), 'iAutoID', 'sName');
    }
    
    /**
     * 取得相同类型的数据
     * @param int $iType
     * @return array
     */
    public static function getPairDomain ($iType = 0)
    {
    	$aWhere = array(
    		'iStatus' => 1,
    		'iType' => $iType
    	);
    	
    	$ret = self::getPair(array(
    			'where' => $aWhere,
    			'order' => 'iOrder desc,iAutoID desc'
    	), 'iAutoID','sName');

    	return $ret;
    }
}