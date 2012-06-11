<?php
/**
 * Controller for manipulations with files in managing frames
 */
class AAFileController extends CExtController
{
	public function actionImgupload()
	{
		$data = array();

		$this->layout = 'ext.autoAdmin.views.layouts.fileUpload';
		$data['fieldID'] = Yii::app()->request->getParam('fieldID', '');
		$data['interface'] = Yii::app()->request->getParam('interface', '');
		if(!$data['fieldID'] || !preg_match('/^i_(\d+)$/i', $data['fieldID'], $matches) || !$data['interface'])
			throw new CHttpException(406, 'Не переданы необходимые параметры');
		$fileDirKey = $matches[1];

		if(!empty($_FILES['file']))
		{
			$data['close'] = true;
			$fileDirs = Yii::app()->user->getState("fileDirs[{$data['interface']}]");
			if(!$fileDirs || !isset($fileDirs[$fileDirKey]))
				throw new CHttpException(400, 'В сессии отсутствуют настройки для сохранения файла.');

			$data['imgname'] = $this->copyImage('file', $fileDirs[$fileDirKey]);
			$data['img'] = getimagesize(Yii::app()->basePath.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.Yii::app()->modules['autoadmin']['wwwDirName'].str_replace('/', DIRECTORY_SEPARATOR, $data['imgname']));
		}
		$this->render('ext.autoAdmin.views.fileUpload', $data);
	}

	function copyImage($var, $uploadDir='/i/other')
	{
		$newFileName = '';
		$newFileName = mb_strtolower(mb_substr($_FILES[$var]['name'], 0, mb_strrpos($_FILES[$var]['name'], '.')));
		$newFileName = AAHelperText::translite($newFileName);
		$newFileName = str_replace(' ', '_', $newFileName);
		$newFileName = preg_replace('/[^a-z\-\_0-9]/ui', '', $newFileName);
		if(mb_strlen($newFileName)>60)
			$newFileName = mb_substr($newFileName, 0, 60);
		$ext = mb_strrchr($_FILES[$var]['name'], '.');
		$newFileName .= $ext;
		$fileLinkDir = $uploadDir;
		$targetPath = Yii::app()->basePath.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.Yii::app()->modules['autoadmin']['wwwDirName'].str_replace('/', DIRECTORY_SEPARATOR, $uploadDir);
		if(!is_dir($targetPath))
		{
			if(!mkdir($targetPath))
				throw new CHttpException(406, "Указанная в настройках директория [{$fileLinkDir}] не существует и не может быть создана.");
		}
		$targetPath .= DIRECTORY_SEPARATOR.$newFileName;
		if(!copy($_FILES[$var]['tmp_name'], $targetPath))
			throw new CHttpException(406, "Файл невозможно сохранить в указанной в настройках директории [{$fileLinkDir}]. Вероятнее всего, проблемы с правами.");
		if(!getimagesize($targetPath))
		{
			throw new CHttpException(406, "Загружаемый файл не является изображением допустимого формата.");
		}
		return $fileLinkDir.'/'.$newFileName;
	}

}