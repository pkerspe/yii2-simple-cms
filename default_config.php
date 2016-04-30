<?php
/** omit this db setup if you have db already configured in your main configuration */
$config['components']['db'] = [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=yii2basic',
    'username' => 'yiitest',
    'password' => 'yiitest',
    'charset' => 'utf8',
];

/** here comes the actual simple cms configuration parts  */

$config['modules']['simplecms_frontend'] = [
    'urlPrefix' => 'cms', //the context alias for the module if you do not want to use simple cms_frotend in your path
    'class' => 'schallschlucker\simplecms\Frontend',
    'languageManager' => 'simplecmsLanguageManager',
    'renderTopMenuNavbar' => true,
    'htmlTitlePrefix' => 'simple-cms: ',
    'cache' => 'cache'
];

$config['modules']['media_manager'] = [
    'class' => 'schallschlucker\simplecms\MediaManager',
    'mediarepositoryPath' => '/tmp/',
];

$config['modules']['simplecms_backend'] = [
    'class' => 'schallschlucker\simplecms\Backend',
    'cache' => 'cache',
    'languageManager' => 'simplecmsLanguageManager',
];

$config['components']['urlManager'] = [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [
    ],
];

$config['components']['simplecmsLanguageManager'] = [
    'class' => 'schallschlucker\simplecms\LanguageManager',
    'languageIdMappings' => [
        '1' => [
            'id' => 1,
            'code' => 'de_DE',
            'displaytext' => [
                'de_DE' => 'deutsch',
                'en_US' => 'german',
                'pl_PL' => 'niemiecki',
                'tr_TR' => 'alman',
            ],
        ],
        'de_DE' => [
            'alias' => '1'
        ],
        'de-DE' => [
            'alias' => '1'
        ],
        '2' => [
            'id' => 2,
            'code' => 'en_US',
            'displaytext' => [
                'de_DE' => 'englisch',
                'en_US' => 'english',
                'pl_PL' => 'angielski',
                'tr_TR' => 'ingilizce',
            ],
        ],
        'en_US' => [
            'alias' => '2',
        ],
        'en-US' => [
            'alias' => '2',
        ],
        '3' => [
            'id' => 3,
            'code' => 'pl_PL',
            'displaytext' => [
                'de_DE' => 'polnisch',
                'en_US' => 'polish',
                'pl_PL' => 'polski',
                'tr_TR' => 'lehçe',
            ],
        ],
        '4' => [
            'id' => 4,
            'code' => 'tr_TR',
            'displaytext' => [
                'de_DE' => 'türkisch',
                'en_US' => 'turkish',
                'pl_PL' => 'turecki',
                'tr_TR' => 'türk',
            ],
        ],
    ],
];