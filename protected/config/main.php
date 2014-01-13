<?php

$dbConfig = json_decode(file_get_contents(dirname(__FILE__).'/db.json'), true);
$pre_config = require(dirname(__FILE__).'/local.php');

// Location where user images are stored
Yii::setPathOfAlias('uploadPath',dirname(__FILE__).DIRECTORY_SEPARATOR.'../../images/uploads');
Yii::setPathOfAlias('uploadURL', '/images/uploads/');

return CMap::mergeArray(array(
    'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
    'name'=>'GigaDB',

    'preload'=>array(
        'log',
        'bootstrap',
    ),

    'import'=>array(
        'application.models.*',
        'application.components.*',
        'application.behaviors.*',
        'application.vendors.*',
        'application.helpers.*',
    ),
    'modules'=>array(
        'gii'=>array(
            'class'=>'system.gii.GiiModule',
            'password'=>'gigadbyii',
            'ipFilters'=>array('*'),
        ),
    ),
    'components'=>array(
        'db'=>array(
            'class'=>'system.db.CDbConnection',
            'connectionString'=>"pgsql:dbname={$dbConfig['database']};host={$dbConfig['host']}",
            'username'=>$dbConfig['user'],
            'password'=>$dbConfig['password'],
            'charset'=>'utf8',
            'persistent'=>true,
            'enableParamLogging'=>true,
            'schemaCachingDuration'=>30
        ),

        'bootstrap'=>array(
            'class'=>'ext.bootstrap.components.Bootstrap',
        ),
        'cache' => array(
            'class' => 'system.caching.CFileCache'
        ),
        'session' => array(
            'class' => 'system.web.CDbHttpSession',
            'connectionID' => 'db',
            'timeout' => 3600,
        ),
        'errorHandler'=>array(
            'errorAction'=>'site/error',
        ),
        'urlManager'=>array(
            'urlFormat'=>'path',
            'showScriptName'=>false,
            'rules'=>array(
                '/dataset/<id:\d+>'=>'dataset/view/id/<id>',
                //'search'=>'site/index',
                //'download/<search:.+>'=>'site/index',
                //'download'=>'site/index',
                '.*'=>'site/index',
            ),
        ),
        'log'=>array(
            'class'=>'CLogRouter',
            'routes'=>array(
                array(
                    'class'=>'CFileLogRoute',
                    'levels'=>'error, warning, info, debug',
                ),
                //array(
                //    'class'=>'CWebLogRoute',
                //),
            ),
        ),
        'messages'=>array(
            'class'=>'CPhpMessageSource',
        ),
        'user'=>array(
            // enable cookie-based authentication
            'allowAutoLogin'=>true,
            //User WebUser
            'class'=>'WebUser',
        ),
        'authManager'=>array(
            'class'=>'CDbAuthManager',
            'connectionID'=>'db',
        ),
        'image'=>array(
          'class'=>'application.extensions.image.CImageComponent',
              // GD or ImageMagick
          'driver'=>'GD',
      ),
    ),

    'params' => array(
        // date formats
        'js_date_format' => 'dd-mm-yy',
        'db_date_format' => "%Y-%m-%d",
        'display_date_format' => "%gggggggd-%m-%Y",
        'display_short_date_format' => "%d-%m",
        'language' => 'en' ,
        'languages' => array('en' => 'EN', 'zh_tw' => 'TW'),

   ),
), $pre_config);

