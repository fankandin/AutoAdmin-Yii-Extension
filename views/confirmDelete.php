<?php
Yii::app()->clientScript
	->registerCssFile(AutoAdmin::$assetPath.'/css/confirm.css');

$this->breadcrumbs = array(
	$this->pageTitle,
);
?>

<div class="narrow-content">

<h1><?php echo Yii::t('AutoAdmin.common', 'Delete record')?></h1>
<?php
echo CHtml::form($confirmUrl, 'post', array('id'=>'confirm-delete'));
echo CHtml::hiddenField('sure', 1);
?>
<p class="msg"><b><?php echo Yii::t('AutoAdmin.common', 'Attention!')?></b><br/><?php echo Yii::t('AutoAdmin.messages', 'The record will be deleted')?></p>
<?php
$cbForDel = array();
foreach($fields as $i=>$field)
{
	if(in_array($field->type, array('image', 'file')))
	{
		$cbForDel[$i] = Yii::t('AutoAdmin.form', 'Delete the file "<b><i>{file}</i></b>".', array('{file}'=>"{$field->options['directoryPath']}/{$field->value}"));
	}
}
if($cbForDel)
{
	?>
	<fieldset><?php echo CHtml::checkBoxList('filesToDelF', null, $cbForDel);?></fieldset>
	<?php
}
?>
<br/>
<?php echo CHtml::submitButton(Yii::t('AutoAdmin.common', 'Confirm'), array('name'=>null));?>
<div class="cancel">[ <a href="<?php echo $cancelUrl?>"><?php echo Yii::t('AutoAdmin.common', 'Cancel')?></a> ]</div>

<?php echo CHtml::closeTag('form');?>
</div>
<?php $this->widget('AAWidgetLoginpanel', array()); ?>