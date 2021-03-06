<?php
//=======【基本信息设置】=====================================
//微信公众号身份的唯一标识。审核通过后，在微信发送的邮件中查看
$config['wxpay']['APPID'] = 'wxea7278813614daa9';
//受理商ID，身份标识
$config['wxpay']['MCHID'] = '1275175101';
//商户支付密钥Key。审核通过后，在微信发送的邮件中查看
$config['wxpay']['KEY'] = '54b663deedd3d548be623bbc9da276d0';
//JSAPI接口中获取openid，审核后在公众平台开启开发模式后可查看
$config['wxpay']['APPSECRET'] = '9dfd1491b4e197f38691ced74b1b8add';

//=======【JSAPI路径设置】===================================
//获取access_token过程中的跳转uri，通过跳转将code传入jsapi支付页面
$config['wxpay']['JS_API_CALL_URL'] = 'http://'.ENV_DOMAIN.'/payment/weixin/jsapicall.html';

//=======【证书路径设置】=====================================
//证书路径,注意应该填写绝对路径
$config['wxpay']['SSLCERT_PATH'] = LIB_PATH . '/Payment/Weixin/WxPayPubHelper/cacert/apiclient_cert.pem';
$config['wxpay']['SSLKEY_PATH'] = LIB_PATH . '/Payment/Weixin/WxPayPubHelper/cacert/apiclient_key.pem';

//=======【异步通知url设置】===================================
//异步通知url，商户根据实际开发过程设定
$config['wxpay']['NOTIFY_URL'] = 'http://'.ENV_DOMAIN.'/payment/weixin/notify.html';

//=======【curl超时设置】===================================
//本例程通过curl使用HTTP POST方法，此处可修改其超时时间，默认为30秒
$config['wxpay']['CURL_TIMEOUT'] = 60;