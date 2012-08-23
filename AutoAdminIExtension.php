<?php
/**
 * Interface for Yii AutoAdmin extensions.
 *
 * @author Alexander Palamarchuk <a@palamarchuk.info>
 */
interface AutoAdminIExtension
{
	/**
	 * Static initialization. Called in AutoAdmin::init()
	 * Usually used for configuring of folders which are should be imported.
	 */
	public static function init();
}
