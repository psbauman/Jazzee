<?php
namespace Jazzee\PaymentType;
require_once __DIR__ . '/../../../lib/anet_sdk/AuthorizeNet.php'; 
/**
 * Pay via Authorize.net Direct POst Method
 * Form is posted directly to authorize.net
 * anet the posts results to the transaction controller 
 * transaction controller statically calls AuthorizeNetDPM::transaction which records the payment and then
 * sends a redirect back to authorize.net who sends teh applicant back to the original page
 * 
 * Use the class if you don't want to meet anythign but the most basic PCI requirements the AIM method is more reliable and configurable
 * 
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage paymenttypes
 */
class AuthorizeNetDPM extends AuthorizeNetAIM{
  /**
   * Display a form which posts to authorize.net's server
   */
  public function paymentForm(\Jazzee\Entity\Applicant $applicant, $amount){
    if(\is_a($this->_controller,'ApplyPageController')){
      $time = time();
      $fp_sequence = $applicant->getId() . $time;
      $form = new \Foundation\Form();

      $form->newHiddenElement('x_amount', $amount);
      $form->newHiddenElement('x_test_request', ($this->_controller->getConfig()->getStatus() == 'PRODUCTION')?0:1);
      $form->newHiddenElement('x_fp_sequence', $fp_sequence);
      $form->newHiddenElement('x_fp_hash', \AuthorizeNetDPM::getFingerprint($this->_paymentType->getVar('gatewayId'), $this->_paymentType->getVar('gatewayKey'), $amount, $fp_sequence, $time));
      $form->newHiddenElement('x_fp_timestamp', $time);
      $form->newHiddenElement('x_relay_response', "TRUE");
      $form->newHiddenElement('x_relay_url', $this->_controller->getActionPath() . '/../../../../../transaction/' . \urlencode(get_class($this)));
      $form->newHiddenElement('redirect_url', $this->_controller->getActionPath());
      $form->newHiddenElement('x_login', $this->_paymentType->getVar('gatewayId'));

      $form->newHiddenElement('x_cust_id', $applicant->getId());
      $form->newHiddenElement('x_customer_ip', $_SERVER['REMOTE_ADDR']);
      $form->newHiddenElement('x_email', $applicant->getEmail());
      $form->newHiddenElement('x_email_customer', 0);
      $form->newHiddenElement('x_description', $this->_paymentType->getVar('description'));


      $form->setAction($this->_paymentType->getVar('testAccount')?\AuthorizeNetDPM::SANDBOX_URL:\AuthorizeNetDPM::LIVE_URL);
      $field = $form->newField();
      $field->setLegend($this->_paymentType->getName());
      $field->setInstructions("<p><strong>Application Fee:</strong> &#36;{$amount}</p>");

      $e = $field->newElement('TextInput', 'x_card_num');
      $e->setLabel('Credit Card Number');
      $e->addValidator(new \Foundation\Form\Validator\NotEmpty($e));

      $e = $field->newElement('TextInput', 'x_exp_date');
      $e->setLabel('Expiration Date');
      $e->setFormat('mm/yy eg ' . date('m/y'));
      $e->addValidator(new \Foundation\Form\Validator\NotEmpty($e));

      $e = $field->newElement('TextInput', 'x_card_code');
      $e->setLabel('CCV');
      $e->addValidator(new \Foundation\Form\Validator\NotEmpty($e));

      $e = $field->newElement('TextInput', 'x_zip');
      $e->setLabel('Billing Postal Code');
      $e->setInstructions('US Credit Cards which do not provide a postal code will be rejected.');

      $form->newButton('submit', 'Pay with Credit Card');
    } else {
      $form = parent::paymentForm($applicant, $amount);
    }
    return $form;
  }
  
  /**
   * Record transaction information pending
   * $input isn't used here becuase the DPM method uses the post data directly off the global $_POST
   */
  public function pendingPayment(\Jazzee\Entity\Payment $payment, \Foundation\Form\Input $input){
    $input = false;
    $response = new \AuthorizeNetSIM($this->_paymentType->getVar('gatewayId'), $this->_paymentType->getVar('gatewayHash'));
    if($response->isAuthorizeNet()){
      if($response->approved) {
        $payment->setAmount($response->amount);
        $payment->setVar('transactionId', $response->transaction_id);
        $payment->setVar('authorizationCode', $response->authorization_code);
        $payment->pending();
        return true;
      } else {
        $payment->setAmount($response->amount);
        $payment->setVar('transactionId', $response->transaction_id);
        $payment->setVar('rejectedReasonCode', $response->response_reason_code);
        $payment->setVar('rejectedReason', $response->response_reason_text);
        $payment->rejected();
      }
    }
    return false;
  }
  
  public function setup(\Foundation\Form\Input $input){
    parent::setup($input);
    $this->_paymentType->setClass('\\Jazzee\\PaymentType\AuthorizeNetDPM');
  }
  
  /**
   * Parse the transaction results sent from Authorize.net Direct Post
   * @param \TransactionController $transactionController
   */
  public static function transaction($transactionController){
    $applicant = $transactionController->getEntityManager()->getRepository('\Jazzee\Entity\Applicant')->find($_POST['x_cust_id']);
    if(!$applicant) throw new \Jazzee\Exception("{$_POST['x_cust_id']} is not a valid applicant id.  Anet post: " . var_export($_POST, true));
    $matches = array();
    preg_match('#page/(\d{1,})/?#', $_POST['redirect_url'], $matches);
    if(!isset($matches[1])) throw new \Jazzee\Exception("No page id match found in redirect_url: '{$_POST['redirect_url']}");
    $applicationPage = $transactionController->getEntityManager()->getRepository('\Jazzee\Entity\ApplicationPage')->find($matches[1]);
    if(!$applicationPage) throw new \Jazzee\Exception("{$matches[1]} is not a valid applicationPage id");
    $answer = new \Jazzee\Entity\Answer();
    $answer->setPage($applicationPage->getPage());
    $applicant->addAnswer($answer);
    
    $payment = new \Jazzee\Entity\Payment();
    $payment->setType($transactionController->getEntityManager()->getRepository('\Jazzee\Entity\PaymentType')->find($_POST['paymentType']));
    $answer->setPayment($payment);
    $fakeInput = new \Foundation\Form\Input(array());
    $result = $payment->getType()->getJazzeePaymentType($transactionController)->pendingPayment($payment, $fakeInput);
    $transactionController->getEntityManager()->persist($applicant);
    $transactionController->getEntityManager()->persist($answer);
    $transactionController->getEntityManager()->persist($payment);
    foreach($payment->getVariables() as $var) $transactionController->getEntityManager()->persist($var);
    $transactionController->getEntityManager()->flush();
    print \AuthorizeNetDPM::getRelayResponseSnippet($_POST['redirect_url']);
  }
  
  /**
   * Attempt to settle payments
   * @param AdminCronController $cron
   */
  public static function runCron(\AdminCronController $cron){
    if(time() - (int)$cron->getVar('authorizeNetDpmPaymentLastRun') > self::MIN_CRON_INTERVAL){
      $cron->setVar('authorizeNetDpmPaymentLastRun', time());
      $paymentType = $cron->getEntityManager()->getRepository('\Jazzee\Entity\PaymentType')->findOneBy(array('class'=>'\Jazzee\PaymentType\AuthorizeNetDPM'));
      $count = 0;
      if($paymentType){
        $payments = $cron->getEntityManager()->getRepository('\Jazzee\Entity\Payment')->findBy(array('type'=>$paymentType->getId(), 'status'=>\Jazzee\Entity\Payment::PENDING),array(), 100);
        $fakeInput = new \Foundation\Form\Input(array());
        foreach($payments as $payment){
          $result = $paymentType->getJazzeePaymentType($cron)->settlePayment($payment, $fakeInput);
          if($result === true){
            $count++;
            $cron->getEntityManager()->persist($payment);
            foreach($payment->getVariables() as $var) $cron->getEntityManager()->persist($var);
          }
        }
      }
      if($count) $cron->log("Settled {$count} AuthorizeNetDpm Payments.");
    }
  }
}