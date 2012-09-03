<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo Yii::app()->language?>" lang="<?php echo Yii::app()->language?>">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta name="language" content="<?php echo Yii::app()->language?>"/>
<?php
Yii::app()->clientScript
	->registerCssFile(AutoAdmin::$assetPath.'/css/file-upload.css')
	->registerCoreScript('jquery');
?>
	<title><?php echo CHtml::encode($this->pageTitle)?></title>
</head>

<body>
<div id="iframe-content">
<?php echo $content?>
</div>
</body>
</html>