<?
if(!$isGuest)
{
	?>
	<div id="logout">
		Вы вошли как <b><?=$userName?></b>. [<a href="<?=$this->createUrl('aaauth/logout')?>">выйти</a>]
		<?
		if($userLevel && in_array($userLevel, array('admin', 'root')))
		{
			?>
			<br/>/<a href="<?=$this->createUrl('aaauth/users')?>">Управление пользователями панели</a>/
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