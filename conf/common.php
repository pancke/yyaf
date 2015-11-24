<?php
//登录cookie配置
$config['cookie']['prefix'] = '51wom';
$config['cookie']['authkey'] = 'auth';
$config['cookie']['cryptkey'] = 'kdi##20.83(&%$63ldwl';
$config['cookie']['ad'] = 'ad';
$config['cookie']['media'] = 'media';
$config['cookie']['frontexpire'] = 86400 * 30;

//密码加密
$config['passwd']['cryptkey'] = 'kdil2!!@8EfJI37.020##dEie';

//URL规则
$config['route']['rewrite'] = array(
    // Image View    '/^view\/(?<biz>banner)\/(?<key>[a-z0-9]{40})\/(?<w>\d+)x(?<h>\d+)\.(?<ext>jpg|gif|png|bmp)$/i'            => '/file/index/view',    '/^view\/(?<biz>banner)\/(?<key>[a-z0-9]{40})\/(?<w>\d+)x(?<h>\d+)_(?<crop>c)\.(?<ext>jpg|gif|png|bmp)$/i' => '/file/index/view',    '/^view\/(?<biz>banner)\/(?<key>[a-z0-9]{40})\.(?<ext>.*)$/i'                                 => '/file/index/view',    '/^view\/(?<key>[a-z0-9]{40})\/(?<w>\d+)x(?<h>\d+)\.(?<ext>jpg|gif|png|bmp)$/i'                                 => '/file/index/view',    '/^view\/(?<key>[a-z0-9]{40})\/(?<w>\d+)x(?<h>\d+)_(?<crop>c)\.(?<ext>jpg|gif|png|bmp)$/i'                      => '/file/index/view',    '/^view\/(?<key>[a-z0-9]{40})\.(?<ext>.*)$/i'                                                      => '/file/index/view',        // File Download    '/^download\/(?<biz>banner)\/(?<key>[a-z0-9]{40})\/(?<w>\d+)x(?<h>\d+)\.(?<ext>jpg|gif|png|bmp)$/i'            => '/file/index/download',    '/^download\/(?<biz>banner)\/(?<key>[a-z0-9]{40})\/(?<w>\d+)x(?<h>\d+)_(?<crop>c)\.(?<ext>jpg|gif|png|bmp)$/i' => '/file/index/download',    '/^download\/(?<biz>banner)\/(?<key>[a-z0-9]{40})\.(?<ext>.*)$/i'                                 => '/file/index/download',    '/^download\/(?<key>[a-z0-9]{40})\/(?<w>\d+)x(?<h>\d+)\.(?<ext>jpg|gif|png|bmp)$/i'                                 => '/file/index/download',    '/^download\/(?<key>[a-z0-9]{40})\/(?<w>\d+)x(?<h>\d+)_(?<crop>c)\.(?<ext>jpg|gif|png|bmp)$/i'                      => '/file/index/download',    '/^download\/(?<key>[a-z0-9]{40})\.(?<ext>.*)$/i'                                => '/file/index/download',
);

$config['sImgFont'] = APP_PATH.'/../conf/STZHONGS.ttf';

$config['domain']['www']        = ENV_DOMAIN;$config['domain']['static']     = ENV_DOMAIN;$config['domain']['file']       = ENV_DOMAIN;

// LOG配置
$config['logger']['sBaseDir'] = APP_PATH . '/../logs';
$config['logger']['common'] = array(
    'sSplit' => 'Ymd',
    'sDir' => 'common'
);

// URL配置
$config['url']['upload'] = 'http://' . ENV_DOMAIN . '/file/upload';
$config['url']['dfsview'] = 'http://' . ENV_DOMAIN . '/view';
$config['url']['bannerupload'] = 'http://' . ENV_DOMAIN . '/file/bannerupload';

// 邮箱跳转
$config['aMailServer'] = array(
    '163.com' => 'http://mail.163.com',
    'gmail.com' => 'https://gmail.com',
    'qq.com' => 'http://mail.qq.com',
    '126.com' => 'http://mail.126.com',
    'sohu.com' => 'http://mail.sohu.com',
    'sina.com.cn' => 'http://mail.sina.com.cn',
    '139.com' => 'http://mail.10086.cn',
    'tom.com' => 'http://mail.tom.com',
    'aliyun.com' => 'https://mail.aliyun.com'
);

// 短信模板
$config['aSmsTempID'] = array(
    '1' => 41908,        //媒体审核
    '2' => 41906,        //广告审核
    '3' => 41907,        //收到订单
    '4' => 41913,        //已经接单
    '5' => 41914,        //提交预览
    '6' => 41916,        //确定内容
    '7' => 41917,        //已经执行
    '8' => 41918,        //已经确认
    '10' => 44461,       //确认效果
    '9' => 44998         //忘记密码
);

return $config;