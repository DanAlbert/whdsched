<?php

class ShiftControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{

    public function setUp()
    {
		$this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
		parent::setUp();
    }

    public function testIndexAction()
    {
		$params = array('action' => 'index', 'controller' => 'Shift', 'module' => 'default');
		$urlParams = $this->urlizeOptions($params);
		$url = $this->url($urlParams);
		$this->dispatch($url);
		
		// assertions
		$this->assertModule($urlParams['module']);
		$this->assertController($urlParams['controller']);
		$this->assertAction($urlParams['action']);
		$this->assertQueryContentContains(
			'div#view-content p',
			'View script for controller <b>' . $params['controller'] . '</b> and script/action name <b>' . $params['action'] . '</b>'
			);
    }

    public function testCreateAction()
    {
		$params = array('action' => 'create', 'controller' => 'Shift', 'module' => 'default');
		$urlParams = $this->urlizeOptions($params);
		$url = $this->url($urlParams);
		$this->dispatch($url);
		
		// assertions
		$this->assertModule($urlParams['module']);
		$this->assertController($urlParams['controller']);
		$this->assertAction($urlParams['action']);
		$this->assertQueryContentContains(
			'div#view-content p',
			'View script for controller <b>' . $params['controller'] . '</b> and script/action name <b>' . $params['action'] . '</b>'
			);
    }

    public function testAssignAction()
    {
		$params = array('action' => 'assign', 'controller' => 'Shift', 'module' => 'default');
		$urlParams = $this->urlizeOptions($params);
		$url = $this->url($urlParams);
		$this->dispatch($url);
		
		// assertions
		$this->assertModule($urlParams['module']);
		$this->assertController($urlParams['controller']);
		$this->assertAction($urlParams['action']);
		$this->assertQueryContentContains(
			'div#view-content p',
			'View script for controller <b>' . $params['controller'] . '</b> and script/action name <b>' . $params['action'] . '</b>'
			);
    }

    public function testAvailableAction()
    {
        $params = array('action' => 'available', 'controller' => 'Shift', 'module' => 'default');
        $urlParams = $this->urlizeOptions($params);
        $url = $this->url($urlParams);
        $this->dispatch($url);
        
        // assertions
        $this->assertModule($urlParams['module']);
        $this->assertController($urlParams['controller']);
        $this->assertAction($urlParams['action']);
        $this->assertQueryContentContains(
            'div#view-content p',
            'View script for controller <b>' . $params['controller'] . '</b> and script/action name <b>' . $params['action'] . '</b>'
            );
    }

    public function testTakeAction()
    {
        $params = array('action' => 'take', 'controller' => 'Shift', 'module' => 'default');
        $urlParams = $this->urlizeOptions($params);
        $url = $this->url($urlParams);
        $this->dispatch($url);
        
        // assertions
        $this->assertModule($urlParams['module']);
        $this->assertController($urlParams['controller']);
        $this->assertAction($urlParams['action']);
        $this->assertQueryContentContains(
            'div#view-content p',
            'View script for controller <b>' . $params['controller'] . '</b> and script/action name <b>' . $params['action'] . '</b>'
            );
    }


}











