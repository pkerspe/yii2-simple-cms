# Yii2-simple-cms
A basic yii2 content management module for easily creating and maintaining a page structure, including navigation widget, full text search, image and document management and a wysiwyg html editor for the content pages.
Does not include an fine granulated access rights management, only differentiates between: anonymous/guest and logged in users. 
The extensions supports site structures in multiple languages for international sites.

> **NOTE:** Module is in initial development. Anything may change at any time.

## Documentation

Yii2-simple-cms documentation is available online: 

See [Project page](https://github.com/pkerspe/yii2-simple-cms) for source code
and [Project wiki](https://github.com/pkerspe/yii2-simple-cms/wiki) for more details, screenshots etc.

Installation instructions are located in the [installation guide](https://github.com/pkerspe/yii2-simple-cms/wiki)

Prefered way is using composer by adding the reuquirement to your composer.json and running composer update afterwards:

    "require": {
        "schallschlucker/yii2-simple-cms": ">=0.2.4",
    }


After installation run migration for database table creation (it is assumed at this point, that the yii2 db module is configured properly in your configuration). Currently the scripts support only mysql databases, other databases might work as well but haven't been tested yet and thus are disabled in the migration script):

	yii migrate --migrationPath=@schallschlucker/simplecms/migrations

#Usage

the extension is split into two modules: the frontend and the backend module.

Frontend provides the needed controllers to: - display page content - display documents - display a search form and a search result page Widgets to: - render a navigation menu (extending yii\bootstrap\Nav widget) - render the navigation structure in different formats like a html list (ol or ul and li nodes), xml, json - render the search bar - render extended search form - render the search results list

The backend provides administrative functions for maintaining the page tree structure (including drag and drop functionality, keyboard shortcuts and context menus for easy creation of new pages).

Both modules can be deployed in the same application, but it is recommended to follow the frontend/backend approach to clearly separate the frontend (user view) from the administrative backend interface.

Content pages in simple cms can be created in multiple language, since simple cms uses internal language id's which might differ from your applications language codes (i.e. ISO 2 letter code or 5 letter code) you need ti initalize the LanguageManager in the components section with a valid mapping to map you applications language codes to the simple cms language codes. Here is an example (which also uses aliases to e.g. map the application language code "de-DE" to the simple cms language code "1" etc.). You can add support for additional languages in simple cms just by adding new mappings.:

	'components' => [
		'simplecmsLanguageManager' => [
       	    		'class' => 'schallschlucker\simplecms\LanguageManager',
			'languageIdMappings' => [
				'1' => [
					'code' => 'de', 
					'displaytext' => [
						'de' => 'deutsch', 
						'en' => 'german',
						'pl' => 'niemiecki',
						'tr' => 'alman',
					],
				],
				'de-DE' => [
					'alias' => '1'
				],
				'2' => [
					'code' => 'en', 
					'displaytext' => [
						'de' => 'englisch', 
						'en' => 'english',
						'pl' => 'angielski',
						'tr' => 'ingilizce',
					],
				],
				'en-US' => [
					'alias' => '2',
				],
				'3' => [
					'code' => 'pl', 
					'displaytext' => [
						'de' => 'polnisch', 
						'en' => 'polish',
						'pl' => 'polski',
						'tr' => 'lehçe',
					],
				],
				'4' => [
					'code' => 'tr', 
					'displaytext' => [
						'de' => 'türkisch', 
						'en' => 'turkish',
						'pl' => 'turecki',
						'tr' => 'türk',
					],
				],
			],
       		],
	],
	'modules' => [
		'simplecms_backend' => [
	            	'class' => 'schallschlucker\simplecms\Backend',
			'languageManager' => simplecmsLanguageManager
	        ],
		'simplecms_frontend' => [
	            	'class' => 'schallschlucker\simplecms\Frontend',
			'languageManager' => simplecmsLanguageManager
	        ],
	],

After the modules registered, you should be able to open the administration backend by calling the "simplecms_backend" route e.g. by calling:
http://<your-server>/index.php?r=simplecms_backend
or if pretty URLs are acitvated:
http://<your-server>/simplecms_backend

Then you should see CMS Administration Backend with a root node in the page-tree.

# Please note:
In order for the page administration to work you need to be logged in, otherwise an error will occur since the user id will be stored for auditing purposes upon page creation or modification.

## License

yii2-simple-cms is released under the Apache License 2.0. See the bundled [LICENSE.md](LICENSE.md) for details.
