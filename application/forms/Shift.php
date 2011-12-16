<?php

class Application_Form_Shift extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
		
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
		
		$location = $this->createElement('multiCheckbox', 'location');
		$location->setLabel('Location');
		$location->addMultiOptions(array(
			'WHD'  => 'Helpdesk',
			'Lab'  => 'Labs',
			'KEC'  => 'KEC 1130',
			'Owen' => 'Owen 237',
		));
		
		$this->addElement($location);
		
		$this->addElement('text', 'startDate', array(
			'label'      => 'Start Date (MM-DD-YYYY)',
			'required'   => true,
			'filters'    => array('StringTrim'),
			'validators' => array(
				array(
					'validator' => 'StringLength',
					'options'   => array(10, 10),
				)
			)
		));
		
		$this->addElement('text', 'endDate', array(
			'label'      => 'End Date (MM-DD-YYYY)',
			'required'   => true,
			'filters'    => array('StringTrim'),
			'validators' => array(
				array(
					'validator' => 'StringLength',
					'options'   => array(10, 10),
				)
			)
		));
		
		$this->addElement('submit', 'submit', array(
			'ignore' => true,
			'label'  => 'Save Changes',
		));
		
		$this->addElement('hash', 'csrf', array(
			'ignore' => true,
		));
    }
}

