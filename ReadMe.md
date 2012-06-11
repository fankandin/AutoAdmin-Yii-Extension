Yii AutoAdmin CMS extension
===========================

### Resources

* [Yii AutoAdmin Extension on Google Code](https://code.google.com/p/autoadmin-yii-extension/)
* [Yii AutoAdmin Extension on GitHub](https://github.com/vintage-dreamer/AutoAdmin-Yii-Extension)
* [Yii Framework](http://yiiframework.com/)

### Requirements

* Yii 1.1 or above

## Installation  

There are only four steps to install the extensions:

	# Place the distributive files into [protected/extensions] folder of your Yii application.
	# Edit the config: add the module and urlManager rules. (see details).
	# Create any folder in www? (your DocumentRoot?) directory where the CMS entrance point will be.
	# Create autoadmin folder in application modules directory by the standart Yii schema, but without CWebModule file. 

If you plan to use the AutoAdmin authorization system, you also need to import SQL dump which you can find in [autoAdmin/schemas] of the distributive directory.

### Config

You need to add something like this into modules section of your Yii config:

```php
        'autoadmin'=>array(
                'class'=>'ext.autoAdmin.AutoAdmin',
                'basePath' => dirname(__FILE__).'/../modules/autoadmin',
                'layout' => 'main',
                'wwwDirName' => 'www',
                'dbConnection' => 'dbAdmin',
                'dbSchema' => 'mydb', //You should place here a database name if you use a different name in the "dbAdmin" param
                'dbAdminTablePrefix' => 'aa_',
                'dbAdminSchema' => 'admin',
                'authMode' => true,
                'openMode' => false,
                'logMode' => true,
        ),
``` 

That's for components -> urlManager -> rules section:
```php
		'<controller:aajax>/<action:\w+>' => 'autoadmin/<controller>/<action>',
		'<controller:afile>/<action:\w+>' => 'autoadmin/<controller>/<action>',
		'<controller:\w+>/foreign-<key:\w+>' => 'autoadmin/<controller>/foreign<key>',
``` 

### Interfaces developing

Your autoadmin module folder is a standart Yii module folder: with controllers and layouts (but without CWebModule inherited file). You need to develope controllers by AutoAdmin? standart, you need to use some layout, but you needn't use special views scripts because of the AutoAdmin? distributive provides them and connects automatically.

Here is an example of AutoAdmin? controller:

```php
class LightningsController extends Controller
{
        public function actionStrokes()
        {
                $this->module->tableName('lightnings');
                $this->module->setPK('id');
                $this->module->fieldsConf(array(
                                array('amplitude', 'num', 'Amplitude', array('show', 'null')),
                                array('stroke_when', 'datetime', 'Stroke date and time'));
                $this->module->sortDefault(array('stroke_when'=>-1));
                $this->pageTitle = 'Lightning strokes';
                $this->module->process();
        }
}
```

### Entrance point folder

We reccomend to create the special folder for CMS and protect it with .htaccess Apache mechanism. You need to connect the special config here, with Yii index.php file and .htaccess.
Notes

	# You needn't to use the suggested SQL schema's dump cannonically, with separate a database or a schema. You can just create the tables in a same (with your application) database. But note: you may change table names' prefixes, constraints and indices, but you mustn't change the base table names, fields names or fields types.

## License

