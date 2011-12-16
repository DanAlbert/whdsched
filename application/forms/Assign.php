<?php

class Application_Form_Assign extends Zend_Form
{
    public function init()
    {
		$consultantMapper = new Application_Model_ConsultantMapper();
		$termMapper = new Application_Model_TermMapper();
		
		$this->setMethod('post');
		
		$consultant = $this->createElement('select', 'consultant');
		$consultant->setLabel('Consulant');
		foreach ($consultantMapper->fetchAll() as $c)
		{
			$consultant->addMultiOption($c->getId(), $c->getName());
		}
		
		$this->addElement($consultant);
		
		$day = $this->createElement('select', 'day');
		$day->setLabel('Day');
		$day->addMultiOption(0, 'Sunday');
		$day->addMultiOption(1, 'Monday');
		$day->addMultiOption(2, 'Tuesday');
		$day->addMultiOption(3, 'Wednesday');
		$day->addMultiOption(4, 'Thursday');
		$day->addMultiOption(5, 'Friday');
		$day->addMultiOption(6, 'Saturday');
		
		$this->addElement($day);
		
		$this->addElement('text', 'startTime', array(
			'label'      => 'Start Time',
			'required'   => true,
			'filters'    => array('StringTrim', 'Digits'),
			'validators' => array(
				array(
					'validator' => 'StringLength',
					'options'   => array(4, 4),
				),
			)
		));
		
		$this->addElement('text', 'endTime', array(
			'label'      => 'End Time',
			'required'   => true,
			'filters'    => array('StringTrim', 'Digits'),
			'validators' => array(
				array(
					'validator' => 'StringLength',
					'options'   => array(0, 40),
				),
			)
		));
		
		$location = $this->createElement('select', 'location');
		$location->setLabel('Location');
		$location->addMultiOption('WHD', 'Helpdesk');
		$location->addMultiOption('Lab', 'Labs');
		$location->addMultiOption('KEC', 'KEC 1130');
		$location->addMultiOption('Owen', 'Owen 237');
		
		$this->addElement($location);
		
		$terms = $this->createElement('select', 'term');
		$terms->setLabel('Term');
		foreach ($termMapper->fetchAll() as $t)
		{
			$terms->addMultiOption($t->getId(), $t->getName());
		}
		
		$this->addElement($terms);
		
		$this->addElement('submit', 'submit', array(
			'ignore' => true,
			'label'  => 'Save Changes',
		));
		
		$this->addElement('hash', 'csrf', array(
			'ignore' => true,
		));
    }
}

