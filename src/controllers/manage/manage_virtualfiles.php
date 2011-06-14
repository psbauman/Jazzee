<?php
/**
 * Manage Virtual Files
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage manage
 */
class ManageVirtualfilesController extends \Jazzee\AdminController {
  const MENU = 'Manage';
  const TITLE = 'Virtual Files';
  const PATH = 'manage/virtualfiles';
  
  const ACTION_INDEX = 'View Files';
  const ACTION_EDIT = 'New File';
  const ACTION_NEW = 'Replace File';
  const ACTION_DELETE = 'Delete File';
  
  /**
   * List cycles
   */
  public function actionIndex(){
    $this->setVar('files', $this->_em->getRepository('\Jazzee\Entity\VirtualFile')->findAll());
  }
  
  /**
   * Edit a file
   * @param integer $filId
   */
   public function actionEdit($fileId){ 
    if($file = $this->_em->getRepository('\Jazzee\Entity\VirtualFile')->find($fileId)){
      $form = new \Foundation\Form;
      
      $form->setAction($this->path("manage/virtualfiles/edit/" . $file->getId()));
      $field = $form->newField();
      $field->setLegend('Edit Virtual File: ' . $file->getName());
      $element = $field->newElement('TextInput','name');
      $element->setLabel('File Name');
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
      $element->setValue($file->getName());
      
      $element = $field->newElement('FileInput','contents');
      $element->setLabel('File');
      $element->setFormat('Leave blank to keep existing file');
      $element->addFilter(new \Foundation\Form\Filter\Blob($element));
      
  
      $form->newButton('submit', 'Save Changes');
      $this->setVar('form', $form);  
      if($input = $form->processInput($this->post)){
        $file->setName($input->get('name'));
        if($input->get('contents')) $file->setContents($input->get('contents'));
        $this->_em->persist($file);
        $this->addMessage('success', "Changes Saved Successfully");
        $this->redirectPath('manage/virtualfiles');
      }
    } else {
      $this->addMessage('error', "Error: File #{$fileId} does not exist.");
    }
  }
   
  /**
   * Create a new virtual file
   */
  public function actionNew(){
    $form = new \Foundation\Form;
      
    $form->setAction($this->path("manage/virtualfiles/new"));
    $field = $form->newField();
    $field->setLegend('New Virtual File');
    $element = $field->newElement('TextInput','name');
    $element->setLabel('File Name');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addFilter(new \Foundation\Form\Filter\UrlSafe($element));
    
    $element = $field->newElement('FileInput','contents');
    $element->setLabel('File');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addFilter(new \Foundation\Form\Filter\Blob($element));

    $form->newButton('submit', 'Add File');
    $this->setVar('form', $form);  
    if($input = $form->processInput($this->post)){
      $file = new \Jazzee\Entity\VirtualFile();
      $file->setName($input->get('name'));
      $file->setContents($input->get('contents'));
      $this->_em->persist($file);
      $this->addMessage('success', "New Virtual File Saved");
      $this->redirectPath('manage/virtualfiles');
    }
  }
}
?>