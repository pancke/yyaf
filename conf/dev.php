<?php
$config['database']['default']['master'] = array(
    'dsn' => 'mysql:host=120.24.78.234;dbname=51Wom',
    'user' => '51wom',
    'pass' => '51wom#2$%(3',
    'init' => array(
        'SET CHARACTER SET utf8',
        'SET NAMES utf8'
    )
);
$config['database']['default']['salve'] = array(
    'dsn' => 'mysql:host=120.24.78.234;dbname=51Wom',
    'user' => '51wom',
    'pass' => '51wom#2$%(3',
    'init' => array(
        'SET CHARACTER SET utf8',
        'SET NAMES utf8'
    )
);

$config['cache']['bll'] = array(
    array(
        'host' => '120.24.78.234',
        'port' => 11211
    )
);

// 邮箱服务器配制
// $config["mailer"] = array(
//     'from_email' => 'pancke@163.com',
//     'from_name' => 'pancke',
//     'smtp_host' => 'smtp.163.com',
//     'smtp_user' => 'pancke',
//     'smtp_pass' => 'xjc.123',
//     'smtp_port' => '25',
//     'smtp_secure' => ''
// );
$config["mailer"] = array(
    'from_email' => 'kefu@51wom.com',
    'from_name' => '51wom',
    'smtp_host' => 'smtp.exmail.qq.com',
    'smtp_user' => 'kefu@51wom.com',
    'smtp_pass' => '021Qianma2',
    'smtp_port' => '25',
    'smtp_secure' => ''
);

// 云通讯配制
$config['CCP'] = array(
    'host' => 'app.cloopen.com',
    // 'host' => 'sandboxapp.cloopen.com',
    'port' => '8883',
    'version' => '2013-12-26',
    'sid' => '8a48b5514fd49643014fdfbcae4920ee',
    'token' => '18ed961805904eabb9198da4d25c28f0',
    'appid' => '8a48b5514fd49643014fdfc0aa9f2103'
);

return $config;