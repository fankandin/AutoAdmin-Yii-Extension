<?
if(!$isGuest)
{
	?>
	<div id="logout">
		<?=Yii::t('AutoAdmin.access', 'You\'ve been entered as <b>{userName}</b>', array('{userName}'=>$userName))?>. [<a href="<?=$this->createUrl('aaauth/logout')?>"><?=Yii::t('AutoAdmin.access', 'logout')?></a>]
		<?
		if($userLevel && in_array($userLevel, array('admin', 'root')))
		{
			?>
			<br/>/<a href="<?=$this->createUrl('aaauth/users')?>"><?=Yii::t('AutoAdmin.access', 'Panel\'s users managing')?></a>/
			<?
		}
		?>
	</div>
	<?
}
?>
<div style="text-align: right; clear: both; margin-top: 15px; padding-right: 5px; font-size: 90%; font-style: italic;">
	&copy; 2003-2012
	<a target="_blank" href="http://www.palamarchuk.info/">Alexander Palamarchuk</a>
</div>