<?php
Yii::app()->clientScript
	->registerCssFile(AutoAdmin::$assetPath.'/css/generator.css');
?>
<h1><?php echo Yii::t('AutoAdmin.generator', 'AutoAdmin Generator')?></h1>
<h2><?php echo $this->pageTitle?></h2>
<table id="table-select">
	<thead>
		<tr>
			<th><?php echo Yii::t('AutoAdmin.generator', 'SQL table name')?></th>
			<th><?php echo Yii::t('AutoAdmin.generator', 'Columns')?></th>
		</tr>
	</thead>
	<tbody>
<?php
if(!empty($tables))
{
	$url = $this->createUrl('aagenerator/table');
	foreach($tables as $tableName=>$table)
	{
		?>
		<tr>
			<td>
				<a href="<?php echo AAHelperUrl::addParam($url, 'table', $tableName);?>"><?php echo $tableName?></a></td>
			<td>
				<?php
				if(!empty($table->columns))
					echo implode(', ', array_slice(array_keys($table->columns), 0, 5));
				if(count($table->columns) > 5)
					echo ', &hellip;';
				?>
			</td>
		</tr>
		<?php
	}
}
?>
	</tbody>
</table>
