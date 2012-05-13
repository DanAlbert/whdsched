<?php

class Application_Model_CancelledMeeting
{
	private $id;
	private $meeting;
	private $date;
	
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
		$meetingMapper = new Application_Model_MeetingMapper();
		
		foreach ($data as $key => $value)
		{
			switch ($key)
			{
			case 'id':
				$this->setId($value);
				break;
			case 'meeting_id':
				$this->setMeeting($meetingMapper->find($value));
				break;
			case 'day':
				$this->setDate($value);
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
	
	public function setId(int $id)
	{
		$this->id = $id;
	}
	
	public function getMeeting()
	{
		return $this->meeting;
	}
	
	public function setMeeting(Application_Model_Meeting $meeting)
	{
		$this->meeting = $meeting;
	}
	
	public function getDate()
	{
		return $this->date;
	}
	
	public function setDate(string $date)
	{
		$this->date = $date;
	}
}
