# Yii2-simplecmd
A basic yii2 content management module for easily creating and maintaining a page structure, including navigation widget, full text search, image and document management and a wysiwyg html editor for the content pages.
Does not include an fine granulated access rights management, only differentiates between: anonymous/guest and logged in users. 
The extensions supports site structures in multiple languages for international sites.

> **NOTE:** Module is in initial development. Anything may change at any time.

## Documentation

Yii2-simple-cms documentation is available online: 

See [Project page](https://github.com/pkerspe/yii2-simple-cms) for source code
and [Project wiki](https://github.com/pkerspe/yii2-simple-cms/wiki) for more details, screenshots etc.

Installation instructions are located in the [installation guide](https://github.com/pkerspe/yii2-simple-cms/wiki)

Prefered way is by using composer:

    "require": {
        "schallschlucker/yii2-simple-cms": ">=0.1",
    }


After installation run migration for database table creation:

	php yii migrate --migrationPath=@schallschlucker/simplecms/migrations

## License

yii2-simple-cms is released under the Apache License 2.0. See the bundled [LICENSE.md](LICENSE.md) for details.
