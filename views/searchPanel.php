<?
echo CHtml::form('./', 'get', array('class'=>'searchPanel'));

foreach($getParams as $param=>$value)
{
	if($param == 'searchq' || $param == 'searchby')
		continue;
	echo CHtml::hiddenField($param, $value);
}
echo CHtml::label('Поиск: ', 'searchq');
echo CHtml::textField('searchq', ((!empty($_GET['searchq'])) ? $_GET['searchq'] : ''));

$searchOptions = array();
foreach($SearchBy as $k)
{
	$searchOptions[$k] = $fields['names'][$k];
}
echo CHtml::dropDownList('searchby', (isset($_GET['searchby']) ? $_GET['searchby'] : null), $searchOptions);

echo CHtml::submitButton('OK', array('name'=>null));
?>
[<a href="<?=AAHelperUrl::update(Yii::app()->request->requestUri, array('searchq', 'searchby'))?>">x</a>]
<?

echo CHtml::closeTag('form');
?>