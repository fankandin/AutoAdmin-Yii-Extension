<?php
/**
 * Helper for manipulations with files
 *
 * @author Alexander Palamarchuk <a@palamarchuk.info>
 */
class AAHelperFile
{
	/**
	 * Delete the file (uploaded by AutoAdmin).
	 * @param string $fileName File name
	 * @return boolean Success of deletion
	 * @throws CException 
	 */
	public static function deleteFile($fileName)
	{
		if(!$fileName)
			return false;
		$fpath = Yii::app()->basePath.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.Yii::app()->modules['autoadmin']['wwwDirName'].str_replace('/', DIRECTORY_SEPARATOR, $fileName);
		if(file_exists($fpath))
		{
			if(@unlink($fpath))
				return true;
			else
				throw new AAException(Yii::t('AutoAdmin.common', 'Attention! The file ({fpath}) cannot be deleted', array('{fpath}'=>$fpath)));
		}
		return false;
	}
}
