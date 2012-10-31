<?php
$actionURL = AAHelperUrl::update($baseURL, array('searchQ', 'searchBy'));
$getParams = AAHelperUrl::uriToParamsArray($actionURL);
if($getParams)
	$actionURL = AAHelperUrl::update($actionURL, array_keys($getParams));
echo CHtml::form($actionURL, 'get', array('id'=>'search-panel'));
foreach($getParams as $param=>$value)
	echo CHtml::hiddenField($param, $value);
echo CHtml::label(Yii::t('AutoAdmin.common', 'Search').':', 'searchQ');
echo CHtml::textField('searchQ', (isset($searchOptions['query']) && !is_array($searchOptions['query']) ? $searchOptions['query'] : ''), array('id'=>'searchQ'));

$inSearch = array();
$selectedIndex = null;

foreach($fields as $k=>$field)
{
	if(!empty($field->options['inSearch']))
	{
		$inSearch[$k] = $field->label;
		if(!empty($searchOptions['field']) && $searchOptions['field']->name == $field->name)
			$selectedIndex = $k;
	}
}
echo CHtml::dropDownList('searchBy', $selectedIndex, $inSearch);
echo CHtml::submitButton('OK', array('name'=>null, 'title'=>Yii::t('AutoAdmin.common', 'Search')));
echo CHtml::resetButton(Yii::t('AutoAdmin.common', 'Reset'), array('name'=>null, 'title'=>Yii::t('AutoAdmin.common', 'Reset')));

echo CHtml::closeTag('form');
?>