<?php
/**
 * Search TOEFL Scores
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage scores
 */
class ScoresToeflController extends \Jazzee\AdminController {
  const MENU = 'Scores';
  const TITLE = 'Search TOEFL';
  const PATH = 'scores/toefl';
  
  const ACTION_INDEX = 'Search';
  const REQUIRE_APPLICATION = false;
  
  /**
   * Search TOEFL Scores
   */
  public function actionIndex(){
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path('scores/toefl'));
    $field = $form->newField();
    $field->setLegend('Search TOEFL Scores');
    
    $element = $field->newElement('TextInput','firstName');
    $element->setLabel('First Name');
    $element = $field->newElement('TextInput','lastName');
    $element->setLabel('Last Name');
    
    $form->newButton('submit', 'Search');
    $this->setVar('form', $form);
    if($input = $form->processInput($this->post)){
      $results = $this->_em->getRepository('\Jazzee\Entity\TOEFLScore')->findByName($input->get('firstName') . '%',$input->get('lastName') . '%');
      $this->setVar('results', $results);
    }
  }
}
?>