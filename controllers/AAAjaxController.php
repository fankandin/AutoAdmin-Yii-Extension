<?php
class AAAjaxController extends CExtController
{
	/**
	 * Search of AutoComplete type for fields that contained foreign keys.
	 *
	 * POST params:
	 * 	string $term Searching value (the search will be executed by {$term}%)
	 * 	string $fieldBy Field in SQL table is to search by
	 * 	string $keyData Encoded into a row data about a table which is referred by the key
	 * 
	 * The meta-symbol logic is machinery-clever:
	 *  1. If you enter simple string $s, SQL search LIKE-condition will be "LIKE $s%" - by beginning of fields.
	 *  2. If you enter "*" among symbols in $s then also "LIKE $s%", but your "*" will be replaced with SQL-analogs "%".
	 *  3. If you enter "$" in the end of $s then (and only then) SQL-meta "%" will not be added to "LIKE $s". But "*" will be replaced as it should be done with case 2.
	 * 
	 * @todo Security problem: there is need to restrict the search within the tables which are not denied for user (ideally - only within the controller the query is going out from).
	 */
	public function actionForeignkey()
	{
		$term = Yii::app()->request->getPost('term');
		$fieldBy = Yii::app()->request->getPost('fieldBy');
		$keyData = Yii::app()->request->getPost('keyData');

		if(empty($term) || empty($fieldBy) || empty($keyData))
			throw new CHttpException(406);
		$field = AAHelperUrl::decodeParam($keyData);

		//Primitively exclude an opportunity to access another DB, input injections and so other
		if(preg_match('/[^a-z_]/i', $field->options['table']) || !isset($field->options['searchBy'][$fieldBy]))
			throw new CHttpException(406);

		$data = array();
		$matches = array();
		$q = Yii::app()->db->createCommand();
		$q->from = $field->options['table'];
		$q->select(array_merge(array($field->options['pk']), array_keys($field->options['select'])));

		$term = str_replace('%', '\%', $term);
		if(false !== mb_strpos($term, '*'))
			$term = str_replace('*', '%', $term);
		if(mb_substr($term, -1) == '$')
			$term = mb_substr($term, 0, -1);
		elseif(mb_substr($term, -1) != '%')
			$term = $term.'%';

		$q->where(Yii::app()->db->quoteColumnName($fieldBy)." LIKE :term", array(':term'=>$term));
		$q->order($fieldBy);
		$result = $q->queryAll();

		foreach($result as $r)
		{
			$label = "";
			foreach($r as $kField=>$value)
			{
				if($kField != $field->options['pk'])
					$label .= ($label ? ' - ' : '').$value;
			}
			$matches[] = array('label'=>$label, 'value'=>$r[$field->options['pk']]);
		}
		echo CJSON::encode($matches);
	}
}
