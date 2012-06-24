<?
$baseURL = HelperUrl::stripParam($baseURL, 'searchQ');
$baseURL = HelperUrl::stripParam($baseURL, 'searchBy');
echo CHtml::form($baseURL, 'get', array('id'=>'search-panel'));
/*
foreach($getParams as $param=>$value)
{
	if($param == 'searchq' || $param == 'searchby')
		continue;
	echo CHtml::hiddenField($param, $value);
}
 * 
 */
echo CHtml::label(Yii::t('AutoAdmin.common', 'Search').':', 'searchQ');
echo CHtml::textField('searchQ', (!empty($searchQ) ? $searchQ : ''), array('id'=>'searchQ'));

$inSearch = array();
foreach($fields as $i=>&$field)
{
	if(!empty($field->options['inSearch']))
	{
		$inSearch[$i] = $field->label;
	}
}
echo CHtml::dropDownList('searchBy', (isset($searchBy) ? $searchBy : null), $inSearch);
echo CHtml::submitButton('OK', array('name'=>null, 'title'=>Yii::t('AutoAdmin.common', 'Search')));

echo CHtml::closeTag('form');
?>