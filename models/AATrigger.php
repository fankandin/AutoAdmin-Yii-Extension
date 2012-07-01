<?php
/**
 * AATrigger is a class to set and execute triggers - methods of users' controllers, after or before write operation in DB.
 * Ideology is close to DB triggers.
 *
 * @author Alexander Palamarchuk <a@palamarchuk.info>
 * 
 * @property CController $controller Yii controller object.
 * @property array $functions User's controller method that must be called by trigger.
 */
class AATrigger
{
	private $_controller;
	private $_functions;

	public function __construct(&$controller)
	{
		$this->_controller = $controller;
		$this->_functions = array('before'=>array(), 'after'=>array());
	}

	/**
	 * Adds a trigger.
	 * @param string $methodName User controller's method to call.
	 * @param array|null $actions Array of "insert", "update" or "delete". If null every action will be used.
	 * @param string $when "before" or "after" action with DB.
	 * @throws CException 
	 */
	public function addTrigger($methodName, $actions=null, $when='after')
	{
		if(!in_array($when, array('after', 'before')) || ($actions && !is_array($actions)))
			throw new AAException('Wrong trigger configuration');
		if(!$actions)
			$actions = array('insert', 'update', 'delete');
		foreach($actions as $action)
		{
			if(!isset($this->_functions[$when][$action]))
				$this->_functions[$when][$action] = array();
			$this->_functions[$when][$action][] = $methodName;
		}
	}

	/**
	 * Executes all triggers for action and in accordance with order in relation to DB action.
	 * @param array $arg
	 * @param string $action "insert", "update" or "delete".
	 * @param string $when "before" or "after" action with DB.
	 * @throws CException 
	 */
	public function execute($arg, $action, $when)
	{
		if(!isset($this->_functions[$when][$action]))
			return false;
		foreach($this->_functions[$when][$action] as $func)
		{
			try
			{
				$this->_controller->{$func}($arg);
				//To log!
			}
			catch(Exception $e)
			{
				Yii::t('AutoAdmin.errors', 'An error was occured during execution the trigger "{func}"', array('{func}'=>$func));
			}
		}
		return true;
	}
}
