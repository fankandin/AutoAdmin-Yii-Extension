<?
$this->breadcrumbs = array(
	$this->pageTitle,
);
?>

<div class="narrow-content">

<h1><?=Yii::t('AutoAdmin.common', 'Delete record')?></h1>
<p class="msg"><b><?=Yii::t('AutoAdmin.common', 'Attention!')?></b><br/><?=Yii::t('AutoAdmin.messages', 'The record will be deleted')?></p>
<br/>[ <a href="<?=$confirmUrl?>"><?=Yii::t('AutoAdmin.common', 'Confirm')?></a> ]
&nbsp;&nbsp;[ <a href="<?=$cancelUrl?>"><?=Yii::t('AutoAdmin.common', 'Cancel')?></a> ]<br/>

</div>