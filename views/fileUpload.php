<?
if(empty($uploadedFileAbs))
{
	?>
<?=CHtml::form('./?action=upload', 'post', array('enctype'=>'multipart/form-data'))?>
	<?=CHtml::hiddenField(AutoAdmin::INPUT_PREFIX."[upload][field]", $field)?>
	<h1><?=Yii::t('AutoAdmin.form', 'File upload')?></h1>
	<div class="item">
		<label for="fieldUpload"><?=Yii::t('AutoAdmin.form', 'File to upload')?>:</label>
		<?=CHtml::fileField(AutoAdmin::INPUT_PREFIX."[uploadFile]", '', array('id'=>'fieldUpload'))?>
	</div>
	<div class="item">
		<label for="fieldAlt"><?=Yii::t('AutoAdmin.form', 'Text for ALT=""')?>:</label>
		<?=CHtml::textField(AutoAdmin::INPUT_PREFIX."[upload][alt]", '', array('id'=>'fieldAlt'))?>
	</div>
	<div class="item">
		<?=CHtml::submitButton(Yii::t('AutoAdmin.form', 'Upload'))?>
	</div>
<?=CHtml::closeTag('form');?>
	<?
}
else
{
	ob_start();
	$img = getimagesize($uploadedFileAbs);
	if($img[2] < 4)
	{
		?><img src="<?=$uploadedFileSrc?>" <?=$img[3]?> alt="<?=$alt?>" class="photo"/><?
	}
	elseif($img[2] == 4 || $img[2] == 13)
	{	//Flash
		?>
<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0" <?=$img[3]?>>
	<param name="movie" value="<?=$uploadedFileSrc?>"/>
	<param name="quality" value="high"/>
	<embed src="<?=$uploadedFileSrc?>" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" <?=$img[3]?>></embed>
</object>
		<?
	}
	$code = ob_get_contents();
	ob_end_clean();
	?>
<script type="text/javascript">
	window.opener.aaInsert('<?=$code?>', window.opener.$('#editform').find('[name="<?=$fieldName?>"]'));
	window.close();
</script>
	<?
}
