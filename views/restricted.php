<?
$this->pageTitle = Yii::t('AutoAdmin.access', 'Access denied');
Yii::app()->clientScript
	->registerCssFile(AutoAdmin::$assetPath.'/css/edit-result.css');

$this->breadcrumbs = array(
	$this->pageTitle,
);
?>

<h1><?=$this->pageTitle?></h1>

<?
switch($manageAction)
{
	case 'read':
		$message = Yii::t('AutoAdmin.access', 'You don\'t have permissions to read the data here');
		break;
	case 'add':
		$message = Yii::t('AutoAdmin.access', 'You don\'t have permissions to add data here here');
		break;
	case 'delete':
		$message = Yii::t('AutoAdmin.access', 'You don\'t have permissions to delete the data here');
		break;
	default:	//Default error is as for edit mode
		$message = Yii::t('AutoAdmin.access', 'You don\'t have permissions to edit the data here');
		break;
}
?>
<p class="msg"><?=$message?></p>
<p>[<a href="#" onclick="window.history.go(-1)"><?=Yii::t('AutoAdmin.common', 'Go back')?></a>]</p>