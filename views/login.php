<?php
$this->pageTitle = Yii::t('AutoAdmin.access', 'Authentication');
Yii::app()->clientScript
	->registerCssFile(AutoAdmin::$assetPath.'/css/login.css');

$this->breadcrumbs = array(
	$this->pageTitle,
);
?>

<h1><?php echo $this->pageTitle?></h1>

<p class="greeting"><?php echo Yii::t('AutoAdmin.access', 'You\'ve entered as <b>{userName}</b>. It\'s nice to see you!', array('{userName}'=>$userName))?></p>