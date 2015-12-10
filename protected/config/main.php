<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
Yii::setPathOfAlias ( 'booster', dirname ( __FILE__ ) . DIRECTORY_SEPARATOR . '../extensions/yiibooster' );

return array (
		'basePath' => dirname ( __FILE__ ) . DIRECTORY_SEPARATOR . '..',
		'name' => 'DashboardWeb',
		
		// preloading 'log' component
		'preload' => array (
				'log' 
		),
		
		// autoloading model and component classes
		'import' => array (
				'application.models.*',
				'application.components.*',
				'application.extensions.*',
				'application.extensions.log4php.*',
				'application.extensions.log4php.appenders.*',
				'application.extensions.log4php.configurators.*',
				'application.extensions.log4php.filters.*',
				'application.extensions.log4php.helpers.*',
				'application.extensions.log4php.layouts.*',
				'application.extensions.log4php.pattern.*',
				'application.extensions.log4php.renderers.*',
				'application.extensions.log4php.xml.*' 
		),
		
		'modules' => array (
				
				// uncomment the following to enable the Gii tool
				
				'gii' => array (
						'class' => 'system.gii.GiiModule',
						'password' => '123',
						
						// If removed, Gii defaults to localhost only. Edit carefully to taste.
						'ipFilters' => array (
								'127.0.0.1',
								'::1' 
						) 
				) 
		)
		,
		
		// application components
		'components' => array (
				'user' => array (
						
						// enable cookie-based authentication
						'allowAutoLogin' => true 
				),
				
				// uncomment the following to enable URLs in path-format
				/*
				 * 'urlManager'=>array(
				 * 'urlFormat'=>'path',
				 * 'rules'=>array(
				 * '<controller:\w+>/<id:\d+>'=>'<controller>/view',
				 * '<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				 * '<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
				 * ),
				 * ),
				 */
				'db' => array (
						'connectionString' => 'sqlite:' . dirname ( __FILE__ ) . '/../data/testdrive.db' 
				),
				
				// uncomment the following to use a MySQL database
				
				'db' => array (
						'connectionString' => 'mysql:host=127.0.0.1;dbname=dashboard',
						'emulatePrepare' => true,
						'username' => 'root',
						'password' => '',
						'charset' => 'utf8' 
				),
				
				'errorHandler' => array (
						
						// use 'site/error' action to display errors
						'errorAction' => 'site/error' 
				),
				'log' => array (
						'class' => 'CLogRouter',
						'routes' => array (
								array (
										'class' => 'CFileLogRoute',
										'levels' => 'error, warning' 
								) 
						)
						// uncomment the following to show log messages on web pages
						/*
						 * array(
						 * 'class'=>'CWebLogRoute',
						 * ),
						 */
						 
				),
				
				'bootstrap' => array (
						'class' => 'booster.components.Booster' 
				) 
		),
		'preload' => array (
				'bootstrap' 
		),
		
		// application-level parameters that can be accessed
		// using Yii::app()->params['paramName']
		'params' => array (
				
				// this is used in contact page
				'adminEmail' => 'webmaster@example.com',
				
				// datos para la conexion de AMI
				'host' => '172.16.150.10',
				'port' => 5038,
				'username' => 'dashboard',
				'secret' => 'dashboard',
				'connect_timeout' => 5000,
				'read_timeout' => 5000,
				'scheme' => 'tcp://' 
		)
		 
);