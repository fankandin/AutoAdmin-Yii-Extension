<?php
if(!$isGuest)
{
	?>
	<div id="logout">
		<?php echo Yii::t('AutoAdmin.access', 'You\'ve been entered as <b>{userName}</b>', array('{userName}'=>$userName))?>. [<a href="<?php echo $this->createUrl('aaauth/logout')?>"><?php echo Yii::t('AutoAdmin.access', 'logout')?></a>]
		<?php
		if($userLevel && in_array($userLevel, array('admin', 'root')))
		{
			?>
			<br/>/<a href="<?php echo $this->createUrl('aaauth/users')?>"><?php echo Yii::t('AutoAdmin.access', 'Panel\'s users managing')?></a>/
			<?php
		}
		?>
	</div>
	<?php
}
?>
<div style="text-align: right; clear: both; margin-top: 15px; padding-right: 5px; font-size: 90%; font-style: italic;">
	&copy; 2003-2012
	<a target="_blank" href="http://www.palamarchuk.info/">Alexander Palamarchuk</a>
</div>