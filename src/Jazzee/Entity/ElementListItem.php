<?php
namespace Jazzee\Entity;

/** 
 * ElementListItem
 * Elements like selects and checkboxes have list items
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="element_list_items") 
 * @package    jazzee
 * @subpackage orm
 **/
class ElementListItem{
  /**
    * @Id 
    * @Column(type="bigint")
    * @GeneratedValue(strategy="AUTO")
  */
  private $id;
  
  /** 
   * @ManyToOne(targetEntity="Element",inversedBy="listItems")
   * @JoinColumn(onDelete="CASCADE") 
   */
  private $element;
  
  /** @Column(type="integer") */
  private $weight;
  
  /** @Column(type="boolean") */
  private $active = true;
  
  /** @Column(type="string") */
  private $value;

  /**
   * Get id
   *
   * @return bigint $id
   */
  public function getId(){
    return $this->id;
  }
  
  /**
   * Generate a Temporary id
   *
   * This should only be used when we need to termporarily generate an item 
   * but have no intention of persisting it.  Use a string to be sure we cant persist
   */
  public function tempId(){
    $this->id = uniqid('item');
  }
  
  /**
   * Replace Page UUID
   * @PreUpdate
   * 
   * When an list items is modified it changes its page's UUID
   */
  public function replacePageUuid(){
    $this->element->replacePageUuid();
  }
  
  /**
   * Set element
   *
   * @param Entity\Element $element
   */
  public function setElement(Element $element){
    $this->element = $element;
  }
  
  /**
   * get element
   *
   * @return Entity\Element $element
   */
  public function getElement(){
    return $this->element;
  }

  /**
   * Set weight
   *
   * @param integer $weight
   */
  public function setWeight($weight){
    $this->weight = $weight;
  }

  /**
   * Get weight
   *
   * @return integer $weight
   */
  public function getWeight(){
    return $this->weight;
  }
  
  /**
   * Make item active
   */
  public function activate(){
    $this->active = true;
  }
  
 /**
   * Deactivate item
   */
  public function deactivate(){
    $this->active = false;
  }

  /**
   * Check if item is active
   * @return boolean $active
   */
  public function isActive(){
    return $this->active;
  }

  /**
   * Set value
   *
   * @param string $value
   */
  public function setValue($value){
    $this->value = $value;
  }

  /**
   * Get value
   *
   * @return string $value
   */
  public function getValue(){
    return $this->value;
  }
}