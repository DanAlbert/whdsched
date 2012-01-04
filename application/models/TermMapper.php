<?php

class Application_Model_TermMapper
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
			$this->setDbTable('Application_Model_DbTable_Terms');
		}
		
		return $this->_dbTable;
	}
	
	public function save(Application_Model_Term $term)
	{
		$data = array(
			'term'	   => $term->getTerm(),
			'year'	   => $term->getYear(),
			'start_date' => $term->getStartDate(),
			'end_date'   => $term->getEndDate(),
		);
		
		$id = $term->getId();
		if ($id == null)
		{
			$data['id'] = $this->makeTermId($term);
			$term->setId($this->getDbTable()->insert($data));
			return $term->getId();
		}
		else
		{
			return $this->getDbTable()->update($data, array('id = ?' => $id));
		}
		
		return true;
	}
	
	public function delete(Application_Model_Term $term)
	{
		$this->getDbTable()->delete(array('id = ?' => $term->getId()));
	}
	
	public function find($id)
	{
		$result = $this->getDbTable()->find($id);
		if (count($result) == 0)
		{
			return null;
		}
		
		$row = $result->current();
		
		$term = new Application_Model_Term();
		$term->setId($id);
		$term->setTerm($row->term);
		$term->setYear($row->year);
		$term->setStartDate($row->start_date);
		$term->setEndDate($row->end_date);
		
		return $term;
	}
	
	public function fetchAll()
	{
		$resultSet = $this->getDbTable()->fetchAll();
		$terms = array();
		
		foreach ($resultSet as $row)
		{
			$term = new Application_Model_Term();
			$term->setId($row->id);
			$term->setTerm($row->term);
			$term->setYear($row->year);
			$term->setStartDate($row->start_date);
			$term->setEndDate($row->end_date);
			
			$terms[] = $term;
		}
		
		return $terms;
	}
	
	public function fetchAllByYear($year)
	{
		if (DEBUG_DB_TERM)
		{
			Zend_Registry::get('log')->debug('Fetching all terms for ' . $year);
		}
		
		$resultSet = $this->getDbTable()->fetchAll(array('year' => $year));
		$terms = array();
		
		foreach ($resultSet as $row)
		{
			$term = new Application_Model_Term();
			$term->setId($row->id);
			$term->setTerm($row->term);
			$term->setYear($row->year);
			$term->setStartDate($row->start_date);
			$term->setEndDate($row->end_date);
			
			$terms[] = $term;
		}
		
		return $terms;
	}

	public function getTermOf($timestamp)
	{
		$terms = $this->fetchAllByYear(date('Y', $timestamp));

		foreach ($terms as $term)
		{
			list($y, $m, $d) = explode('-', $term->getStartDate());
			$start = mktime(0, 0, 0, $m, $d, $y);

			list($y, $m, $d) = explode('-', $term->getEndDate());
			$end = mktime(0, 0, 0, $m, $d, $y);

			if (($timestamp < $end) and ($timestamp > $start))
			{
				return $term;
			}
		}

		throw new Exception('No term exists');
	}

	public function getCurrentTerm()
	{
		try
		{
			$this->getTermOf(time());
		}
		catch (Exception $e)
		{
			throw $e;
		}
	}

	public function getCurrentOrNextTerm()
	{
		if (DEBUG_DB_TERM)
		{
			Zend_Registry::get('log')->debug('Getting current or next term');
		}
		
		$time = time();
		$terms = $this->fetchAllByYear(date('Y', $time));

		foreach ($terms as $term)
		{
			list($y, $m, $d) = explode('-', $term->getStartDate());
			$start = mktime(0, 0, 0, $m, $d, $y);

			list($y, $m, $d) = explode('-', $term->getEndDate());
			$end = mktime(0, 0, 0, $m, $d, $y);

			if (($time < $end) and ($time > $start))
			{
				if (DEBUG_DB_TERM)
				{
					Zend_Registry::get('log')->debug('Found current term');
				}
				
				return $term;
			}

			if ($start > $time)
			{
				if (isset($next))
				{
					list($y, $m, $d) = explode('-', $next->getStartDate());
					$nextStart = mktime(0, 0, 0, $m, $d, $y);

					if ($start < $nextStart)
					{
						if (DEBUG_DB_TERM)
						{
							Zend_Registry::get('log')->debug('Found closer future term');
						}
						
						$next = $term;
					}
				}
				else
				{
					if (DEBUG_DB_TERM)
					{
						Zend_Registry::get('log')->debug('Term is in future');
					}
					$next = $term;
				}
			}
		}

		if (!isset($next))
		{
			throw new Exception('No term exists');
		}
		else
		{
			return $next;
		}
	}
	
	public function makeTermId(Application_Model_Term $term)
	{
		switch ($term->getTerm())
		{
		case 'Summer':
			$t = '00';
			$y = '' . ($term->getYear() + 1);
			break;
		case 'Fall':
			$t = '01';
			$y = '' . ($term->getYear() + 1);
			break;
		case 'Winter':
			$t = '00';
			$y = '' . $term->getYear();
			break;
		case 'Spring':
			$t = '00';
			$y = '' . $term->getYear();
			break;
		}
		
		return $t . $y;
	}
}

