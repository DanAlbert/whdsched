<?php

class Application_Model_Shift
{
	protected $_id;
	protected $_startTime;
	protected $_endTime;
	protected $_location;
	protected $_date;
	protected $_consultantId;
	
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
		foreach ($data as $key => $value)
		{
			switch ($key)
			{
			case 'id':
				$this->setId($value);
				break;
			case 'start_time':
				$this->setStartTime($value);
				break;
			case 'end_time':
				$this->setEndTime($value);
				break;
			case 'location':
				$this->setLocation($value);
				break;
			case 'date':
				$this->setDate($value);
				break;
			case 'consultant_id':
				$this->setConsultantId($value);
				break;
			default:
				throw new Exception("Invalid parameter: {$key}");
				break;
			}
		}
	}
	
	public function getId()
	{
		return (int)$this->_id;
	}
	
	public function setId($id)
	{
		$this->_id = $id;
	}
	
	public function getStartTime()
	{
		return $this->_startTime;
	}
	
	public function setStartTime($startTime)
	{
		$this->_startTime = $startTime;
	}
	
	public function getEndTime()
	{
		return $this->_endTime;
	}
	
	public function setEndTime($endTime)
	{
		$this->_endTime = $endTime;
	}
	
	public function getLocation()
	{
		return $this->_location;
	}
	
	public function setLocation($location)
	{
		$this->_location = $location;
	}
	
	public function getDate()
	{
		return $this->_date;
	}
	
	public function setDate($date)
	{
		$this->_date = $date;
	}
	
	public function getConsultantId()
	{
		return $this->_consultantId;
	}
	
	public function setConsultantId($consultantId)
	{
		$this->_consultantId = $consultantId;
	}
}

