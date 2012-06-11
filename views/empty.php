<?
$assetsPath = Yii::getPathOfAlias('ext.autoAdmin.assets');
Yii::app()->clientScript
	->registerCssFile(AutoAdmin::$assetPathCSS.'/list.css');

if(empty($this->breadcrumbs))
	$this->breadcrumbs = array($this->pageTitle);
?>

<h1><?=$this->pageTitle?></h1>
<?
if(!empty($partialViews['up']))
{
	$this->renderPartial($partialViews['up'], $clientData);
}

if(!empty($partialViews['down']))
{
	$this->renderPartial($partialViews['down'], $clientData);
}
?>