<?php

class Application_Model_MeetingMapper
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
			$this->setDbTable('Application_Model_DbTable_Meetings');
		}
		
		return $this->_dbTable;
	}
	
	public function save(Application_Model_Meeting $meeting)
	{
		$data = array(
			'id'         => $meeting->getId(),
			'day'        => $meeting->getDay(),
			'start_time' => $meeting->getStartTime(),
			'end_time'   => $meeting->getEndTime(),
			'location'   => $meeting->getLocation(),
			'term_id'    => $meeting->getTerm()->getId(),
		);
		
		$id = $meeting->getId();
		if ($id == null)
		{
			unset($data['id']);
			$meeting->setId($this->getDbTable()->insert($data));
			return $meeting->getId();
		}
		else
		{
			return $this->getDbTable()->update($data, array('id = ?' => $id));
		}
	}
	
	public function delete(Application_Model_Meeting $meeting)
	{
		$this->getDbTable()->delete(array('id = ?' => $meeting->getId()));
	}
	
	public function find($id)
	{
		$termMapper = new Application_Model_TermMapper();
		
		$result = $this->getDbTable()->find($id);
		if (count($result) == 0)
		{
			return null;
		}
		
		$row = $result->current();
		
		$meeting = new Application_Model_Meeting();
		$meeting->setId($id);
		$meeting->setDay($row->day);
		$meeting->setStartTime($row->start_time);
		$meeting->setEndTime($row->end_time);
		$meeting->setLocation($row->location);
		$meeting->setTerm($termMapper->find($row->term_id));
		
		return $meeting;
	}
	
	public function fetchAll()
	{
		$termMapper = new Application_Model_TermMapper();
		
		$resultSet = $this->getDbTable()->fetchAll();
		$meetings = array();
		
		foreach ($resultSet as $row)
		{
			$meeting = new Application_Model_Meeting();
			$meeting->setId($row->id);
			$meeting->setDay($row->day);
			$meeting->setStartTime($row->start_time);
			$meeting->setEndTime($row->end_time);
			$meeting->setLocation($row->location);
			$meeting->setTerm($termMapper->find($row->term_id));
			
			$meetings[] = $meeting;
		}
		
		return $meetings;
	}
	
	public function fetchAllByTerm(Application_Model_Term $term)
	{
		$termMapper = new Application_Model_TermMapper();
		
		$select = $this->getDbTable()->select();
		$select->where('term_id = :term_id')->bind(array(
			':term_id' => $term->getId(),
		));
		
		$resultSet = $this->getDbTable()->fetchAll($select);
		$meetings = array();
		
		foreach ($resultSet as $row)
		{
			$meeting = new Application_Model_Meeting();
			$meeting->setId($row->id);
			$meeting->setDay($row->day);
			$meeting->setStartTime($row->start_time);
			$meeting->setEndTime($row->end_time);
			$meeting->setLocation($row->location);
			$meeting->setTerm($termMapper->find($row->term_id));
			
			$meetings[] = $meeting;
		}
		
		return $meetings;
	}
}
