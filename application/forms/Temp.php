<?php

class Application_Form_Temp extends Zend_Form
{

	protected $shift;
	
	public function __construct($shift)
	{
		$this->shift = $shift;
		Zend_Form::__construct();
	}
	
	public function init()
	{
		$consultantMapper = new Application_Model_ConsultantMapper();
		
		list($h, $m, $s) = explode(':', $this->shift->getStartTime());
		list($end, $m, $s) = explode(':', $this->shift->getEndTime());
		
		// Shift spans to next day
		if ($end < $h)
		{
			// 0200 will represented as 2600 for the while below
			$end += 24;
		}
		
		// Only show hors element for shifts longer than one hour
		if ($end - $h > 1)
		{
			$hours = $this->createElement('multiCheckbox', 'hours');
			$hours->setRequired(true);
			$hours->setLabel('Hours');
			while ($h < $end)
			{
				$hours->addMultiOption($h, $h . ':00');
				if (++$h >= 24)
				{
					$h -= 24; // Next day
					$end -= 24; // Back to normal representation
				}
			}

			$this->addElement($hours);
		}
		
		$consultants = $this->createElement('select', 'preferred');
		$consultants->setRequired(true);
		$consultants->setLabel('Preferred Consultant');
		$consultants->addMultiOption(-1, 'None');
		foreach ($consultantMapper->fetchAll() as $consultant)
		{
			$consultants->addMultiOption($consultant->getId(), $consultant->getName());
		}
		
		$this->addElement($consultants);
		
		/*$this->addElement('text', 'timeout', array(
			'label'	  => 'Grace Period (hours) for Preferred Consultant',
			'required'   => true,
			'filters'	=> array('Digits'),
		));*/
		
		$this->addElement('submit', 'submit', array(
			'ignore' => true,
			'label'  => 'Save Changes',
		));
		
		$this->addElement('hash', 'csrf', array(
			'ignore' => true,
		));
	}


}

