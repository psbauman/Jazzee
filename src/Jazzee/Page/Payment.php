<?php
namespace Jazzee\Page;
/**
 * Payment Page
 * 
 * Branching form to select payment type
 * 
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage pages
 */
class Payment extends AbstractPage {
  /**
   * The payment type and the amount are selected first
   * then we display the form for the payment type
   * @see StandardPage::makeForm()
   */
  protected function makeForm(){
    $form = new \Foundation\Form;
    $form->setAction($this->_controller->getActionPath());
    $form->setCSRFToken($this->_controller->getCSRFToken());
    $field = $form->newField();
    $field->setLegend($this->_applicationPage->getTitle());
    $field->setInstructions($this->_applicationPage->getInstructions());
    
    $element = $field->newElement('SelectList', 'paymentType');
    $element->setLabel('Payment Method');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $allowedTypes = explode(',',$this->_applicationPage->getPage()->getVar('allowedPaymentTypes'));
    $paymentTypes = $this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\PaymentType')->findBy(array('isExpired'=>false), array('name' => 'ASC'));
    foreach($paymentTypes as $type){
      if($this->_controller instanceof \Jazzee\AdminController or in_array($type->getId(), $allowedTypes)) $element->newItem($type->getId(), $type->getName());
    }
    $element = $field->newElement('RadioList', 'amount');
    $element->setLabel('Type of payment');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    for($i = 1; $i<=$this->_applicationPage->getPage()->getVar('amounts'); $i++){
      $element->newItem($this->_applicationPage->getPage()->getVar('amount'.$i), $this->_applicationPage->getPage()->getVar('description'.$i));
    }
    $form->newHiddenElement('level', 1);
    $form->newButton('submit', 'Select');
    return $form;
  }
  
  /**
   * Validate form input from either the intial payment selection form
   * or the ApplyPayment form
   * @see StandardPage::validateInput()
   */
  public function validateInput($input){
    if($input['level'] == 1){
      $result = $this->getForm()->processInput($input);
      //if there is an problem with the input then do what we would normally do
      if(!$result) return false;
    }
    //we are eithier processing a good choice of payment and amount or the input from an \Jazzee\Payment form
    //eithier way we need to create the apply payment form
    $this->_form = $this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\PaymentType')->find($input['paymentType'])->getJazzeePaymentType($this->_controller)->paymentForm($this->_applicant, $input['amount']);
    
    $this->_form->setCSRFToken($this->_controller->getCSRFToken());
    $this->_form->newHiddenElement('level', 2);
    $this->_form->newHiddenElement('paymentType',$input['paymentType']);
    
    //if we were processing a good choice of payment and amount we now return false so the newly created form can be displayed to the applicant
    if($input['level'] == 1) return false;
    //otherwise we process the input from the ApplyPayment form
    return $this->_form->processInput($input);
  }
  
  public function newAnswer($input){
    $answer = new \Jazzee\Entity\Answer();
    $answer->setPage($this->_applicationPage->getPage());
    $this->_applicant->addAnswer($answer);
    $payment = new \Jazzee\Entity\Payment();
    $payment->setType($this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\PaymentType')->find($input->get('paymentType')));
    $answer->setPayment($payment);
    $result = $payment->getType()->getJazzeePaymentType($this->_controller)->pendingPayment($payment, $input);
    
    if($result){
      $this->_controller->addMessage('success', 'Your payment has been recorded.');
      $this->_form = null;
    } else {
      $this->_controller->addMessage('error', 'There was a problem processing your payment.');
    }
    $this->_controller->getEntityManager()->persist($answer);
    $this->_controller->getEntityManager()->persist($payment);
    foreach($payment->getVariables() as $var) $this->_controller->getEntityManager()->persist($var);
    return $result;
  }
  
  public function updateAnswer($input, $answerID){
    return false;
  }
  
  public function deleteAnswer($answerId){
    //can't delete payment answers
    return;
  }
  
  public function fill($answerId){
    //no edit so not fill
  }
  
  public function getAnswers(){
    return $this->_applicant->findAnswersByPage($this->_applicationPage->getPage());
  }
  
  public function getXmlAnswers(\DOMDocument $dom){
    $answers = array();
    foreach($this->_applicant->findAnswersByPage($this->_applicationPage->getPage()) as $answer){
      $payment = $answer->getPayment();
      $answerXml = $dom->createElement('payments');
      $answerXml->setAttribute('answerId', $answer->getId());
      $answerXml->setAttribute('updatedAt', $answer->getUpdatedAt()->format('c'));
      $eXml = $dom->createElement('payment');
      $eXml->setAttribute('type', $payment->getType()->getName());
      $eXml->setAttribute('status', $payment->getStatus());
      $eXml->appendChild($dom->createCDATASection($payment->getAmount()));
      $answerXml->appendChild($eXml);
      $answers[] = $answerXml;
    }
    return $answers;
  }
  
  /**
   * Setup the default variables
   */
  public function setupNewPage(){
    $defaultVars = array(
      'amounts' => 0,
      'allowedPaymentTypes' => ''
    );
    foreach($defaultVars as $name=>$value){
      $var = $this->_applicationPage->getPage()->setVar($name, $value);
      $this->_controller->getEntityManager()->persist($var);
    }    
  }
  
  /**
   * Create a table from answers
   * and append any attached PDFs
   * @param \Jazzee\ApplicantPDF $pdf 
   */
  public function renderPdfSection(\Jazzee\ApplicantPDF $pdf){
//    if($this->getAnswers()){
//      $pdf->addText($this->_applicationPage->getTitle(), 'h3');
//      $pdf->write();
//      $pdf->startTable();
//      $pdf->startTableRow();
//      $pdf->addTableCell('Payment');
//      $pdf->addTableCell('Status');
//      foreach($this->getAnswers() as $answer){
//        $pdf->startTableRow();
//        $payment = $answer->getPayment();
//        $string  = 'Type: ' . $payment->getType()->getName() . "\n";
//        $string .= 'Amount: $' . $payment->getAmount();
//        $pdf->addTableCell($string);
//
//        $class = $payment->getType()->getClass();
//        switch($payment->getStatus()){
//          case \Jazzee\Entity\Payment::PENDING:
//            $applicantStatus = $class::PENDING_TEXT;
//            break;
//          case \Jazzee\Entity\Payment::SETTLED:
//            $applicantStatus = $class::SETTLED_TEXT;
//            break;
//          case \Jazzee\Entity\Payment::REJECTED:
//            $applicantStatus = $class::REJECTED_TEXT;
//            $applicantStatus .= "\nReason: " . $payment->getVar('rejectedReason');
//            break;
//          case \Jazzee\Entity\Payment::REFUNDED:
//            $applicantStatus = $class::REFUNDED_TEXT;
//            $applicantStatus .= "\nReason: " . $payment->getVar('refundReason');
//            break;
//        }
//        $pdf->addTableCell($applicantStatus);
//      }
//      $pdf->writeTable();
//    }
//    
    //probably better if we just do nothing for payment
  }
  
  public function getStatus(){
    $answers = $this->getAnswers();
    //Only complete for pendign and settled payments
    foreach($answers as $answer){
      if($answer->getPayment()->getStatus() == \Jazzee\Entity\Payment::SETTLED OR $answer->getPayment()->getStatus() == \Jazzee\Entity\Payment::PENDING) return self::COMPLETE;
    }
    //if none of the payments are pending or settled we are incomplete
    return self::INCOMPLETE;
  }
  
  public static function applyPageElement(){
    return 'Payment-apply_page';
  }
  
  public static function pageBuilderScriptPath(){
    return 'resource/scripts/page_types/JazzeePagePayment.js';
  }
  
  public static function applicantsSingleElement(){
   return 'Payment-applicants_single';
 }
  
  /**
   * Rus cron jobs for payments types
   * @param AdminCronController $cron
   */
  public static function runCron(\AdminCronController $cron){
    foreach($cron->getEntityManager()->getRepository('\Jazzee\Entity\PaymentType')->findAll() as $paymentType){
      $class = $paymentType->getClass();
      if(method_exists($class, 'runCron')){
        $class::runCron($cron);
      }
    }
  }
}