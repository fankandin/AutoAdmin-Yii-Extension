<?php
/**
 * The Controller provides all service for managing users and access rights.
 */
class AAAuthController extends CExtController
{
	public $breadcrumbs;

	/**
	 * Provides the authentication.
	 */
	public function actionLogin()
	{
		$data = array();

		$returnURL = Yii::app()->user->getReturnUrl();
		$data = array();
		if(!Yii::app()->user->isGuest && Yii::app()->user->id)
		{
			$userName = Yii::app()->user->getState('firstname').' '.Yii::app()->user->getState('surname');
			$this->render('ext.autoAdmin.views.login', array('userName'=>$userName, 'returnUrl'=>$returnURL));
			Yii::app()->end();
		}

		$model = new AALoginForm;
		$model->scenario = 'login';
		if(isset($_POST['AALoginForm']))
		{
			$model->attributes = $_POST['AALoginForm'];

			if($model->validate())
			{
				if($model->authenticate())
					$this->redirect(Yii::app()->request->redirect($returnURL));
				else
				{
					$model->addError('password', 'Неправильный пароль или логин.');
				}
			}
		}
		if(!$this->isRootDefined())
		{	//Root isn't defined yet. So we have to request for it
			$this->forward('users');
		}

		$data['model'] = $model;
		$this->render('ext.autoAdmin.views.loginForm', $data);
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}

	/**
	 * Manages with users of the manel.
	 */
	public function actionUsers()
	{
		if(Yii::app()->user->isGuest && $this->isRootDefined())
			Yii::app()->request->redirect('../login/');
		elseif(!Yii::app()->user->isGuest && Yii::app()->user->level == 'user')
			throw new CHttpException(403, Yii::t('AutoAdmin.access', 'You do not have permissions to access'));
		
		$this->module->dbConnection = 'dbAdmin';
		$this->module->dbSchema = '';	//We must set to null the default schema, because of all the admin tables are named with AutoAdminAccess::sqlAdminTableName()
		$this->module->tableName(AutoAdminAccess::sqlAdminTableName('users'));
		$this->module->setPK('id');

		$fieldsConf = array(
			array('login', 'string', Yii::t('AutoAdmin.access', 'Login'), array('search', 'show', 'maxlength'=>21)),
			array('password', 'password', Yii::t('AutoAdmin.access', 'Password'), array('maxlength'=>32, 'pattern'=>'.{4,}')),
			array('level', 'enum', Yii::t('AutoAdmin.access', 'User level'), array(
					'enum'=>array(
						'root' => Yii::t('AutoAdmin.access', 'Master'),
						'admin' => Yii::t('AutoAdmin.access', 'Administrator'),
						'user' => Yii::t('AutoAdmin.access', 'Plain user'),
					),
					'default'=>'user', 'show',
					'description'=>Yii::t('AutoAdmin.access', '<b>Master</b> can do everything. <b>Administrator</b> can do all but to create new Masters. <b>User</b> is a plain user whose rights can be restricted either by Master and Administrator.'),
				)),
			array('interface_level', 'num', Yii::t('AutoAdmin.access', 'Interface Level'), array('default'=>1, 'show')),
			array('email', 'string', Yii::t('AutoAdmin.access', 'E-mail'), array('null', 'maxlength'=>40)),
			array('surname', 'string', Yii::t('AutoAdmin.access', 'Surname'), array('show', 'search', 'maxlength'=>21)),
			array('firstname', 'string', Yii::t('AutoAdmin.access', 'First name'), array('show', 'maxlength'=>21)),
			array('middlename', 'string', Yii::t('AutoAdmin.access', 'Middle name'), array('show', 'maxlength'=>21)),
			array('regdate', 'datetime', Yii::t('AutoAdmin.access', 'Registration date'), array('readonly', 'default'=>date('Y-m-d H:i:s'))),
		);
		$levelOpts =& AutoAdmin::fByNameOpts('level', $fieldsConf);
		if(!Yii::app()->user->isGuest && Yii::app()->user->level == 'admin')
		{
			unset($levelOpts['enum']['root']);
			unset($levelOpts['enum']['admin']);
			$levelOpts['bind'] = 'user';
		}
		elseif(Yii::app()->user->isGuest && !$this->isRootDefined())
		{
			$levelOpts['default'] = 'root';
		}
		$this->module->fieldsConf($fieldsConf);
		
		if(!Yii::app()->user->isGuest && in_array(Yii::app()->user->level, array('root', 'admin')))
			$this->module->setSubHref('sections');
		$this->module->sortDefault(array('login'));
		$this->pageTitle = Yii::t('AutoAdmin.access', 'Users of the administration panel');
		$this->breadcrumbs = array(
			$this->pageTitle
		);

		$this->module->process();
	}

	/**
	 * Provides managing with sections that group interfaces of the panel.
	 */
	public function actionSections()
	{
		if(Yii::app()->user->isGuest || !in_array(Yii::app()->user->level, array('root', 'admin')))
			throw new CHttpException(403, Yii::t('AutoAdmin.access', 'You do not have permissions to access'));
			
		$this->module->dbConnection = 'dbAdmin';
		$this->module->dbSchema = '';

		$this->module->tableName(AutoAdminAccess::sqlAdminTableName('sections'));
		$this->module->setPK('id');
		$this->module->fieldsConf(array(
				array('title', 'string', Yii::t('AutoAdmin.access', 'Section Title'), array('show')),
			));
		$this->module->setSubHref('interfaces');
		$this->pageTitle = Yii::t('AutoAdmin.access', 'Sections of interfaces');

		
		$bk = Yii::app()->request->getParam('bk', array('id'=>null));
		
		$this->breadcrumbs[$this->breadcrumbUsers($bk['id'])] = '../users/';
		$this->breadcrumbs[] = $this->pageTitle;

		$this->module->process();
	}

	/**
	 * Provides managing with interfaces of the panel.
	 */
	public function actionInterfaces()
	{
		if(Yii::app()->user->isGuest || !in_array(Yii::app()->user->level, array('root', 'admin')))
			throw new CHttpException(403, Yii::t('AutoAdmin.access', 'You do not have permissions to access'));
		$bk = Yii::app()->request->getParam('bk', array('id'=>null));
		$sectionID = $bk['id'];

		//We suggest to user the way to create interfaces automatically, which is based on his own controllers
		if(in_array(Yii::app()->user->level, array('root', 'admin')))
		{
			$data = array(
				'interfacesList' => $this->getInterfaces(true),
			);
			if(!empty($_POST['AAimportInterfaces']))
			{
				$data['addedInterfaces'] = array();
				foreach($_POST['AAimportInterfaces'] as $interfaceAlias)
				{
					$ca =& $data['interfacesList'][$interfaceAlias];
					$affected = Yii::app()->dbAdmin->createCommand()->insert(
							AutoAdminAccess::sqlAdminTableName('interfaces'),
							array(
								'section_id'=>$sectionID,
								'alias'=>$interfaceAlias,
								'title'=>"{$ca[0]} - {$ca[1]}",
							)
						);
					if($affected)
						$data['addedInterfaces'][$interfaceAlias] = $ca;
				}
			}
			else
			{
				$data['actionURI'] = Yii::app()->request->getRequestUri();
			}

			$this->module->setPartialView('ext.autoAdmin.views.partialInterfacesImport', 'up');
			$this->module->setPartialViewData($data);
		}

		$this->module->dbConnection = 'dbAdmin';
		$this->module->dbSchema = '';

		$this->module->tableName(AutoAdminAccess::sqlAdminTableName('interfaces'));
		$this->module->setPK('id');
		$this->module->fieldsConf(array(
				array('alias', 'string', Yii::t('AutoAdmin.access', 'Alias'), array('show')),
				array('section_id', 'foreign', Yii::t('AutoAdmin.access', 'Section'), array('group', 'bindBy'=>'id',
						'foreign'=>array(
							'table'		=> AutoAdminAccess::sqlAdminTableName('sections'),
							'pk'		=> 'id',
							'select'	=> array('title'),
							'searchBy'	=> array('title'=>Yii::t('AutoAdmin.access', 'Section Title')),
							'order'		=> 'title',
						),
					)),
				array('level', 'num', Yii::t('AutoAdmin.access', 'Interface Level'), array('default'=>1, 'show')),
				array('title', 'string', Yii::t('AutoAdmin.access', 'Interface Title'), array('null', 'show')),
				array('info', 'string', Yii::t('AutoAdmin.access', 'Interface Note'), array('null')),
			));

		$this->module->setSubHref('rights');
		$this->module->sortDefault(array('alias'));

		$this->pageTitle = Yii::t('AutoAdmin.access', 'Interfaces of the administration panel');
		$bkp = Yii::app()->request->getParam('bkp', array(0=>array('id'=>null)));
		$this->breadcrumbs[$this->breadcrumbUsers($bkp[0]['id'])] = '../users/';
		$this->breadcrumbs[$this->breadcrumbSections($sectionID)] = '../sections/'.($bkp[0]['id'] ? "?bk[id]={$bkp[0]['id']}" : '');
		$this->breadcrumbs[] = $this->pageTitle;

		$this->module->process();
	}

	/**
	 * Provides managing with sections that group interfaces of the panel.
	 */
	public function actionRights()
	{
		if(Yii::app()->user->isGuest || !in_array(Yii::app()->user->level, array('root', 'admin')))
			throw new CHttpException(403, Yii::t('AutoAdmin.access', 'You do not have permissions to access'));
		$bkp = Yii::app()->request->getParam('bkp', array(0=>array('id'=>null), 1=>array('id'=>null)));
		$bk = Yii::app()->request->getParam('bk', array('id'=>null));
		$userID = $bkp[0]['id'];
		$interfaceID = $bk['id'];

		$this->module->dbConnection = 'dbAdmin';
		$this->module->dbSchema = '';

		$this->module->tableName(AutoAdminAccess::sqlAdminTableName('access'));
		$this->module->setPK('user_id', 'interface_id');
		$fieldsConf = array(
				array('user_id', 'foreign', Yii::t('AutoAdmin.access', 'User'), array('group', 'bind'=>$userID, 'readonly',
						'foreign'=>array(
							'table'		=> AutoAdminAccess::sqlAdminTableName('users'),
							'pk'		=> 'id',
							'select'	=> array('surname', 'firstname', 'login'),
							'searchBy'	=> array('login'=>Yii::t('AutoAdmin.access', 'Login'), 'surname'=>Yii::t('AutoAdmin.access', 'Surname')),
							'order'		=> 'surname',
							'conditions'=> "level = :level",
							'params'	=> array(':level'=>'user'),
						),
					)),
				array('interface_id', 'foreign', Yii::t('AutoAdmin.access', 'Interface'), array('group', 'bindBy'=>'id', 'default'=>$interfaceID, 'readonly',
						'foreign'=>array(
							'table'		=> AutoAdminAccess::sqlAdminTableName('interfaces'),
							'pk'		=> 'id',
							'select'	=> array('alias', 'title'),
							'searchBy'	=> array('alias'=>Yii::t('AutoAdmin.access', 'Alias'), 'title'=>Yii::t('AutoAdmin.access', 'Interface Title')),
							'order'		=> 'alias',
						),
					)),
				array('read', 'boolean', Yii::t('AutoAdmin.access', 'View right'), array('show', 'default'=>true)),
				array('add', 'boolean', Yii::t('AutoAdmin.access', 'Add right'), array('show', 'default'=>true)),
				array('edit', 'boolean', Yii::t('AutoAdmin.access', 'Edit right'), array('show', 'default'=>true)),
				array('delete', 'boolean', Yii::t('AutoAdmin.access', 'Delete right'), array('show', 'default'=>true)),
			);

		$breadcrumbUsers = $this->breadcrumbUsers($userID);
		$this->breadcrumbs[$breadcrumbUsers] = '../users/';
		$this->breadcrumbs[$this->breadcrumbSections($bkp[1]['id'])] = '../sections/'.($bkp[1]['id'] ? "?bk[id]={$bkp[1]['id']}" : '');
		$breadcrumbInterfaces = $this->breadcrumbInterfaces($interfaceID);
		$this->breadcrumbs[$breadcrumbInterfaces] = '../interfaces/'.(($bkp[1]['id'] && $bkp[0]['id']) ? "?bkp[0][id]={$bkp[0]['id']}&bk[id]={$bkp[1]['id']}" : '');
		$this->pageTitle = Yii::t('AutoAdmin.access', 'User "{user}" access rights to the interface "{interface}"',
				array(
					'{user}' => (preg_match('/\(\-\>([^\(\)]+)\)/iu', $breadcrumbUsers, $m) ? $m[1] : ''),
					'{interface}' => (preg_match('/\(\-\>([^\(\)]+)\)/iu', $breadcrumbInterfaces, $m) ? $m[1] : ''),
				)
			);
		$this->breadcrumbs[] = $this->pageTitle;

		if($bkp[0]['id'])
			$fieldsConf[0][3]['default'] = $bkp[0]['id'];
		$this->module->fieldsConf($fieldsConf);
		$this->module->process();
	}

	/**
	 * Checks is there any root users in system DB (superuser who can set access right to other).
	 * @return bool Exists at least one or not.
	 */
	public function isRootDefined()
	{
		$q = Yii::app()->dbAdmin->createCommand();
		$q->select('id');
		$q->from(AutoAdminAccess::sqlAdminTableName('users'));
		$q->where("level = :level", array(':level'=>'root'));
		return (bool)$q->queryScalar();
	}

	/**
	 * Returns a title for $this->breadcrumbs for Users.
	 * @param int $userID The User ID, that can be get from $_GET
	 * @return string Title for breadcrumb
	 */
	public function breadcrumbUsers($userID)
	{
		$crumb = Yii::t('AutoAdmin.access', 'Users of the administration panel');
		if($userID)
		{
			$where = array('AND', "id=:id");
			$params = array(':id'=>$userID);
			//The access of users with level 'users' must be denied outside. Root's allowed to view everything.
			if(Yii::app()->user->level == 'admin')
			{
				$where[] = "level = :level";
				$params[':level'] = 'user';
			}
			
			$crumb .= ' (->'.Yii::app()->dbAdmin->createCommand()
							->select('login')->from(AutoAdminAccess::sqlAdminTableName('users'))
							->where($where, $params)->queryScalar().')';
		}
		return $crumb;
	}

	/**
	 * Returns a title for $this->breadcrumbs for Sections.
	 * @param int $sectionID The Section ID, that can be get from $_GET
	 * @return string Title for breadcrumb
	 */
	public function breadcrumbSections($sectionID)
	{
		$crumb = Yii::t('AutoAdmin.access', 'Sections of interfaces');
		if($sectionID)
		{
			$crumb .= ' (->'.Yii::app()->dbAdmin->createCommand()
							->select('title')->from(AutoAdminAccess::sqlAdminTableName('sections'))
							->where("id=:id", array(':id'=>$sectionID))
							->queryScalar().')';
		}
		return $crumb;
	}

	/**
	 * Returns a title for $this->breadcrumbs for Interfaces.
	 * @param int $interfaceID The Interface ID, that can be get from $_GET
	 * @return string Title for breadcrumb
	 */
	public function breadcrumbInterfaces($interfaceID)
	{
		$crumb = Yii::t('AutoAdmin.access', 'Interfaces of the administration panel');
		if($interfaceID)
		{
			$crumb .= ' (->'.Yii::app()->dbAdmin->createCommand()
							->select('title')->from(AutoAdminAccess::sqlAdminTableName('interfaces'))
							->where("id=:id", array(':id'=>$interfaceID))
							->queryScalar().')';
		}
		return $crumb;
	}

	/**
	 * Gets default aliases from all user-defined controllers.
	 * @param bool $filterWithExisting Whether to filter the result list with existing (recorded in DB).
	 * @return array An array contains information about interfaces. Format of an element: {defaultAlias}=>array({controllerName}, {actionName}).
	 */
	public function getInterfaces($filterWithExisting=false)
	{
		$interfaces = array();
		$controllersDir = Yii::import('application.modules.autoadmin.controllers.*');
		if(is_dir($controllersDir))
		{
			$cFiles = CFileHelper::findFiles($controllersDir, array('fileTypes'=>array('php')));
			foreach($cFiles as $cfile)
			{
				$controllerName = substr($cfile, strrpos($cfile, DIRECTORY_SEPARATOR)+1, -4);
				$methods = @get_class_methods($controllerName);
				if($methods)
				{
					$controllerID = substr($controllerName, 0, strrpos($controllerName, 'Controller'));
					foreach($methods as $methodName)
					{
						if($methodName == 'actions' || !preg_match('/^action([a-z_]+)$/i', $methodName, $m))
							continue;
						$actionID = $m[1];
						$interfaces[AutoAdmin::interfaceID($controllerID, $actionID)] = array($controllerID, $actionID);
					}
				}
			}
		}
		if($interfaces && $filterWithExisting)
		{
			$exInterfaces = Yii::app()->dbAdmin->createCommand()
				->select('id, alias')->from(AutoAdminAccess::sqlAdminTableName('interfaces'))
				->queryAll();
			foreach($exInterfaces as $exInterface)
			{
				if(isset($interfaces[$exInterface['alias']]))
					unset($interfaces[$exInterface['alias']]);
			}
		}

		return $interfaces;
	}
}