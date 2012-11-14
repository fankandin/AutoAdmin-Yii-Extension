<?php
Yii::app()->clientScript
	->registerCssFile(AutoAdmin::$assetPath.'/css/edit.css')
	->registerScriptFile(AutoAdmin::$assetPath.'/js/edit.js');

$url = AAHelperUrl::replaceParam($baseURL, 'action', ($manageAction == 'edit' ? 'update' : 'insert'));

if(empty($this->breadcrumbs) && !$iframeMode)
	$this->breadcrumbs[$this->pageTitle] = AAHelperUrl::stripParam($url, array('action', 'id'));
else
{
	$key = array_search($this->pageTitle, $this->breadcrumbs);
	if(is_numeric($key))
	{
		unset($this->breadcrumbs[$key]);
		$this->breadcrumbs[$this->pageTitle] = AAHelperUrl::stripParam($url, array('action', 'id'));
	}
}
?>
<h1><?php echo $this->pageTitle?></h1>
<?php
if(!empty($partialViews['up']))
	$this->renderPartial($partialViews['up'], $clientData);

if(!empty($clientData['subtitle']))
{
	?><h2><?php echo $clientData['subtitle']?></h2><?php
}
if(!empty($clientData['subhtml']))
{
	echo $clientData['subhtml'];
}

if($manageAction == 'edit')
{	//Display subheader within information about data unit (which is beeing edited now)
	$h2MaxParts = 2;
	$h2 = '';
	$i = 0;
	foreach($fields as $field)
	{
		if($field->isReadonly && $field->type=='string' && $field->showInList)
		{
			$h2 .= ($h2 ? '. ' : '').$field->value;
			if(++$i >= $h2MaxParts)
				break;
		}
	}
	if($h2)
	{
		?><h2><?php echo $h2?></h2><?php
		$this->pageTitle = $h2;
	}
}
if(!empty($clientData['subtitle']))
{
	?><h2><?php echo $clientData['subtitle']?></h2><?php
}

echo CHtml::form($url, 'post', array('id'=>'editform', 'enctype'=>'multipart/form-data', 'autocomplete'=>'off'));
echo CHtml::hiddenField('interface', $interface);
$tabindex = 1;
$commonTagOptions = array('tabindex'=>&$tabindex);

if(!empty($formError))
{
	?>
	<p class="error"><?php echo Yii::t('AutoAdmin.form', 'Error:').' '.$formError['message']?></p>
	<?php
}

foreach($fields as $field)
{
	$itemContent = $field->formInput($this, $commonTagOptions);
	if($field->description)
		$itemContent .= CHtml::tag('div', array(), $field->description);
	$itemClass = 'item block_'.$field->type;
	if($field->allowNull)
		$itemClass .= ' nullf';
	if(!empty($formError) && $formError['field']->name == $field->name)
		$itemClass .= ' error';

	echo CHtml::tag('div', array('class'=>$itemClass), $itemContent);

	$tabindex++;
}

if(!empty($iframes))
{
	if($manageAction == 'add')
	{
		?>
		<div class="item">
			<div class="iframe-na"><?php echo Yii::t('AutoAdmin.form', 'Submit the form in order to be able to edit additional links')?>.</div>
		</div>
		<?php
	}
	else
	{
		$bkp = $bindKeysParent;
		array_push($bkp, $bindKeys);
		foreach($iframes as $iframe)
		{
			?>
			<div class="item">
				<?php
				echo CHtml::tag('iframe', array(
					'src' => AAHelperUrl::update(Yii::app()->request->requestUri, 
						array('id', 'action'),
						array(
								'foreign'	=> $iframe['action'],
								'bkp'		=> $bkp,
								'bk'		=> $fields->pk,
							)
					)), null, true);
				if(!empty($iframe['foreign']->description))
				{
					?><div class="desc"><?php echo $iframe['foreign']->description?></div><?
				}
				?>
			</div>
			<?php
		}
	}
}
echo CHtml::submitButton(Yii::t('AutoAdmin.common', 'Save'), array('name'=>null));

echo CHtml::closeTag('form');
if(!empty($partialViews['down']))
	$this->renderPartial($partialViews['down'], $clientData);
if(!$iframeMode)
	$this->widget('AAWidgetLoginpanel', array());
?>