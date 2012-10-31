<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo Yii::app()->language?>" lang="<?php echo Yii::app()->language?>">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta name="language" content="<?php echo Yii::app()->language?>"/>
	<meta name="author" content="Alexander Palamarchuk, a@palamarchuk.info"/>
	<meta name="copyright" content="Alexander Palamarchuk, a@palamarchuk.info"/>
	<title><?php echo CHtml::encode($this->pageTitle)?> | <?php echo Yii::app()->name?></title>
	<?php Yii::app()->clientScript->registerCssFile(AutoAdmin::$assetPath.'/css/screen.css')?>
</head>

<body>

<div id="head">
	<div id="logo"><a href="/_admin/"></a></div>
	<div id="site-name"><?php echo Yii::app()->name;?></div>
	<div id="cms-name">[Powered by: AutoAdmin CMS&trade;]</div>
</div>
<?php
/*
$menu = array(
	array('label'=>'Sport', 'url'=>array('/sport/countries/'), 'itemOptions'=>array('id'=>'menu_item1')),
	array('label'=>'Brands', 'url'=>array('/brands/'), 'itemOptions'=>array('id'=>'menu_item2')),
);
$this->widget('zii.widgets.CMenu',array(
	'items'=>$menu,
	'id'=>'menu'
));
*/
if(isset($this->breadcrumbs))
{
	$this->widget('zii.widgets.CBreadcrumbs', array(
		'homeLink'=>'<a href="'.$this->createUrl('/').'/" id="home">Main panel</a>',
		'links'=>$this->breadcrumbs,
	));
}
?>
<div id="content">
<?php
echo $content;

if($this->id == 'default' && $this->action->id == 'index')
	$this->widget('AAWidgetLoginpanel', array());
?>
</div>
<div id="footer"></div>

<script type="text/javascript">
$(document).ready(function(){
	$('div.sourcecode a').click(function() {
		$(this).parent().find('div.code').toggle();
		$(this).toggleClass('opened');
	});
});
</script>
</body>
</html>