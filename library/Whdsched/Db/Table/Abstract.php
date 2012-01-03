<?php

class Whdsched_Db_Table_Abstract extends Zend_Db_Table_Abstract
{
	public function getName()
	{
		return $this->_name;
	}
	
	protected function _setupTableName()
	{
		parent::_setupTableName();
		
		$config = Zend_Registry::get('config');
		$prefix = $config['resources']['db']['params']['prefix'];
		$this->_name = $prefix . $this->_name;
	}
}
