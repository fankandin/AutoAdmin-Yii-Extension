AutoAdmin is a "CMS framework". It's a perfect solution for web projects with **free designed databases**. It really does for portals as well as for "turnkey websites". Easy-to-Learn and easy-to-use.

AutoAdmin includes built-in module to provide shared access to interfaces with different rights.

##Links

* [Try the Demo](http://palamarchuk.info/autoadmin/)
* [Yii AutoAdmin Extension on GitHub](https://github.com/vintage-dreamer/AutoAdmin-Yii-Extension)

##Requirements

Yii 1.1 or above (AutoAdmin requires jQuery 1.7.1 and jQuery UI 1.8).

The extension uses PDO interfaces and was tested on MySQL and PostgreSQL databases.

##Setup

###Quick start

You may download the special [exemplary pack](http://palamarchuk.info/download/AutoAdmin_exemplary_distributive.zip) which is a very good solution for **quick start**. It contains full directories structure, configs, controllers and SQL dump.

###Manual start

_*Note:* There are several enhancements in version 1.1 that have simplified setup process. So for setup of previous versions see an appropriate ReadMe._

There are only two steps to install the AutoAdmin extension:

1. Put the distributive files into _[protected/extensions]_ folder of your Yii application.
2. Create module folder _[autoadmin]_ in _[protected/modules]_ directory using a standart Yii module structure, but without module class AutoAdminModule.php (which inherites _CWebModule_) - it will be included from the extension. 

###Yii config setup

Set necessary parameters:
~~~
[php]
<?php
//In this example we read the main config.
//Note if you use fully separate config just set approriate sections in the returning array.
$main = require(dirname(__FILE__).'/main.php');

$main['modules'] = array(
	'autoadmin'=>array(
		'class'=>'ext.autoAdmin.AutoAdmin',
		'basePath' => dirname(__FILE__).'/../modules/autoadmin',
		'wwwDirName' => 'www',	//your DocumentRoot
	),
);
//...
$main['components'] = array(
	'urlManager' => array(
		//...
		'rules'=>array(
			//Module paths should be configured in a standart way
			'/<module:autoadmin>' => 'autoadmin/default/index',
			'/<module:autoadmin>/<controller:\w+>' => 'autoadmin/<controller>/index',
			'/<module:autoadmin>/<controller:\w+>/<action:\w+>' => 'autoadmin/<controller>/<action>',
		)
		//...
~~~

After all, your common AutoAdmin file structure should be something like this (recommended case):
~~~
 - protected/
 - - ...
 - - extensions/
 - - - ...
 - - - autoAdmin/
 - - - - assets/
 - - - - controllers/
 - - - - helpers/
 - - - - messages/
 - - - - models/
 - - - - schemas/
 - - - - views/
 - - - - AutoAdmin.php
 - - - - AutoAdminAccess.php
 - - - - AutoAdminIExtension.php
 - - - - LICENSE
 - - - - ReadMe.md
 - - ...
 - - modules/
 - - - ...
 - - - autoadmin/
 - - - - controllers/
 - - - - models/
 - - - - views/
 - www/
 - - ...
~~~

###Authentication system

Optionally you can use the built-in AutoAdmin's shared access system.

Firstly import SQL dump which you can find in _[autoAdmin/schemas]_ of the distributive directory. It's recommended to do it in a separate database (if you have such a possibility).

Add this params to the config:
~~~
[php]
$main['modules'] = array(
	'autoadmin'=>array(
		//...
		'authMode' => true,	//Switch on authorization system
		'openMode' => true,	//Use for temporary switching off all access limitations
		'logMode' => false,	//Switch log mode
	),
);
~~~

Create a dedicated user for service DB (imported from distributive dump) and grant him approriate access rights. If you use the only, common DB, just clone settings from a primary connection to "dbAdmin".

~~~
[php]
$main['components'] = array(
		//...
	'db' => array(
		'class'=>'CDbConnection',
		'connectionString' => 'mysql:host=localhost;dbname=yourdb',
		'username' => 'yourlogin_that_can_edit',
		'password' => 'freepussyriot',
		'charset' => 'utf8',
			//...
	),
	'dbAdmin' => array(
		'class'=>'CDbConnection',
		'connectionString' => 'mysql:host=localhost;dbname=yourdb_autoadmin',
		'username' => 'yourlogin_aa',
		'password' => 'freepussyriot',
		'charset' => 'utf8',
			//...
	),
		//...
~~~

If you use different DB schemas, you may configure them using special params:
~~~
[php]
$main['modules'] = array(
	'autoadmin'=>array(
		//...
		'dbSchema' => 'public',
		'dbAdminSchema' => 'autoadmin',
	),
);
~~~

At first time you enter AutoAdmin you'll be forwarded to the special form to create root (and other) users.

Create actions (AutoAdmin interfaces) and only then grant personal rights to users on them. Use the link in the right bottom corner:

![The link to access sharing interfaces](http://palamarchuk.info/i/autoadmin/autoadmin_shared1.png "")

![Manipulating with interfaces' sharing](http://palamarchuk.info/i/autoadmin/autoadmin_shared2.jpg "")

##Usage

You may try real working AutoAdmin CMS [here](http://palamarchuk.info/autoadmin/). In this "showroom" you'll find several good examples of interfaces with source PHP and SQL code.

###Trivial interface
Let's suppose you have the SQL table:

![SQL structure](http://palamarchuk.info/i/autoadmin/autoadmin_trivial1_sql.png "")

Then your AutoAdmin action would be like this:
~~~
[php]
class SportController extends Controller
{
	public function actionContinents()
	{
		$this->module->tableName('continents');
		$this->module->setPK('id');
		$this->module->fieldsConf(array(
				array('name_en', 'string', 'Continent', array('show')),
			));
		$this->module->setSubHref('countries');
		$this->module->sortDefault(array('name_en'));
		$this->module->process();
	}

	public function actionCountries()
	{
		$this->module->tableName('countries');
		$this->module->setPK('id');
		$this->module->fieldsConf(array(
			array('flag_ico', 'image', 'Flag', array('show', 'directoryPath'=>'/i/flags')),
			array('flag', 'image', 'Flag', array('directoryPath'=>'/i/flags/120', 'description'=>'120x80 px')),
			array('name_en', 'string', 'Country name', array('show')),
			array('continent_id', 'foreign', 'Continent', array('bindBy'=>'id', 'foreign'=>array(
					'table'		=> 'continents',
					'pk'		=> 'id',
					'select'	=> array('name_en'),
					'order'		=> 'name_en',
			))),
		));
		$this->module->sortDefault(array('name_en'));

		$this->module->process();
 	}
}
~~~
![Illustration of the trivial interface](http://palamarchuk.info/i/autoadmin/autoadmin_sh1.jpg "")

###Complicated interface
![SQL structure](http://palamarchuk.info/i/autoadmin/autoadmin_compl1_sql.png "")

~~~
[php]
public function actionTeams()
{
	$this->module->tableName('teams');	//SQL table name
	$this->module->setPK('id');	//Primary key name (use an array for composite keys)
	$this->module->fieldsConf(array(
		//	SQL field name; Field type; Field Label; Options
		array('name_en', 'string', 'Team name', array('show', 'search')),
		array('country_id', 'foreign', 'Country', array(
				'show',	//Show in List mode
				'search',	//User is allowed to search by this field
				'bindBy'=>'id',	//Country is fixed by previous interface. Set that field.
				'foreign'=>array(	//Foreign key options
					'table'		=> 'countries',	//Table which it belongs to
					'pk'		=> 'id',	//Foreign table's PK
					'select'	=> array('name_en'),	//Foreign field to select for listing
					'searchBy'	=> array('name_en'=>'Country name'),//Foreign field to search by
					'order'		=> 'name_en',	//Foreign field to order by
				)
			)),
		array('emblem', 'image', 'Team emblem', array(
				'null',	//Field can be NULL
				'directoryPath'=>'/i/teams/football',//Directory to upload images (web-based)
			)),
		array('emblem_sm', 'image', 'Team emblem <small>(small size)</small>', array('show', 'null', 'directoryPath'=>'/i/teams/football/sm')),
	));
	$this->module->sortDefault(array('name_en'));	//Default sorting
	$this->module->rowsOnPage = 20;	//Customizing "rows on page" in List mode
	$this->pageTitle = 'Sports teams';

	//Customizing CSS
	Yii::app()->clientScript->registerCssFile(Yii::app()->request->baseUrl.'/css/sport.css');
	//Attaching the custom view that is displaying this information to you.
	$this->module->setPartialView('teams-up', $area='up');

	$this->module->process();	//Initiate main processing
}
~~~
![Illustration of the complicated interface](http://palamarchuk.info/i/autoadmin/autoadmin_sh2.jpg "")

###Many to many link
~~~
[php]
$this->module->foreignLink('spheres', array(
	'label'			=> 'The spheres of activity',	//Iframe title
	'show'			=> true,	//Show in List mode

	'linkTable'		=> 'brands_spheres',	//Table of links (between many-to-many)
	'inKey'			=> 'brand_id',	//In key (for local PK)
	'outKey'		=> array('sphere_id'=>'id'),	//Out key (for external PK)
	'targetTable'	=> 'spheres',	//External table on the other side of many-to-many
	'targetFields'	=> array('title_en'),	//Fields to select for listing
));
~~~
![Illustration of many-to-many interface](http://palamarchuk.info/i/autoadmin/autoadmin_sh3.jpg "")

Much more examples you can find in [the AutoAdmin showroom](http://palamarchuk.info/autoadmin/).

###Field types
You can easily develope custom field types. A Field class have to inherite AAField and redefine methods you want to make custom.

AutoAdmin includes the following types:

#### string
Standart text strings. Usually used with VARCHAR SQL type.

#### text
Textareas for HTML-formatted texts. Usually used with TEXT type.

#### tinytext
Textareas for short texts without complicated formatting. Usually used with TEXT type.

#### wysiwig
TineMCE visual text editor. Usually used with TEXT type.
Note: to use this field you need to install [TineMCE extension](http://www.yiiframework.com/extension/tinymce).

#### num
Numbers - integer and decimal. Usually used with INTEGER and DECIMAL (NUMERIC, FLOAT etc.) types.

#### enum
Predefined sets of values. Usually used with ENUM type.

#### date
Dates. Usually used with DATE type.

#### datetime
Date and time. Usually used with DATETIME and TIMESTAMP types.

#### time
Time. Usually used with TIME type in SQL. Fully supports 'limit' options to restrict lower and upper values.

#### boolean
Boolean checkbox (yes or no). Usually used with BOOLEAN type.

#### password
For passwords. Will be hashed.

#### file
To upload files in public areas and give links to them. Uses database to store path to file only.

#### image
To upload images. Uses database to store path to file only.

#### foreign
For values from other tables which are linked with the field through a foreign key (you may use virtual connection like as in MyISAM).

###Spatial field types
You can manage spatial SQL data in AutoAdmin after installing [the AutoAdminGIS extension](http://www.yiiframework.com/extension/autoadmingis). After that the following field types will be accessible: **gispoint**, **gislinestring**, **gispolygon**. For more information see [AutoAdminGIS page](http://www.yiiframework.com/extension/autoadmingis).

###Custom field types
AudoAdmin is an extendable system. Particularly you can create your own field types by programming classes that implement *AAIField* interface.

Complicated content-management tasks may require complex logic custom fields need. In that case you can create a subextension for AutoAdmin. An example of such development is [the AutoAdminGIS extension](http://www.yiiframework.com/extension/autoadmingis).

##Supported languages
English, russian.

##What's next?

* Gii-like auto-generator of interfaces.
* Built-in interfaces to read all logs (now you have to list it in DB directly).