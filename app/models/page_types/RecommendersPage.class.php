<?php
/**
 * Get recommendation information from applicants and send out invitations
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
class RecommendersPage extends StandardPage {
  /**
   * The time to wait between sending emails to recommenders in seconds
   * @cons integer 2 weeks (86400 * 14)
   */
  const RECOMMENDATION_EMAIL_WAIT_TIME = 1209600;
  
  public function newAnswer($input){
    $a = $this->applicant->Answers->get(null);
    $a->pageID = $this->applicationPage->Page->id;
    $lor = $a->Children->get(null); 
    $lor->pageID = $this->applicationPage->Page->Children->getFirst()->id;
    $answer = new RecommendationAnswer($a);
    $answer->update($input);
    $this->applicant->save();
    $this->form->applyDefaultValues();
    return true;
  }
  
  public function updateAnswer($input, $answerID){
    if($a = $this->applicant->getAnswerByID($answerID)){
      $answer = new RecommendationAnswer($a);
      $answer->update($input);
      $a->save();
      $this->form->applyDefaultValues();
    }
  }
  
  /**
   * Send the invitaiton email
   * @param integer $answerID
   */
  public function sendEmail($answerID){
    if($a = $this->applicant->getAnswerByID($answerID)){
      if(!$a->locked OR (empty($a->Children->getFirst()->Elements) AND time() - strtotime($this->answer->updatedAt) > RecommendersPage::RECOMMENDATION_EMAIL_WAIT_TIME)){
        $answer = new RecommendationAnswer($a);
        $answer->sendEmail();
        $this->applicant->save();
        return true;
      }
    }
  }

  public function fill($answerID){
    if($a = $this->applicant->getAnswerByID($answerID)){
      $answer = new RecommendationAnswer($a);
      foreach($answer->getElements() as $id => $element){
        $value = $answer->getFormValueForElement($id);
        if($value) $this->form->elements['el' . $id]->value = $value;
      }
    }
  }
  
  public function getAnswers(){
    $answers = array();
    foreach($this->applicant->getAnswersForPage($this->applicationPage->Page->id) as $a){
      $answers[] = new RecommendationAnswer($a);
    }
    return $answers;
  }
  
  /**
   * Create the recommenders form
   * @param Page $page
   */
  public static function setupNewPage(Page $page){
    $types = Doctrine::getTable('ElementType')->findAll(Doctrine::HYDRATE_ARRAY);
    $elementTypes = array();
    foreach($types as $type){
      $elementTypes[$type['class']] = $type['id'];
    };
    foreach(array('firstName'=>'First Name','lastName'=>'Last Name','institution'=>'Institution','email'=>'Email Address','phone'=>'Phone Number') as $name => $title){
      $element = $page->Elements->get(null);
      $element->elementType = $elementTypes['TextInputElement'];
      $element->title = $title;
      $element->required = true;
      $element->save();
      $page->setVar("{$name}Element", $element->id);
    }
    $element = $page->Elements->get(null);
    $element->elementType = $elementTypes['RadioListElement'];
    $element->title = 'Do you waive your right to view this letter at a later time?';
    $element->required = true;
    $element->save();
    $page->setVar("waiveRightElement", $element->id);
    $item = $element->ListItems->get(null);
    $item->value = 'No';
    $item->save();
    $item = $element->ListItems->get(null);
    $item->value = 'Yes';
    $item->save();
    
    
    $page->save();
  }
}

/**
 * Answer for Recommendations
 */
class RecommendationAnswer extends StandardAnswer {

  public function update(FormInput $input){
    //PHPs uniquid function is time based and therefor guessable
    //A stright random MD5 sum is too long for email and tends to line break causing usability problems for the recommender
    //So we get unique through uniquid and we get random by prefixing it with a part of an MD5
    //hopefully this results in a URL friendly short, but unguessable string
    $string = '';
    foreach($this->elements as $id => $element){
      $string .= $input->{'el'.$id};
    }
    $string = mt_rand() . $string . mt_rand();
    $prefix = substr(md5($string),rand(0,24), rand(6,8));
    $this->answer->uniqueID = uniqid($prefix);
    parent::update($input);
  }
  
  public function applyTools($basePath){
    $arr = array();
    if(!$this->answer->locked){
      $arr = parent::applyTools($basePath);
      $arr['Send Invitation'] = "{$basePath}/do/sendEmail/{$this->answer->id}";
      //if there is no recommendation response and it has been more than the required elapsed time allow the email to be resent.
    } else if(empty($this->answer->Children->getFirst()->Elements) AND time() - strtotime($this->answer->updatedAt) > RecommendersPage::RECOMMENDATION_EMAIL_WAIT_TIME){
      $arr['Resend Invitation'] = "{$basePath}/do/sendEmail/{$this->answer->id}";
    }
    return $arr;
  }
  
  public function applyStatus(){
    $arr = parent::applyStatus();
    if(!empty($this->answer->Children->getFirst()->Elements)){
      $arr['Status'] = 'This recommendation was recieved on ' . date('l F jS Y g:ia', strtotime($this->answer->Children->getFirst()->updatedAt));
    } else if($this->answer->locked){
      $arr['Invitation Sent'] = date('l F jS Y g:ia', strtotime($this->answer->updatedAt));
      $arr['Status'] = 'You cannot make changes to this recommendation becuase the invitation has already been sent.  You will be able to resend the invitation in ' . (floor((time() - strtotime($this->answer->updatedAt) + RecommendersPage::RECOMMENDATION_EMAIL_WAIT_TIME)/86400)) . ' days';
    }
    return $arr;
  }
  
  /**
   * Send invitation email to the recommender
   */
  public function sendEmail(){
    $mail = JazzeeMail::getInstance();
    $search = array(
     '%APPLICANT_NAME%',
     '%DEADLINE%',
     '%LINK%',
     '%PROGRAM_CONTACT_NAME%',
     '%PROGRAM_CONTACT_EMAIL%',
     '%PROGRAM_CONTACT_PHONE%',
     '%RECOMMENDER_FIRST_NAME%',
     '%RECOMMENDER_LAST_NAME%',
     '%RECOMMENDER_INSTITUTION%',
     '%RECOMMENDER_EMAIL%',
     '%RECOMMENDER_PHONE%',
     '%APPLICANT_WAIVE_RIGHT%'
    );
    if($this->answer->Page->getVar('lorDeadline')){
      $deadline = strtotime($this->answer->Page->getVar('lorDeadline'));
    } else {
      $deadline = strtotime($this->answer->Applicant->Application->close);
    }
    $replace = array(
     "{$this->answer->Applicant->firstName} {$this->answer->Applicant->lastName}",
     date('l F jS Y g:ia', $deadline),
     $mail->path('lor/' . $this->answer->uniqueID),
     $this->answer->Applicant->Application->contactName,
     $this->answer->Applicant->Application->contactEmail,
     $this->answer->Applicant->Application->contactPhone
    );
    $replace[] = $this->getDisplayValueForElement($this->answer->Page->getVar('firstNameElement'));
    $replace[] = $this->getDisplayValueForElement($this->answer->Page->getVar('lastNameElement'));
    $replace[] = $this->getDisplayValueForElement($this->answer->Page->getVar('institutionElement'));
    $replace[] = $this->getDisplayValueForElement($this->answer->Page->getVar('emailElement'));
    $replace[] = $this->getDisplayValueForElement($this->answer->Page->getVar('phoneElement'));
    $replace[] = $this->getDisplayValueForElement($this->answer->Page->getVar('waiveRightElement'));
    $text = str_ireplace($search, $replace, $this->answer->Page->getVar('recommenderEmail'));

    $message = new EmailMessage;
    $message->to($this->getDisplayValueForElement($this->answer->Page->getVar('emailElement')), '');
    $message->from($this->answer->Applicant->Application->contactEmail, $this->answer->Applicant->Application->contactName);
    $message->subject = 'Letter of Recommendation Request';
    $message->body = $text;
    if(!$mail->send($message)){
      return false;
    }
    $this->answer->locked = true;
    $this->answer->save();
    return true;
  }
}
?>