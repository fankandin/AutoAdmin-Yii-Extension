<?php
$this->pageTitle = Yii::t('AutoAdmin.access', 'Authentication');
Yii::app()->clientScript
	->registerCssFile(AutoAdmin::$assetPath.'/css/login.css');

$this->breadcrumbs = array(
	$this->pageTitle,
);
?>

<h1><?=$this->pageTitle?></h1>

<?
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
		<?=$form->labelEx($model, 'login');?>
		<?=$form->textField($model,'login');?>
		<?=$form->error($model,'login');?>
	</div>

	<div class="row">
		<?=$form->labelEx($model, 'password');?>
		<?=$form->passwordField($model, 'password');?>
		<?=$form->error($model,'password');?>
	</div>

<!--
	<div class="row rememberMe">
		<?//$form->checkBox($model,'rememberMe');?>
		<?//$form->label($model,'rememberMe');?>
		<?//$form->error($model,'rememberMe');?>
	</div>
-->

	<div class="row buttons">
		<?=CHtml::submitButton(Yii::t('AutoAdmin.access', 'Enter'), array('class'=>'submit'));?>
	</div>

<? $this->endWidget(); ?>
