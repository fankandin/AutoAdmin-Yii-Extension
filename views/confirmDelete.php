<?
Yii::app()->clientScript
	->registerCssFile(AutoAdmin::$assetPath.'/css/confirm.css');

$this->breadcrumbs = array(
	$this->pageTitle,
);
?>

<div class="narrow-content">

<h1><?=Yii::t('AutoAdmin.common', 'Delete record')?></h1>
<?
echo CHtml::form($confirmUrl, 'post', array('id'=>'confirm-delete'));
echo CHtml::hiddenField('sure', 1);
?>
<p class="msg"><b><?=Yii::t('AutoAdmin.common', 'Attention!')?></b><br/><?=Yii::t('AutoAdmin.messages', 'The record will be deleted')?></p>
<?
$cbForDel = array();
foreach($fields as $i=>&$field)
{
	if(in_array($field->type, array('image', 'file')))
	{
		$cbForDel[$i] = Yii::t('AutoAdmin.form', 'Delete the file "<b><i>{file}</i></b>".', array('{file}'=>"{$field->options['directoryPath']}/{$field->value}"));
	}
}
if($cbForDel)
{
	?>
	<fieldset><?=CHtml::checkBoxList('filesToDelF', null, $cbForDel);?></fieldset>
	<?
}
?>
<br/>
<?=CHtml::submitButton(Yii::t('AutoAdmin.common', 'Confirm'), array('name'=>null));?>
<div class="cancel">[ <a href="<?=$cancelUrl?>"><?=Yii::t('AutoAdmin.common', 'Cancel')?></a> ]</div>

<?=CHtml::closeTag('form');?>

</div>