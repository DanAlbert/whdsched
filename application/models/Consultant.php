<?php

class Application_Model_Consultant
{
	const MAIL_DOMAIN = 'engr.oregonstate.edu';
	
	protected $_id;
	protected $_firstName;
	protected $_lastName;
	protected $_engr;
	protected $_phone;
	protected $_preferredEmail = null;
	protected $_maxHours = 20;
	protected $_receiveNightly = true;
	protected $_receiveInstant = false;
	protected $_receiveTaken = true;
	protected $_admin;
	protected $_hidden;
	
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
			case 'preferred_email':
				$this->setPreferredEmail($value);
				break;
			case 'max_hours':
				$this->setMaxHours($value);
				break;
			case 'recv_nightly':
				$this->setReceiveNightly($value);
				break;
			case 'recv_instant':
				$this->setReceiveInstant($value);
				break;
			case 'receive_taken':
				$this->setReceiveTaken($value);
				break;
			case 'admin':
				$this->setAdmin($value);
				break;
			case 'hidden':
				$this->setHidden($value);
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

	public function getShortName()
	{
		$lastInitial = substr($this->getLastName(), 0, 1);
		return "{$this->getFirstName()} {$lastInitial}";
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
		if ($this->getPreferredEmail() !== null)
		{
			return $this->getPreferredEmail();
		}
		else
		{
			return $this->getEngr() . '@' . self::MAIL_DOMAIN;
		}
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
	
	public function getPreferredEmail()
	{
		return $this->_preferredEmail;
	}
	
	public function setPreferredEmail($preferredEmail)
	{
		if (trim($preferredEmail) == '')
		{
			$this->_preferredEmail = null;
		}
		else
		{
			$this->_preferredEmail = $preferredEmail;
		}
	}
	
	public function getMaxHours()
	{
		return $this->_maxHours;
	}
	
	public function setMaxHours($maxHours)
	{
		$this->_maxHours = $maxHours;
	}
	
	public function getReceiveNightly()
	{
		return $this->_receiveNightly;
	}
	
	public function setReceiveNightly($receiveNightly)
	{
		$this->_receiveNightly = $receiveNightly;
	}
	
	public function getReceiveInstant()
	{
		return $this->_receiveInstant;
	}
	
	public function setReceiveInstant($receiveInstant)
	{
		$this->_receiveInstant = $receiveInstant;
	}
	
	public function getReceiveTaken()
	{
		return $this->_receiveTaken;
	}
	
	public function setReceiveTaken($receiveTaken)
	{
		$this->_receiveTaken = $receiveTaken;
	}
	
	public function isAdmin()
	{
		return ($this->_admin != 0);
	}
	
	public function setAdmin($admin)
	{
		$this->_admin = $admin;
	}
	
	public function isHidden()
	{
		return ($this->_hidden != 0);
	}
	
	public function setHidden($hidden)
	{
		$this->_hidden = $hidden;
	}
}

