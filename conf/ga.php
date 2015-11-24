<?php
//Permission数据库配制
$config['database']['permission']['master'] = array(
		'dsn' => 'mysql:host=127.0.0.1;dbname=touandai',
		'user' => 'root',
		'pass' => 'xjc.123',
		'init' => array(
			'SET CHARACTER SET utf8',
			'SET NAMES utf8'
		)
);
$config['database']['permission']['salve'] = array(
    'dsn' => 'mysql:host=127.0.0.1;dbname=touandai',
    'user' => 'root',
    'pass' => 'xjc.123',
    'init' => array(
        'SET CHARACTER SET utf8',
        'SET NAMES utf8'
    )
);

return $config;