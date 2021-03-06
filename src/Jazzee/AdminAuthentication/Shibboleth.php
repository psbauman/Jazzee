<?php
/**
 * Shibboleth Admin Authentication Controller
 * 
 * Shibboleth is a leading identity provider for educational institutions
 * Because Shibboleth is the preferred authentication mechanism at the University of California this 
 * module is the most likley to be up to date.
 * 
 */
namespace Jazzee\AdminAuthentication;
class Shibboleth implements \Jazzee\Interfaces\AdminAuthentication{
  const SESSION_VAR_ID = 'shibd_userid';
  
  /**
   * Our authenticated user
   * @var \Jazzee\Entity\User
   */
  private $_user;
  
  /**
   * Config instance
   * @var \Jazzee\Interfaces\AdminController 
   */
  private $_controller;
  
  /**
   * Constructor
   * 
   * Require authentication and setup the user if a valid session is detected
   * 
   * @param \Jazzee\Interfaces\AdminController
   */
  public function __construct(\Jazzee\Interfaces\AdminController $controller){
    $this->_controller = $controller;
    if($this->_controller->getStore()->check(self::SESSION_VAR_ID)){
      $this->_user = $this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\User')->find($this->_controller->getStore()->get(self::SESSION_VAR_ID));
    } else if(isset($_SERVER['Shib-Application-ID'])){ //this happens when the user is SHib authenticated, but not application authenticated.
       $this->loginUser();
    }
  }
  
  public function isValidUser(){
    return (bool)$this->_user;
  }
  
  public function getUser(){
    return $this->_user;
  }
  
  public function loginUser(){
    $config = $this->_controller->getConfig();
    if(!isset($_SERVER['Shib-Application-ID'])){
      header('Location: ' . $config->getShibbolethLoginUrl());
      exit();
    }
    if (!isset($_SERVER[$config->getShibbolethUsernameAttribute()])) throw new \Jazzee\Exception($config->getShibbolethUsernameAttribute() . ' attribute is missing from authentication source.');
    $uniqueName = $_SERVER[$config->getShibbolethUsernameAttribute()];
    $firstName = isset($_SERVER[$config->getShibbolethFirstNameAttribute()])?$_SERVER[$config->getShibbolethFirstNameAttribute()]:null;
    $lastName = isset($_SERVER[$config->getShibbolethLastNameAttribute()])?$_SERVER[$config->getShibbolethLastNameAttribute()]:null;
    $mail = isset($_SERVER[$config->getShibbolethEmailAddressAttribute()])?$_SERVER[$config->getShibbolethEmailAddressAttribute()]:null;

    $this->_user = $this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\User')->findOneBy(array('uniqueName'=>$uniqueName, 'isActive'=>true));
    if($this->_user){
      $this->_user->setFirstName($firstName);
      $this->_user->setLastName($lastName);
      $this->_user->setEmail($mail);
      $this->_controller->getStore()->expire();
      $this->_controller->getStore()->touchAuthentication();
      $this->_controller->getStore()->set(self::SESSION_VAR_ID, $this->_user->getId());
      $this->_controller->getEntityManager()->persist($this->_user);
    }
  }
  
  public function logoutUser(){
    $this->_user = null;
    $this->_controller->getStore()->expire();
    header('Location: ' . $this->_controller->getConfig()->getShibbolethLogoutUrl());
    die();
  }
}

