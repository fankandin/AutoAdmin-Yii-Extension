<?php
/**
 * Работа с авторизацией пользователей
 */
class AAUserIdentity extends CUserIdentity
{
	const ERROR_USER_DISABLED = 25;
    private $_id;

    public function authenticate()
    {
		$q = Yii::app()->dbAdmin->createCommand();
		$q->from(AutoAdminAccess::sqlAdminTableName('users'));
		$q->where(array('AND',
					'login = :userName',
				),
				array(':userName'=>$this->username)
			);

        $user = $q->queryRow();
        if(!$user)
            $this->errorCode = self::ERROR_USERNAME_INVALID;
        elseif($user['password'] != self::hashPassword($this->password))
            $this->errorCode = self::ERROR_PASSWORD_INVALID;
        elseif($user['disabled'])
            $this->errorCode = self::ERROR_USER_DISABLED;
        else
        {
            $this->errorCode = self::ERROR_NONE;

            $this->_id = $user['id'];
			$this->setState('level', $user['level']);
			$this->setState('interfaceLevel', $user['interface_level']);
            $this->setState('surname', $user['surname']);
            $this->setState('firstname', $user['firstname']);

			Yii::app()->dbAdmin->createCommand()->insert(AutoAdminAccess::sqlAdminTableName('authorizations'), array(
					'user_id' => $user['id'],
					'when_enter' => date('Y-m-d H:i:s'),
					'ip'=> Yii::app()->request->getUserHostAddress(),
				));
			$tableSchema = Yii::app()->dbAdmin->schema->getTable(AutoAdminAccess::sqlAdminTableName('authorizations'));
			$this->setState('authID', Yii::app()->dbAdmin->getLastInsertID(($tableSchema->sequenceName ? $tableSchema->sequenceName : null)));
        }
        return !$this->errorCode;
    }
 
    public function getId()
    {
        return $this->_id;
    }

	/**
	 * Хэширует пароли для записи в БД или соответствующего сравнения
	 * @param string $origin Оригинальный пароль
	 * @param string $method Метод хэширования
	 * @return string Хэш пароля
	 */
	public static function hashPassword($origin, $method='md5')
	{
		switch($method)
		{
			default:
				return md5((Yii::app()->params['hashSault'] ? Yii::app()->params['hashSault'] : '').$origin);
		}
	}
}