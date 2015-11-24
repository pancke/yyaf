<?php
//后台进程配置 s:i:H:d:m:w
$config = array(
    array('path' => '/cmd/media/statavgread', 'cron' => '0 0 2 * * *', 'num' => 1 ),    //计算自媒体的平均阅读量
    array('path' => '/cmd/media/alterauto', 'cron' => '0 1 0 * * *', 'num' => 1 ),        //修改下一个订单号(符可Ymd000001)
    array('path' => '/cmd/media/statcrawl', 'cron' => '0 1 0 * * *', 'num' => 1 ),        //发送抓取数据结果邮件
);

return $config;
