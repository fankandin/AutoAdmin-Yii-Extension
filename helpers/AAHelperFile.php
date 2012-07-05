<?php
/**
 * Helper for manipulations with files
 *
 * @author Alexander Palamarchuk <a@palamarchuk.info>
 */
class AAHelperFile
{
	/**
	 * Converts HTML-oriented path (SRC="") to a strict full file path. Also converts directory separators.
	 * @param string $src HTML-oriented path (SRC="").
	 * @return string Full file path.
	 */
	public static function srcToPath($src)
	{
		return Yii::app()->basePath.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.Yii::app()->modules['autoadmin']['wwwDirName'].str_replace('/', DIRECTORY_SEPARATOR, $src);
	}

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
		$fpath = self::srcToPath($fileName);
		
		if(file_exists($fpath))
		{
			if(@unlink($fpath))
				return true;
			else
				throw new AAException(Yii::t('AutoAdmin.common', 'Attention! The file ({fpath}) cannot be deleted', array('{fpath}'=>$fpath)));
		}
		return false;
	}

	/**
	 * Copy an image uploaded with HTML form to the specified directory.
	 * In view scripts one should use something like <img src="{$fileBaseDir}/{@return}"/>
	 *
	 * @param string $paramName Parameter name as it passed by a form.
	 * @param string $fileBaseDir A base directory constant for the file (should be used in view scripts as prefix before $filePath from DB table).
	 * @return string New file's name.
	 * @throws CException 
	 */
	public static function uploadFile($paramName, $fileBaseDir)
	{
		if(empty($_FILES[AutoAdmin::INPUT_PREFIX]['tmp_name'][$paramName]) || !empty($_FILES[AutoAdmin::INPUT_PREFIX]['error'][$paramName]))
			throw new AAException(Yii::t('AutoAdmin.errors', 'An error occured with uploading of the file for field "{field}"', array('{field}'=>$paramName)));
		$uploadedFileName =& $_FILES[AutoAdmin::INPUT_PREFIX]['name'][$paramName];

		$newfname = '';
		$toDir = self::srcToPath($fileBaseDir);
		$newfname = mb_strtolower(mb_substr($uploadedFileName, 0, mb_strrpos($uploadedFileName, '.')));
		$newfname = AAHelperText::translite($newfname);
		$newfname = str_replace(' ', '_', $newfname);
		$newfname = preg_replace('/[^a-z\-\_0-9]/ui', '', $newfname);
		if(mb_strlen($newfname)>60)
			$newfname = mb_substr($newfname, 0, 60);
		$ext = mb_substr(mb_strrchr($uploadedFileName, '.'), 1);
		if(!is_dir($toDir))
		{
			if(!mkdir($toDir, 0777, true))
				throw new AAException(Yii::t('AutoAdmin.errors', 'The directory "{dirname}" cannot be created', array('{dirname}'=>$toDir)));
		}
		while(file_exists($toDir.DIRECTORY_SEPARATOR.$newfname.'.'.$ext))
			$newfname .= '_'.rand(0, 9);
		$newfname .= ".{$ext}";
		if(!copy($_FILES[AutoAdmin::INPUT_PREFIX]['tmp_name'][$paramName], $toDir.DIRECTORY_SEPARATOR.$newfname))
			throw new AAException(Yii::t('AutoAdmin.errors', 'The file ({filename}) cannot be copied', array('{filename}'=>$newfname)));
		return $newfname;
	}
}
