AutoAdmin is a "CMS framework". It's a perfect solution for web projects with **free designed databases**. It really does for portals as well as for "turnkey websites". Easy-to-Learn and easy-to-use.

AutoAdmin includes built-in module to provide shared access to interfaces with different rights.

##Links

* [Try the Demo](http://palamarchuk.info/autoadmin/)
* [Yii AutoAdmin Extension on GitHub](https://github.com/vintage-dreamer/AutoAdmin-Yii-Extension)

##Requirements

Yii 1.1 or above (AutoAdmin requires jQuery 1.7.1 and jQuery UI 1.8).

The extension uses PDO interfaces and was tested on MySQL and PostgreSQL databases.

##Setup

_*Note:* There are several enhancements in version 1.1 that have simplified setup process. So for setup of previous versions see an appropriate ReadMe._

There are only two steps to install the AutoAdmin extension:

1. Put the distributive files into _[protected/extensions]_ folder of your Yii application.
2. Create module folder _[autoadmin]_ in _[protected/modules]_ directory using a standart Yii module structure, but without module class (which inherites _CWebModule_) - it will be included from the extension. 

If you plan to use the built-in AutoAdmin shared access system, you also need to import SQL dump which you can find in _[autoAdmin/schemas]_ of the distributive directory.

###Yii config setup

Set necessary parameters:
~~~
[php]
$main['modules'] = array(
	'autoadmin'=>array(
		'class'=>'application.modules.autoadmin.AutoAdmin',
		'basePath' => dirname(__FILE__).'/../modules/autoadmin',
		'wwwDirName' => 'www',	//your DocumentRoot
		//Optional params:
		'authMode' => false,	//Switch built-in authorization system
		'openMode' => true,	//If true resets all limits on rights
		'logMode' => false,	//Switch log mode
	),
);
//...
$main['components'] = array(
	'urlManager' => array(
		//...
		'rules'=>array(
			//Module paths should be configured in a standart way
			'/' => 'autoadmin/default/index',
			'<controller:\w+>' => 'autoadmin/<controller>/index',
			'<controller:\w+>/<action:\w+>' => 'autoadmin/<controller>/<action>',
		)
		//...
~~~

We recommend using a separate config for AutoAdmin. It can be easy made by creating a folder (e.g. _[/www/_admin]_) with copies of _.htaccess_ (with the same ModRewrite rules and perhaps HTTP authorization instructions) and _index.php_ which refers to a separate Yii configuration file.

##Usage

You may try real working AutoAdmin CMS [here](http://palamarchuk.info/autoadmin/). In this "showroom" you'll find several good examples of interfaces with source PHP and SQL code.

###Trivial interface
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
		$this->module->setAccessRights(array('read'));
	}
}
~~~
![Illustration of the trivial interface](http://palamarchuk.info/i/autoadmin/autoadmin_sh1.jpg "")

###Complicated interface
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
You can manage spatial SQL data in AutoAdmin after installing [the AutoAdminGIS extension](http://www.yiiframework.com/extension/autoadmingis). After that the following field types will be accessible: *gispoint*, *gislinestring*, *gispolygon*. For more information see [AutoAdminGIS page](http://www.yiiframework.com/extension/autoadmingis).

###Custom field types
AudoAdmin is an extendable system. Particularly you can create your own field types by programming classes that implement *AAIField* interface.

Complicated content-management tasks may require complex logic custom fields need. In that case you can create a subextension for AutoAdmin. An example of such development is [the AutoAdminGIS extension](http://www.yiiframework.com/extension/autoadmingis).

##Supported languages
English, russian.

##What's next?

* Gii-like auto-generator of interfaces.
* New field types (WYSIWYG is being ported).
* Built-in interfaces to read all logs (now you have to list it in DB directly).