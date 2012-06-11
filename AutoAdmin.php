<?php
/**
 * 
 * AutoAdmin. Flexible DataBase Management System.
 * @version 0.9a (Yii Framework Edition)
 * @author Alexander Palamarchuk <a@palamarchuk.info>. 2003-2012
 * @copyright Alexander Palamarchuk <a@palamarchuk.info>. 2003-2012
 * 
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
 * @todo Using Yii validators with forms.
 */
Yii::import('ext.autoAdmin.*');
Yii::import('ext.autoAdmin.models.*');
Yii::import('ext.autoAdmin.models.field.*');
Yii::import('ext.autoAdmin.helpers.*');
Yii::import('ext.autoAdmin.controllers.*');
Yii::import('ext.autoAdmin.views.layouts.*');

class AutoAdmin extends CWebModule
{
	/**
	 * Prefix for names of input elements in the general form.
	 */
	const INPUT_PREFIX = 'AA';

	public $manageAction;
	public $managePage;

	private $_interface;
	private $_access;
	private $_controller;
	private $_trigger;

	private $_data;
	private $_tableName;
	private $_iframeMode = false;
	private $_viewData;

	/**
	 *
	 * @var bool Whether to authenticate users or not with internal mechanisms.
	 */
	public $authMode = false;
	/**
	 *
	 * @var bool Whether to operate in open mode (without internal authentification and authorization)
	 */
	public $openMode = false;
	/**
	 *
	 * @var bool Whether to log all DB queries of update, insert or delete type.
	 */
	public $logMode = true;
	/**
	 *
	 * @var string An alias of CDbConnection that set up in config. It will be used as Yii::app()->{$dbConnection}.
	 */
	public $dbConnection = 'db';
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
	 * @var string DB schema that contains tables which are operated by a user.
	 */
	public $dbSchema;
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
	 * @var string Path to layout extension views.
	 */
	public $viewsPath = 'ext.autoAdmin.views.';
	/**
	 * 
	 * @var array Additional view scripts to display in data listing mode
	 */
	public $partialViews = array();
	/**
	 *
	 * @var array User data for passing to a view script
	 */
	public $clientViewData = array();
	/**
	 *
	 * @var string Path to assets of JS scripts.
	 */
	public static $assetPathJS;
	/**
	 *
	 * @var string Path to assets of CSS files.
	 */
	public static $assetPathCSS;

	/**
	 * Inizialize all the settings through an array of options
	 * Here is an example:
	 * <code>
	 * <?php
	 * 		$columns = array(
	 *			array('Зарегистрирован', 'when_reg', 'datetime', array('null', 'readonly', 'show'=>15, 'sort'=>-1)),
	 *			array('E-mail', 'email', 'string', array('null', 'show'=>20, 'sort')),
	 *		);
	 * ?>
	 * </code>
	 * @param array $columns Array with options.
	 *
	 */
	public function init()
	{
		$this->controllerMap['aafile'] = array('class'=>'ext.autoAdmin.controllers.AAFileController');
		$this->controllerMap['aaajax'] = array('class'=>'ext.autoAdmin.controllers.AAAjaxController');
		$this->controllerMap['aaauth'] = array('class'=>'ext.autoAdmin.controllers.AAAuthController');

		self::$assetPathJS = Yii::app()->assetManager->publish(Yii::getPathOfAlias('ext.autoAdmin.assets.js'));
		self::$assetPathCSS = Yii::app()->assetManager->publish(Yii::getPathOfAlias('ext.autoAdmin.assets.css'));

		$this->cache = new AACache();
		$this->_data = new AAData();
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
				$controller->redirect(array('aaauth/login'));
			}
			else
				throw new CHttpException(403);
		}

		if(preg_match('/^foreign/i', $action->id))
		{
			$this->_iframeMode = true;
			$this->_controller->layout = 'ext.autoAdmin.views.layouts.iframe';
		}
		else
			$this->_controller->layout = $this->layout;	//property layout is a parameter from config. @see $layout

		$this->manageAction = Yii::app()->request->getParam('action', null);
		$this->managePage = Yii::app()->request->getParam('page', 1);
		if($this->managePage < 1)
				$this->managePage = 1;

		$this->_data->binding = Yii::app()->request->getParam('bk', array());
		
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
		$this->_interface = $interface;
	}

	/**
	 * Initializes configuration of managed DB tables.
	 * @param array $columns Configuration array
	 */
	public function fieldsConf($columns)
	{
		try
		{
			$this->_data->loadColumnsConf($columns, $this->_tableName);
			//Creating the oppourtunity to upload files in directories that set by the controller
			foreach($this->_data->fields as &$field)
			{
				//if($field->$field->options['directoryPath'])
				//Yii::app()->user->setState("fileDirs[{$this->_interface}]", $this->_data->uploadDirs);
			}
		}
		catch(Exception $e)
		{
			$this->resultMode(array('errorOccured'=>true, 'msg'=>'Ошибка в конфигурационных данных: '.$e->getMessage()));
		}
	}

	/**
	 * Sets the main DBtable name or returns it's name if null has been passed.
	 * @param string|null $tableName Table name.
	 * @return null|string Table name if $tableName is null.
	 * @throws CException 
	 */
	public function tableName($tableName=null)
	{
		if(is_null($tableName))
			return $this->_tableName;
		if($tableName && is_string($tableName))
			$this->_tableName = $tableName;
		else
			throw new CException(Yii::t('AutoAdmin.errors', 'Wrong data for DB Table Name'));
	}

	/**
	 * Gets the full composite table name for SQL queries using @link AutoAdmin::dbSchema.
	 * @param string Custom table name that you can use instead of @link AutoAdmin::dbSchema.
	 * @return string Full composite table name ready to use in SQL quiries.
	 */
	public function getFullTableName($tableName=null)
	{
		return ($this->dbSchema ? "{$this->dbSchema}." : '').($tableName ? $tableName : $this->_tableName);
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
	 * @throws CException 
	 */
	private function loadPK()
	{
		$pkValues = Yii::app()->request->getParam('id', array());
		if(!$pkValues)
			return false;
		if(array_keys($this->_data->pk) === array_keys($pkValues))
			$this->_data->loadPK($pkValues);
		else
			throw new CException(Yii::t('AutoAdmin.errors', 'Wrong data for PrimaryKey'));
	}

	/**
	 * Sets the access rights to the administation interface.
	 * The most default rights are true for everithing. This function overrides default rights, but doesn't override permissions for a current user which were set with the special interface in service DB.
	 * @param array $rights An array of rights.
	 * The permission for a particular right can be set in different ways. For example:
	 * <code>
	 * <?
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
			throw new CException(Yii::t('AutoAdmin.errors', 'Wrong params for access rights'));
		if(empty($this->_access))
			throw new CException(Yii::t('AutoAdmin.errors', 'You must specify the interface to use access rights'));
		foreach($rights as $key=>$value)
		{
			if(is_string($key))
			{
				$right = $key;
				$permition = ($value==-1 ? false : (bool)$value);
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
				throw new CException(Yii::t('AutoAdmin.errors', 'Function setSubHref() can be called only after fieldsConf()'));

			if($this->_iframeMode)
			{
				$this->subHref = AAHelperUrl::update("../foreign-{$href}/", null, array(
						'foreign'=>Yii::app()->request->getParam('foreign'),
					));
			}
			else
				$this->subHref = "../{$href}/";
			//We have to store all parent "bk" sets. So we did it using "bkp" param as a stack.
			$bk = Yii::app()->request->getParam('bk', array());
			$bkp = Yii::app()->request->getParam('bkp', array());	//Parent interfaces' bindings
			array_push($bkp, $bk);
			$this->subHref = AAHelperUrl::addParam($this->subHref, 'bkp', $bkp);
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
	 * Update conditions of Yii DAO standard.
	 * You don't know the alias of a table when you programme it. So you ought to use original name as prefix (e.g. "table_name.field_name = :param"). You may not use prefix only if you're sure that the field name is a unique one.
	 * @param mixed $condition Yii DAO standart conditions.
	 * @param string $tableName Original table name (function will replace by "{$tableName}.").
	 * @param string $tableAlias New alias instead of $tableName.
	 */
	public static function addTableAliasToCond(&$condition, $tableName, $tableAlias)
	{
		if(is_array($condition))
		{
			foreach($condition as &$cond)
			{
				self::addTableAliasToCond($cond, $tableName, $tableAlias);
			}
		}
		elseif(is_string($condition))
		{
			$condition = str_replace("{$tableName}.", "{$tableAlias}.", $condition);
		}
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
	 * Executes direct processing of state and activate internal methods.
	 * Must be called only at the very end of a controller method.
	 */
	public function process()
	{
		$this->_access = new AutoAdminAccess($this->_interface);
		$this->_access->setOpenMode($this->openMode);
		$this->_access->loadAccessSettings();
		$this->loadPk();

		if(!$this->testConf())
			throw new CException(Yii::t('AutoAdmin.errors', 'Incorrect configuration'));
		$this->_viewData = array(
			'getParams' => $_GET,
			'viewsPath'	=> $this->viewsPath,
			'rights'	=> $this->_access->exportRights(),
			'pk'		=> $this->_data->pk,
			'partialViews'	=> $this->partialViews,
			'clientData'	=> $this->clientViewData,
			'interface'		=> $this->_interface,
			'bindKeys'		=> Yii::app()->request->getParam('bk', array()),
			'bindKeysParent'=> Yii::app()->request->getParam('bkp', array()),
			'isGuest'		=> Yii::app()->user->isGuest,
			'userName'		=> (!Yii::app()->user->isGuest ? Yii::app()->user->getState('firstname').' '.Yii::app()->user->getState('surname') : ''),
			'userLevel'		=> (!Yii::app()->user->isGuest ? Yii::app()->user->level : 0),
		);

		switch($this->manageAction)
		{
			case 'add':	//Form for data adding
			{
				if(!$this->_access->checkRight('add'))
					$this->blockAccess('add');
				$this->editMode();
				break;
			}
			case 'edit':	//Form for data editing
			{
				if(!$this->_access->checkRight('edit'))
					$this->blockAccess('edit');
				$this->prepareEditMode();
				$this->editMode();
				break;
			}
			case 'insert':	//Process inserting data in DB
			{
				if(!$this->_access->checkRight('add'))
					$this->blockAccess('add');
				$result = $this->save('add');
				$this->resultMode(array('msg'=>Yii::t('AutoAdmin.messages', ($result ? 'The record was added' : 'The record was not added'))));
				break;
			}
			case 'update':	//Process updating data in DB
			{
				if(!$this->_access->checkRight('edit'))
					$this->blockAccess('edit');
				$result = $this->save('edit');
				$this->resultMode(array('msg'=>Yii::t('AutoAdmin.messages', ($result ? 'The record has been changed' : 'The record has not been changed'))));
				break;
			}
			case 'delete':	//Process deletion data in DB
			{
				if(!$this->_access->checkRight('delete'))
					$this->blockAccess('delete');
				if($this->confirmDelete && !Yii::app()->request->getParam('sure', false))
				{
					$confirmUrl = AAHelperUrl::update(Yii::app()->request->requestUri, array('action'), array('action'=>'delete', 'sure'=>1));
					$cancelUrl = AAHelperUrl::stripParam(Yii::app()->request->requestUri, array('action'));
					foreach($this->_data->pk as $pkField=>$value)
						$cancelUrl = AAHelperUrl::stripParam($cancelUrl, "id[{$pkField}]");
					$this->_controller->render($this->viewsPath.'confirmDelete', array('confirmUrl'=>$confirmUrl, 'cancelUrl'=>$cancelUrl));
					Yii::app()->end();
				}
				$result = $this->delete();
				$this->resultMode(array('msg'=>Yii::t('AutoAdmin.messages', ($result ? 'The record was deleted' : 'The record has not been deleted'))));
				break;
			}
			case 'empty':	//Interface which is programmed directly by user
			{
				if(!$this->_access->checkRight('read'))
					$this->blockAccess('read');
				$this->_controller->render($this->viewsPath.'empty', $this->_viewData);
				break;
			}
			default:	//Data list show (default mode)
			{
				if(!$this->_access->checkRight('read'))
					$this->blockAccess('read');
				$this->listMode();
				break;
			}
		}
	}

	/**
	 * Builds the base part of query for common list or single row of data.
	 * @param \CDbCommand $q DAO built command
	 * @param array $qWhere An array to pass in DAO as where() argument.
	 * @param array $qParams An array to pass in DAO as "parameters" argument in where().
	 * @param bool $strictShowInList Whether to restrict output within fields which marked as "showInList" or show every one.
	 */
	private function getBaseQuery(&$q, &$qWhere, &$qParams, $strictShowInList=true)
	{
		$selectFields = array();
		foreach($this->_data->pk as $pkField=>$pkValue)
			$selectFields[] = "{$this->_tableName}.{$pkField}";
		foreach($this->_data->fields as &$field)
		{
			if($field->showInList || !$strictShowInList)
			{
				$selectFields[] = "{$this->_tableName}.{$field->name}";
				//A field can provide its own parts for query: SELECT and JOIN.
				if(method_exists($field, 'modifySqlQuery'))
				{
					$sqlModifing = $field->modifySqlQuery();
					if(!empty($sqlModifing['select']))
						$selectFields = array_merge($selectFields, $sqlModifing['select']);
					if(!empty($sqlModifing['join']))
					{
						if(empty($sqlModifing['join']['type']) || $sqlModifing['join']['type']=='inner')
							$joinF = 'join';
						else
							$joinF = strtolower($sqlModifing['join']['type'])."Join";
						$q->{$joinF}($this->getFullTableName($sqlModifing['join']['table']), $sqlModifing['join']['conditions'], (!empty($sqlModifing['join']['params']) ? $sqlModifing['join']['params'] : array()));
					}
				}
			}
		}

		$q->select($selectFields);
		$q->from($this->getFullTableName());
	}

	/**
	 * SQL query for output in a common list of data rows.
	 * @return \CDbCommand DAO built command
	 */
	private function getListQuery()
	{
		$q = Yii::app()->{$this->dbConnection}->createCommand();
		$qWhere = array();
		$qParams = array();
		$this->getBaseQuery($q, $qWhere, $qParams);

		foreach($this->_data->fields as &$field)
		{
			if(!is_null($field->bind))
			{
				if($field->bind == 'NULL')
					$qWhere[] = "{$this->_tableName}.{$field->name} IS NULL";
				else
				{
					$qWhere[] = "{$this->_tableName}.{$field->name} = :_bind_{$field->name}";
					$qParams[":_bind_{$field->name}"] = $field->bind;
				}
			}
		}

		//Search by a field (user-defined)
		if(Yii::app()->request->getParam('searchBy') && $this->_data->getFieldByName((int)Yii::app()->request->getParam('searchBy')))
		{
			$this->prepareSearch($q, (int)Yii::app()->request->getParam('searchBy'));
		}

		if($qWhere)
			$q->where(array_merge(array('AND'), $qWhere), $qParams);

		//Preparing sort fields for ORDER BY in the query
		$sortBy = Yii::app()->request->getQuery('sortBy', null);
		if(!is_null($sortBy) && isset($this->_data->fields[abs($sortBy)-1]))
		{	//User sorting was called
			$this->_data->setSortOrder(array($this->_data->fields[abs($sortBy)-1]->name => ($sortBy < 0 ? -1 : 1)));
		}

		if(!empty($this->_data->orderBy))
		{
			$qOrder = array();
			foreach($this->_data->orderBy as $order)
			{
				if($order['field']->type == 'foreign')
				{
					$selectNames = array_keys($order['field']->options['select']);
					$orderPiece = "{$order['field']->options['tableAlias']}.{$selectNames[0]}";
				}
				else
					$orderPiece = "{$this->_tableName}.{$order['field']->name}";
				if($order['dir'] == -1)
					$orderPiece .= " DESC";
				$qOrder[] = $orderPiece;
			}
			if($qOrder)
				$q->order($qOrder);
		}

		$q->limit($this->rowsOnPage);
		$q->offset(($this->managePage-1)*$this->rowsOnPage);
		return $q;
	}

	/**
	 * Data list mode.
	 */
	private function listMode()
	{
		$dataToPass = $this->_viewData;
		if($this->subHref)	//ссылка на вложенный раздел
			$dataToPass['urlSub'] = $this->subHref;

		if(!empty($_GET['msg']))
			$this->view->addMessage($_GET['msg']);

		$q = $this->getListQuery();
		$queryResult = $q->queryAll();
		$dataToPass['dataRows'] = array();

		foreach($queryResult as $row)
		{
			$dataToPass['dataRows'][] = $this->_data->loadRow($row);
		}
				
		if($this->_data->foreignLinks)
		{
			$dataToPass['foreignData'] = $this->getForeignData($queryResult);
		}

		//Now use the same query to calculate overall count of rows
		$sqlCount = "SELECT COUNT(*) FROM ".$q->getFrom();
		if($q->getJoin())
			$sqlCount .= " ".implode(' ', $q->getJoin());
		if($q->where)
			$sqlCount .= " WHERE ".$q->getWhere();
		$total = Yii::app()->{$this->dbConnection}->createCommand($sqlCount)->queryScalar($q->params);

		$dataToPass = array_merge($dataToPass, array(
				'total'			=> $total,
				'rowsOnPage'	=> $this->rowsOnPage,
				'currentPage'	=> $this->managePage,
				'checkboxes'	=> $this->checkboxes,
				'addActions'	=> $this->addActions,
				'fields'		=> $this->_data->fields,
				'baseURL'		=> Yii::app()->request->requestUri,
				'sortBy'		=> Yii::app()->request->getParam('sortBy', 1),
				'searchBy'		=> Yii::app()->request->getParam('searchBy'),
			));

		$this->_controller->render($this->viewsPath.'list', $dataToPass);
	}

	/**
	 * Prepares data for editing and showing in form.
	 * @throws CHttpException 
	 */
	private function prepareEditMode()
	{
		$q = Yii::app()->{$this->dbConnection}->createCommand();
		$qWhere = array();
		$qParams = array();
		$this->getBaseQuery($q, $qWhere, $qParams, false);

		foreach($this->_data->pk as $pkField=>$pkValue)
		{
			$qWhere[] = "{$this->_tableName}.{$pkField} = :id_{$pkField}";
			$qParams[":id_{$pkField}"] = $pkValue;
		}

		foreach($this->_data->fields as $field)
		{
			if($field->bind)
			{
				$qWhere[] = "{$this->_tableName}.{$field->name} = :bk_{$field->name}";
				$qParams[":bk_{$field->name}"] = $field->bind;
			}
		}

		if($qWhere)
			$q->where(array_merge(array('AND'), $qWhere), $qParams);
		$row = $q->queryRow();
		if(!$row)
			throw new CHttpException(404, Yii::t('AutoAdmin.errors', 'Can\'t find the record'));
		$this->_viewData['fields'] = $this->_data->loadRow($row);
		$this->_viewData['actionType'] = 'edit';
	}

	/**
	 * Outputs form for inserting or updating data.
	 */
	private function editMode()
	{
		$dataToPass = $this->_viewData;
		if(empty($dataToPass['fields']))
		{	//Situation in insert mode
			$dataToPass['fields'] = $this->_data->fields;
			$dataToPass['actionType'] = 'add';
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
	private function resultMode($dataToPass=array())
	{
		$dataToPass['redirectURL'] = AAHelperUrl::stripParam(Yii::app()->request->requestUri, array('action', 'sure'));
		foreach($this->_data->pk as $pkField=>$value)
			$dataToPass['redirectURL'] = AAHelperUrl::stripParam($dataToPass['redirectURL'], "id[{$pkField}]");
		$this->_controller->render($this->viewsPath.'editResult', array_merge($dataToPass, $this->_viewData));
	}

	/**
	 * Adding / Updating data.
	 * @param string $actionType
	 * @return mixed
	 */
	private function save($actionType)
	{
		if(!isset($_POST[self::INPUT_PREFIX]))
			throw new CHttpException(400);

		$q = Yii::app()->{$this->dbConnection}->createCommand();
		$values = array();
		foreach($this->_data->fields as &$field)
		{
			if(!empty($_POST['isChangedAA'][$field->name]))
				$field->isChanged = true;
			if(!$field->isReadonly)
				$field->loadFromForm($_POST[self::INPUT_PREFIX]);
			if($actionType == 'add' || $field->isChanged)
				$values[$field->name] = $field->valueForSql();
		}

		$affected = false;
		if($actionType == 'add')
		{	//Compose SQL-query for insertion
			$transaction = Yii::app()->{$this->dbConnection}->beginTransaction();
			try
			{
				$this->_trigger->execute(null, 'before', 'insert');
				$affected = $q->insert($this->getFullTableName(), $values);
				if($affected)
				{
					$tableSchema = Yii::app()->{$this->dbConnection}->schema->getTable($this->_tableName);
					if(count($this->_data->pk) == 1)	//Can use a sequence (AutoIncrement)
						$pk = array($this->_data->pk[$this->_data->getPKField(0)] => Yii::app()->{$this->dbConnection}->getLastInsertID(($tableSchema->sequenceName ? $tableSchema->sequenceName : null)));
					else
						$pk = $this->_data->rowPK($values);
					$this->_trigger->execute($pk, 'after', 'insert');
				}
				$transaction->commit();
			}
			catch(Exception $e)
			{
				$this->processQueryError($e);
				$transaction->rollBack();
			}
		}
		elseif($values)
		{	//Compose SQL-query for updating
			$params = array();
			$where = array('AND');
			foreach($this->_data->pk as $pkField=>$pkValue)
			{
				$where[] = "{$this->_tableName}.{$pkField} = :_id{$pkField}";
				$params[":_id{$pkField}"] = $pkValue;
			}

			$transaction = Yii::app()->{$this->dbConnection}->beginTransaction();
			try
			{
				$this->_trigger->execute($this->_data->pk, 'before', 'update');
				$affected = $q->update($this->getFullTableName(), $values, $where, $params);
				if($affected)
				{
					$pk = $this->_data->rowPK($values);
					$this->_trigger->execute($pk, 'after', 'update');
				}
				$transaction->commit();
			}
			catch(Exception $e)
			{
				$this->processQueryError($e);
				$transaction->rollBack();
			}
		}
		
		if($affected)
		{	//Updates took place
			$this->cache->updateDependency();

			if($this->logMode)
				$this->_access->log("ID: ".var_export($pk, true)."\n SQL: ".$q->text."\n Params: ".$q->params);
		}

		return (bool)$affected;
	}

	/**
	 * Deletes the data.
	 * @return integer Number of rows affected by the execution 
	 * 
	 * @todo To add an opportunity to choose whether to delete files (which are linked with fields in a row that is beeing deleted) or not
	 */
	private function delete()
	{
		$params = array();
		$where = array('AND');
		foreach($this->_data->pk as $pkField=>$pkValue)
		{
			$where[] = "{$this->_tableName}.{$pkField} = :_id{$pkField}";
			$params[":_id{$pkField}"] = $pkValue;
		}
		$q = Yii::app()->{$this->dbConnection}->createCommand();

		$fileSQLf = array(); //We should delete files
		foreach($this->_data->fields as &$field)
		{	//Defining if the field has a file
			if(in_array($field->type, array('image', 'file')))
				$fileSQLf[$field->name] = $field->options['directoryPath'];
		}
		if($fileSQLf)
		{	//Selecting data about deleting files from the deleting row
			$q->select(array_keys($fileSQLf));
			$q->from($this->getFullTableName());
			$q->where($where, $params);
			$filesToDelete = $q->queryRow();
			$q->reset();
		}

		$transaction = Yii::app()->{$this->dbConnection}->beginTransaction();
		try
		{
			$this->_trigger->execute($this->_data->pk, 'before', 'delete');
			$result = $q->delete($this->_tableName, $where, $params);
			if($result)
			{
				if(!empty($filesToDelete))
				{	//Only in case of success of main record deletion we do delete files
					foreach($filesToDelete as $fieldName=>$fileName)
						AAHelperFile::deleteFile($fileSQLf[$fieldName].DIRECTORY_SEPARATOR.$fileName);
				}
			}
			$this->_trigger->execute($this->_data->pk, 'after', 'delete');
			$transaction->commit();
		}
		catch(Exception $e)
		{
			$this->processQueryError($e);
			$transaction->rollBack();
		}

		if($this->_access->isLogMode())
		{
			$this->_access->logctrl->fixQuery($q->text);
		}

		return $result;
	}

	/**
	 * Prepares query for data searching
	 * @param type $sqlCommand
	 * @param type $i
	 * @return string 
	 */
	private function prepareSearch(&$sqlCommand, $i)
	{
		$search = $_GET['searchq'];
		if($this->_data->fields[$i]->type == 'foreign')
		{
			$where = array('OR', array());
			foreach($this->_data->foreignKeys[$this->_data->fields[$i]->name]['valueFields'] as $vfield)
				$where[1][] = array('LIKE', "{$this->_data->foreignKeys[$this->_data->fields[$i]->name]['table_alias']}.{$vfield}", "%{$search}%");
			$sqlCommand->where($where);
		}
		elseif($this->_data->fields[$i]->type == 'date' || $this->_data->fields[$i]->type == 'datetime')
		{
			/*
			if(($k = array_search($_GET['searchq'], Hot::$months)) || ($k = array_search($_GET['searchq'], Hot::$months3)))
			{
				$where .= " MONTH(`{$this->_tableName}`.`".$this->_data->fields[$i]->name."`) ";
				$where .= " = '{$k}'";
			}
			elseif(preg_match('/^(\d{4})$/u', $_GET['searchq'], $ar))
			{
				$where .= " YEAR(`{$this->_tableName}`.`".$this->_data->fields[$i]->name."`) ";
				$where .= " = '{$ar[1]}'";
			}
			elseif(preg_match('/^(\d?\d)[\.\,\-\s]?(\d?\d)[\.\,\-\s]?(\d{4})\s*\-\s*(\d?\d)[\.\,\-\s]?(\d?\d)[\.\,\-\s]?(\d{4})$/', $_GET['searchq'], $ar))
			{
				$where .= " `{$this->_tableName}`.`".$this->_data->fields[$i]->name."`";
				$where .= " >= '{$ar[3]}-{$ar[2]}-{$ar[1]}".(($this->_data->fields[$i]->type == 'datetime') ? ' 00:00:00':'')."'";
				$where .= " AND `{$this->_tableName}`.`".$this->_data->fields[$i]->name."`";
				$where .= " <= '{$ar[6]}-{$ar[5]}-{$ar[4]}".(($this->_data->fields[$i]->type == 'datetime') ? ' 23:59:59':'')."'";
			}
			elseif(preg_match('/^(\d{4})[\.\,\-\s]?(\d?\d)[\.\,\-\s]?(\d?\d)$/', $_GET['searchq'], $ar))
			{
				$where .= " `{$this->_tableName}`.`".$this->_data->fields[$i]->name."` ";
				$where .= " = '{$ar[1]}-{$ar[2]}-{$ar[3]}'";
			}
			elseif(preg_match('/^(\d?\d)[\.\,\-\s]?(\d?\d)[\.\,\-\s]?(\d{4})$/', $_GET['searchq'], $ar))
			{
				$where .= " `{$this->_tableName}`.`".$this->_data->fields[$i]->name."` ";
				$where .= " = '{$ar[3]}-{$ar[2]}-{$ar[1]}'";
			}
			elseif(preg_match('/^(\d?\d)[\.\,\-\s](\d{4})$/', $_GET['searchq'], $ar))
			{
				$where .= " `{$this->_tableName}`.`".$this->_data->fields[$i]->name."` ";
				$where .= " LIKE '%{$ar[1]}-{$ar[2]}'";
			}
			elseif(preg_match('/^(\d?\d)[\.\,\-\s](\d{4})$/', $_GET['searchq'], $ar))
			{
				$where .= " `{$this->_tableName}`.`".$this->_data->fields[$i]->name."` ";
				$where .= " LIKE '%{$ar[2]}-{$ar[1]}'";
			}
			elseif(preg_match('/^(\d?\d)[\.\,\-\s](\d?\d)$/', $_GET['searchq'], $ar))
			{
				$where .= " `{$this->_tableName}`.`".$this->_data->fields[$i]->name."` ";
				$where .= " LIKE '%-{$ar[2]}-{$ar[1]}%'";
			}
			*/
		}
		elseif($this->_data->fields[$i]->type == 'string' || $this->_data->fields[$i]->type == 'text' || $this->_data->fields[$i]->type == 'text2' || $this->_data->fields[$i]->type == 'editor' || $this->_data->fields[$i]->type == 'num')
		{
			$sqlCommand->where(array('LIKE', "{$this->_tableName}.{$this->_data->fields[$i]->name}", "%{$search}%"));
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
	 * Loads data from linked foreign tables as "many to many".
	 * @todo The function while doesn't work with composite PrimaryKey ID
	 * @param array $nativeData Data from native table, foreign data extract by.
	 */
	public function getForeignData(&$nativeData)
	{
		$data = array();

		$nativeKeys = array();
		foreach($this->_data->pk as $pkField=>$pkValue)
		{
			$nativeKeys[$pkField] = array();
			foreach($nativeData as $r)
				$nativeKeys[$pkField][] = $r[$pkField];
		}
		if(!$nativeKeys)
			return $data;

		$q = Yii::app()->{$this->dbConnection}->createCommand();
		foreach($this->_data->foreignLinks as $outAlias=>$link)
		{
			$q->from($this->getFullTableName($link['linkTable'])." AS t1, ".$this->getFullTableName($link['targetTable'])." AS t2");

			//Collect fields for selection
			$fields = array();
			foreach($link['inKey'] as $inKey=>$nativeKey)
				$fields[] = "t1.{$inKey}";
			if(!empty($link['linkFields']))
			{
				foreach($link['linkFields'] as $field)
					$fields[] = "t1.{$field}";
			}
			if(!empty($link['targetFields']))
			{
				foreach($link['targetFields'] as $field)
					$fields[] = "t2.{$field}";
			}
			$q->select($fields);

			$where = array('AND');
			$whereJoin =& $where[array_push($where, array('AND')) - 1];	//INNER JOIN conditions must have a the separate group
			foreach($link['outKey'] as $outKey=>$targetKey)
				$whereJoin[] = "{$outKey} = {$targetKey}";	//INNER JOIN conditions
			foreach($link['inKey'] as $inKey=>$nativeKey)
				$where[] = array('IN', $inKey, $nativeKeys[$nativeKey]);
			$q->where($where);

			$result = $q->queryAll();
			$data[$outAlias] = array();	//result data
			foreach($result as $r)
			{
				$exportKey = array();
				foreach($this->_data->pk as $pkField=>$pkValue)
					$exportKey[] = $r[array_search($pkField, $link['inKey'])];
				$exportKey = serialize($exportKey);
				if(!isset($data[$outAlias][$exportKey]))
					$data[$outAlias][$exportKey] = array();
				$data[$outAlias][$exportKey][] = $r;
			}
			$q->reset();
		}
		return $data;
	}

	/**
	 * Defines the default configuration for "order by".
	 * Call this method only after initDBConf() method.
	 * @param array $fields An array of SQL fields' names 
	 */
	public function sortDefault($fields)
	{
		if(!is_array($fields))
			throw new CException(Yii::t('AutoAdmin.errors', 'Wrong data for default sorting'));
		$this->_data->setSortOrder($fields);
	}

	/**
	 * The alias for AAFields::foreignLink().
	 * @see AAFields::foreignLink()
	 * @param string $outAlias
	 * @param array $linkConf 
	 */
	public function foreignLink($outAlias, $linkConf)
	{
		$this->_data->setForeignLink($outAlias, $linkConf);
	}

	/**
	 * Processes an exception after a bad SQL query.
	 * Logs the problem and renders the view.
	 * @param Exception $e 
	 */
	private function processQueryError($e)
	{
		$this->resultMode(array(
				'errorOccured'=>true,
				'msg'=>Yii::t('AutoAdmin.errors', 'The error was occured during the query execution. You may contact the technical support and say the number #{errorNumber}', array('{errorNumber}'=>1)),
			));
		if($this->logMode)
			$this->_access->logError('exception', $e->getMessage());

		Yii::app()->end();
	}

	/**
	 * Blocks access to the interface: shows the message, ends the application.
	 * @param string $actionType Type of action (predefined).
	 * @throws CHttpException 
	 */
	public function blockAccess($actionType)
	{
		$this->_controller->render($this->viewsPath.'restricted', array('actionType'=>$actionType));
		Yii::app()->end();
	}
}
