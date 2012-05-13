<?php

class Application_Model_MeetingAttendee
{
	private $id;
	private $consultant;
	private $meeting;
	
	public function __construct(array $data = null)
	{
		if (is_array($data))
		{
			$this->setData($data);
		}
		else
		{
			$this->setId(null);
		}
	}
	
	public function setData(array $data)
	{
		$consultantMapper = new Application_Model_ConsultantMapper();
		$meetingMapper = new Application_Model_MeetingMapper();
		
		foreach ($data as $key => $value)
		{
			switch ($key)
			{
			case 'id':
				$this->setId($value);
				break;
			case 'consultant_id':
				$this->setConsultant($consultantMapper->find($value));
				break;
			case 'meeting_id':
				$this->setMeeting($meetingMapper->find($value));
				break;
			default:
				throw new Exception("Invalid parameter: {$key}");
				break;
			}
		}
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	public function setId($id)
	{
		$this->id = $id;
	}
	
	public function getConsultant()
	{
		return $this->consultant;
	}
	
	public function setConsultant(Application_Model_Consultant $consultant)
	{
		$this->consultant = $consultant;
	}
	
	public function getMeeting()
	{
		return $this->meeting;
	}
	
	public function setMeeting(Application_Model_Meeting $meeting)
	{
		$this->meeting = $meeting;
	}
}
