<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta name="language" content="ru"/>
<?
Yii::app()->clientScript
	->registerCssFile(AutoAdmin::$assetPath.'/css/file-upload.css')
	->registerCoreScript('jquery');
?>
	<title><?=CHtml::encode($this->pageTitle);?></title>
	<link rel="shortcut icon" href="/favicon.ico"/>
</head>

<body>
<div id="iframe-content">
<?=$content;?>
</div>
</body>
</html>