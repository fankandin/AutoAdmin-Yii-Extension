<?php
/**
 * 
 * AutoAdmin. Flexible DataBase Management System.
 * @version 1.1.4 (Yii Framework Edition)
 * @author Alexander Palamarchuk <a@palamarchuk.info>. 2003-2012
 * @copyright Alexander Palamarchuk <a@palamarchuk.info>. 2003-2012
 * 
 * @property string $manageAction The current interface mode.
 * @property AutoAdminAccess $accessManaging authorized access
 * @property string $interface Unique alias for the current management interface.
 * @property CController $controller Yii controller object.
 * @property AATrigger $trigger Trigger manipulating (DB triggers analogue).
 * @property AAFields $data An object that manages DB fields configuration.
 * @property string $tableName Main table name.
 * @property bool $iframeMode Wheter the current interface running in iframe mode (as foreignLink).
 * @property array $viewData Common storage for view scripts.
 * 
 * @todo Gii-like automatic creation interfaces configurations (Controllers & Actions).
 */
Yii::import('ext.autoAdmin.*');
Yii::import('ext.autoAdmin.models.*');
Yii::import('ext.autoAdmin.models.field.*');
Yii::import('ext.autoAdmin.helpers.*');
Yii::import('ext.autoAdmin.controllers.*');

class AutoAdmin extends CWebModule
{
	/**
	 * Prefix for names of input elements in the general form.
	 */
	const INPUT_PREFIX = 'AA';

	protected $_manageAction;
	protected $_access;
	protected $_interface;

	protected $_controller;
	protected $_trigger;

	protected $_data;
	protected $_db;
	protected $_iframeMode = false;
	protected $_viewData = array();

	/**
	 *
	 * @var int Current page (for paginator).
	 */
	public $managePage;

	/**
	 *
	 * @var bool Whether to authenticate users or not with internal mechanisms.
	 */
	public $authMode = false;
	/**
	 *
	 * @var bool Whether to operate in open mode (without internal authentification and authorization)
	 */
	public $openMode = true;
	/**
	 *
	 * @var bool Whether to log all DB queries of update, insert or delete type.
	 */
	public $logMode = false;
	/**
	 *
	 * @var AACache The object to operate with the site's global cache.
	 */
	public $cache;
	/**
	 * 
	 * @var string Public root directory of the site
	 */
	public $wwwDirName = 'www';
	/**
	 *
	 * @var string An alias of CDbConnection that set up in config. It will be used as Yii::app()->{$dbConnection}.
	 */
	public $dbConnection = 'db';
	/**
	 *
	 * @var string DB schema that contains tables which are operated by a user.
	 */
	public $dbSchema;
	/**
	 *
	 * @var string Prefix for names of service DB tables.
	 */
	public $dbAdminTablePrefix = 'aa_';
	/**
	 *
	 * @var string DB schema that contains AutoAdmin service tables.
	 */
	public $dbAdminSchema;
	/**
	 *
	 * @var string Link down to subtable interface.
	 * Service parameters will be added automatically.
	 */
	public $subHref;
	/**
	 *
	 * @var bool Whether to show checkboxes id list mode.
	 * Used to have a possibility to operate with rows in JavaScript.
	 * Also initiate setting all the data rows in FORM tag with link to the current page.
	 */
	public $checkboxes = false;
	/**
	 *
	 * @var array Additional actions for each row in list mode
	 */
	public $addActions;
	/**
	 *
	 * @var integer Maximum rows on page.
	 */
	public $rowsOnPage = 80;
	/**
	 *
	 * @var bool Whether to confirm any data deletion.
	 */
	public $confirmDelete = true;
	/**
	 *
	 * @var string Path to default layout. Can be configured.
	 */
	public $layout = 'ext.autoAdmin.views.layouts.main';
	/**
	 *
	 * @var string Path to layout extension views.
	 */
	public $viewsPath = 'ext.autoAdmin.views.';
	/**
	 * 
	 * @var array Additional view scripts to display in the list & edit modes
	 */
	public $partialViews = array();
	/**
	 *
	 * @var array User data for passing to a view script
	 */
	public $clientViewData = array();
	/**
	 *
	 * @var string Path to asset data.
	 */
	public static $assetPath;
	/**
	 *
	 * @var array Options for search by fields in the list mode.
	 * Format:
	 * array(
	 *  'by' => [the index of a field in AutoAdmin::fieldsConf() to search by],
	 *  'query' => [value to search],
	 * )
	 */
	public $searchOptions;

	/**
	 *
	 * @var array AutoAdmin specific extensions. An array of names which will be tried to use as "E"-prefixed ending part of folder name.
	 * @example "gis" key leads to "autoAdminEGis" folder.
	 */
	public $extensions = array();

	/**
	 * Inits of the class.
	 */
	public function init()
	{
		Yii::app()->user->setStateKeyPrefix('AUTOADMIN');
		$this->controllerMap['aafile'] = array('class'=>'ext.autoAdmin.controllers.AAFileController');
		$this->controllerMap['aaajax'] = array('class'=>'ext.autoAdmin.controllers.AAAjaxController');
		$this->controllerMap['aaauth'] = array('class'=>'ext.autoAdmin.controllers.AAAuthController');
		$this->controllerMap['aagenerator'] = array('class'=>'ext.autoAdmin.controllers.AAGeneratorController');
		self::$assetPath = Yii::app()->assetManager->publish(Yii::getPathOfAlias('ext.autoAdmin.assets'));

		$this->cache = new AACache();
		$this->_data = new AAData();
		$this->_db = new AADb($this->_data);
		//Link AADb properties with AutoAdmin properties for more convenient configurating these properties by a user.
		AADb::$dbConnection =& $this->dbConnection;
		$this->_db->dbSchema =& $this->dbSchema;
		AutoAdminAccess::$dbTablePrefix = $this->dbAdminTablePrefix;

		if($this->extensions)
		{
			foreach($this->extensions as $key=>$value)
			{
				if(is_string($key))
				{
					$extension = $key;
					$initData = &$value;
				}
				else
				{
					$extension = $value;
					$initData = array();
				}
				Yii::import("ext.autoAdminE{$extension}.*");
				//Yii::import("ext.autoAdminE{$extension}.AutoAdminE{$extension}");	//fix for "E"-prefix for case-sensitive file systems
				$extClass = "AutoAdminE{$extension}";
				$extClass::init($initData);
			}
		}
	}

	/**
	 * Classic Yii CWebModule method to perform some actions before user defined controller's ones.
	 * @param \CController $controller
	 * @param \CAction $action
	 * @return boolean 
	 */
	public function beforeControllerAction($controller, $action)
	{
		$this->_controller = $controller;
		$this->_trigger = new AATrigger($this->_controller);
		$this->_interface = self::interfaceID($controller->id, $action->id);

		if(!$this->authMode || $controller->id == 'aaauth')
			$this->openMode = true;
		if($this->authMode && Yii::app()->user->isGuest && ($controller->id != 'aaauth'))
		{
			if(!Yii::app()->request->isAjaxRequest)
			{
				Yii::app()->user->loginUrl = array('aaauth/login');
				Yii::app()->user->loginRequired();
			}
			else
				throw new CHttpException(403);
		}

		if(Yii::app()->request->getQuery('foreign', null))
		{
			$this->_iframeMode = Yii::app()->request->getQuery('foreign');
			$this->_controller->layout = 'ext.autoAdmin.views.layouts.iframe';
		}
		else
			$this->_controller->layout = $this->layout;

		$this->_manageAction = Yii::app()->request->getParam('action', 'list');
		if(!in_array($this->_manageAction, array('add', 'insert', 'edit', 'update', 'delete', 'upload', 'empty')))
			$this->_manageAction = 'list';
		$this->managePage = Yii::app()->request->getParam('page', 1);
		if($this->managePage < 1)
				$this->managePage = 1;

		$this->_data->binding = Yii::app()->request->getParam('bk', array());

		Yii::app()->clientScript->registerCoreScript('jquery')
				->registerCoreScript('jquery.ui')
				->registerCssFile(Yii::app()->clientScript->getCoreScriptUrl().'/jui/css/base/jquery-ui.css');

		$this->_viewData = array(
			'getParams'		=> $_GET,
			'viewsPath'		=> $this->viewsPath,
			'iframeMode'	=> (bool)$this->_iframeMode,
		);

		if(parent::beforeControllerAction($controller, $action))
		{
			return true;
		}
		else
			return false;
	}

	/**
	 * Generates the predefined name for an interface defining by controller's and action's IDs.
	 * @param string $controllerID Controller ID.
	 * @param string $actionID Action ID.
	 * @return string The name of the interface, based on controller's action's IDs.
	 */
	public static function interfaceID($controllerID, $actionID)
	{
		return strtolower("{$controllerID}-{$actionID}");
	}

	/**
	 * Overrides the standart interface name.
	 * @param string $interface The name of an interface that identificates it in unique way.
	 */
	public function setInterface($interface)
	{
		if(isset($this->_access))
			throw new AAException(Yii::t('AutoAdmin.errors', 'You can only specify the interface before access rights'));
		$this->_interface = $interface;
	}

	/**
	 * Inits all the settings through an array of options
	 * @param array $columns Array with options.
	 * 
	 * Here is an example:
	 * <code>
	 * <?php
	 * 		$columns = array(
	 *			array('Registered', 'when_reg', 'datetime', array('null', 'readonly', 'show'=>15, 'sort'=>-1)),
	 *			array('E-mail', 'email', 'string', array('null', 'show'=>20, 'sort')),
	 *		);
	 * ?>
	 * </code>
	 */
	public function fieldsConf($columns)
	{
		$this->_data->loadColumnsConf($columns, $this->tableName());
	}

	/**
	 * Sets the main DBtable name or returns it's name if null has been passed.
	 * @param string|null $tableName Table name.
	 * @return null|string Table name if $tableName is null.
	 * @throws AAException 
	 */
	public function tableName($tableName=null)
	{
		if(is_null($tableName))
			return $this->_db->tableName;
		if($tableName && is_string($tableName))
			$this->_db->tableName = $tableName;
		else
			throw new AAException(Yii::t('AutoAdmin.errors', 'Wrong data for DB Table Name'));
	}

	/**
	 * The alias to AAFields::setPK().
	 * @see AAFields::setPK()
	 */
	public function setPK($pk)
	{
		return $this->_data->setPK($pk);
	}

	/**
	 * Loads PrimaryKey values passed through a form or a link.
	 * @return boolean
	 * @throws AAException 
	 */
	protected function loadPK()
	{
		$pkValues = Yii::app()->request->getParam('id', array());
		if(!$pkValues)
			return false;
		if(array_keys($this->_data->pk) === array_keys($pkValues))
			$this->_data->loadPK($pkValues);
		else
			throw new AAException(Yii::t('AutoAdmin.errors', 'Wrong data for PrimaryKey'));
	}

	/**
	 * Sets the access rights to the administation interface.
	 * The most default rights are true for everithing. This function overrides default rights, but doesn't override permissions for a current user which were set with the special interface in service DB.
	 * @param array $rights An array of rights.
	 * 
	 * The permission for a particular right can be set in different ways. For example:
	 * <code>
	 * <?php
	 * $rights1 = array('read', 'edit');	//User will be able to read and update but only if it wasn't denied for him in the special DB table.
	 * $rights2 = array('delete'=>false, 'edit'=>-1, 'add'=>0); //All these variants are equivalent and sets the deny for delete, update or add actions.
	 * $rights3 = array('delete'=>false);	//Denies only delete. Other permissions are by default.
	 * $this->module->setAccessRights($rights1);	//call the function
	 * ?>
	 * </code>
	 */
	public function setAccessRights($rights)
	{
		if(!is_array($rights))
			throw new AAException(Yii::t('AutoAdmin.errors', 'Wrong params for access rights'));
		$this->initAccess();
		$this->_access->setRight(null, false);
		foreach($rights as $key=>$value)
		{
			if(is_string($key))
			{
				$right = $key;
				$permition = ($value===-1 ? false : (bool)$value);
			}
			else
			{
				$right = $value;
				$permition = true;
			}
			$this->_access->setRight($right, $permition);
		}
	}

	/**
	 * Sets the link to a sublevel interface.
	 * Call this function only after fieldsConf().
	 * @param string $href Base href: the controller's action name - in non-directly ($directly=false) mode, a complete href othwerwise.
	 * @param bool $directly If set as true, no changes will be applied to $href. Otherwise it would be processed in compliance with internal rules.
	 */
	public function setSubHref($href, $directly=false)
	{
		if($directly)
			$this->subHref = $href;
		else
		{
			if(!$this->_data->fields)
				throw new AAException(Yii::t('AutoAdmin.errors', 'Function setSubHref() can be called only after fieldsConf()'));

			$this->subHref = $this->_controller->createUrl("{$this->_controller->id}/{$href}");
			//We have to store all parent "bk" sets. So we did it using "bkp" param as a stack.
			$bk = Yii::app()->request->getParam('bk', array());
			$bkp = Yii::app()->request->getParam('bkp', array());	//Parent interfaces' bindings
			array_push($bkp, $bk);
			$this->subHref = AAHelperUrl::replaceParam($this->subHref, 'bkp', $bkp);
		}
	}

	/**
	 * Adds a trigger. Manipulates with AATrigger::addTrigger().
	 * @param string $methodName User controller's method to call.
	 * @param array|null $actions Array of "insert", "update" or "delete". If null every action will be used.
	 * @param string $when "before" or "after" action with DB.
	 */
	public function setTrigger($methodName, $actions=null, $when='after')
	{
		$this->_trigger->addTrigger($methodName, $actions=null, $when='after');
	}

	/**
	 * Executes the testing of compiled user's configuration.
	 * @return bool whether the configuration is correct.
	 */
	public function testConf()
	{
		if(!$this->_data->pk)
			return false;
		return true;
	}

	/**
	 * Initiates AutoAdminAccess object.
	 */
	protected function initAccess()
	{
		$this->_access = new AutoAdminAccess($this->_interface);
		$this->_access->setOpenMode($this->openMode);
		$this->_access->loadAccessSettings();
	}

	/**
	 * Executes direct processing of state and activate internal methods.
	 * Must be called only at the very end of a controller method.
	 */
	public function process()
	{
		if(!isset($this->_access))
			$this->initAccess();
		$this->loadPk();
		if(!$this->testConf())
			throw new AAException(Yii::t('AutoAdmin.errors', 'Incorrect configuration'));
		$this->_viewData = array_merge($this->_viewData, array(
			'rights'	=> $this->_access->exportRights(),
			'pk'		=> $this->_data->pk,
			'partialViews'	=> $this->partialViews,
			'clientData'	=> $this->clientViewData,
			'interface'		=> $this->_interface,
			'bindKeys'		=> Yii::app()->request->getParam('bk', array()),
			'bindKeysParent'=> Yii::app()->request->getParam('bkp', array()),
			'manageAction'	=> &$this->_manageAction,
		));

		switch($this->_manageAction)
		{
			case 'add':	//Form for data adding
			{
				if(!$this->_access->checkRight('add'))
					$this->blockAccess();
				$this->editMode();
				break;
			}
			case 'edit':	//Form for data editing
			{
				if(!$this->_access->checkRight('edit'))
					$this->blockAccess();
				$this->editMode();
				break;
			}
			case 'insert':	//Process inserting data in DB
			{
				if(!$this->_access->checkRight('add'))
					$this->blockAccess();
				$affected = $this->insert();
				$this->resultMode(array('msg'=>Yii::t('AutoAdmin.messages', ($affected ? 'The record was added' : 'The record was not added'))));
				break;
			}
			case 'update':	//Process updating data in DB
			{
				if(!$this->_access->checkRight('edit'))
					$this->blockAccess();
				$affected = $this->update();
				$this->resultMode(array('msg'=>Yii::t('AutoAdmin.messages', ($affected ? 'The record has been changed' : 'The record has not been changed'))));
				break;
			}
			case 'delete':	//Process deletion data in DB
			{
				if(!$this->_access->checkRight('delete'))
					$this->blockAccess();
				$deletingRow = $this->_db->getCurrentRow();
				if(!$deletingRow)
					throw new CHttpException(404, Yii::t('AutoAdmin.errors', 'Can\'t find the record'));

				if($this->confirmDelete && !Yii::app()->request->getPost('sure', false))
				{
					$confirmUrl = AAHelperUrl::update(Yii::app()->request->requestUri, array('action'), array('action'=>'delete'));
					$cancelUrl = AAHelperUrl::stripParam(Yii::app()->request->requestUri, array('action'));
					foreach($this->_data->pk as $pkField=>$value)
						$cancelUrl = AAHelperUrl::stripParam($cancelUrl, "id[{$pkField}]");
					foreach($this->_data->fields as $k=>&$field)
						$field->value = $deletingRow->fields[$k]->value;
					$this->_controller->render($this->viewsPath.'confirmDelete', array('confirmUrl'=>$confirmUrl, 'cancelUrl'=>$cancelUrl, 'fields'=>$this->_data->fields));
					Yii::app()->end();
				}
				else
				{
					$affected = $this->delete($deletingRow);
					$this->resultMode(array('msg'=>Yii::t('AutoAdmin.messages', ($affected ? 'The record was deleted' : 'The record has not been deleted'))));
				}
				break;
			}
			case 'upload':	//Uploading a file for field
			{
				if(!$this->_access->checkRight('edit'))
					$this->blockAccess();
				$viewData = array('field'=>Yii::app()->request->getParam('field'));
				if(!empty($_POST[self::INPUT_PREFIX]['upload']) && !empty($_FILES[self::INPUT_PREFIX]['name']['uploadFile']))
				{
					$upload = $_POST[self::INPUT_PREFIX]['upload'];
					if(preg_match('/'.self::INPUT_PREFIX.'\[([^\]]+)\]$/', $upload['field'], $matches))
					{
						$field = $this->_data->getFieldByName($matches[1]);
						if(!isset($field->options['directoryPath']))
							throw new AAException(Yii::t('AutoAdmin.errors', 'The parameter "{paramName}" must be set for the field {fieldName}', array('parameter'=>'directoryPath', '{fieldName}'=>$field->name)));
						$uploadDir = $field->options['directoryPath'];
						if($field->options['subDirectoryPath'])
							$uploadDir .= '/'.$field->options['subDirectoryPath'];
						
						$viewData['uploadedFileName'] = AAHelperFile::uploadFile('uploadFile', $uploadDir);
						$viewData['uploadedFileAbs'] = Yii::app()->basePath.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.Yii::app()->modules['autoadmin']['wwwDirName'].str_replace('/', DIRECTORY_SEPARATOR, $uploadDir).DIRECTORY_SEPARATOR.$viewData['uploadedFileName'];
						$viewData['uploadedFileSrc'] = "{$uploadDir}/{$viewData['uploadedFileName']}";
						$viewData['alt'] = !empty($upload['alt']) ? $upload['alt'] : '';
						$viewData['fieldName'] = $field->formInputName();
					}
				}
				$this->_controller->layout = 'ext.autoAdmin.views.layouts.fileUpload';
				$this->_controller->render($this->viewsPath.'fileUpload', $viewData);
				break;
			}
			case 'empty':	//Interface which is programmed directly by user
			{
				if(!$this->_access->checkRight('read'))
					$this->blockAccess();
				$this->_controller->render($this->viewsPath.'empty', $this->_viewData);
				break;
			}
			default:	//Data list show (default mode)
			{
				if(!$this->_access->checkRight('read'))
					$this->blockAccess();
				$this->listMode();
				break;
			}
		}
	}

	/**
	 * Data list mode.
	 */
	protected function listMode()
	{
		$dataToPass = $this->_viewData;
		if($this->subHref)	//ссылка на вложенный раздел
			$dataToPass['urlSub'] = $this->subHref;

		if(Yii::app()->request->getParam('msg'))
			$this->view->addMessage(Yii::app()->request->getParam('msg'));

		$this->_db->initList();

		//For search by GET-params has priority against user-defined!
		$this->searchOptions = array(
			'by' => (!is_null(Yii::app()->request->getQuery('searchBy')) ? Yii::app()->request->getQuery('searchBy') : (isset($this->searchOptions['by']) ? $this->searchOptions['by'] : null)),
			'query' => (!is_null(Yii::app()->request->getQuery('searchQ')) ? Yii::app()->request->getQuery('searchQ') : (isset($this->searchOptions['query']) ? $this->searchOptions['query'] : null)),
		);
		if(isset($this->searchOptions['by']) && isset($this->searchOptions['query']))
			$this->_data->setSearch($this->searchOptions['by'], $this->searchOptions['query']);
		if($this->_data->searchOptions)
			$dataToPass['searchAvailable'] = true;
		else
		{
			foreach($this->_data->fields as $field)
			{
				if(!empty($field->options['inSearch']))
				{
					$dataToPass['searchAvailable'] = true;
					break;
				}
			}
		}

		//Preparing sort fields for ORDER BY in the query
		$sortBy = Yii::app()->request->getQuery('sortBy', null);
		if(!is_null($sortBy) && isset($this->_data->fields[abs($sortBy)-1]) && $this->_data->fields[abs($sortBy)-1]->showInList)
		{	//User sorting was called
			$this->_data->setSortOrder(array($this->_data->fields[abs($sortBy)-1]->name => ($sortBy < 0 ? -1 : 1)));
		}

		$dataToPass['dataRows'] = $this->_db->getList($this->rowsOnPage, ($this->managePage-1)*$this->rowsOnPage);
		if($this->_data->foreignLinks)
			$dataToPass['foreignData'] = $this->_db->getForeignData($dataToPass['dataRows']);

		$dataToPass = array_merge($dataToPass, array(
				'total'			=> $this->_db->getListOverallCount(),
				'rowsOnPage'	=> $this->rowsOnPage,
				'currentPage'	=> $this->managePage,
				'checkboxes'	=> $this->checkboxes,
				'addActions'	=> $this->addActions,
				'fields'		=> $this->_data->fields,
				'searchOptions'	=> $this->_data->searchOptions,
				'baseURL'		=> Yii::app()->request->requestUri,
			));
		if($this->_data->orderBy)
		{
			$k = 0;
			foreach($this->_data->fields as $k=>$field)
			{	//Display table header
				if($this->_data->orderBy[0]['field']->name == $field->name)
				{
					$dataToPass['sortBy'] = ($this->_data->orderBy[0]['dir'] < 0 ? -($k+1) : ($k+1));
					break;
				}
			}
		}
		if(!$this->_data->orderBy)
			$dataToPass['sortBy'] = Yii::app()->request->getParam('sortBy', 1);

		$this->_controller->render($this->viewsPath.'list', $dataToPass);
	}

	/**
	 * Outputs form for inserting or updating data.
	 */
	protected function editMode()
	{
		$dataToPass = $this->_viewData;
		if($this->_manageAction == 'edit')
		{
			if(!empty($this->_viewData['formError']))
				$dataToPass['fields'] = $this->_data->fields;
			else
				$dataToPass['fields'] = $this->_db->getCurrentRow();
			if(!$dataToPass['fields'])
				throw new CHttpException(404, Yii::t('AutoAdmin.errors', 'Can\'t find the record'));
		}
		else
		{
			foreach($this->_data->fields as &$field)
			{
				if(!is_null($field->bind))
					$field->defaultValue = $field->bind;
					
			}
			$dataToPass['fields'] = $this->_data->fields;
		}

		if($this->_data->foreignLinks)
		{
			if(!isset($dataToPass['iframes']))
				$dataToPass['iframes'] = array();
			foreach($this->_data->foreignLinks as $alias=>$foreignLink)
			{
				//Passing the data for IFRAME
				$dataToPass['iframes'][$alias] = array(
					'action'	=> $alias,
					'foreign'	=> $foreignLink,
					'parent'	=> $_GET,
				);
			}
		}

		$dataToPass = array_merge($dataToPass, array(
				'interface'	=> $this->_interface,
				'clientData'=> $this->clientViewData,
				'baseURL'	=> Yii::app()->request->requestUri,
			));

		$this->_controller->render($this->viewsPath.'edit', $dataToPass);
	}

	/**
	 * Outputs results of operations.
	 * @param array $dataToPass Specific data to pass into the view script.
	 */
	protected function resultMode($dataToPass=array())
	{
		$dataToPass['redirectURL'] = AAHelperUrl::stripParam(Yii::app()->request->requestUri, array('action', 'sure'));
		foreach($this->_data->pk as $pkField=>$value)
			$dataToPass['redirectURL'] = AAHelperUrl::stripParam($dataToPass['redirectURL'], "id[{$pkField}]");
		$this->_controller->render($this->viewsPath.'editResult', array_merge($dataToPass, $this->_viewData));
	}

	/**
	 * Inserts the data into DB.
	 * @return mixed
	 */
	protected function insert()
	{
		if(!isset($_POST[self::INPUT_PREFIX]) && !isset($_FILES[self::INPUT_PREFIX]))
			throw new CHttpException(400);
		$affected = 0;

		$this->fillFieldsWithForm();
		$values = array();
		foreach($this->_data->fields as $field)
		{
			if(!is_null($field->value))
			{
				try
				{
					if(!$field->validateValue($field->value))
						$this->processFormError($field, Yii::t('AutoAdmin.form', 'Incorrect value'));
				}
				catch(AAException $e)
				{	//Validation rule may be set incorrectly
					throw new AAException(Yii::t('AutoAdmin.errors', 'Incorrect validation condition for field "{fieldName}"', array('fieldName'=>$field->name)));
				}
			}
			try
			{
				$values[$field->name] = $field->valueForSql();
			}
			catch(AAException $e)
			{
				$this->processFormError($field, $e->getMessage());
			}
		}
		if($values)
		{
			$transaction = $this->_db->beginTransaction();
			try
			{
				$this->_trigger->execute(null, 'before', 'insert');
				$affected = $this->_db->insert($values);
				if($affected)
				{
					$pk = $this->_db->getInsertedPKs($values);
					$this->_trigger->execute($pk, 'after', 'insert');
				}
				$this->_db->transactionCommit($transaction);
			}
			catch(AAException $e)
			{
				$this->_db->transactionRollback($transaction);
				$this->processQueryError($e);
			}
		
			if($affected)
			{
				$this->cache->updateDependency();

				if($this->logMode)
					$this->_access->log('INSERT', array('pk'=>$pk, 'values'=>$values));
			}
		}
		return $affected;
	}

	/**
	 * Updates the data in DB.
	 * @return mixed
	 */
	protected function update()
	{
		if(!isset($_POST[self::INPUT_PREFIX]))
			throw new CHttpException(400);
		$affected = 0;

		$this->fillFieldsWithForm();
		$values = array();
		foreach($this->_data->fields as $field)
		{
			if($field->isChanged)
			{
				if(!is_null($field->value))
				{
					try
					{
						if(!$field->validateValue($field->value))
							$this->processFormError($field, Yii::t('AutoAdmin.form', 'Incorrect value'));
					}
					catch(AAException $e)
					{	//Validation rule may be set incorrectly
						throw new AAException(Yii::t('AutoAdmin.errors', 'Incorrect validation condition for field "{fieldName}"', array('fieldName'=>$field->name)));
					}
				}
				try
				{
					$values[$field->name] = $field->valueForSql();
				}
				catch(AAException $e)
				{
					$this->processFormError($field, $e->getMessage());
				}
			}
		}
		if($values)
		{
			$transaction = $this->_db->beginTransaction();
			try
			{
				$this->_trigger->execute($this->_data->pk, 'before', 'update');
				$affected = $this->_db->update($values);
				if($affected)
				{
					$pk = $this->_data->rowPK($values);
					$this->_trigger->execute($pk, 'after', 'update');
				}
				$this->_db->transactionCommit($transaction);
			}
			catch(AAException $e)
			{
				$this->processQueryError($e);
				$this->_db->transactionRollback($transaction);
			}
		
			if($affected)
			{
				$this->cache->updateDependency();

				if($this->logMode)
					$this->_access->log('UPDATE', array('pk'=>$pk, 'values'=>$values));
			}
		}
		return $affected;
	}

	/**
	 * Deletes the data.
	 * @param Data from a deleting row. Need for additional delete operations (and not to query these data twice).
	 * @return integer Number of rows affected by the execution.
	 */
	protected function delete(&$deletingRow)
	{
		$affected = 0;
		$transaction = $this->_db->beginTransaction();
		try
		{
			$this->_trigger->execute($this->_data->pk, 'before', 'delete');
			$affected = $this->_db->delete();
			if($affected)
			{
				//Need to delete files if they were among the fields and have been checked in the confirmation form
				$fieldsToDel = Yii::app()->request->getPost('filesToDelF', array());
				if($fieldsToDel)
				{
					foreach($fieldsToDel as $iField)
					{
						if(isset($deletingRow->fields[$iField]) && in_array($deletingRow->fields[$iField]->type, array('image', 'file')))
						{
							if(in_array($deletingRow->fields[$iField]->type, array('image', 'file')))
								AAHelperFile::deleteFile($deletingRow->fields[$iField]->options['directoryPath'].DIRECTORY_SEPARATOR.$deletingRow->fields[$iField]->value);
						}
					}
				}
			}
			$this->_trigger->execute($this->_data->pk, 'after', 'delete');
			$this->_db->transactionCommit($transaction);
		}
		catch(AAException $e)
		{
			$this->processQueryError($e);
			$this->_db->transactionRollback($transaction);
		}
		return $affected;
	}

	/**
	 * Fill the AutoAdmin::_data->fields with the data sent by the main edit form.
	 */
	public function fillFieldsWithForm()
	{
		foreach($this->_data->fields as &$field)
		{
			if(!empty($_POST['isChangedAA'][$field->name]) || !empty($_POST['isChangedAA']["{$field->name}_new"]))
				$field->isChanged = true;
			if(!$field->isReadonly)
				$field->loadFromForm(isset($_POST[self::INPUT_PREFIX]) ? $_POST[self::INPUT_PREFIX] : array());
		}
	}

	/**
	 * Sets additional view scripts to output in the common list of data.
	 * @param string $viewScript View script name.
	 * @param string $area An area of placing view output,
	 */
	public function setPartialView($viewScript, $area='up')
	{
		$this->partialViews[$area] = $viewScript;
	}

	/**
	 * Sets user data to pass in a view script.
	 * @param array $data Free data.
	 */
	public function setPartialViewData($data)
	{
		$this->clientViewData = $data;
	}

	/**
	 * Defines the default configuration for "order by".
	 * Call this method only after initDBConf() method.
	 * @param array $fields An array of SQL fields' names 
	 */
	public function sortDefault($fields)
	{
		if(!is_array($fields))
			throw new AAException(Yii::t('AutoAdmin.errors', 'Wrong data for default sorting'));
		$this->_data->setSortOrder($fields);
	}

	/**
	 * Adds foreign data to the internal storage through for AAFields::foreignLink() or if in the iframe mode intercepts action and implements linked foreign action.
	 * @see AAFields::foreignLink()
	 * @param string $outAlias
	 * @param array $linkConf 
	 */
	public function foreignLink($outAlias, $linkConf)
	{
		if($this->_iframeMode)
		{	//We are in the iframe mode. We should intercept the parent action and implement the child one.
			$foreignActionID = "foreign".ucfirst(strtolower($this->_iframeMode));
			if(method_exists($this->_controller, 'action'.ucfirst($foreignActionID)))
			{
				$this->reset();	//reinition
				$this->_controller->forward($foreignActionID);
			}
			else
			{	//an action for ForeignLink isn't configured. Do it by default.
				$inTable = $this->tableName();
				$inPK = key($this->_data->pk);
				$inTitle = $this->_controller->pageTitle;
				$select = array();
				foreach($this->_data->fields as $field)
				{
					if(in_array($field->type, array('string', 'date', 'datetime', 'num')))
						$select[] = $field->name;
				}
				if(!$select)
					throw new AAException(Yii::t('AutoAdmin.errors', 'AutoAdmin cannot configure an automatic Action for the foreign interface on "{outAlias}": there is no selectable fields in the parent interface', array('{outAlias}' => $outAlias)));
				$this->reset();	//reinition
				$this->beforeControllerAction($this->_controller, $this->_controller->action);

				$this->tableName($linkConf['linkTable']);
				$outKey = key($linkConf['outKey']);
				$this->setPK(array($linkConf['inKey'], $outKey));
				$fields = array(
					array($linkConf['inKey'], 'foreign', $inTitle, array('bindBy'=>'id', 'readonly',
							'foreign'=>array(
								'table'		=> $inTable,
								'pk'		=> $inPK,
								'select'	=> $select,
							),
						)),
					array($outKey, 'foreign', $linkConf['label'], array('show',
							'foreign'=>array(
								'table'		=> $linkConf['targetTable'],
								'pk'		=> $linkConf['outKey'][$outKey],
								'select'	=> $linkConf['targetFields'],
							),
						)),
				);
				$this->fieldsConf($fields);
				//$this->sortDefault(array($outKey));
				$this->_controller->pageTitle = $linkConf['label'];
				$this->process();
				Yii::app()->end();
			}
		}
		else
			$this->_data->setForeignLink($outAlias, $linkConf);
	}

	/**
	 * Processes an exception after catching an error from the form.
	 * @param AAIField A field object with AAIField interface.
	 * @param string $errorMEssage Error message.
	 */
	protected function processFormError(&$field, $errorMessage='')
	{
		$this->_viewData['formError'] = array(
			'field' => $field,
			'message' => $errorMessage,
		);
		if($this->_manageAction == 'insert')
			$this->_manageAction = 'add';
		elseif($this->_manageAction == 'update')
			$this->_manageAction = 'edit';
		$this->editMode();
		Yii::app()->end();
	}

	/**
	 * Processes an exception after a bad SQL query.
	 * Logs the problem and renders the view.
	 * @param AAException $e 
	 */
	protected function processQueryError($e)
	{
		$this->resultMode(array(
				'errorOccured'=>true,
				'msg'=>Yii::t('AutoAdmin.errors', 'An error was occured during the query execution. You may contact the technical support and say the number #{errorNumber}', array('{errorNumber}'=>1)),
			));
		if($this->logMode)
			$this->_access->logError('exception', $e->getMessage());

		Yii::app()->end();
	}

	/**
	 * Blocks access to the interface: shows the message, ends the application.
	 * @throws CHttpException 
	 */
	public function blockAccess()
	{
		$this->_controller->render($this->viewsPath.'restricted', array('manageAction'=>$this->_manageAction));
		Yii::app()->end();
	}

	/**
	 * Returns a config row by its SQL name in the config.
	 * Simple helper for customizing fields configs.
	 * @param string $fieldName SQL name of the field.
	 * @param array $fieldsConf An array with a fields configuration which is used in AutoAdmin::fieldsConf().
	 * @return int|false Index of the row element. Returns false if nothing's found or nothing can be found.
	 */
	public static function fByName($fieldName, $fieldsConf)
	{
		if($fieldsConf && is_array($fieldsConf))
		{
			foreach($fieldsConf as $k=>$fieldData)
			{
				if($fieldData[0]==$fieldName)
					return $k;
			}
		}
		return false;
	}

	/**
	 * Returns an array of options for a config row found by its SQL name.
	 * Simple helper for customizing options.
	 * @param string $fieldName SQL name of the field.
	 * @param array $fieldsConf An array with a fields configuration which is used in AutoAdmin::fieldsConf().
	 * @return array A reference to the section of options of the field by its name.
	 * @throw AAException If the field with name $fieldName cannot be found.
	 */
	public static function &fByNameOpts($fieldName, &$fieldsConf)
	{
		$k = self::fByName($fieldName, $fieldsConf);
		if($k === false)
			throw new AAException(Yii::t('AutoAdmin.errors', 'There are no fields with the name {name} in the passed field configuration array', array('{name}'=>$fieldName)));
		return $fieldsConf[$k][3];
	}

	/**
	 * Gets the current mode.
	 * @return string The predefined code of the mode.
	 */
	public function getManageAction()
	{
		return $this->_manageAction;
	}

	/**
	 * Gets the current AudoAdminAccess object.
	 * @return AudoAdminAccess
	 */
	public function getAccess()
	{
		return $this->_access;
	}

	/**
	 * Gets the current interface ID.
	 * @return string Interface ID.
	 */
	public function getInterface()
	{
		return $this->_interface;
	}

	/**
	 * Resets all public-changeble data to default.
	 * Can be used for re-calling or calling another controller avoiding previously set configuration.
	 */
	public function reset()
	{
		$refl = new ReflectionClass($this);
		$properties = $refl->getDefaultProperties();
		foreach($properties as $propName=>$value)
		{
			if($propName == '_controller')
				continue;
			$property = $refl->getProperty($propName);
			if($property->isStatic())
				//self::{$propName} = $value;
				$property->setValue($value);
			else
				$this->{$propName} = $value;
		}
		$this->init();
	}
}
