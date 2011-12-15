<?php

class Application_Model_Consultant
{
	const MAIL_DOMAIN = 'engr.oregonstate.edu';
	protected $_id;
	protected $_firstName;
	protected $_lastName;
	protected $_engr;
	protected $_phone;
	
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
			case 'first_name':
				$this->setFirstName($value);
				break;
			case 'last_name':
				$this->setLastName($value);
				break;
			case 'engr':
				$this->setEngr($value);
				break;
			case 'phone':
				$this->setPhone($value);
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
	
	public function getFirstName()
	{
		return (string)$this->_firstName;
	}
	
	public function setFirstName($firstName)
	{
		$this->_firstName = $firstName;
	}
	
	public function getLastName()
	{
		return (string)$this->_lastName;
	}
	
	public function setLastName($lastName)
	{
		$this->_lastName = $lastName;
	}
	
	public function getName()
	{
		return "{$this->getFirstName()} {$this->getLastName()}";
	}
	
	public function getEngr()
	{
		return (string)$this->_engr;
	}
	
	public function setEngr($engr)
	{
		$this->_engr = $engr;
	}
	
	public function getEmail()
	{
		return $this->getEngr() . '@' . self::MAIL_DOMAIN;
	}
	
	public function getPhone()
	{
		return (string)$this->_phone;
	}
	
	public function setPhone($phone)
	{
		$this->_phone = $phone;
	}
	
	public function getPhoneFormatted()
	{
		$area = substr($this->getPhone(), 0, 3);
		$first = substr($this->getPhone(), 3, 3);
		$last = substr($this->getPhone(), 6, 4);
		
		return "({$area}) {$first}-{$last}";
	}
}

