<?php

class Application_Form_Masquerade extends Zend_Form
{

    public function init()
    {
		$consultantMapper = new Application_Model_ConsultantMapper();
		
		$this->setMethod('post');

		$consultant = $this->createElement('select', 'consultant');
		$consultant->setLabel('Consulant');
		foreach ($consultantMapper->fetchAll() as $c)
		{
			$consultant->addMultiOption($c->getId(), $c->getName());
		}
		
		$this->addElement($consultant);
		
		$this->addElement('submit', 'submit', array(
			'ignore' => true,
			'label'  => 'Save Changes',
		));
		
		$this->addElement('hash', 'csrf', array(
			'ignore' => true,
		));
    }


}

