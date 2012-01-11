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

	public function fetchAllInRange($start, $end)
	{
		$select = $this->getDbTable()->select();
		$select->where('day >= :start AND day <= :end')->bind(array(
			':start' => $start,
			':end'   => $end,
		));

		$resultSet = $this->getDbTable()->fetchAll($select);
		return $this->mapAll($resultSet);
	}
	
	public function fetchAllByTerm(Application_Model_Term $term)
	{
		return $this->fetchAllInRange($term->getStartDate(), $term->getEndDate());
	}
	
	public function fetchAllThisTerm()
	{
		$termMapper = new Application_Model_TermMapper();

		// Don't catch just to throw, but this is PHP
		try
		{
			$term = $termMapper->getCurrentTerm();
		}
		catch (Exception $e)
		{
			throw $e;
		}
		
		return $this->fetchAllByTerm($term);
	}
	
	public function fetchAllUnassignedThisTerm()
	{
		$termMapper = new Application_Model_TermMapper();
		
		try
		{
			$term = $termMapper->getCurrentOrNextTerm();
		}
		catch (Exception $e)
		{
			throw $e;
		}
		
		$start = $term->getStartDate();
		list($y, $m, $d) = explode('-', $start);
		$date = mktime(0, 0, 0, $m, $d, $y);
		while (date('w', $date) != 0)
		{
			$date += 60 * 60 * 24;
		}

		$start = date('Y-m-d', $date);
		$end = date('Y-m-d', $date + (60 * 60 * 24 * 6));

		$shifts = $this->fetchAllInRange($start, $end);

		$available = array();
		foreach ($shifts as $shift)
		{
			$assigned = false;
			$similar = $this->fetchAllSimilar($shift);
			foreach ($similar as $s)
			{
				if ($s->getConsultant() !== null)
				{
					$assigned = true;
					break;
				}
			}
			
			if ($assigned === false)
			{
				$available[] = $shift;
			}
		}
		
		return $available;
	}
	
	public function fetchAllByMonth($month, $year)
	{
		$select = $this->getDbTable()->select();
		$select->where('day >= :start AND day <= :end')->bind(array(
				':start' => date('Y-m-d', mktime(0, 0, 0, $month, 1, $year)),
				':end'   => date('Y-m-d', mktime(0, 0, 0, $month + 1, 0, $year)),
		));
		
		$resultSet = $this->getDbTable()->fetchAll($select);
		return $this->mapAll($resultSet);
	}
	
	public function fetchAllThisMonth()
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
		$tempMapper = new Application_Model_TempShiftMapper();
		
		$date = date('Y-m-d');
		$yesterday = date('Y-m-d', strtotime('-1 days'));
		$time = date('H:i:s');
		$select = $this->getDbTable()->select();
		$id = $consultant->getId();
		
		$select->setIntegrityCheck(false);
		$select->from(array('s' => $this->getDbTable()->getName()))
		       ->joinLeft(array('t' => $tempMapper->getDbTable()->getName()),
		       		's.id = t.shift_id',
		       		array('s.*', 't.temp_consultant_id'))
		       ->order(array('day', 'start_time'))
		       ->limit($limit);
		
		if ($showCurrent === true)
		{
			// User is either the scheduled consultant or the temp
			// The shift is either today or in the future
			// The shift has not yet ended
			$select->where(
				'(s.consultant_id = :id OR t.temp_consultant_id = :id) AND ' .
				'(' .
					// Day is in future
					's.day > :date OR ' .
					// Same day, shift is later in day
					'(' .
						's.day = :date AND ' .
						's.end_time > :time' .
					') OR ' .
					// Same day, shift ends the next day
					'(' .
						's.day = :date AND ' .
						's.start_time > s.end_time' . // Shift ends next day
					') OR' . 
					// Started yesterday, ends today, has not yet ended
					'(' .
						's.day = :yesterday AND ' .
						's.start_time > s.end_time AND ' .
						's.end_time > :time' .
					')' .
				')')->bind(array(
						':id'        => $id,
						':date'      => $date,
						':time'      => $time,
						':yesterday' => $yesterday,
				));
		}
		else
		{
			// User is either the scheduled consultant or the temp
			// The shift is either today or in the future
			// The shift has not yet begun
			$select->where(
				'(s.consultant_id = :id OR t.temp_consultant_id = :id) AND ' .
				'(' .
					// Day is in future
					's.day > :date OR ' .
					// Same day, shift is later in day
					'(' .
						's.day = :date AND ' .
						's.start_time > :time' .
					') OR ' .
				')')->bind(array(
						':id'        => $id,
						':date'      => $date,
						':time'      => $time,
				));
		}
		
		$resultSet = $this->getDbTable()->fetchAll($select);
		return $this->mapAll($resultSet);
	}

	public function fetchAllSimilar(Application_Model_Shift $shift)
	{
		// Similar means:
		// Same term
		// Same day of week
		// Same start time
		// Same end time
		// Same location

		if ($shift === null)
		{
			throw new InvalidArgumentException('Null argument provided');
		}
		
		$termMapper = new Application_Model_TermMapper();

		list($y, $m, $d) = explode('-', $shift->getDate());
		$time = mktime(0, 0, 0, $m, $d, $y);
		$term = $termMapper->getTermOf($time);

		$termStart = $term->getStartDate();
		$termEnd = $term->getEndDate();
		$wday = date('w', $time);
		$start = $shift->getStartTime();
		$end = $shift->getEndTime();
		$loc = $shift->getLocation();
		
		$select = $this->_dbTable->select();
		$select->where(
				'day >= :termStart AND ' .
				'day <= :termEnd AND ' .
				'DAYOFWEEK(day) = :wday AND ' .
				'start_time = :start AND ' .
				'end_time = :end AND ' .
				'location = :loc')->bind(array(
					':termStart' => $termStart,
					':termEnd'   => $termEnd,
					':wday'      => $wday + 1,
					':start'     => $start,
					':end'       => $end,
					':loc'       => $loc,
				));
		
		$resultSet = $this->getDbTable()->fetchAll($select);
		return $this->mapAll($resultSet);
	}
	
	private function mapAll($resultSet)
	{
		$consultants = $this->consultantMapper->fetchAll(true);
		
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
}

