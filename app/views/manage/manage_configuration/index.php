<?php 
/**
 * manage_configuration index view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage manage
 */
if(isset($form)){
  $this->renderElement('form', array('form'=>$form));
}
?>