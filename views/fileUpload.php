<?
if(empty($close))
{
	?>
<?=CHtml::form('./', 'post', array('enctype'=>'multipart/form-data'))?>
	<h1>Добавление изображения</h1>
	<div class="item">
		<label for="fieldAlt">Текст в поле Alt:</label><?=CHtml::textField('alt', '', array('id'=>'fieldAlt'))?>
	</div>
	<div class="item">
		<label for="fieldUpload">Загружаемый файл:</label><?=CHtml::fileField('file', '', array('id'=>'fieldUpload'))?>
	</div>
	<div class="item">
		<?=CHtml::submitButton('Загрузить')?>
	</div>
	<p class="note"><small>Примечание: можно загружать только те изображения, которые расположены на Вашем компьютере.</small></p>
	<?=CHtml::hiddenField('fieldID', $fieldID)?>
	<?=CHtml::hiddenField('interface', $interface)?>
<?=CHtml::closeTag('form');?>
	<?
}
else
{
	ob_start();
	if($img[2] < 4)
	{
		?><img src="<?=$imgname?>" <?=$img[3]?> alt="<?=$_POST['alt']?>" class="photo"/><?
	}
	elseif($img[2] == 4 || $img[2] == 13)
	{	//Flash
		?>
<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0" <?=$img[3]?>>
	<param name="movie" value="<?=$imgname?>"/>
	<param name="quality" value="high"/>
	<embed src="<?=$imgname?>" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" <?=$img[3]?>></embed>
</object>
		<?
	}
	$code = ob_get_contents();
	ob_end_clean();
	?>
<script type="text/javascript">
	window.opener.aaInsert('<?=$code?>', window.opener.$('#<?=$fieldID?>'));
	window.close();
</script>
	<?
}
