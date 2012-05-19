<?php

class Application_Model_Meeting
{
	// Silly key value pairs are for form generation
	public static $VALID_DAYS = array(
		'Monday'    => 'Monday',
		'Tuesday'   => 'Tuesday',
		'Wednesday' => 'Wednesday',
		'Thursday'  => 'Thursday',
		'Friday'    => 'Friday',
		'Saturday'  => 'Saturday',
		'Sunday'    => 'Sunday'
	);
	
	private $id;
	private $day;
	private $startTime;
	private $endTime;
	private $location;
	private $term;
	
	private $attendees;
	private $attendeesFetched;
	
	private $removed;
	
	public function __construct(array $data = null)
	{
		$this->attendees = array();
		$this->attendeesFetched = false;
		
		$this->removed = array();
		
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
		$termMapper = new Application_Model_TermMapper();
		
		foreach ($data as $key => $value)
		{
			switch ($key)
			{
			case 'id':
				$this->setId($value);
				break;
			case 'day':
				$this->setDay($value);
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
			case 'term':
				$this->setTerm($termMapper->find($value));
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
	
	public function getDay()
	{
		return $this->day;
	}
	
	public function setDay($day)
	{
		if (!in_array($day, self::$VALID_DAYS))
		{
			throw new Exception("Invalid day: {$day}");
		}
		
		$this->day = $day;
	}
	
	public function getStartTime()
	{
		return $this->startTime;
	}
	
	public function setStartTime($startTime)
	{
		$this->startTime = $startTime;
	}
	
	public function getEndTime()
	{
		return $this->endTime;
	}
	
	public function setEndTime($endTime)
	{
		$this->endTime = $endTime;
	}
	
	public function getDuration()
	{
		$start =  strtotime($this->startTime);
		$end =  strtotime($this->endTime);
		
		if ($end < $start)
		{
			$end = strtotime("+1 day", $end);
		}
		
		return $end - $start;
	}
	
	public function getTimeString()
	{
		$start =  strtotime($this->startTime);
		$end =  strtotime($this->endTime);
		
		return date("H:i", $start) . ' - ' . date("H:i", $end);
	}
	
	public function getLocation()
	{
		return $this->location;
	}
	
	public function setLocation($location)
	{
		$this->location = $location;
	}
	
	/**
	 *
	 * @return Application_Model_Term
	 */
	public function getTerm()
	{
		return $this->term;
	}
	
	public function setTerm(Application_Model_Term $term)
	{
		$this->term = $term;
	}
	
	public function getAttendees()
	{
		$attendeeMapper = new Application_Model_MeetingAttendeesMapper();
		if (!$this->attendeesFetched)
		{
			$this->attendees = $attendeeMapper->fetchConsultantsByMeeting($this);
			$this->attendeesFetched = true;
		}
		
		return $this->attendees;
	}
	
	public function getRemovedAttendees()
	{
		return $this->removed;
	}
	
	public function addAttendee(Application_Model_Consultant $consultant)
	{
		if (!in_array($conulstant, $this->getAttendees()))
		{
			$this->attendees[] = $consultant;
		}
		
		// If the attendee was previously removed, ensure that they are
		// no longer in the list of attendees to remove.
		$i = array_search($consultant, $this->removed, true);
		if ($i !== false)
		{
			unset($this->removed[$i]);
		}
	}
	
	public function removeAttendee(Application_Model_Consultant $consultant)
	{
		$i = array_search($consultant, $this->getAttendees());
		if ($i !== false)
		{
			$this->removed[] = $this->attendees[$i];
			unset($this->attendees[$i]);
		}
		else
		{
			throw new Whdsched_Exception(
					'Consultant not found in attendee list');
		}
	}
	
	public function getNextOccurence($format = 'Y-m-d')
	{
		$timestamp = $this->getNextTimestamp();
		if ($timestamp === null)
		{
			return $timestamp;
		}
		
		return date($format, $timestamp);
	}
	
	public function getNextTimestamp()
	{
		if (date('j') == $this->getDay())
		{
			$day = strtotime('00:00:00 today');
		}
		else
		{
			$day = strtotime("00:00:00 {$this->getDay()}");
		}
		
		$timestamp = strtotime($this->startTime, $day);
		
		$start = $this->getTerm()->getStartTimestamp();
		$end = $this->getTerm()->getEndTimestamp();
		
		if (($timestamp < $end) and ($timestamp > $start))
		{
			return $timestamp;
		}
		else
		{
			// TODO: Will return null before the term too
			// Can be misleading, as next timestamp will return null
			// if the term has not started yet
			return null;
		}
	}
}
