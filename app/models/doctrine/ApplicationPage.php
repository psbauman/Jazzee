<?php
/**
 * ApplicationPage
 * Override all several getters and setters so Page data gets used when it isn't overridden
 * @package    jazzee
 * @subpackage orm
 * @author     Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 */
class ApplicationPage extends BaseApplicationPage{
  
 /**
   * Get the title
   * If the title is not overridde then use the one from Page
   */
  public function getTitle(){
    if(is_null($this->_data['title']) OR ($this->_data['title'] instanceof Doctrine_Null)){
      return $this->Page->title;
    }
    return $this->_get('title');
  }
  
  /**
   * Set the title
   * If this isn't a global page then store the title in Page and not here
   * @param string $value
   */
  public function setTitle($value){
    if(!$this->Page->isGlobal){
      return $this->Page->title = $value;
    }
    return $this->_set('title',$value);
  }
  
 /**
   * Get the min
   */
  public function getMin(){
    if(is_null($this->_data['min']) OR ($this->_data['min'] instanceof Doctrine_Null)){
      return $this->Page->min;
    }
    return $this->_get('min');
  }
  
/**
   * Set the min
   * If this isn't a global page then store the min in Page and not here
   * @param string $value
   */
  public function setMin($value){
    if(!$this->Page->isGlobal){
      return $this->Page->min = $value;
    }
    return $this->_set('min',$value);
  }

 /**
   * Get the max
   */
  public function getMax(){
    if(is_null($this->_data['max']) OR ($this->_data['max'] instanceof Doctrine_Null)){
      return $this->Page->max;
    }
    return $this->_get('max');
  }
  
/**
   * Set the max
   * If this isn't a global page then store the max in Page and not here
   * @param string $value
   */
  public function setMax($value){
    if(!$this->Page->isGlobal){
      return $this->Page->max = $value;
    }
    return $this->_set('max',$value);
  }
  
  /**
   * Get the optional
   */
  public function getOptional(){
    if(is_null($this->_data['optional']) OR ($this->_data['optional'] instanceof Doctrine_Null)){
      return $this->Page->optional;
    }
    return $this->_get('optional');
  }
  
  /**
   * Set the optional
   * If this isn't a global page then store the optional in Page and not here
   * @param string $value
   */
  public function setOptional($value){
    if(!$this->Page->isGlobal){
      return $this->Page->optional = $value;
    }
    return $this->_set('optional',$value);
  }
  
  /**
   * Get the instructions
   */
  public function getInstructions(){
    if(is_null($this->_data['instructions']) OR ($this->_data['instructions'] instanceof Doctrine_Null)){
      return $this->Page->instructions;
    }
    return $this->_get('instructions');
  }
  
  /**
   * Set the instructions
   * If this isn't a global page then store the instructions in Page and not here
   * @param string $value
   */
  public function setInstructions($value){
    if(!$this->Page->isGlobal){
      return $this->Page->instructions = $value;
    }
    return $this->_set('instructions',$value);
  }
  
  /**
   * Get the leadingText
   */
  public function getLeadingText(){
    if(is_null($this->_data['leadingText']) OR ($this->_data['leadingText'] instanceof Doctrine_Null)){
      return $this->Page->leadingText;
    }
    return $this->_get('leadingText');
  }
  
  /**
   * Set the leadingText
   * If this isn't a global page then store the title in Page and not here
   * @param string $value
   */
  public function setLeadingText($value){
    if(!$this->Page->isGlobal){
      return $this->Page->leadingText = $value;
    }
    return $this->_set('leadingText',$value);
  }
  
  /**
   * Get the trailingText
   */
  public function getTrailingText(){
    if(is_null($this->_data['trailingText']) OR ($this->_data['trailingText'] instanceof Doctrine_Null)){
      return $this->Page->trailingText;
    }
    return $this->_get('trailingText');
  }
  
  /**
   * Set the trailingText
   * If this isn't a global page then store the title in Page and not here
   * @param string $value
   */
  public function setTrailingText($value){
    if(!$this->Page->isGlobal){
      return $this->Page->trailingText = $value;
    }
    return $this->_set('trailingText',$value);
  }
  
  /**
   * After we save the applicationPage make sure all of its pages are properly saved too
   * At some point doctrine is unable to follow the relationships deep enough
   * This method explicitly saves the members of collections with the correct id
   */
  public function postSave(){
    if($this->Page->isModified(true)){
      $this->Page->save();
    }
  }
}