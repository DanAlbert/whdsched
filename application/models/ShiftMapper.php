<?php

class Application_Model_ShiftMapper
{
	protected $_dbTable;
	protected $_sequence = true; // Primary key autoincrements
	
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
			$this->setDbTable('Application_Model_DbTable_Shifts');
		}
		
		return $this->_dbTable;
	}
	
	public function save(Application_Model_Shift $shift)
	{
		$data = array(
			'start_time'    => $shift->getStartTime(),
			'end_time'      => $shift->getEndTime(),
			'location'      => $shift->getLocation(),
			'day'           => $shift->getDate(),
			'consultant_id' => $shift->getConsultantId(),
		);
		
		$id = $shift->getId();
		if ($id == null)
		{
			unset($data['id']);
			return $this->getDbTable()->insert($data);
		}
		else
		{
			return $this->getDbTable()->update($data, array('id = ?' => $id));
		}
		
		return true;
	}
	
	public function delete(Application_Model_Shift $shift)
	{
		$this->getDbTable()->delete(array('id = ?' => $shift->getId()));
	}
	
	public function find($id)
	{
		$result = $this->getDbTable()->find($id);
		if (count($result) == 0)
		{
			return null;
		}
		
		$row = $result->current();
		
		$shift = new Application_Model_Shift();
		$shift->setId($id);
		$shift->setStartTime($row->start_time);
		$shift->setEndTime($row->end_time);
		$shift->setLocation($row->location);
		$shift->setDate($row->day);
		$shift->setConsultantId($row->consultant_id);
		
		return $shift;
	}
	
	public function fetchAll()
	{
		$resultSet = $this->getDbTable()->fetchAll();
		$shifts = array();
		
		foreach ($resultSet as $row)
		{
			$shift = new Application_Model_Shift();
			$shift->setId($row->id);
			$shift->setStartTime($row->start_time);
			$shift->setEndTime($row->end_time);
			$shift->setLocation($row->location);
			$shift->setDate($row->day);
			$shift->setConsultantId($row->consultant_id);
			
			$shifts[] = $shift;
		}
		
		return $shifts;
	}
}

