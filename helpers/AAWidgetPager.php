<?php
/**
 * Pagination for AutoAdmin
 * @author Alexander Palamarchuk <a@palamarchuk.info>
 */

class AAWidgetPager extends CWidget
{
	public $total;
	public $currentPage;
	public $maxPerPage;

	public function init()
	{
		
	}

	public function run()
	{
		$currentPageNum = 1;
		if($this->total > $this->maxPerPage)
		{	//If there`s no nessecary to paginate
			$currentPageNum = floor($this->total / $this->maxPerPage);
			if(($this->total % $this->maxPerPage) != 0)
				$currentPageNum++;	//round to greater
		}
		else
		{
			return;	//no pagination
		}

		$dec = floor(($this->currentPage-1) / 10);	//quantity of tens
		if($dec > 0)
		{
			echo CHtml::link('1', AAHelperUrl::replaceParam(Yii::app()->request->requestUri, 'page', 1)).' &#133; ';
			if($dec > 2)
			{
				$pcntr = floor($dec/2)*10;
				echo CHtml::link('1', AAHelperUrl::replaceParam(Yii::app()->request->requestUri, 'page', $pcntr)).' &hellip; ';
			}
		}

		for($i = 1; $i < 12; $i++)
		{
			$pn = ($dec * 10) + $i;
			if($pn <= $currentPageNum)
			{	//Perhaps will finish earlier
				if($i == 11)
				{	//to next ten
					echo CHtml::link(Yii::t('AutoAdmin.common', 'More'), AAHelperUrl::replaceParam(Yii::app()->request->requestUri, 'page', $pn), array('class'=>'more'));
				}
				else
				{
					if($pn != $this->currentPage)
					{	//active, unactive...
						echo CHtml::link($pn, AAHelperUrl::replaceParam(Yii::app()->request->requestUri, 'page', $pn), array('class'=>'pagenum page'.$i));
					}
					else
					{
						echo CHtml::tag('span', array('class'=>'pagenum active'), $pn);
					}
				}
			}
		}
	}
}
