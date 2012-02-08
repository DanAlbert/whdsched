<?php

class Application_Model_LogMapper
{
	protected $_dbTable;
	
	public function setDbTable($dbTable)
	{
		// If the DbTable class name was passed as a string
		if (is_string($dbTable))
		{
			// Instantiate the class specified by the string
			$dbTable = new $dbTable();
		}
		
		// $dbTable should now be an instance of table abstract
		if (!$dbTable instanceof Zend_Db_Table_Abstract)
		{
			throw new Exception('Invalid table data gateway provided');
		}
		
		$this->_dbTable = $dbTable;
		
		return $this;
	}
	
	public function getDbTable()
	{
		if ($this->_dbTable === null)
		{
			$this->setDbTable('Application_Model_DbTable_Logs');
		}
		
		return $this->_dbTable;
	}
	
	public function save(Application_Model_Log $log)
	{
		$data = array(
			'log_time'   => $log->getLog(),
			'type'       => $log->getType(),
			'message'    => $log->getEndDate(),
		);
		
		$id = $log->getId();
		if ($id == null)
		{
			unset($data['id']);
			$log->setId($this->getDbTable()->insert($data));
			return $log->getId();
		}
		else
		{
			return $this->getDbTable()->update($data, array('id = ?' => $id));
		}
		
		return true;
	}
	
	public function delete(Application_Model_Log $log)
	{
		$this->getDbTable()->delete(array('id = ?' => $log->getId()));
	}
	
	public function find($id)
	{
		$result = $this->getDbTable()->find($id);
		if (count($result) == 0)
		{
			return null;
		}
		
		$row = $result->current();
		
		$log = new Application_Model_Log();
		$log->setId($id);
		$log->setTime($row->log_time);
		$log->setType($row->type);
		$log->setMessage($row->message);
		
		return $log;
	}
	
	public function fetchAll()
	{
		$resultSet = $this->getDbTable()->fetchAll();
		$logs = array();
		
		foreach ($resultSet as $row)
		{
			$log = new Application_Model_Log();
			$log->setId($row->id);
			$log->setTime($row->log_time);
			$log->setType($row->type);
			$log->setMessage($row->message);
			
			$logs[] = $log;
		}
		
		return $logs;
	}
}

