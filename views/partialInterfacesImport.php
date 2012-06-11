<?
if(!empty($addedInterfaces))
{
	?>
	<h2><?=Yii::t('AutoAdmin.access', 'Following interfaces have been added')?>:</h2>
	<table border="1" cellpadding="6" style="margin-left: 5px;">
		<thead>
			<tr>
				<th>Controller</th><th>Action</th><th>Interface Alias</th>
			</tr>
		</thead>
		<tbody>
	<?
	foreach($addedInterfaces as $interfaceID=>$ca)
	{
		?><tr><td><?=$ca[0]?></td><td><?=$ca[1]?></td><td><?=$interfaceID?></td></tr><?
	}
	?>
		</tbody>
	</table>
	<?
}
else
{
	?>
<form action="<?=$actionURI?>" method="post">
<?
$checkboxes = array();
foreach($interfacesList as $interfaceID=>$ca)
{
	$checkboxes[$interfaceID] = "{$ca[0]} &mdash; {$ca[1]}".' <b>{'.$interfaceID.'}</b>';
}
?>
	<fieldset>
	<?=CHtml::checkBoxList('AAimportInterfaces', null, $checkboxes);?>
		<label style="display: block; background: #f0f0f0; margin: 2px; padding: 2px;">
			<input type="checkbox" onchange="$(this.form).find('fieldset input[type=checkbox][name^=AAimportInterfaces]').attr('checked', this.checked)"/>
			<u><b><?=Yii::t('AutoAdmin.access', 'All');?></b></u>
		</label>
	<?=CHtml::submitButton(Yii::t('AutoAdmin.access', 'Import'), array('name'=>null));?>
	</fieldset>
</form>
	<?
}
?>