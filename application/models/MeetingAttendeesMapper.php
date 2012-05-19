<?php

class Application_Model_MeetingAttendeesMapper
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
			$this->setDbTable('Application_Model_DbTable_MeetingAttendees');
		}
		
		return $this->_dbTable;
	}
	
	public function save(Application_Model_MeetingAttendee $attendee)
	{
		$data = array(
			'id'            => $attendee->getId(),
			'consultant_id' => $attendee->getConsultant()->getId(),
			'meeting_id'    => $attendee->getMeeting()->getId(),
		);
		
		$id = $attendee->getId();
		if ($id == null)
		{
			unset($data['id']);
			$attendee->setId($this->getDbTable()->insert($data));
			return $attendee->getId();
		}
		else
		{
			return $this->getDbTable()->update($data, array('id = ?' => $id));
		}
		
		return true;
	}
	
	public function delete(Application_Model_MeetingAttendee $attendee)
	{
		$this->getDbTable()->delete(array('id = ?' => $attendee->getId()));
	}
	
	public function deleteWhere(Application_Model_Meeting $meeting,
			Application_Model_Consultant $consultant)
	{
		$this->getDbTable()->delete(array(
			'consultant_id = ?' => $consultant->getId(),
			'meeting_id = ?'    => $meeting->getId()
		));
	}
	
	public function find($id)
	{
		$consultantMapper = new Application_Model_ConsultantMapper();
		$meetingMapper = new Application_Model_MeetingMapper();
		
		$result = $this->getDbTable()->find($id);
		if (count($result) == 0)
		{
			return null;
		}
		
		$row = $result->current();
		
		$attendee = new Application_Model_MeetingAttendee();
		$attendee->setId($id);
		$attendee->setConsultant($consultantMapper->find($row->consultant_id));
		$attendee->setMeeting($meetingMapper->find($row->meeting_id));
		
		return $attendee;
	}
	
	public function findWhere(Application_Model_Meeting $meeting,
			Application_Model_Consultant $consultant)
	{
		$select = $this->getDbTable()->select();
		$select->where(
				'consultant_id = :consultant_id AND ' .
				'meeting_id = :meeting_id')->bind(array(
			':consultant_id' => $consultant->getId(),
			':meeting_id'    => $meeting->getId(),
		));

		$resultSet = $this->getDbTable()->fetchAll($select);
		
		$row = $resultSet[0];
		
		$attendee = new Application_Model_MeetingAttendee();
		$attendee->setId($row->id);
		$attendee->setMeeting($meeting);
		$attendee->getConsultant($consultant);
		
		return $attendee;
	}
	
	public function fetchAll()
	{
		$consultantMapper = new Application_Model_ConsultantMapper();
		$meetingMapper = new Application_Model_MeetingMapper();
		
		$resultSet = $this->getDbTable()->fetchAll();
		$attendees = array();
		
		foreach ($resultSet as $row)
		{
			$attendee = new Application_Model_MeetingAttendee();
			$attendee->setId($row->id);
			$attendee->setConsultant($consultantMapper->find($row->consultant_id));
			$attendee->setMeeting($meetingMapper->find($row->meeting_id));
			
			$attendees[] = $attendee;
		}
		
		return $attendees;
	}
	
	public function fetchByMeeting(Application_Model_Meeting $meeting)
	{
		$consultantMapper = new Application_Model_ConsultantMapper();
		
		$select = $this->getDbTable()->select();
		$select->where('meeting_id = :meeting_id')->bind(array(
			':meeting_id' => $meeting->getId(),
		));

		$resultSet = $this->getDbTable()->fetchAll($select);
		
		$attendees = array();
		foreach ($resultSet as $row)
		{
			$attendee = new Application_Model_MeetingAttendee();
			$attendee->setId($row->id);
			$attendee->setMeeting($meeting);
			$attendee->getConsultant($consultantMapper->find($row->consultant_id));
			
			$attendees[] = $attendee;
		}
		
		return $attendees;
	}
	
	public function fetchConsultantsByMeeting(Application_Model_Meeting $meeting)
	{
		$consultantMapper = new Application_Model_ConsultantMapper();
		
		$select = $this->getDbTable()->select();
		$select->where('meeting_id = :meeting_id')->bind(array(
			':meeting_id' => $meeting->getId(),
		));

		$resultSet = $this->getDbTable()->fetchAll($select);
		
		$consultants = array();
		foreach ($resultSet as $row)
		{
			$consultant = $consultantMapper->find($row->consultant_id);
			if ($consultant !== null)
			{
				$consultants[] = $consultant;
			}
		}
		
		return $consultants;
	}
}
