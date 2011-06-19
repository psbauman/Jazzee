<?php
namespace Jazzee\Entity\PaymentType;
require_once __DIR__ . '/../../../../lib/anet_sdk/AuthorizeNet.php'; 
/**
 * Pay via Authorize.net Advanced Integration Method
 */
class AuthorizeNetAIM extends AbstractPaymentType{
  const PENDING_TEXT = 'Approved';
  const SETTLED_TEXT = 'Approved';
  const REJECTED_TEXT = 'Rejected or Voided';
  const REFUNDED_TEXT = 'Refunded';
  
  /**
   * Display the button to pass applicant to Authorize.net's hosted payment page
   * @see ApplyPayment::paymentForm()
   */
  public function paymentForm(\Jazzee\Entity\Applicant $applicant, $amount, $actionPath){
    $form = new \Foundation\Form();
    //we pass the amount back as a hidden element so PaymentPage will have it again
    $form->newHiddenElement('amount', $amount);

    $form->setAction($actionPath);
    $field = $form->newField();
    $field->setLegend($this->_paymentType->getName());
    $field->setInstructions("<p><strong>Application Fee:</strong> &#36;{$amount}</p>");
    
    $e = $field->newElement('TextInput', 'cardNumber');
    $e->setLabel('Credit Card Number');
    $e->addValidator(new \Foundation\Form\Validator\NotEmpty($e));
    $e->addValidator(new \Foundation\Form\Validator\CreditCard($e, explode(',',$this->_paymentType->getVar('acceptedCards'))));
    
    $e = $field->newElement('ShortDateInput', 'expirationDate');
    $e->setLabel('Expiration Date');
    $e->addValidator(new \Foundation\Form\Validator\NotEmpty($e));
    $e->addValidator(new \Foundation\Form\Validator\DateAfter($e, 'last month'));
    $e->addFilter(new \Foundation\Form\Filter\DateFormat($e, 'mY'));
    
    $e = $field->newElement('TextInput', 'cardCode');
    $e->setLabel('CCV');
    $e->addValidator(new \Foundation\Form\Validator\NotEmpty($e));
    
    $e = $field->newElement('TextInput', 'postalCode');
    $e->setLabel('Billing Postal Code');
    $e->setInstructions('US Credit Cards which do not provide a postal code will be rejected.');

    $form->newButton('submit', 'Pay with Credit Card');
    return $form;
  }
  
  /**
   * Setup the payment types and the AIM credentials
   * @see ApplyPayment::setupForm()
   */
  public function getSetupForm(){
    $form = new \Foundation\Form();
    $field = $form->newField();
    $field->setLegend('Setup Authorize.net AIM Payments');        
    $element = $field->newElement('TextInput','name');
    $element->setLabel('Payment Name');
    $element->setValue($this->_paymentType->getName());
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $element = $field->newElement('TextInput','description');
    $element->setLabel('Description');
    $element->setFormat('Appears on credit card statement for applicant');
    $element->setValue($this->_paymentType->getVar('description'));
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $element = $field->newElement('TextInput','gatewayId');
    $element->setLabel('Payment Gateway ID');
    $element->setValue($this->_paymentType->getVar('gatewayId'));
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $element = $field->newElement('TextInput','gatewayKey');
    $element->setLabel('Payment Gateway Key');
    $element->setValue($this->_paymentType->getVar('gatewayKey'));
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $element = $field->newElement('TextInput','gatewayHash');
    $element->setLabel('Payment Gateway Hashphrase');
    $element->setValue($this->_paymentType->getVar('gatewayHash'));
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $element = $field->newElement('RadioList','testAccount');
    $element->setLabel('Is this a test account?');
    $element->setFormat('Test accounts are handled differenty by Authorize.net and need to be sent to a different URL.');
    $element->setValue($this->_paymentType->getVar('testAccount'));
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->newItem(0, 'No');
    $element->newItem(1, 'Yes');

    $element = $field->newElement('CheckboxList','acceptedCards');
    $element->setLabel('What credit card types do you accept?');
    $types = \Foundation\Form\Validator\CreditCard::listTypes();
    foreach($types as $id => $value){
      $element->newitem($id, $value);
    }
    $element->setValue(explode(',',$this->_paymentType->getVar('acceptedCards')));
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $form->newButton('submit', 'Save');
    return $form;
  }
  
  public function setup(\Foundation\Form\Input $input){
    $this->_paymentType->setName($input->get('name'));
    $this->_paymentType->setClass('\\Jazzee\\Entity\\PaymentType\AuthorizeNetAim');
    $this->_paymentType->setVar('description', $input->get('description'));
    $this->_paymentType->setVar('gatewayId', $input->get('gatewayId'));
    $this->_paymentType->setVar('gatewayKey', $input->get('gatewayKey'));
    $this->_paymentType->setVar('gatewayHash', $input->get('gatewayHash'));
    $this->_paymentType->setVar('testAccount', $input->get('testAccount'));
    $this->_paymentType->setVar('acceptedCards', implode(',',$input->get('acceptedCards')));
  }
  
  /**
   * Record transaction information pending until it is settled with the bank
   * @see ApplyPaymentInterface::pendingPayment()
   */
  public function pendingPayment(\Jazzee\Entity\Payment $payment, \Foundation\Form\Input $input){
    $aim = new \AuthorizeNetAIM($this->_paymentType->getVar('gatewayId'), $this->_paymentType->getVar('gatewayKey'));
    $aim->setSandBox($this->_paymentType->getVar('testAccount')); //test accounts get sent to the sandbox
    $aim->amount = $input->get('amount');
    $aim->cust_id = $payment->getAnswer()->getApplicant()->getId();
    $aim->customer_ip = $_SERVER['REMOTE_ADDR'];
    $aim->email = $payment->getAnswer()->getApplicant()->getEmail();
    $aim->email_customer = 0;
    $aim->card_num = $input->get('cardNumber');
    $aim->exp_date = $input->get('expirationDate');
    $aim->card_code = $input->get('cardCode');
    $aim->zip = $input->get('cardCode');
    $aim->description = $this->_paymentType->getVar('description');
    $config = new \Jazzee\Configuration();
    $aim->test_request = ($config->getStatus() == 'PRODUCTION')?0:1;
    $response = $aim->authorizeAndCapture();
    if($response->approved) {
      $payment->setAmount($response->amount);
      $payment->setVar('transactionId', $response->transaction_id);
      $payment->setVar('authorizationCode', $response->authorization_code);
      $payment->pending();
      return true;
    } else {
      $payment->setAmount($response->amount);
      $payment->setVar('transactionId', $response->transaction_id);
      $payment->setVar('reasonCode', $response->response_reason_code);
      $payment->setVar('reasonText', $response->response_reason_text);
      $payment->rejected();
      return false;
    }
  }
  
  /**
   * Attempt to settle payment with anet's API
   * @see ApplyPaymentInterface::settlePaymentForm()
   */
  public function getSettlePaymentForm(\Jazzee\Entity\Payment $payment){
    $form = new Form;
    $field = $form->newField(array('legend'=>"Settle {$this->paymentType->name} Payment"));
    $element = $field->newElement('Plaintext','info');
    $element->value = "{$this->paymentType->name} transactions have to be settled by Authorize.net.  To check the status of this payment click 'Attempt Settlement'";
    $form->newButton('submit', 'Attempt Settlement');
    return $form;
  }
  
  /**
   * Once checks have been cashed we settle the payment
   * @see ApplyPaymentInterface::settlePayment()
   */
  public function settlePayment(\Jazzee\Entity\Payment $payment, \Foundation\Form\Input $input){
    $td = new AuthorizeNetTD($this->paymentType->getVar('gatewayId'), $this->paymentType->getVar('gatewayKey'));
    $td->setSandBox($this->paymentType->getVar('testAccount')); //test accounts get sent to the sandbox
    // Get Transaction Details
    $transactionId = $payment->getVar('transactionId');
    $response = $td->getTransactionDetails($transactionId);
    if($response->isError())
      throw new Jazzee_Exception("Unable to get transaction details for {$payment->id} transcation id {$transactionId}", E_ERROR, 'There was a problem getting payment information.');
    //has this transaction has been settled already
    if($response->xml->transaction->transactionStatus == 'settledSuccessfully'){
      $payment->settled();
      $payment->setVar('settlementTimeUTC', (string)$response->xml->transaction->batch->settlementTimeUTC);
      $payment->save();
      return true;
    } else if($response->xml->transaction->transactionStatus == 'voided'){
      $payment->rejected();
      if(isset($input->reason))
        $payment->setVar('rejectedReason', $input->reason);
      else
        $payment->setVar('rejectedReason', 'This payment was voided.');
      $payment->save();
      return true;
    }
    return false;
  }
  
  /**
   * Record the reason the payment was rejected
   * @see ApplyPaymentInterface::rejectPaymentForm()
   */
  public function getRejectPaymentForm(\Jazzee\Entity\Payment $payment){
    $form = new Form;
    $field = $form->newField(array('legend'=>"Reject {$this->paymentType->name} Payment"));        
    $element = $field->newElement('Textarea','reason');
    $element->label = 'Reason displayed to Applicant';
    $element->addValidator('NotEmpty');
    
    $form->newButton('submit', 'Save');
    return $form;
  }
  
  /**
   * Void a transaction before it is settled
   * @see ApplyPaymentInterface::rejectPayment()
   */
  public function rejectPayment(\Jazzee\Entity\Payment $payment, \Foundation\Form\Input $input){
    $aim = new AuthorizeNetAIM($this->paymentType->getVar('gatewayId'), $this->paymentType->getVar('gatewayKey'));
    $aim->setSandBox($this->paymentType->getVar('testAccount')); //test accounts get sent to the sandbox
    $aim->test_request = $this->config->status == 'PRODUCTION'?0:1;
    $response = $aim->void($payment->getVar('transactionId'));
    if($response->approved) {
      $payment->rejected();
      $payment->setVar('reasonText', $input->reason);
      $payment->save();
      return true;
    }
    //if we cant void we are probably already settled so try and settle the payment in our system
    return $this->settlePayment($payment, $input);
  }
  
  /**
   * Record the reason the payment was refunded
   * @see ApplyPaymentInterface::rejectPaymentForm()
   */
  public function getRefundPaymentForm(\Jazzee\Entity\Payment $payment){
    $td = new AuthorizeNetTD($this->paymentType->getVar('gatewayId'), $this->paymentType->getVar('gatewayKey'));
    $td->setSandBox($this->paymentType->getVar('testAccount')); //test accounts get sent to the sandbox
    // Get Transaction Details
    $transactionId = $payment->getVar('transactionId');
    $response = $td->getTransactionDetails($transactionId);
    if($response->isError())
      throw new Jazzee_Exception("Unable to get transaction details for {$payment->id} transcation id {$transactionId}", E_ERROR, 'There was a problem getting payment information.');
      
    $form = new Form;
    $field = $form->newField(array('legend'=>"Refund {$this->paymentType->name} Payment"));      
    $element = $field->newElement('Plaintext', 'details');
    $element->label = 'Details';
    $element->value = "Refund \${$payment->amount} to card " . $response->xml->transaction->payment->creditCard->cardNumber;  
    $element = $field->newElement('Textarea','reason');
    $element->label = 'Reason displayed to Applicant';
    $element->addValidator('NotEmpty');
    
    $form->newHiddenElement('cardNumber', substr($response->xml->transaction->payment->creditCard->cardNumber, strlen($response->xml->transaction->payment->creditCard->cardNumber)-4, 4));
    $form->newButton('submit', 'Save');
    return $form;
  }
  
  /**
   * Check payments are refunded outside Jazzee and then marked as refunded
   * @see ApplyPaymentInterface::refundPayment()
   */
  public function refundPayment(\Jazzee\Entity\Payment $payment, \Foundation\Form\Input $input){
    $aim = new AuthorizeNetAIM($this->paymentType->getVar('gatewayId'), $this->paymentType->getVar('gatewayKey'));
    $aim->setSandBox($this->paymentType->getVar('testAccount')); //test accounts get sent to the sandbox
    $aim->test_request = $this->config->status == 'PRODUCTION'?0:1;
    $response = $aim->credit($payment->getVar('transactionId'), $payment->amount, $input->cardNumber);
    if($response->approved) {
      $payment->refunded();
      $payment->setVar('reasonText', $input->reason);
      $payment->save();
      return true;
    }
    return false;
  }
  
  public function applicantTools(\Jazzee\Entity\Payment $payment){
    $arr = array();
    switch($payment->status){
      case Payment::PENDING:
        $arr[] = array(
          'title' => 'Settle Payment',
          'class' => 'settlePayment',
          'path' => "settlePayment/{$payment->id}"
        );
        $arr[] = array(
          'title' => 'Reject Payment',
          'class' => 'rejectPayment',
          'path' => "rejectPayment/{$payment->id}"
        );
        break;
      case Payment::SETTLED:
        $arr[] = array(
          'title' => 'Refund Payment',
          'class' => 'refundPayment',
          'path' => "refundPayment/{$payment->id}"
        );
    }
    return $arr;
  }
}