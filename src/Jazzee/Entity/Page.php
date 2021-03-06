<?php
namespace Jazzee\Entity;

/** 
 * Page
 * A page is not directly associated with an application - it can be a single case or a global page associated with many applications
 * @Entity(repositoryClass="\Jazzee\Entity\PageRepository")
 * @HasLifecycleCallbacks 
 * @Table(name="pages", 
 * uniqueConstraints={@UniqueConstraint(name="page_uuid",columns={"uuid"})})
 * @package    jazzee
 * @subpackage orm
 **/
class Page{
  /**
    * @Id 
    * @Column(type="bigint")
    * @GeneratedValue(strategy="AUTO")
  */
  private $id;
  
  /** @Column(type="string") */
  private $uuid;
  
  /** @Column(type="string") */
  private $title;
  
  /** 
   * @ManyToOne(targetEntity="PageType")
   * @JoinColumn(onDelete="SET NULL") 
   */
  private $type;
  
  /** @Column(type="boolean") */
  private $isGlobal;
  
  /** 
   * @ManyToOne(targetEntity="Page",inversedBy="children")
   * @JoinColumn(onDelete="CASCADE") 
   */
  private $parent;
  
  /** 
   * @OneToMany(targetEntity="Page", mappedBy="parent")
   */
  private $children;
  
  /** 
   * @OneToMany(targetEntity="PageVariable", mappedBy="page")
   */
  private $variables;

  /** 
   * @OneToMany(targetEntity="Element", mappedBy="page")
   * @OrderBy({"weight" = "ASC"})
   */
  private $elements;
  
  /** @Column(type="integer", nullable=true) */
  private $min;
  
  /** @Column(type="integer", nullable=true) */
  private $max;
  
  /** @Column(type="boolean") */
  private $isRequired;
  
  /** @Column(type="boolean") */
  private $answerStatusDisplay;
  
  /** @Column(type="text", nullable=true) */
  private $instructions;
  
  /** @Column(type="text", nullable=true) */
  private $leadingText;
  
  /** @Column(type="text", nullable=true) */
  private $trailingText;
  
  /**
   * a Generic application Jazzee page we store it so it can be persistent
   * @var \Jazzee\Interfaces\Page 
   */
  private $_applicationPageJazzeePage;
  
  public function __construct(){
    $this->children = new \Doctrine\Common\Collections\ArrayCollection();
    $this->variables = new \Doctrine\Common\Collections\ArrayCollection();
    $this->elements = new \Doctrine\Common\Collections\ArrayCollection();
    $this->isGlobal = false;
    $this->isRequired = true;
    $this->answerStatusDisplay = false;
    $this->replaceUuid();
  }
  
  /**
   * Get id
   *
   * @return bigint $id
   */
  public function getId(){
    return $this->id;
  }
  
  /**
   * Get uuid
   *
   * @return string $uuid
   */
  public function getUuid(){
    return $this->uuid;
  }
  
  /**
   * Set uuid
   *
   * @param string $uuid
   */
  public function setUuid($uuid){
    $this->uuid = $uuid;
  }
  
  /**
   * Generate a Temporary id
   *
   * This should only be used when we need to termporarily generate a page 
   * but have no intention of persisting it.  Use a string to be sure we cant persist
   */
  public function tempId(){
    $this->id = uniqid('page');
  }
  
  /**
   * Replace UUID
   * @PreUpdate
   * UUIDs are designed to be permanent.  
   * You should only replace it if the page is being modified
   */
  public function replaceUuid(){
    $this->uuid = \Foundation\UUID::v4();
    if($this->parent) $this->parent->replaceUuid();
  }

  /**
   * Set title
   *
   * @param string $title
   */
  public function setTitle($title){
    $this->title = $title;
  }

  /**
   * Get title
   *
   * @return string $title
   */
  public function getTitle(){
    return $this->title;
  }

  /**
   * Make page global
   */
  public function makeGlobal(){
    $this->isGlobal = true;
  }
  
  /**
   * UnMake page global
   */
  public function notGlobal(){
    $this->isGlobal = false;
  }

  /**
   * Get Global status
   * @return boolean $isGlobal
   */
  public function isGlobal(){
    return $this->isGlobal;
  }

  /**
   * Set min
   *
   * @param integer $min
   */
  public function setMin($min){
    if(empty($min)) $min = null;
    $this->min = $min;
  }

  /**
   * Get min
   *
   * @return integer $min
   */
  public function getMin(){
    return $this->min;
  }

  /**
   * Set max
   *
   * @param integer $max
   */
  public function setMax($max){
    if(empty($max)) $max = null;
    $this->max = $max;
  }

  /**
   * Get max
   *
   * @return integer $max
   */
  public function getMax(){
    return $this->max;
  }

  /**
   * Make page required
   */
  public function required(){
    $this->isRequired = true;
  }
  
/**
   * Make page optional
   */
  public function optional(){
    $this->isRequired = false;
  }

  /**
   * Get required status
   * @return boolean $required
   */
  public function isRequired(){
    return $this->isRequired;
  }

  /**
   * Show the answer status
   */
  public function showAnswerStatus(){
    $this->answerStatusDisplay = true;
  }
  
/**
   * Hide the answer status
   */
  public function hideAnswerStatus(){
    $this->answerStatusDisplay = false;
  }

  /**
   * Should we show the answer status
   * @return boolean $showAnswerStatus
   */
  public function answerStatusDisplay(){
    return $this->answerStatusDisplay;
  }

  /**
   * Set instructions
   *
   * @param text $instructions
   */
  public function setInstructions($instructions){
    if(empty($instructions)) $instructions = null;
    $this->instructions = $instructions;
  }

  /**
   * Get instructions
   *
   * @return text $instructions
   */
  public function getInstructions(){
    return $this->instructions;
  }

  /**
   * Set leadingText
   *
   * @param text $leadingText
   */
  public function setLeadingText($leadingText){
    if(empty($leadingText)) $leadingText = null;
    $this->leadingText = $leadingText;
  }

  /**
   * Get leadingText
   *
   * @return text $leadingText
   */
  public function getLeadingText(){
    return $this->leadingText;
  }

  /**
   * Set trailingText
   *
   * @param text $trailingText
   */
  public function setTrailingText($trailingText){
    if(empty($trailingText)) $trailingText = null;
    $this->trailingText = $trailingText;
  }

  /**
   * Get trailingText
   *
   * @return text $trailingText
   */
  public function getTrailingText(){
    return $this->trailingText;
  }

  /**
   * Set type
   *
   * @param Entity\PageType $type
   */
  public function setType(PageType $type){
    $this->type = $type;
  }

  /**
   * Get type
   *
   * @return Entity\PageType $type
   */
  public function getType(){
    return $this->type;
  }

  /**
   * Get parent
   *
   * @return Entity\Page $parent
   */
  public function getParent(){
    return $this->parent;
  }
  
  /**
   * Set parent
   *
   * @param Entity\Page $parent
   */
  public function setParent($parent){
    $this->parent = $parent;
  }

  /**
   * Add Child
   *
   * @param \Jazzee\Entity\Page $page
   */
  public function addChild(\Jazzee\Entity\Page $page){
    $this->children[] = $page;
    if($page->getParent() != $this) $page->setParent($this);
  }

  /**
   * Get children
   *
   * @return Doctrine\Common\Collections\Collection $children
   */
  public function getChildren(){
    return $this->children;
  }
  
  /**
   * Get a child by id
   *
   * @param integer $id
   * @return \Jazzee\Entity\Page
   */
  public function getChildById($id){
    foreach($this->children as $child) if($child->getId() == $id) return $child;
    return false;
  }
  
  /**
   * Set page variable
   * 
   * we retunt he variable to is can be persisted
   * 
   * @param string $name
   * @param string $value
   * 
   * @return \Jazzee\Entity\PageVariable 
   */
  public function setVar($name, $value){
    foreach($this->variables as $variable)
      if($variable->getName() == $name){
        $variable->setValue($value);
        return $variable;
      }
    //create a new empty variable with that name
    $variable = new PageVariable;
    $variable->setPage($this);
    $variable->setName($name);
    $this->variables[] = $variable;
    $variable->setValue($value);
    return $variable;
  }

  /**
   * get page variable
   * @param string $name
   * @return string $value
   */
  public function getVar($name){
    foreach($this->variables as $variable)
      if($variable->getName() == $name)return $variable->getValue();
  }
  /**
   * get page variables
   * @return array \Jazzee\Entity\PageVariable
   */
  public function getVariables(){
    return $this->variables;
  }

  /**
   * Add element
   *
   * @param Entity\Element $element
   */
  public function addElement(\Jazzee\Entity\Element $element){
    $this->elements[] = $element;
    if($element->getPage() != $this) $element->setPage($this);
  }

  /**
   * Get elements
   *
   * @return array \Jazzee\Entity\Element
   */
  public function getElements(){
    return $this->elements;
  }
  
	/**
   * Get element by ID
   * @param integer $id
   * @return Entity\Element $element
   */
  public function getElementById($id){
    foreach($this->elements as $element) if($element->getId() == $id) return $element;
    return false;
  }
  
  /**
   * Get element by title
   * @param string $title
   * @return Element $element
   */
  public function getElementByTitle($title){
    foreach($this->elements as $element) if($element->getTitle() == $title) return $element;
    return false;
  }
  
  /**
   * Get element by fixed ID
   * @param integer $id
   * @return Entity\Element $element
   */
  public function getElementByFixedId($id){
    foreach($this->elements as $element) if($element->getFixedId() == $id) return $element;
    return false;
  }
  
  /**
   * Create a temporary application page and return a created Jazzee page
   * @return \Jazzee\Interfaces\Page 
   */
  public function getApplicationPageJazzeePage(){
    if($this->_applicationPageJazzeePage == null){
      $ap = new ApplicationPage;
      $ap->setPage($this);
      $this->_applicationPageJazzeePage = $ap->getJazzeePage();
    }
    return $this->_applicationPageJazzeePage;
  }
}

/**
 * Page Repository
 * Special Repository methods for Pages
 * @package jazzee
 * @subpackage orm
 */
class PageRepository extends \Doctrine\ORM\EntityRepository{
  
  /**
   * Check if a page has any answers associated with it
   * @param Page $page
   * @return boolean
   */
  public function hasAnswers(Page $page){
    $query = $this->_em->createQuery('SELECT a.id FROM Jazzee\Entity\Answer a WHERE a.page = :pageId');
    $query->setParameter('pageId', $page->getId());
    $query->setMaxResults(1);
    $result = $query->getResult();
    return count($result);
  }
  
  /**
   * Check if a page has any answers associated with it in a specific cycle
   * @param Page $page
   * @param Cycle $cycle
   * @return boolean
   */
  public function hasCycleAnswers(Page $page, Cycle $cycle){
    $query = $this->_em->createQuery('SELECT answer.id FROM Jazzee\Entity\Answer answer JOIN answer.applicant applicant JOIN applicant.application application WHERE answer.page = :pageId AND application.cycle = :cycleId');
    $query->setParameter('pageId', $page->getId());
    $query->setParameter('cycleId', $cycle->getId());
    $query->setMaxResults(1);
    $result = $query->getResult();
    return count($result);
  }
  
}