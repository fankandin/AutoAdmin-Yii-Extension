<?
Yii::app()->clientScript
	->registerCssFile(AutoAdmin::$assetPath.'/css/list.css')
	->registerScriptFile(AutoAdmin::$assetPath.'/js/list.js')
	->registerCssFile(AutoAdmin::$assetPath.'/css/lightbox.css')
	->registerScriptFile(AutoAdmin::$assetPath.'/js/jquery.lightbox-0.5.min.js', CClientScript::POS_END);

if(empty($this->breadcrumbs))
	$this->breadcrumbs = array($this->pageTitle);

$urlEdit = AAHelperUrl::addParam($baseURL, 'action', 'edit');
$urlAdd = AAHelperUrl::addParam($baseURL, 'action', 'add');
$urlDelete = AAHelperUrl::addParam($baseURL, 'action', 'delete');
?>

<h1><?=$this->pageTitle?></h1>
<?
if(!empty($partialViews['up']))
	$this->renderPartial($partialViews['up'], $clientData);

if(!empty($clientData['subtitle']))
{
	?><h2><?=$clientData['subtitle']?></h2><?
}
if(!empty($clientData['subhtml']))
{
	echo $clientData['subhtml'];
}
?>
<table id="panel-up">
<tbody>
	<tr>
		<td class="nav-pages">
			<div>
				<?=Yii::t('AutoAdmin.common', 'Pages')?>: 
				<?$this->widget('AAWidgetPager', array('total'=>$total, 'maxPerPage'=>$rowsOnPage, 'currentPage'=>$currentPage))?>
			</div>
		</td>
<?
if(!empty($searchAvailable))
{
	?>
		<td>
			<?=$this->renderPartial($viewsPath.'searchPanel', array(
				'searchOptions'	=> $searchOptions,
				'fields'	=> $fields,
				'baseURL'	=> $baseURL,
			))?>
		</td>
	<?
}
if(in_array('add', $rights))
{
	?>
		<td class="panel-add">
			<a href="<?=$urlAdd?>"><?=Yii::t('AutoAdmin.common', 'Add')?></a>
		</td>
	<?
}
?>
	</tr>
</tbody>
</table>

<?
if($checkboxes)
	echo CHtml::form('./', 'post', array('id'=>'listForm', 'class'=>'list'));

$numCols = 2;
?>
<table id="data-list">
<thead>
	<tr>
		<th></th>
<?
if(!empty($urlSub))
{
		?><th>
			<?=(!empty($clientData['subHrefTitle']) ? $clientData['subHrefTitle'].'<br/>' : '')?>
			<small><?=Yii::t('AutoAdmin.common', 'Click on the icons below to go the next interface')?></small>
		</th><?
	$numCols++;
}

$urlForSort = AAHelperUrl::stripParam($baseURL, 'sortBy');
$sortDir = ($sortBy < 0 ? '-' : '');
foreach($fields as $k=>$field)
{	//Display table header
	if(!$field->showInList)
		continue;
	$class = 'data';
	if(!is_null($sortBy) && abs($sortBy)==$k+1)
	{
		$urlSort = AAHelperUrl::addParam($urlForSort, 'sortBy', -1*$sortBy);
		$class .= ' '.($sortBy <= 0 ? 'sort-desc' : 'sort-asc');
	}
	else
		$urlSort = AAHelperUrl::addParam($urlForSort, 'sortBy', $sortDir.($k+1));

	echo CHtml::tag('th', array('class'=>$class), CHtml::link($field->label, $urlSort));
	$numCols++;
}

//Additional columns through tables of links
if(!empty($_data['foreignKeysLinks']))
{
	foreach($_data['foreignKeysLinks'] as $k=>$foreignLink)
	{
		if(!empty($foreignData[$k]) && !empty($foreignLink['show']))
		{
			?>
			<th class="foreign"><?=$foreignLink['label']?></th>
			<?
			$numCols++;
		}
	}
}
?>
		<th></th>
	</tr>
</thead>
<tbody>
<?
foreach($dataRows as $rowI=>$dataRow)
{
	?>
	<tr id="tr_<?=(1)?>">
		<td class="row-number"><span><?=(($rowI+1) + $rowsOnPage*($currentPage-1))?>.</span></td>
	<?
	if(!empty($urlSub))
	{
		?>
		<td class="subtable"><?=CHtml::link('-', AAHelperUrl::addParam($urlSub, 'bk', $dataRow->pk))?></td>
		<?
	}

	foreach($dataRow as $k=>$field)
	{	//Output fields that were set for show
		if(!$field->showInList)
			continue;
		?>
		<td class="t-<?=$field->type?>">
		<?
		if(is_null($field->value))
		{
			?><span class="null">NULL</span><?
		}
		else
		{
			if(isset($searchOptions['field']) && $searchOptions['field']->name == $field->name)
			{
				$value = $field->printValue();
				if(is_array($searchOptions['query']))
				{
					foreach($searchOptions['query'] as $term)
					{
						$value = str_ireplace($term, CHtml::tag('span', array('class'=>'found'), $term), $value);
					}
				}
				else
					$value = str_ireplace($searchOptions['query'], CHtml::tag('span', array('class'=>'found'), $searchOptions['query']), $value);
				echo $value;
			}
			else
				echo $field->printValue();
		}
		if($checkboxes)
		{
			echo CHtml::checkBox('cb[]', null, array('value'=>$id));
		}
		?>
		</td>
		<?
	}
	//Additional columns through a table of links
	if(!empty($_data['foreignLinks']))
	{
		$exportKey = array();
		foreach($dataRow->pk as $pkField=>$pkValue)
			$exportKey[] = $pkValue;
		$exportKey = serialize($exportKey);

		foreach($_data['foreignLinks'] as $k=>$foreignLink)
		{
			if(empty($foreignData[$k]))
				continue;
			if(!empty($foreignLink['show']))
			{
				$foreignValue = '';
				if(!empty($foreignLink['targetFields']))
				{
					$t = '';
					if(!empty($foreignData[$k][$exportKey]))
					{
						foreach($foreignData[$k][$exportKey] as $row)
						{
							foreach($foreignLink['targetFields'] as $field)
								$t .= ($t ? '; ' : '').$row[$field];
						}
					}
					$foreignValue .= "({$t}): ";
					
				}
				if(!empty($foreignLink['linkFields']))
				{
					$t = '';
					if(!empty($foreignData[$k][$exportKey]))
					{
						foreach($foreignData[$k][$exportKey] as $row)
						{
							foreach($foreignLink['linkFields'] as $field)
								$t .= ($t ? '; ' : '').$row[$field];
						}
					}
					$foreignValue .= "<span>{$t}</span>";
				}
				?>
				<td class="foreign"><?=$foreignValue?></td>
				<?
			}
		}
	}
	?>
		<td class="control">
	<?
	$urlPK = '';
	foreach($dataRow->pk as $pkField=>$pkValue)	//To take into account composite PK
		$urlPK .= "&id[{$pkField}]=".urlencode($pkValue);
	if(in_array('edit', $rights))
		echo CHtml::link(Yii::t('AutoAdmin.common', 'Edit'), $urlEdit.$urlPK, array('class'=>'edit'));
	if(in_array('delete', $rights))
		echo CHtml::link(Yii::t('AutoAdmin.common', 'Delete'), $urlDelete.$urlPK, array('class'=>'delete'));

	if(!empty($addActions))
	{
		foreach($addActions as $v)
		{
			$href = $v['href'];
			if(!empty($v['eval']))
			{
				$href = preg_replace('/\%\$([a-z_]+)\$\%/e', 'urlencode($r->$1)', $href);
			}
			echo CHtml::link(
					(!empty($v['img']) ? CHtml::image($v['img'], '', array('title'=>$v['title'])) : $v['title']),
					$href.$id.(!empty($v['popup']) ? '&compact' : ''),
					array('class'=>($v['popup'] ? 'popup' : ''))
				);
		}
	}
	?>
		</td>
	</tr>
	<?
}
?>
</tbody>
</table>
<?
if($checkboxes)
	echo CHtml::closeTag('form');
?>
<table id="panel-down">
<tbody>
	<tr>
		<td class="nav-pages">
			<div>
				<?=Yii::t('AutoAdmin.common', 'Pages')?>: 
				<?$this->widget('AAWidgetPager', array('total'=>$total, 'maxPerPage'=>$rowsOnPage, 'currentPage'=>$currentPage))?>
			</div>
		</td>
<?
if(in_array('add', $rights))
{
	?>
		<td class="panel-add"><a href="<?=$urlAdd?>"><?=Yii::t('AutoAdmin.common', 'Add')?></a></td>
	<?
}
?>
	</tr>
</tbody>
</table>
<?
if(!empty($partialViews['down']))
	$this->renderPartial($partialViews['down'], $clientData);

if(empty($iframeMode))
	$this->renderPartial($viewsPath.'footer', array('isGuest'=>$isGuest, 'userName'=>$userName, 'userLevel'=>$userLevel));
?>