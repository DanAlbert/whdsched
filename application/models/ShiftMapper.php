<?php

class Application_Model_ShiftMapper
{
	protected $_dbTable;
	protected $_sequence = true; // Primary key autoincrements
	
	protected $consultantMapper;
	
	public function __construct()
	{
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
			$this->setDbTable('Application_Model_DbTable_Shifts');
		}
		
		return $this->_dbTable;
	}
	
	public function save(Application_Model_Shift $shift)
	{
		if ($shift->getConsultant() !== null)
		{
			$consultantId = $shift->getConsultant()->getId();
		}
		else
		{
			$consultantId = null;
		}
		
		$data = array(
			'start_time'	=> $shift->getStartTime(),
			'end_time'	  => $shift->getEndTime(),
			'location'	  => $shift->getLocation(),
			'day'		   => $shift->getDate(),
			'consultant_id' => $consultantId,
		);
		
		$id = $shift->getId();
		if ($id == null)
		{
			unset($data['id']);
			$shift->setId($this->getDbTable()->insert($data));
			return $shift->getId();
			
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
		return $this->map($row);
	}
	
	public function fetchAll()
	{
		$resultSet = $this->getDbTable()->fetchAll();
		$consultants = $this->consultantMapper->fetchAll();
		return $this->mapAll($resultSet);
	}
	
	public function fetchAllByDate($timestamp)
	{
		$dateString = date('Y-m-d', $timestamp);
		
		$resultSet = $this->getDbTable()->fetchAll(
				array('day = ?' => $dateString),
				array('start_time', 'location'));
		return $this->mapAll($resultSet);
	}
	
	public function fetchAllByTerm(Application_Model_Term $term)
	{
		$select = $this->getDbTable()->select();
		$select->where('day >= :start AND day <= :end')->bind(array(
			':start' => $term->getStartDate(),
			':end'   => $term->getEndDate(),
		));
		
		$resultSet = $this->getDbTable()->fetchAll($select);
		return $this->mapAll($resultSet);
	}
	
	public function fetchAllThisTerm()
	{
		// Don't catch just to throw, but this is PHP
		try
		{
			$term = $this->getCurrentTerm();
		}
		catch (Exception $e)
		{
			throw $e;
		}
		
		return $this->fetchAllByTerm($term);
	}
	
	public function fetchAllByMonth($month)
	{
		$select = $this->getDbTable()->select();
		$select->where('day >= :start AND day <= :end')->bind(array(
				':start' => date('Y-m-d', mktime(0, 0, 0, $month, 1, date('Y'))),
				':end'   => date('Y-m-d', mktime(0, 0, 0, $month + 1, 0, date('Y'))),
		));
		
		$resultSet = $this->getDbTable()->fetchAll($select);
		return $this->mapAll($resultSet);
	}
	
	public function fetchAlLThisMonth()
	{
		return $this->fetchAllByMonth(date('m'));
	}
	
	public function fetchUpcomingShifts($showCurrent = false, $limit = null)
	{
		$date = date('Y-m-d');
		$time = date('H:i:s');
		$select = $this->getDbTable()->select();
		
		$select->order(array('date', 'start_time'));
		$select->limit($limit);
		
		if ($showCurrent === true)
		{
			// The shift is either today or in the future
			// The shift has not yet ended
			$select->where('day >= :date AND end_time > :time')->bind(array(
					':date' => $date,
					':time' => $time,
			));
		}
		else
		{
			// The shift is either today or in the future
			// The shift has not yet begun
			$select->where('day >= :date AND start_time > :time')->bind(array(
					':date' => $date,
					':time' => $time,
			));
		}
		
		$resultSet = $this->getDbTable()->fetchAll($select);
		return $this->mapAll($resultSet);
	}
	
	public function fetchUpcomingShiftsByConsultant(
			Application_Model_Consultant $consultant,
			$showCurrent = false,
			$limit = null)
	{
		$date = date('Y-m-d');
		$time = date('H:i:s');
		$select = $this->getDbTable()->select();
		$id = $consultant->getId();
		
		$select->setIntegrityCheck(false);
		$select->from(array('s' => 'shifts'))
		       ->joinLeft(array('t' => 'temp_shifts'),
		       		's.id = t.shift_id',
		       		array('s.*', 't.temp_consultant_id'))
		       ->order(array('day', 'start_time'))
		       ->limit($limit);
			
		if ($showCurrent === true)
		{
			// User is either the scheduled consultant or the temp
			// The shift is either today or in the future
			// The shift has not yet ended
			$select->where('(s.consultant_id = :id OR t.temp_consultant_id = :id) AND ' .
					's.day >= :date AND ' .
					's.end_time > :time')->bind(array(
							':id'   => $id,
							':date' => $date,
							':time' => $time,
					));
		}
		else
		{
			// User is either the scheduled consultant or the temp
			// The shift is either today or in the future
			// The shift has not yet begun
			$select->where('(s.consultant_id = :id OR t.temp_consultant_id = :id) AND ' .
					's.day >= :date AND ' .
					's.start_time > :time')->bind(array(
							':id'   => $id,
							':date' => $date,
							':time' => $time,
					));
		}
		
		$resultSet = $this->getDbTable()->fetchAll($select);
		return $this->mapAll($resultSet);
	}
	
	private function mapAll($resultSet)
	{
		$consultants = $this->consultantMapper->fetchAll();
		
		$shifts = array();
		foreach ($resultSet as $row)
		{
			$shift = $this->map($row, $consultants);
			$shifts[$shift->getId()] = $shift;
		}
		
		return $shifts;
	}
	
	private function map($row, array $consultants = null)
	{
		$shift = new Application_Model_Shift();
		$shift->setId($row->id);
		$shift->setStartTime($row->start_time);
		$shift->setEndTime($row->end_time);
		$shift->setLocation($row->location);
		$shift->setDate($row->day);
		
		if ($consultants !== null)
		{
			if (array_key_exists($row->consultant_id, $consultants))
			{
				$shift->setConsultant($consultants[$row->consultant_id]);
			}
			else
			{
				$shift->setConsultant(null);
			}
		}
		else
		{
			$shift->setConsultant(
					$this->consultantMapper->find($row->consultant_id));
		}
		
		return $shift;
	}
	
	private function getCurrentTerm()
	{
		$termMapper = new Application_Model_TermMapper();
		$terms = $termMapper->fetchAllByYear(date('Y'));
		$time = time();
	
		foreach ($terms as $term)
		{
			list($y, $m, $d) = explode('-', $term->getStartDate());
			$start = mktime(0, 0, 0, $m, $d, $y);
	
			list($y, $m, $d) = explode('-', $term->getEndDate());
			$end = mktime(0, 0, 0, $m, $d, $y);
	
			if (($time < $end) and ($time > $start))
			{
				return $term;
			}
		}
	
		throw new Exception('No term exists');
	}
}

