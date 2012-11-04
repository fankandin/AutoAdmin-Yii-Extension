<?php
Yii::app()->clientScript
	->registerCssFile(AutoAdmin::$assetPath.'/css/generator.css');
?>
<h1><?php echo Yii::t('AutoAdmin.generator', 'AutoAdmin Generator')?></h1>
<h2><?php echo $this->pageTitle?></h2>

<?php		
echo CHtml::form('./', 'post', array('id'=>'editform', 'autocomplete'=>'off'));
echo CHtml::hiddenField('table', $tableName);
?>
<div class="code">
	<?php highlight_string("<?php\n\n".strval($construction)."\n\n?>")?> 
</div>
<p><?php echo Yii::t('AutoAdmin.generator', 'Copy the text above and past it into your controller\'s code.')?></p>
<?php
//echo CHtml::submitButton(Yii::t('AutoAdmin.common', 'Save'), array('name'=>null));
echo CHtml::closeTag('form');
?>