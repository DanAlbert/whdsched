<?php

class Application_Model_CancelledMeetingMapper
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
			$this->setDbTable('Application_Model_DbTable_CancelledMeetings');
		}
		
		return $this->_dbTable;
	}
	
	public function save(Application_Model_CancelledMeeting $cancelled)
	{
		$data = array(
			'id'         => $cancelled->getId(),
			'meeting_id' => $cancelled->getMeeting()->getId(),
			'date'       => $cancelled->getDate(),
		);
		
		$id = $cancelled->getId();
		if ($id == null)
		{
			unset($data['id']);
			$cancelled->setId($this->getDbTable()->insert($data));
			return $cancelled->getId();
		}
		else
		{
			return $this->getDbTable()->update($data, array('id = ?' => $id));
		}
		
		return true;
	}
	
	public function delete(Application_Model_CancelledMeeting $cancelled)
	{
		$this->getDbTable()->delete(array('id = ?' => $cancelled->getId()));
	}
	
	public function find($id)
	{
		$meetingMapper = new Application_Model_MeetingMapper();
		
		$result = $this->getDbTable()->find($id);
		if (count($result) == 0)
		{
			return null;
		}
		
		$row = $result->current();
		
		$cancelled = new Application_Model_CancelledMeeting();
		$cancelled->setId($id);
		$cancelled->setMeeting($meetingMapper->find($row->meeting_id));
		$cancelled->setDate($row->day);
		
		return $cancelled;
	}
	
	public function fetchAll()
	{
		$meetingMapper = new Application_Model_MeetingMapper();
		
		$resultSet = $this->getDbTable()->fetchAll();
		$meetings = array();
		
		foreach ($resultSet as $row)
		{
			$cancelled = new Application_Model_CancelledMeeting();
			$cancelled->setId($id);
			$cancelled->setMeeting($meetingMapper->find($row->meeting_id));
			$cancelled->setDate($row->day);
			
			$meetings[] = $cancelled;
		}
		
		return $meetings;
	}
	
	public function fetchByMeeting(Application_Model_Meeting $meeting)
	{
		// TODO: Implement
	}
}

