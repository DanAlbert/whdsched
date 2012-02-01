<?php

class Application_Form_EditConsultant extends Zend_Form
{

    public function init()
    {
		$this->setMethod('post');
		
		$this->addElement('text', 'firstName', array(
			'label'	  => 'First Name',
			'required'   => true,
			'filters'	=> array('StringTrim'),
			'validators' => array(
				array(
					'validator' => 'StringLength',
					'options'   => array(0, 40),
				),
			)
		));
		
		$this->addElement('text', 'lastName', array(
			'label'	  => 'Last Name',
			'required'   => true,
			'filters'	=> array('StringTrim'),
			'validators' => array(
				array(
					'validator' => 'StringLength',
					'options'   => array(0, 40),
				),
			)
		));
		
		$this->addElement('text', 'phone', array(
			'label'	  => 'Phone Number',
			'required'   => true,
			'filters'	=> array('Digits'),
			'validators' => array(
				array(
					'validator' => 'StringLength',
					'options'   => array(10, 10),
				)
			)
		));
		
		$this->addElement('text', 'email', array(
			'label'      => 'Alternate Email',
			'required'   => false,
			'validators' => array(
				array(
					'validator' => 'EmailAddress',
					'options'   => array(
						'mx'   => true,
						'deep' => true,
					),
				),
			),
		));
		
		$this->addElement('text', 'maxHours', array(
			'label'	  => 'Max Hours',
			'required'   => true,
			'filters'	=> array('Digits'),
			'validators' => array(
				array(
					'validator' => 'Between',
					'options'   => array(
						'min' => 1,
						'max' => 40,
					),
				),
			),
		));
		
		$nightly = $this->createElement('radio', 'nightly');
		$nightly->setLabel('Receive nightly temp shift emails');
		$nightly->addMultiOptions(array(
			'yes' => 'Yes',
			'no'  => 'No',
		));
		$nightly->setValue('yes');
		
		$this->addElement($nightly);
		
		$instant = $this->createElement('radio', 'instant');
		$instant->setLabel('Receive instant temp shift emails');
		$instant->addMultiOptions(array(
			'yes' => 'Yes',
			'no'  => 'No',
		));
		$instant->setValue('no');
		
		$this->addElement($instant);
		
		$instant = $this->createElement('radio', 'taken');
		$instant->setLabel('Receive email when your shift is taken');
		$instant->addMultiOptions(array(
			'yes' => 'Yes',
			'no'  => 'No',
		));
		$instant->setValue('yes');
		
		$this->addElement($instant);
		
		$this->addElement('submit', 'submit', array(
			'ignore' => true,
			'label'  => 'Save Changes',
		));
		
		$this->addElement('hash', 'csrf', array(
			'ignore' => true,
		));
    }


}

