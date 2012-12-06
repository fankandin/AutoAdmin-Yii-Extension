The AutoAdmin is a "CMS framework". It's a perfect solution for web projects with **free designed databases**. It really does for portals as well as for "turnkey websites". Easy-to-Learn and easy-to-use.

The AutoAdmin includes built-in module to provide shared access to interfaces with different rights.

##Links

* [Try the Demo](http://palamarchuk.info/autoadmin/)
* [Yii AutoAdmin Extension on GitHub](https://github.com/vintage-dreamer/AutoAdmin-Yii-Extension)

##Requirements

PHP 5.3, Yii 1.1x

The extension uses PDO interfaces and was tested on MySQL and PostgreSQL databases.

##Setup

###Quick start

You may download the special [exemplary pack](http://palamarchuk.info/download/AutoAdmin_exemplary_distributive.zip) which is a very good solution for **quick start**. It contains full directories structure, configs, controllers and SQL dump.

###Manual start

_*Note:* There are several enhancements in version 1.1 that have simplified setup process. So for setup of previous versions see an appropriate ReadMe._

There are only two steps to install the AutoAdmin extension:

1. Put the distributive files into _[protected/extensions]_ folder of your Yii application.
2. Create module folder _[autoadmin]_ in _[protected/modules]_ directory using a standart Yii module structure, but without module class file _AutoAdminModule.php_ (which inherites _CWebModule_) - it will be included from the extension. 

###Yii config setup

Set necessary parameters:
~~~
[php]
<?php
//In this example we read the main config.
//Note if you use a fully separate config just set appropriate sections in the returning array.
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

Create a dedicated user for service DB (imported from distributive dump) and grant him appropriate access rights. If you use the only, common DB, just clone settings from a primary connection to "dbAdmin".

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

If you use different DB schemes, you may configure them using special params:
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

###Fields configuration
Data fields configurations must be set by passing a special-formatted array as an argument to the _AutoAdmin::fieldsConf()_ function.
~~~
[php]
	$this->module->fieldsConf(array(
		array([SQL field name], [AutoAdmin field type], [Form label], array([options])),
		//...
	));
~~~

To avoid "monkey-coding" you can use the AutoAdmin Code Generator to generate default interfaces (Yii actions) based on SQL tables service info. To use the Generator go to the "_/autoadmin/aagenerator/_" URL or just click on the link in the right bottom corner of any page. The feature requires the root access level (or disabling the authentication system).

![AutoAdmin Code Generator](http://palamarchuk.info/i/autoadmin/autoadmin_gen1.png "")

AutoAdmin provides various built-in types. Most of them accept the following standart options:

 * _show_: Data from a field should be displayed in the list mode.
 * _search_: A field can be searched by in the list mode.
 * _null_: A field can contain the NULL value.
 * _default_: Field's default value.
 * _bind_: The list query will be constrained with _WHERE ... AND field-name=bind-value_. The _bind-value_ is the value of this option.
 * _bindBy_: The list query will be constrained with _WHERE ... AND field-name=bind-value_. The _bind-value_ is a value passed by a parent interface in a parameter named as PrimaryKey field name from a parent interface. Usually it just has _'id'_ value.
 * _default_: Field's default value.
 * _pattern_: A regexp pattern as input parameter for HTML 5.

#### string
Classical text strings. Usually used with VARCHAR SQL type.

Additional options

* _maxlength_: The maximum length of a string.

#### text
Textareas for HTML-formatted texts. Usually used with TEXT type.

Additional options

* _directoryPath_: HTML-oriented diectory path to upload images to. Won't be stored as part of a value in DB.

#### tinytext
Textareas for short texts without complicated formatting. Usually used with TEXT type.

#### wysiwig
TineMCE visual text editor. Usually used with TEXT type.
To use this field you need to download the ["TinyMCE jQuery package"](http://www.tinymce.com/download/download.php), then unpack it to [/js/] directory of your DocumentRoot. If you use another JS directory you can set it up in options, as well as documented TinyMCE options (overriding the default ones):

~~~
[php]
	...
	array('content', 'wysiwig', 'Page content', array('show', 'null', 'directoryPath'=>'/i/articles/', 'subDirectoryPath'=>date('Y-m'),
			'tinyMCE'=>array(
				'dir'=>'/js/tinymce',
				'options'=>array(
					//Documented TineMCE options which you can override individually
					'plugins' => 'pagebreak,style,layer,table,save,advhr,advimage',
				),
			),
		)),
~~~

#### num
Numbers - integer or decimal. Usually used with INTEGER and DECIMAL (NUMERIC, FLOAT etc.) types.

Additional options:

* _max_: The maximum number.
* _min_: The minimum number.

#### enum
Predefined sets of values. Usually used with ENUM type.

Obligatory options:

* _enum_: An array of 'SQL value'=>'Option label' pairs

~~~
[php]
	..., 'enum'=>array('deg1'=>'I degree', 'deg2'=>'II degree', 'deg3'=>'III degree'), ...
~~~

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

Additional options:

* _maxlength_: The maximum password length.

#### image
To upload images. Uses database to store path to file only.

Obligatory options:

* _directoryPath_: HTML-oriented diectory path to upload images to. Won't be stored as part of a value in DB.

Additional options:

* _description_: Additional info for an upload form.
* _subDirectoryPath_: Relative directory path after _directoryPath_. Will be stored as part of a value in DB. Used for dynamic subdirectories.
* _popup_: The boolean parameter used to show images by popuping them instead of inline displaying in the listmode.

~~~
[php]
	..., 'directoryPath'=>'/i/flags/120', 'subDirectoryPath'=>date('Ym'), 'description'=> '120x80px'
~~~

#### file
To upload files in public areas and give links to them. Uses database to store path to file only.

Obligatory options:

* _directoryPath_: HTML-oriented diectory path to upload images to. Won't be stored as part of a value in DB.

Additional options:

* _description_: Additional info for an upload form.
* _subDirectoryPath_: Relative directory path after _directoryPath_. Will be stored as part of a value in DB. Used for dynamic subdirectories.

#### foreign
For values from other tables which are linked with the field through a foreign key (you may use virtual connection like as in MyISAM).

Obligatory options:
* _foreign_: Describes one-to-many connections.
~~~
[php]
	..., 'foreign', array(
		'table'	=> 'continents',
			'pk'		=> 'id',	//foreign primary key
			'select'	=> array('name_en'),	//foreign fields to select
			'order'		=> 'name_en',	//foreign fields to order by
		), ...
~~~

###Spatial field types
You can manage spatial SQL data in the AutoAdmin after installing [the AutoAdminGIS extension](http://www.yiiframework.com/extension/autoadmingis). After that the following field types will be accessible: **gispoint**, **gislinestring**, **gispolygon**. For more information see [AutoAdminGIS page](http://www.yiiframework.com/extension/autoadmingis).

###Custom field types
AudoAdmin is an extendable system. Particularly you can create your own field types by programming classes that implement *AAIField* interface.

You may also inherit built-in field-type classes and modify theirs behavior, add custom options etc.

Complicated content-management tasks may require complex logic custom fields need. In that case you can create a sub-extension for the AutoAdmin. An example of such development is [the AutoAdminGIS extension](http://www.yiiframework.com/extension/autoadmingis).

##Supported languages
English, Russian.

##What's next?

* Built-in interfaces to read all logs (now you have to list it in DB directly).