<?php
/**
 * LoginForm class.
 * LoginForm is the data structure for keeping
 * user login form data. It is used by the 'login' action of 'SiteController'.
 */
class AALoginForm extends CFormModel
{
	public $login;
	public $password;
	public $rememberMe = false;
	public $isNew = 'new';

	/**
	 * Declares the validation rules.
	 * The rules state that username and password are required,
	 * and password needs to be authenticated.
	 */
	public function rules()
	{
		return array(
			array('login', 'required'),
			array('password', 'required', 'on'=>'login'),
			array('login', 'length', 'min'=>1, 'max'=>60),
			array('password', 'length', 'min'=>4, 'max'=>32),
			array('password', 'required', 'on'=>'login'),
			array('rememberMe', 'boolean'),
			array('password', 'authenticate', 'on'=>'login'),
			//array('isNew', 'required'),
			array('isNew', 'in', 'range'=>array('new', 'exist')),
		);
	}

	/**
	 * Declares attribute labels.
	 */
	public function attributeLabels()
	{
		return array(
			'rememberMe'=> Yii::t('AutoAdmin.access', 'Remember Me'),
			'login'		=> Yii::t('AutoAdmin.access', 'Login'),
			'password'	=> Yii::t('AutoAdmin.access', 'Password'),
		);
	}

	/**
	 * Authenticates the password.
	 * This is the 'authenticate' validator as declared in rules().
	 */
	public function authenticate($attribute=null, $params=null)
	{
		$identity = new AAUserIdentity($this->login, $this->password);
		$identity->authenticate();

		switch($identity->errorCode)
		{
			case AAUserIdentity::ERROR_NONE:
			{
				$duration = $this->rememberMe ? 3600*24*7 : 0; // 30 days
				Yii::app()->user->login($identity, $duration);
				return true;
			}
			case AAUserIdentity::ERROR_USER_DISABLED:
				$this->addError('login', Yii::t('AutoAdmin.errors', 'Sorry, your account is blocked'));
				break;
			default:
				$this->addError('password', Yii::t('AutoAdmin.errors', 'Login or password is incorrect'));
				break;
		}
		return false;
	}

    public function safeAttributes()
    {
        return array('login', 'password', 'rememberMe');
    }
}
