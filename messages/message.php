<?php

/*
 * This file is part of the simple cms project for Yii2
 *
 * (c) Schallschlucker Agency Paul Kerspe - project homepage <https://github.com/pkerspe/yii2-simple-cms>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
return [ 
	'sourcePath' => __DIR__ . '/../',
	'messagePath' => __DIR__,
	'languages' => [ 
		'de',
		'en' 
	],
	'translator' => 'Yii::t',
	'sort' => false,
	'overwrite' => true,
	'removeUnused' => false,
	'only' => [ 
		'*.php' 
	],
	'except' => [ 
		'.svn',
		'.git',
		'.gitignore',
		'.gitkeep',
		'.hgignore',
		'.hgkeep',
		'/messages',
		'/assets'
	],
	'format' => 'php' 
];
