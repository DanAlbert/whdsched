<?php

class Application_Model_TempShiftMapper
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
			$this->setDbTable('Application_Model_DbTable_TempShifts');
		}
		
		return $this->_dbTable;
	}
	
	public function save(Application_Model_TempShift $tempShift)
	{
		$data = array(
			'shift_id'           => $tempShift->getShiftId(),
			'temp_consultant_id' => $tempShift->getTempConsultantId(),
			'post_time'          => $tempShift->getPostTime(),
			'response_time'      => $tempShift->getResponseTime(),
			'hours'              => $tempShift->getHours(),
			'assigned_to'        => $tempShift->getAssignedConsultant(),
			'timeout'            => $tempShift->getTimeout(),
		);
		
		$id = $tempShift->getId();
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
	
	public function delete(Application_Model_TempShift $tempShift)
	{
		$this->getDbTable()->delete(array('id = ?' => $tempShift->getId()));
	}
	
	public function find($id)
	{
		$result = $this->getDbTable()->find($id);
		if (count($result) == 0)
		{
			return null;
		}
		
		$row = $result->current();
		
		$tempShift = new Application_Model_TempShift();
		$tempShift->setId($id);
		$tempShift->setShiftId($row->shift_id);
		$tempShift->setTempConsultant($row->temp_consultant_id);
		$tempShift->setPostTime($row->post_time);
		$tempShift->setResponseTime($row->response_time);
		$tempShift->setHours($row->hours);
		$tempShift->setAssignedConsultant($row->assigned_to);
		$tempShift->setTimeout($row->timeout);
		
		return $tempShift;
	}
	
	public function fetchAll()
	{
		$resultSet = $this->getDbTable()->fetchAll();
		$tempShifts = array();
		
		foreach ($resultSet as $row)
		{
			$tempShift = new Application_Model_TempShift();
			$tempShift->setId($row->id);
			$tempShift->setShiftId($row->shift_id);
			$tempShift->setTempConsultant($row->temp_consultant_id);
			$tempShift->setPostTime($row->post_time);
			$tempShift->setResponseTime($row->response_time);
			$tempShift->setHours($row->hours);
			$tempShift->setAssignedConsultant($row->assigned_to);
			$tempShift->setTimeout($row->timeout);
			
			$tempShifts[] = $tempShift;
		}
		
		return $tempShifts;
	}
}

