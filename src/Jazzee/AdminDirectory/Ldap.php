<?php
/**
 * LDAP Admin Directory
 * 
 * Use LDAP to find users and their attributes
 * 
 */
namespace Jazzee\AdminDirectory;
class Ldap implements \Jazzee\Interfaces\AdminDirectory{
  /**
   * Our directory Server resource
   * @var resource
   */
  private $_directoryServer;
  
  /**
   * Controller instance
   * @var \Jazzee\AdminController
   */
  private $_controller;
  
  public function __construct(\Jazzee\AdminController $controller){
    $this->_controller = $controller;
    if(!$this->_directoryServer = ldap_connect($this->_controller->getConfig()->getLdapHostname(), $this->_controller->getConfig()->getLdapPort())){
      throw new \Jazzee\Exception('Unable to connect to ldap server ' . $this->_controller->getConfig()->getLdapHostname() . ' at port' . $this->_controller->getConfig()->getLdapPort());
    }
    if(!ldap_bind($this->_directoryServer, $this->_controller->getConfig()->getLdapBindRdn(), $this->_controller->getConfig()->getLdapBindPassword())){
      throw new \Jazzee\Exception('Unable to bind to ldap server');
    }
  }
  
  public function search(array $attributes){
    $result = array();
    $filters = array();
    $filter = '';
    foreach($attributes as $key=>$value)$filters[] = "{$key}={$value}";
    if(count($filters) == 1){
      $filter = $filters[0];
    } else if(count($filters) > 1){
      $filter = '(&';
      foreach($filters as $f) $filter .= "({$f})";
      $filter .= ')';
    }
    $searchResult = ldap_search($this->_directoryServer, $this->_controller->getConfig()->getLdapSearchBase(), $filter);
    ldap_sort($this->_directoryServer, $searchResult, $this->_controller->getConfig()->getLdapFirstNameAttribute());
    ldap_sort($this->_directoryServer, $searchResult, $this->_controller->getConfig()->getLdapLastNameAttribute());
    if(ldap_count_entries($this->_directoryServer, $searchResult)){
      $entries = ldap_get_entries($this->_directoryServer, $searchResult);
      for ($i=0; $i<$entries["count"]; $i++) {
        $arr = array(
          'userName' => '',
          'firstName' => '',
          'lastName' => '',
          'emailAddress' => '',
        );
        if(!empty($entries[$i][strtolower($this->_controller->getConfig()->getLdapUsernameAttribute())][0])) $arr['userName'] = $entries[$i][strtolower($this->_controller->getConfig()->getLdapUsernameAttribute())][0];
        if(!empty($entries[$i][strtolower($this->_controller->getConfig()->getLdapFirstNameAttribute())][0])) $arr['firstName'] = $entries[$i][strtolower($this->_controller->getConfig()->getLdapFirstNameAttribute())][0];
        if(!empty($entries[$i][strtolower($this->_controller->getConfig()->getLdapLastNameAttribute())][0])) $arr['lastName'] = $entries[$i][strtolower($this->_controller->getConfig()->getLdapLastNameAttribute())][0];
        if(!empty($entries[$i][strtolower($this->_controller->getConfig()->getLdapEmailAddressAttribute())][0])) $arr['emailAddress'] = $entries[$i][strtolower($this->_controller->getConfig()->getLdapEmailAddressAttribute())][0];
        
        $result[] = $arr;
      }
    }
    return $result;
  }
}

