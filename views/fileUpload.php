<?php
if(empty($uploadedFileAbs))
{
	echo CHtml::form('./?action=upload', 'post', array('enctype'=>'multipart/form-data'));
	echo CHtml::hiddenField(AutoAdmin::INPUT_PREFIX."[upload][field]", $field);
	?>
	<h1><?php echo Yii::t('AutoAdmin.form', 'File upload')?></h1>
	<div class="item">
		<label for="fieldUpload"><?php echo Yii::t('AutoAdmin.form', 'File to upload')?>:</label>
		<?php echo CHtml::fileField(AutoAdmin::INPUT_PREFIX."[uploadFile]", '', array('id'=>'fieldUpload'))?>
	</div>
	<div class="item">
		<label for="fieldAlt"><?php echo Yii::t('AutoAdmin.form', 'Text for ALT=""')?>:</label>
		<?php echo CHtml::textField(AutoAdmin::INPUT_PREFIX."[upload][alt]", '', array('id'=>'fieldAlt'))?>
	</div>
	<div class="item">
		<?php echo CHtml::submitButton(Yii::t('AutoAdmin.form', 'Upload'))?>
	</div>
	<?php
	echo CHtml::closeTag('form');
}
else
{
	ob_start();
	$img = getimagesize($uploadedFileAbs);
	if($img[2] < 4)
	{
		?><img src="<?php echo $uploadedFileSrc?>" <?php echo $img[3]?> alt="<?php echo $alt?>" class="photo"/><?
	}
	elseif($img[2] == 4 || $img[2] == 13)
	{	//Flash
		?>
<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0" <?php echo $img[3]?>>
	<param name="movie" value="<?php echo $uploadedFileSrc?>"/>
	<param name="quality" value="high"/>
	<embed src="<?php echo $uploadedFileSrc?>" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" <?php echo $img[3]?>></embed>
</object>
		<?php
	}
	$code = ob_get_contents();
	ob_end_clean();
	?>
<script type="text/javascript">
	window.opener.aaInsert('<?php echo $code?>', window.opener.$('#editform').find('[name="<?php echo $fieldName?>"]'));
	window.close();
</script>
	<?php
}
?>