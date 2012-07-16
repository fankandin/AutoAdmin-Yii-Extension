AutoAdmin is a "CMS framework". It's a perfect solution for web projects with free designed databases. It really does for portals as well as for "turnkey websites". Easy-to-Learn and easy-to-use.
AutoAdmin includes built-in module to provide shared access to interfaces with different rights.

##Links

* [Try the Demo](http://palamarchuk.info/autoadmin/)
* [Yii AutoAdmin Extension on GitHub](https://github.com/vintage-dreamer/AutoAdmin-Yii-Extension)

##Requirements

Yii 1.1 or above (AutoAdmin requires jQuery 1.7.1 and jQuery UI 1.8).

The extension uses PDO interfaces and was tested on MySQL and PostgreSQL databases.

##Setup

There are only four steps to install the AutoAdmin extension:

1. Put the distributive files into [protected/extensions] folder of your Yii application.
2. Edit the config: add the AutoAdmin module and set urlManager rules (see below).
3. Create any folder in [www] (your DocumentRoot) directory to serve as the point of entrance by web (e.g. /_admin/).
4. Create module folder [autoadmin] in [protected/modules] directory using a standart Yii module structure, but without module class (which inherites CWebModule) - it will be included from the extension. 

If you plan to use the built-in AutoAdmin shared access system, you also need to import SQL dump which you can find in [autoAdmin/schemas] of the distributive directory.

###Yii config setup

We recommend use a separate config for AutoAdmin.

Set necessary parameters:
~~~
[php]
$main['modules'] = array(
	'autoadmin'=>array(
		'class'=>'application.modules.autoadmin.AutoAdminDemo',
		'basePath' => dirname(__FILE__).'/../modules/autoadmin',
		'wwwDirName' => 'www',	//your DocumentRoot
		//Optional params:
		'authMode' => true,	//Switch built-in authorization system
		'openMode' => false,	//If true resets all limits on rights
		'logMode' => false,	//Switch log mode
	),
);
//...
$main['components'] = array(
	'urlManager' => array(
		//...
		'rules'=>array(
			'<controller:aa[a-z]+>/<action:\w+>' => 'autoadmin/<controller>/<action>',
			'<controller:\w+>/foreign-<key:\w+>' => 'autoadmin/<controller>/foreign<key>',
			//Module paths should be configured in a standart way
			'/' => 'autoadmin/default/index',
			'<controller:\w+>' => 'autoadmin/<controller>/index',
			'<controller:\w+>/<action:\w+>' => 'autoadmin/<controller>/<action>',
		)
		//...
~~~

Default AutoAdmin's skin is designed with Overcast jQuery UI style. For example you may use these options:
~~~
[php]
$main['components'] = array(
	//...
	'clientScript'=>array(
		'scriptMap'=>array(
			'jquery.js'		=> 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.js',
			'jquery.min.js'	=> 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js',
			'jquery.ui'		=> 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js',
			'jquery-ui.css'	=> 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/overcast/jquery-ui.css',
		)
	),
~~~

##Usage

You may try real working AutoAdmin CMS [here](http://palamarchuk.info/autoadmin/). In that "showroom" you'll find several good examples of interfaces with source PHP and SQL code.

###Trivial interface:
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

###Complicated interface:
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
					'searchBy'	=> array('name_en'=>'Country name'),	//Foreign field to search by
					'order'		=> 'name_en',	//Foreign field to order by
				)
			)),
		array('emblem', 'image', 'Team emblem', array(
				'null',	//Field can be NULL
				'directoryPath'=>'/i/teams/football',	//Directory to upload images (web-based style)
			)),
		array('emblem_sm', 'image', 'Team emblem <small>(small size)</small>', array('show', 'null', 'directoryPath'=>'/i/teams/football/sm')),
	));
	$this->module->sortDefault(array('name_en'));	//Default sorting
	$this->module->rowsOnPage = 20;	//Customizing "rows on page" in List mode
	$this->pageTitle = 'Sports teams';

	Yii::app()->clientScript->registerCssFile(Yii::app()->request->baseUrl.'/css/sport.css');	//Customizing CSS
	$this->module->setPartialView('teams-up', $area='up');	//Attaching the custom view that is displaying this information to you.

	$this->module->process();	//Initiate main processing
}
~~~

###Many to many link:
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

#### boolean
Boolean checkbox (yes or no). Usually used with BOOLEAN type.

#### password
For passwords. Will be hashed.

#### file
To upload files in public areas and give links to them. Uses database to store path to file only.

#### image
To upload images. Uses database to store path to file only.

#### foreign
For values from other tables which are linked with the field through a foreign key (you may use virtual connection like in MyISAM).

##What's next?

* Gii-like auto-generator of interfaces.
* New field types (WYSIWYG is being ported).
* Built-in interfaces to read all logs (now you have to list it in DB directly).