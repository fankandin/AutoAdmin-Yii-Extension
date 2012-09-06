<?php
/**
 * Class for easy creating breadcrumbs in AutoAdmin controllers.
 * 
 * WARNING!
 * In the current realization this mechanism doesn't give you defense against the CMS user\'s injections in url, in "bk" & "bkp" params. That's insafe only for reading data from the table that you configured in from() clause. But if it's a critical for you, please do operate with the breadcrumbs manually.
 *
 * @author Alexander Palamarchuk <a@palamarchuk.info>
 */
class AABreadcrumbs
{
	public $controller;
	public $command;

	public function __construct(&$controller)
	{
		$this->controller = $controller;
		$this->query = new AABreadcrumbsQuery(Yii::app()->{AADb::$dbConnection});
	}

	/**
	 * Inserts the breadcrumbs-element into the breadcrumbs array at the numeric position.
	 * @param int $position
	 * @param string $label 
	 * @param string $link 
	 * @return string The resulting label.
	 */
	public function insertToPosition($position, $label, $link)
	{
		$bcKeys = array_keys($this->controller->breadcrumbs);
		if(!$bcKeys)
		{
			$this->controller->breadcrumbs = array($label=>$link);
		}
		else
		{
			$bc = array();
			foreach($bcKeys as $i=>$key)
			{
				if($i==$position)
					$bc[$label] = $link;
				$bc[$key] = $this->controller->breadcrumbs[$key];
			}
			$this->controller->breadcrumbs = $bc;
		}
	}

	/**
	 * Adds a level into the breadcrumbs, converting service parameters in the link and extracting a label in help with SQL-query that is preliminary defined by $this->query.
	 * @param int $level Zero or negative value as step downwards.
	 * @param string $action Controller's action to link to.
	 * @param string $label Label for breadcrumb.
	 * @param bool $labelNonStatic Forces to make a query and use $label as postfix for the found label.
	 * @return string A final breadcrumb label.
	 * @throws AAException 
	 */
	public function addLevel($level, $action, $label=null, $labelNonStatic=false)
	{
		if($level > 0)
			return;

		if($labelNonStatic || is_null($label))
		{
			if(!isset($this->query->command->from) || !isset($this->query->command->select))
				throw new AAException(Yii::t('AutoAdmin.errors', 'Breadcrumbs can be generated only after AABreadcrumbs->query->select()->from() initializing. Or use call with static label.'));
			if(!$this->query->command->where)	//otherwise means a user set where by himself.
			{
				if($level == 0)
				{
					$bk = Yii::app()->request->getParam('bk');
				}
				else
				{
					$bkp = Yii::app()->request->getParam('bkp');
					if(!isset($bkp[abs($level)-1]))
						throw new AAException(Yii::t('AutoAdmin.errors', 'Incorrect breadcrumbs level.'));
					$bk = $bkp[abs($level)-1];
				}
				if(is_null($bk) || !is_array($bk))
					throw new AAException(Yii::t('AutoAdmin.errors', 'Auto-generation of the breadcrumbs can be done only with the default inside-controller navigation.'));
				$where = array('AND');
				$params = array();
				foreach($bk as $pk=>$pkValue)
				{
					$where[] = "{$pk}=:{$pk}";
					$params[":{$pk}"] = $pkValue;
				}
				$this->query->where($where, $params);
				$this->query->command->limit(1);
			}
			$newLabel = $this->query->command->queryScalar();
			$label = ($label && $labelNonStatic) ? "{$newLabel}. {$label}" : $newLabel;
		}

		if($level == 0)
		{
			$this->controller->breadcrumbs[] = $this->controller->pageTitle = AAHelperText::ucfirst($label).'. '.$this->controller->pageTitle;
		}
		else
		{
			$params = AAHelperUrl::uriToParamsArray(Yii::app()->request->getRequestUri());
			if($params)
			{	//Removing GET-params which relate to the current page
				$paramsToExclude = array('page', 'sortBy', 'searchBy', 'searchQ', 'msg', 'action', 'bk', 'bkp', 'id', 'foreign');
				foreach($paramsToExclude as $param)
				{
					if(array_key_exists($param, $params))
						unset($params[$param]);
					if(!$params)
						break;
					else
					{
						foreach($params as $key=>$value)
						{
							if(preg_match("/^{$param}\[/i", $key))
							{
								unset($params[$key]);
								continue 2;
							}
						}
					}
				}
			}
			$link = "../{$action}/";
			$bkp = Yii::app()->request->getParam('bkp', array());
			if(isset($bkp[0]))
			{
				foreach($bkp as $pkLevel=>$pk)
				{
					if($pkLevel < abs($level)-1)
						continue;
					if($pkLevel==0)
						$paramBase = 'bk';
					else
						$paramBase = 'bkp['.($pkLevel-1).']';
					foreach($pk as $keyField=>$keyValue)
					{
						$params[$paramBase.'['.$keyField.']'] = $keyValue;
					}
				}
			}
			if($params)
			{
				$paramStr = '';
				foreach($params as $param=>$value)
					$paramStr .= ($paramStr ? '&' : '?').$param.'='.$value;
				$link .= $paramStr;
			}

			$this->insertToPosition((count($this->controller->breadcrumbs)+$level), $label, $link);
		}
		$this->query->command->reset();
		return $label;
	}
}
