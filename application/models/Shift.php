<?php

class Application_Model_Shift
{
	protected $_id;
	protected $_startTime;
	protected $_endTime;
	protected $_location;
	protected $_date;
	protected $_consultant;
	
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
	
	/**
	 * Copy constructor
	 */
	public function __clone()
	{
		$this->_id = null;
		$this->_consultant = clone $this->_consultant;
	}
	
	public function __toString()
	{
		return $this->getTimeString() . ' ' . $this->getLocation();
	}
	
	public function getGeneralDescription()
	{
		$wday = date('l', strtotime($this->getDate()));
		return "{$wday} {$this->getTimeString()} {$this->getLocation()}";
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
			case 'consultant':
				$this->setConsultant($value);
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
	
	public function getStartTimestamp()
	{
		list($y, $mo, $d) = explode('-', $this->getDate());
		list($h, $mi, $s) = explode(':', $this->getStartTime());
		return mktime($h, $mi, $s, $mo, $d, $y);
	}
	
	public function getEndTimestamp()
	{
		list($y, $mo, $d) = explode('-', $this->getDate());
		list($h, $mi, $s) = explode(':', $this->getEndTime());
		return mktime($h, $mi, $s, $mo, $d, $y);
	}
	
	public function getDuration()
	{
		list($sh, $m, $s) = explode(':', $this->getStartTime());
		list($eh, $m, $s) = explode(':', $this->getEndTime());

		if ($eh < $sh)
		{
			$eh += 24;
		}

		return $eh - $sh;
	}
	
	public function getTimeString()
	{
		return substr($this->getStartTime(), 0, 5) . ' - ' .
				substr($this->getEndTime(), 0, 5);
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
	
	public function getConsultant()
	{
		return $this->_consultant;
	}
	
	public function setConsultant($consultant)
	{
		$this->_consultant = $consultant;
	}
	
	public function isOwned()
	{
		return ($this->getConsultant() !== null);
	}
	
	public function isOwnedBy(Application_Model_Consultant $consultant)
	{
		assert($consultant !== null);
		return ($this->isOwned() and
				($this->getConsultant()->getId() == $consultant->getId()));
	}
	
	public function isMultiDay()
	{
		list($sh, $m, $s) = explode(':', $this->getStartTime());
		list($eh, $m, $s) = explode(':', $this->getEndTime());

		return ($eh < $sh);
	}
}

