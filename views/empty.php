<?php
Yii::app()->clientScript
	->registerCssFile(AutoAdmin::$assetPath.'/css/list.css');

if(empty($this->breadcrumbs))
	$this->breadcrumbs = array($this->pageTitle);
?>

<h1><?php echo $this->pageTitle?></h1>
<?php
if(!empty($partialViews['up']))
{
	$this->renderPartial($partialViews['up'], $clientData);
}

if(!empty($partialViews['down']))
{
	$this->renderPartial($partialViews['down'], $clientData);
}
?>