<?php

class Application_Model_Log
{
	protected $_id;
	protected $_time;
	protected $_type;
	protected $_message;
	
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
			case 'log_time':
				$this->setTime($value);
				break;
			case 'type':
				$this->setType($value);
				break;
			case 'message':
				$this->setMessage($value);
				break;
			default:
				throw new Exception("Invalid parameter: {$key}");
				break;
			}
		}
	}
	
	public function getId()
	{
		return $this->_id;
	}
	
	public function setId($id)
	{
		$this->_id = $id;
	}
	
	public function getTime()
	{
		return $this->_time;
	}
	
	public function getTimeFormatted()
	{
		return date('M j Y H:i:s', strtotime($this->getTime()));
	}
	
	public function getDate()
	{
		list($date, $time) = explode(' ', $this->getTime());
		return $date;
	}
	
	public function setTime($time)
	{
		$this->_time = $time;
	}
	
	public function getType()
	{
		return $this->_type;
	}
	
	public function setType($type)
	{
		$this->_type = $type;
	}
	
	public function getMessage()
	{
		return $this->_message;
	}
	
	public function setMessage($message)
	{
		$this->_message = $message;
	}
}

