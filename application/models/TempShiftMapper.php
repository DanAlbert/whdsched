<?php

class Application_Model_TempShiftMapper
{
	protected $_dbTable;
	protected $_sequence = true; // Primary key autoincrements
	
	protected $shiftMapper;
	protected $consultantMapper;
	
	public function __construct()
	{
		$this->shiftMapper = new Application_Model_ShiftMapper();
		$this->consultantMapper = new Application_Model_ConsultantMapper();
	}
	
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
	
	// You were here before you had to dig Andy out from under a heaping pile of OS X server
	// TODO: Replace temp consultant id, shift id, assigned consultant id with the actual models
	public function save(Application_Model_TempShift $tempShift)
	{
		if ($tempShift->getTempConsultant() !== null)
		{
			$tempConsultantId = $tempShift->getTempConsultant()->getId();
		}
		else
		{
			$tempConsultantId = null;
		}
		
		if ($tempShift->getAssignedConsultant() !== null)
		{
			$assignedId = $tempShift->getAssignedConsultant()->getId();
		}
		else
		{
			$assignedId = null;
		}
		
		$data = array(
			'shift_id'           => $tempShift->getShift()->getId(),
			'temp_consultant_id' => $tempConsultantId,
			'post_time'          => $tempShift->getPostTime(),
			'response_time'      => $tempShift->getResponseTime(),
			'assigned_to'        => $assignedId,
			'timeout'            => $tempShift->getTimeout(),
		);
		
		$id = $tempShift->getId();
		if ($id == null)
		{
			unset($data['id']);
			$tempShift->setId($this->getDbTable()->insert($data));
			return $tempShift->getId();
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
		$tempShift->setShift($this->shiftMapper->find($row->shift_id));
		$tempShift->setTempConsultant($this->consultantMapper->find($row->temp_consultant_id));
		$tempShift->setPostTime($row->post_time);
		$tempShift->setResponseTime($row->response_time);
		$tempShift->setAssignedConsultant($this->consultantMapper->find($row->assigned_to));
		$tempShift->setTimeout($row->timeout);
		
		return $tempShift;
	}
	
	public function findByShift(Application_Model_Shift $shift)
	{
		$result = $this->getDbTable()->fetchAll(array('shift_id = ?' => $shift->getId()));
		if (count($result) == 0)
		{
			return null;
		}
		
		$row = $result->current();
		
		$tempShift = new Application_Model_TempShift();
		$tempShift->setId($row->id);
		$tempShift->setShift($this->shiftMapper->find($row->shift_id));
		$tempShift->setTempConsultant($this->consultantMapper->find($row->temp_consultant_id));
		$tempShift->setPostTime($row->post_time);
		$tempShift->setResponseTime($row->response_time);
		$tempShift->setAssignedConsultant($this->consultantMapper->find($row->assigned_to));
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
			$tempShift->setShift($this->shiftMapper->find($row->shift_id));
			$tempShift->setTempConsultant($this->consultantMapper->find($row->temp_consultant_id));
			$tempShift->setPostTime($row->post_time);
			$tempShift->setResponseTime($row->response_time);
			$tempShift->setAssignedConsultant($this->consultantMapper->find($row->assigned_to));
			$tempShift->setTimeout($row->timeout);
			
			$tempShifts[] = $tempShift;
		}
		
		return $tempShifts;
	}
	
	public function fetchAvailable()
	{
		$resultSet = $this->getDbTable()->fetchAll();
		$tempShifts = array();
		
		foreach ($resultSet as $row)
		{
			if ($row->temp_consultant_id === null)
			{
				$shift = $this->shiftMapper->find($row->shift_id);
				if ($shift === null)
				{
					throw new Exception("Temp shift matches no shift: {$row->id}");
				}
				
				list($y, $mo, $d) = explode('-', $shift->getDate());
				list($h, $mi, $s) = explode(':', $shift->getStartTime());
				if (mktime($h, $mi, $s, $mo, $d, $y) > time())
				{
					$tempShift = new Application_Model_TempShift();
					$tempShift->setId($row->id);
					$tempShift->setShift($shift);
					$tempShift->setTempConsultant($this->consultantMapper->find($row->temp_consultant_id));
					$tempShift->setPostTime($row->post_time);
					$tempShift->setResponseTime($row->response_time);
					$tempShift->setAssignedConsultant($this->consultantMapper->find($row->assigned_to));
					$tempShift->setTimeout($row->timeout);

					$tempShifts[] = $tempShift;
				}
			}
		}
		
		return $tempShifts;
	}
}

