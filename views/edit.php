<?php
Yii::app()->clientScript
	->registerCssFile(AutoAdmin::$assetPath.'/css/edit.css')
	->registerScriptFile(AutoAdmin::$assetPath.'/js/edit.js');

$url = AAHelperUrl::replaceParam($baseURL, 'action', ($manageAction == 'edit' ? 'update' : 'insert'));

if(empty($this->breadcrumbs))
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
$itemsI = 0;
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
	$class = 'item block_'.$field->type;
	if($itemsI%4 < 2)
		$class .= ' m';
	if($field->allowNull)
		$class .= ' nullf';
	if(!empty($formError) && $formError['field']->name == $field->name)
		$class .= ' error';
	?>
	<div class="<?php echo $class?>">
	<?php
		echo $field->formInput($this, $commonTagOptions);
		if($field->description)
		{
			?><div class="desc"><?php echo $field->description?></div><?
		}
	?>
	</div>
	<?php
	if(!(++$itemsI%2))
	{
		?><br clear="all"/><?php
	}
	$tabindex++;
}
if(!empty($iframes))
{
	if($manageAction == 'add')
	{
		?><div class="item"><div class="iframe-na"><i><?php echo Yii::t('AutoAdmin.form', 'Submit the form in order to be able to edit additional links')?>.</i></div></div><?php
	}
	else
	{
		$bkp = $bindKeysParent;
		array_push($bkp, $bindKeys);
		foreach($iframes as $iframe)
		{
			$iframeUrl = ($this->action->id=='index' ? './' : '../')."foreign-{$iframe['action']}/";
			$iframeUrl = AAHelperUrl::update($iframeUrl, null, array(
					'bkp'		=> $bkp,
					'bk'		=> $fields->pk,
					'foreign'	=> AAHelperUrl::encodeParam($iframe['foreign']),
				));
			?>
			<div class="item<?php echo (!empty($iframe['wide']) || in_array('wide', $iframe) ? ' wide' : '')?>">
			<?php
			echo CHtml::tag('iframe', array(
					'src'	=> $iframeUrl,
				),
				null, true);
			if($field->description)
			{
				?><div class="desc"><?php echo $field->description?></div><?
			}
			?>
			</div>
			<?php
		}
	}
}
?>
<div class="br">&nbsp;</div>
<?php
echo CHtml::submitButton(Yii::t('AutoAdmin.common', 'Save'), array('name'=>null));

echo CHtml::closeTag('form');
if(!empty($partialViews['down']))
	$this->renderPartial($partialViews['down'], $clientData);
if(empty($iframeMode))
	$this->renderPartial($viewsPath.'footer', array('isGuest'=>$isGuest, 'userName'=>$userName, 'userLevel'=>$userLevel));
?>