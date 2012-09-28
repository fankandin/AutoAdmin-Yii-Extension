<?php
class AutoAdminAccess
{
	/**
	 *
	 * @var bool Whether to operate in open mode (without internal authentification and authorization). Must be set up from @link AutoAdmin.
	 */
	private $openMode = true;
	/**
	 *
	 * @var int Current interface ID.
	 */
	private $interfaceID;

	/**
	 *
	 * @var array Possible access rights.
	 */
	private $possibleRights = array('read', 'add', 'edit', 'delete');
	/**
	 * 
	 * @var array Preset of the access rights.
	 */
	private $rights = array('read', 'add', 'edit', 'delete');
	/**
	 *
	 * @var int Level of the current interface.
	 */
	public $level;
	/**
	 *
	 * @var string Prefix for service tables. Can be changed from the config.
	 */
	public static $dbTablePrefix = 'aa_';

	/**
	 * AutoAdminAccess constructor.
	 * @param string $interfaceAlias An alias identificator for the current interface (which also is an unique key).
	 */
	public function __construct($interfaceAlias)
	{
		$interface = Yii::app()->dbAdmin->createCommand()
			->select('id, level')
			->from(self::sqlAdminTableName('interfaces'))
			->where("alias = :alias", array(':alias'=>$interfaceAlias))
			->queryRow();
		if($interface)
		{
			$this->interfaceID = $interface['id'];
			$this->level = $interface['level'];
		}
		if(!$this->interfaceID && !$this->isOpenMode())
		{
			throw new CHttpException(403, Yii::t('AutoAdmin.access', 'Open Mode is inactive, but the interface {alias} is unknown. There is need to to set the correct configuration.', array('{alias}'=>$interfaceAlias)));
		}
		$this->setOpenMode(!empty(Yii::app()->modules['autoadmin']['openMode']));
	}

	/**
	 * Loads the rights on the current interface for the user.
	 */
	public function loadAccessSettings()
	{
		$this->rights = $this->possibleRights;
		if(!$this->isOpenMode())
		{
			if(Yii::app()->user->isGuest)
				$this->rights = array();
			elseif(!in_array(Yii::app()->user->level, array('root', 'admin')))
			{
				$this->rights = array();
				$uRights = Yii::app()->dbAdmin->createCommand()
					->select(array('read', 'add', 'edit', 'delete'))
					->from(self::sqlAdminTableName('access'))
					->where(array('AND',
								"interface_id = :interfaceID",
								"user_id = :userID"
							),
							array(':interfaceID'=>$this->interfaceID, ':userID'=>Yii::app()->user->id)
						)
					->queryRow();
				if($uRights)
				{
					if($uRights['read'])
						$this->rights[] = 'read';
					if($uRights['add'])
						$this->rights[] = 'add';
					if($uRights['edit'])
						$this->rights[] = 'edit';
					if($uRights['delete'])
						$this->rights[] = 'delete';
				}
				elseif(Yii::app()->user->interfaceLevel && !is_null($this->level))
				{	//Rights are not personalized. Use the levels collation.
					if(Yii::app()->user->interfaceLevel >= $this->level)
					{
						$this->rights = array('read', 'add', 'edit', 'delete');
					}
				}
			}
		}
	}

	/**
	 * Checks the standart right of the user on the current interface.
	 * @param string $right One from set [read, update, add, delete].
	 * @return boolean Whether right is true or false.
	 */
	public function checkRight($right)
	{
		if($this->isOpenMode() || in_array($right, $this->rights))
			return true;
		else
			return false;
	}

	/**
	 * Sets the standart right of the user on the current interface.
	 * May be used in two directions: allow the right or deny.
	 * @param string|null $right One from set [read, update, add, delete]. If null, $permition will be applied to all defaultly possible rights.
	 * @param bool $permition Allow or deny.
	 * @return boolean Whether the operation was successful.
	 */
	public function setRight($right, $permition)
	{
		if(is_null($right))
		{
			$this->rights = ($permition ? $this->possibleRights : array());
			return true;
		}
		elseif(!in_array($right, $this->possibleRights))
		{
			throw new AAException(Yii::t('AutoAdmin.errors', 'Wrong params for access rights'));
		}
		else
		{
			if(!in_array($right, $this->rights))
			{
				if($permition)
					$this->rights[] = $right;
			}
			elseif(!$permition)
			{
				array_splice($this->rights, array_search($right, $this->rights), 1);
			}
			return true;
		}
		return false;
	}

	/**
	 * Exports the current rights of the user as array.
	 * @return array $this->rights
	 */
	public function exportRights()
	{
		return $this->rights;
	}

	/**
	 * Checks if there is the Open Mode (all users do not authorize) now or not.
	 * @return bool Whether the Open Mode is active.
	 */
	public function isOpenMode()
	{
		return $this->openMode;
	}

	/**
	 * Sets the Open Mode or disables it.
	 * @param bool $flag Whether to set the Open Mode is active or not.
	 */
	public function setOpenMode($flag=true)
	{
		$this->openMode = $flag;
	}

	/**
	 * Logs an error.
	 * @param string $errorType Predefined (in SQL) code.
	 * @param string $message Any text.
	 */
	public function logError($errorType, $message)
	{
		Yii::app()->dbAdmin->createCommand()->insert(self::sqlAdminTableName('errors'), array(
				'authorization_id'	=> (Yii::app()->user->isGuest ? new CDbExpression('NULL') : Yii::app()->user->authID),
				'error_type'		=> $errorType,
				'info'				=> $message
			));
	}

	/**
	 * Logs unconditioned text message which is linked to a current user's session.
	 * @param string $message 
	 */
	public function log($message, $data)
	{
		Yii::app()->dbAdmin->createCommand()->insert(self::sqlAdminTableName('logs'), array(
				'interface_id'		=> ($this->interfaceID ? new CDbExpression('NULL') : $this->interfaceID),
				'authorization_id'	=> (Yii::app()->user->isGuest ? new CDbExpression('NULL') : Yii::app()->user->authID),
				'when_event'		=> date('Y-m-d H:i:s'),
				'message'			=> $message,
				'data'				=> serialize($data),
			));
	}

	/**
	 * Gets the full name of the predefined service table adapted for SQL query. Includes schema name, prefix and other configurable params.
	 * @param string $tableName Base name of a service table to process into the full construction.
	 * @return string The full compound ready for using in a SQL query.
	 */
	public static function sqlAdminTableName($tableName)
	{
		return (!empty(Yii::app()->modules['autoadmin']['dbAdminSchema']) ? Yii::app()->modules['autoadmin']['dbAdminSchema'].'.' : '').self::$dbTablePrefix.$tableName;
	}
}
