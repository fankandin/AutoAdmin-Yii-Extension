<?php
Yii::app()->clientScript
	->registerCssFile(AutoAdmin::$assetPath.'/css/edit-result.css');
?>

<div class="narrow-content">

	<h1><?php echo $this->pageTitle?></h1>

	<p class="msg"><?php echo $msg?></p>
</div>
<script language="JavaScript">window.setTimeout(function() {document.location.href = "<?php echo $redirectURL?>"}, <?php echo (empty($errorOccured) ? 1000 : 10000)?>);</script>

<?php
if(empty($iframeMode))
	$this->widget('AAWidgetLoginpanel', array());
?>