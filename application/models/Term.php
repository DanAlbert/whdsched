<?php

class Application_Model_Term
{
	protected $_id;
	protected $_term;
	protected $_year;
	protected $_startDate;
	protected $_endDate;
	
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
			case 'term':
				$this->setTerm($value);
				break;
			case 'year':
				$this->setYear($value);
				break;
			case 'start_date':
				$this->setStartDate($value);
				break;
			case 'end_date':
				$this->setEndDate($value);
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
	
	public function getTerm()
	{
		return $this->_term;
	}
	
	public function setTerm($term)
	{
		$this->_term = $term;
	}
	
	public function getYear()
	{
		return $this->_year;
	}
	
	public function getName()
	{
		return "{$this->getTerm()} {$this->getYear()}";
	}
	
	public function setYear($year)
	{
		$this->_year = $year;
	}
	
	public function getStartDate()
	{
		return $this->_startDate;
	}
	
	public function setStartDate($startDate)
	{
		$this->_startDate = $startDate;
	}
	
	public function getEndDate()
	{
		return $this->_endDate;
	}
	
	public function setEndDate($endDate)
	{
		$this->_endDate = $endDate;
	}
}

