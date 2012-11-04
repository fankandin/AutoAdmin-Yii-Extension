<?php
/**
 * Login panel for AutoAdmin
 * @author Alexander Palamarchuk <a@palamarchuk.info>
 */

final class AAWidgetLoginpanel extends CWidget
{
	public $userName;
	public $userLevel;
	public $controller;

	public function init()
	{
		$this->controller = Yii::app()->controller;
		$this->userName = (!Yii::app()->user->isGuest ? Yii::app()->user->getState('firstname').' '.Yii::app()->user->getState('surname') : '');
		$this->userLevel = (!Yii::app()->user->isGuest ? Yii::app()->user->level : 0);
	}

	public function run()
	{
		if(!Yii::app()->user->isGuest)
		{
			?>
			<div id="logout">
				<?php echo Yii::t('AutoAdmin.access', 'You\'ve entered as <b>{userName}</b>', array('{userName}'=>$this->userName))?>. <span id="logout-link">[<a href="<?php echo $this->controller->createUrl('aaauth/logout')?>"><?php echo Yii::t('AutoAdmin.access', 'logout')?></a>]</span>
				<?php
				if($this->userLevel && in_array($this->userLevel, array('admin', 'root')))
				{
					?>
					<br/><span id="access-link"><a href="<?php echo $this->controller->createUrl('aaauth/users')?>"><?php echo Yii::t('AutoAdmin.access', 'Panel\'s users managing')?></a></span>
					<br/><span id="generator-link"><a href="<?php echo $this->controller->createUrl('aagenerator/index')?>"><?php echo Yii::t('AutoAdmin.generator', 'AutoAdmin Generator')?></a></span>
					<?php
				}
				?>
			</div>
			<?php
		}
		/**
		 * Please remember this software is under New BSD License.
		 * If you need to change or hide disclaimers for commercial use please contact <a@palamarchuk.info>
		 */
		?>
		<div id="copyright">
			&copy; 2003-<?php echo date('Y');?> AutoAdmin CMS Framework
			<br/>authored by <a target="_blank" href="http://www.palamarchuk.info/">Alexander Palamarchuk</a>
		</div>
		<?php
	}
}
