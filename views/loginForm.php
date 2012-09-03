<?php
$this->pageTitle = Yii::t('AutoAdmin.access', 'Authentication');
Yii::app()->clientScript
	->registerCssFile(AutoAdmin::$assetPath.'/css/login.css');

$this->breadcrumbs = array(
	$this->pageTitle,
);
?>

<h1><?php echo $this->pageTitle?></h1>

<?php
$form = $this->beginWidget('CActiveForm', array(
	'id'=>'login-form',
	'action'=>"./",
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
));
?>
	<div class="row">
		<?php echo $form->labelEx($model, 'login');?>
		<?php echo $form->textField($model,'login');?>
		<?php echo $form->error($model,'login');?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model, 'password');?>
		<?php echo $form->passwordField($model, 'password');?>
		<?php echo $form->error($model,'password');?>
	</div>

<!--
	<div class="row rememberMe">
		<?php //$form->checkBox($model,'rememberMe');?>
		<?php //$form->label($model,'rememberMe');?>
		<?php //$form->error($model,'rememberMe');?>
	</div>
-->

	<div class="row buttons">
		<?php echo CHtml::submitButton(Yii::t('AutoAdmin.access', 'Enter'), array('class'=>'submit'))?>
	</div>

<?php $this->endWidget()?>
