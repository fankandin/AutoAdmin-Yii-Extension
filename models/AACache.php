<?php
/**
 * Class for manipulation with the site's global cache
 *
 * @property array $tags Tags for reseting the cache.
 * 
 * @author Alexander Palamarchuk <a@palamarchuk.info>
 */
class AACache
{
	private $_tags = array();

	/**
	 * Adds the tag(-s) to internal array of tags that must be reset after affecting a DB table.
	 * @param string|array $tags Tags (can be a string or an array).
	 * @return boolean whether the adding was successful.
	 */
	public function setTag($tags)
	{
		if(is_string($tags))
		{
			if(!in_array($tags, $this->_tags))
				$this->_tags[] = $tags;
		}
		elseif(is_array($tags))
		{
			foreach($tags as $tag)
				if(!in_array($tag, $this->_tags))
					$this->_tags[] = $tag;
		}
		else
			return false;
			
		return true;
	}

	/**
	 * Updates dependency (all or by a passed tag) in cache.
	 * @param string $tag The only tag (from internally available ones) whose dependency must be updated.
	 * @return boolean whether the updating was successful.
	 */
	public function updateDependency($tag=null)
	{
		if(!$tag && in_array($tag, $this->_tags))
			Yii::app()->setGlobalState($tag, time());
		elseif($tag)
		{
			foreach($this->_tags as $tag)
				Yii::app()->setGlobalState($tag, time());
		}
		else
			return false;
		return true;
	}
}
