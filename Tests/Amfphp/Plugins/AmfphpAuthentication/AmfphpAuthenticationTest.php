<?php
/*
 *  This file is part of amfPHP
 *
 * LICENSE
 *
 * This source file is subject to the license that is bundled
 * with this package in the file license.txt.
 */


require_once dirname(__FILE__).'/../../../../Amfphp/Plugins/AmfphpAuthentication/AmfphpAuthentication.php';
require_once dirname(__FILE__) . '/../../../../Amfphp/ClassLoader.php';
require_once dirname(__FILE__) . "/../../../TestData/Services/AuthenticationService.php";

/**
 * Test class for AmfphpAuthentication.
 * @package Tests_AmfphpAuthentication
 * @author Ariel Sommeria-klein
 */
class AmfphpAuthenticationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var AmfphpAuthentication
     */
    protected $object;

    /**
     *
     * @var <AuthenticationService>
     */
    protected $serviceObj;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new AmfphpAuthentication;
        $this->serviceObj = new AuthenticationService();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        session_unset();
    }

    public function testAddRole()
    {
        AmfphpAuthentication::addRole("admin");
        $roles = $_SESSION[AmfphpAuthentication::SESSION_FIELD_ROLES];
        $this->assertEquals(array("admin"), $roles);
    }

    public function testClearSessionInfo()
    {
        AmfphpAuthentication::addRole("bla");
        AmfphpAuthentication::clearSessionInfo();
        $this->assertFalse(isset ($_SESSION[AmfphpAuthentication::SESSION_FIELD_ROLES]));
    }


    public function testLoginAndAccess(){
        $this->serviceObj->login("admin", "adminPassword");
        $this->object->serviceObjectCreatedFilter($this->serviceObj, "adminMethod");
    }

    public function testNormalAccessToUnprotectedMethods(){
        $this->object->serviceObjectCreatedFilter($this->serviceObj, "logout");

    }

    /**
     * @expectedException Amfphp_Core_Exception
     */
    public function testLogout(){
        $this->serviceObj->login("admin", "adminPassword");
        $this->object->serviceObjectCreatedFilter($this->serviceObj, "adminMethod");
        $this->serviceObj->logout();
        $this->object->serviceObjectCreatedFilter($this->serviceObj, "adminMethod");
    }
    /**
     * @expectedException Amfphp_Core_Exception
     */
    public function testAccessWithoutAuthentication()
    {
        $this->object->serviceObjectCreatedFilter($this->serviceObj, "adminMethod");
    }

    /**
     * @expectedException Amfphp_Core_Exception
     */
    public function testBadRole(){
        $this->serviceObj->login("user", "userPassword");
        $this->object->serviceObjectCreatedFilter($this->serviceObj, "adminMethod");

    }
    
    public function testGetAmfRequestHeaderHandlerFilter()
    {
        $credentialsAssoc = new stdClass();
        $userIdField = Amfphp_Core_Amf_Constants::CREDENTIALS_FIELD_USERID;
        $passwordField = Amfphp_Core_Amf_Constants::CREDENTIALS_FIELD_PASSWORD;
        $credentialsAssoc->$userIdField =  "admin";
        $credentialsAssoc->$passwordField = "adminPassword";
        $credentialsHeader = new Amfphp_Core_Amf_Header(Amfphp_Core_Amf_Constants::CREDENTIALS_HEADER_NAME, true, $credentialsAssoc);
        $ret = $this->object->getAmfRequestHeaderHandlerFilter(null, $credentialsHeader);
        $this->assertEquals($this->object, $ret);
        
        $otherHeader = new Amfphp_Core_Amf_Header("bla");
        $ret = $this->object->getAmfRequestHeaderHandlerFilter(null, $otherHeader);
        $this->assertEquals(null, $ret);
    }

    /**
     * @expectedException Amfphp_Core_Exception
     */
    public function testWithFiltersBlockAccess(){
        Amfphp_Core_FilterManager::getInstance()->callFilters(Amfphp_Core_Common_ServiceRouter::FILTER_SERVICE_OBJECT_CREATED, $this->serviceObj, "adminMethod");
    }

    public function testWithFiltersGrantAccess(){
        $credentialsAssoc = new stdClass();
        $userIdField = Amfphp_Core_Amf_Constants::CREDENTIALS_FIELD_USERID;
        $passwordField = Amfphp_Core_Amf_Constants::CREDENTIALS_FIELD_PASSWORD;
        $credentialsAssoc->$userIdField =  "admin";
        $credentialsAssoc->$passwordField = "adminPassword";
        $credentialsHeader = new Amfphp_Core_Amf_Header(Amfphp_Core_Amf_Constants::CREDENTIALS_HEADER_NAME, true, $credentialsAssoc);
        $hookManager = Amfphp_Core_FilterManager::getInstance();
        $ret = $hookManager->callFilters(Amfphp_Core_Amf_Handler::FILTER_GET_AMF_REQUEST_HEADER_HANDLER, null, $credentialsHeader);
        $ret->handleRequestHeader($credentialsHeader);
        $ret->serviceObjectCreatedFilter($this->serviceObj, "adminMethod");
    }


}
?>