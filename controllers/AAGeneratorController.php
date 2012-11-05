<?php
/**
 * The Controller provides access to generators of actions basing on SQL tables.
 * A DB user set for 'db' components must have rights to read the schemas info.
 */
class AAGeneratorController extends CExtController
{
	public $breadcrumbs;

	/**
	 * Index page with tables selector.
	 */
	public function actionIndex()
	{
		if(!$this->module->authMode || $this->module->openMode || (Yii::app()->user->isGuest || !in_array(Yii::app()->user->level, array('root'))))
			throw new CHttpException(403);

		$generator = new AAGenerator($this->module->dbSchema);
		$this->pageTitle = Yii::t('AutoAdmin.generator', 'SQL table selection');
		$this->breadcrumbs[] = $this->pageTitle;
		$this->render($this->module->viewsPath.'generatorTableSelect', array('tables'=>$generator->getTables()));
	}

	/**
	 * Creating an interface.
	 */
	public function actionTable()
	{
		if(!$this->module->authMode || $this->module->openMode || (Yii::app()->user->isGuest || !in_array(Yii::app()->user->level, array('root'))))
			throw new CHttpException(403, Yii::t('AutoAdmin.access', 'You do not have permissions to access'));
		$generator = new AAGenerator($this->module->dbSchema);

		$table = Yii::app()->request->getParam('table');
		if(!$table)
			throw new CHttpException(400, Yii::t('AutoAdmin.generator', 'Table has not been selected'));
		elseif(strpos($table, '.')!==false || !in_array($table, $generator->getTableNames()))	//don't allow to add schemas ans DB names in table names
			throw new CHttpException(400, Yii::t('AutoAdmin.generator', 'Incorrect table "{table}"', array('{table}'=>$table)));

		$this->pageTitle = Yii::t('AutoAdmin.generator', 'Creation interface for the table "{table}"', array('{table}'=>$table));
		$this->breadcrumbs[Yii::t('AutoAdmin.generator', 'SQL table selection')] = $this->createUrl('aagenerator/index');
		$this->breadcrumbs[] = $this->pageTitle;
		
		$this->render($this->module->viewsPath.'generatorTableForm', array(
			'tableName'=>$table,
			'construction'=>$generator->generate($table),
		));
	}
}