<?php

class Application_Model_TempShift
{
	protected $_id;
	protected $_shiftId;
	protected $_tempConsultantId;
	protected $_postTime;
	protected $_responseTime;
	protected $_hours;
	protected $_assignedConsultant;
	protected $_timeout;
	
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
			case 'shift_id':
				$this->setShiftId($value);
				break;
			case 'temp_consultant_id':
				$this->setTempConsultantId($value);
				break;
			case 'post_time':
				$this->setPostTime($value);
				break;
			case 'resposne_time':
				$this->setResponseTime($value);
				break;
			case 'hours':
				$this->setHours($value);
				break;
			case 'assigned_to':
				$this->setAssignedConsultant($value);
				break;
			case 'timout':
				$this->setTimeout($value);
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
	
	public function getShiftId()
	{
		return $this->_shiftId;
	}
	
	public function setShiftId($shiftId)
	{
		$this->_shiftId = $shiftId;
	}
	
	public function getTempConsultantId()
	{
		return $this->_tempConsultantId;
	}
	
	public function setTempConsultantId($tempConsultantId)
	{
		$this->_tempConsultantId = $tempConsultantId;
	}
	
	public function getPostTime()
	{
		return $this->_postTime;
	}
	
	public function setPostTime($postTime)
	{
		$this->_postTime = $postTime;
	}
	
	public function getResponseTime()
	{
		return $this->_responseTime;
	}
	
	public function setResponseTime($responseTime)
	{
		$this->_responseTime = $responseTime;
	}
	
	public function getHours()
	{
		return $this->_hours;
	}
	
	public function setHours($hours)
	{
		$this->_hours = $hours;
	}
	
	public function getAssignedConsultant()
	{
		return $this->_assignedConsultant;
	}
	
	public function setAssignedConsultant($assignedConsultant)
	{
		$this->_assignedConsultant = $assignedConsultant;
	}
	
	public function getTimeout()
	{
		return $this->_timeout;
	}
	
	public function setHours($timeout)
	{
		$this->_timeout = $timeout;
	}
}

