<?php 
namespace Jazzee\Entity;

/** 
 * Answer
 * Applicant answer to a page
 * @Entity
 * @HasLifecycleCallbacks 
 * @Table(name="answers") 
 * @package    jazzee
 * @subpackage orm
 **/

class Answer{
  /**
    * @Id 
    * @Column(type="bigint")
    * @GeneratedValue(strategy="AUTO")
  */
  private $id;
  
  /** 
   * @ManyToOne(targetEntity="Applicant",inversedBy="answers")
   * @JoinColumn(onDelete="CASCADE", onUpdate="CASCADE") 
   */
  protected $applicant;
  
  /** 
   * @ManyToOne(targetEntity="Page")
   * @JoinColumn(onDelete="CASCADE", onUpdate="CASCADE") 
   */
  protected $page;
  
  /** 
   * @ManyToOne(targetEntity="Answer",inversedBy="children")
   * @JoinColumn(onDelete="CASCADE", onUpdate="CASCADE") 
   */
  protected $parent;
  
  /** 
   * @OneToMany(targetEntity="Answer", mappedBy="parent")
   */
  protected $children;
  
  /** 
   * @OneToMany(targetEntity="ElementAnswer", mappedBy="answer")
   */
  protected $elements;
  
  /** 
   * @ManyToOne(targetEntity="AnswerStatusType")
   * @JoinColumn(onUpdate="CASCADE") 
   */
  protected $publicStatus;
  
  /** 
   * @ManyToOne(targetEntity="AnswerStatusType")
   * @JoinColumn(onUpdate="CASCADE") 
   */
  protected $privateStatus;
  
  /** @Column(type="text", nullable=true) */
  protected $attachment;
  
  /** @Column(type="string", length=255, unique=true) */
  protected $uniqueId;
  
  /** @Column(type="boolean") */
  protected $locked;
  
  /** @Column(type="datetime") */
  protected $updatedAt;
  
  /** 
   * @OneToMany(targetEntity="GREScore",mappedBy="answer")
   */
  private $greScores;
  
  /**
   * The Jazzee Answer instance
   * @var \Jazzee\Answer
   */
  private $jazzeeAnswer;
  
  public function __construct(){
    $this->children = new \Doctrine\Common\Collections\ArrayCollection();
    $this->elements = new \Doctrine\Common\Collections\ArrayCollection();
    $this->generateUniqueId();
    $this->locked = false;
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
   * Set attachment
   *
   * @param text $attachment
   */
  public function setAttachment($attachment){
    $this->attachment = $attachment;
  }

  /**
   * Get attachment
   *
   * @return text $attachment
   */
  public function getAttachment(){
    return $this->attachment;
  }
  
  /**
   * Set page
   *
   * @param Entity\Page $page
   */
  public function setPage($page){
    $this->page = $page;
  }

  /**
   * Get page
   *
   * @return Entity\Page $page
   */
  public function getPage(){
    return $this->page;
  }

  /**
   * Generate a unique id
   */
  public function generateUniqueId(){
    //PHPs uniquid function is time based and therefor guessable
    //A stright random MD5 sum is too long for email and tends to line break causing usability problems
    //So we get unique through uniquid and we get random by prefixing it with a part of an MD5
    //hopefully this results in a URL friendly short, but unguessable string
    $prefix = substr(md5(mt_rand()*mt_rand()),rand(0,24), rand(6,8));
    $this->uniqueId = \uniqid($prefix);
  }
  
  /**
   * Set a uniqueid
   * Prefferably call generateUniqueId - but it can also be set manually
   * @param string $uniqueId;
   */
  public function setUniqueId($uniqueId){
    $this->uniqueId = $uniqueId;
  }
  
  /**
   * Get uniqueId
   *
   * @return string $uniqueId
   */
  public function getUniqueId(){
    return $this->uniqueId;
  }

  /**
   * Lock the Answer
   */
  public function lock(){
    $this->locked = true;
  }
  
  /**
   * UnLock the Answer
   */
  public function unlock(){
    $this->locked = false;
  }

  /**
   * Get locked status
   *
   * @return boolean $locked
   */
  public function isLocked(){
    return $this->locked;
  }

  /**
   * Set updatedAt
   *
   * @param string $updatedAt
   */
  public function setUpdatedAt($updatedAt){
    $this->updatedAt = new \DateTime($updatedAt);
  }

  /**
   * Get updatedAt
   *
   * @return DateTime $updatedAt
   */
  public function getUpdatedAt(){
    return $this->updatedAt;
  }

  /**
   * Set applicant
   *
   * @param Entity\Applicant $applicant
   */
  public function setApplicant(Applicant $applicant){
    $this->applicant = $applicant;
    $applicant->addAnswer($this);
  }

  /**
   * Get applicant
   *
   * @return Entity\Applicant $applicant
   */
  public function getApplicant(){
    return $this->applicant;
  }

  /**
   * Set parent
   *
   * @param Entity\Answer $parent
   */
  public function setParent(Answer $parent){
    $this->parent = $parent;
  }

  /**
   * Get parent
   *
   * @return Entity\Answer $parent
   */
  public function getParent(){
    return $this->parent;
  }

  /**
   * Add children
   *
   * @param Entity\Answer $children
   */
  public function addChildren(Answer $children){
    $this->children[] = $children;
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
   * Add elements
   *
   * @param Entity\ElementAnswer $element
   */
  public function addElementAnswer(ElementAnswer $element){
    $this->elements[] = $element;
  }

  /**
   * Get elements
   *
   * @return Doctrine\Common\Collections\Collection $elements
   */
  public function getElementAnswers(){
    return $this->elements;
  }
  
  /**
   * Get ElementAnswers for Element
   * 
   * @param \Jazzee\Entity\Element $element
   * @return array
   */
  public function getElementAnswersForElement(Element $element){
    $arr = array();
    foreach($this->elements as $elementAnswer)if($elementAnswer->getElement() == $element) $arr[] = $elementAnswer;
    return $arr;
  }

  /**
   * Set publicStatus
   *
   * @param Entity\AnswerStatusType $publicStatus
   */
  public function setPublicStatus(AnswerStatusType $publicStatus){
    $this->publicStatus = $publicStatus;
  }

  /**
   * Get publicStatus
   *
   * @return Entity\AnswerStatusType $publicStatus
   */
  public function getPublicStatus(){
    return $this->publicStatus;
  }

  /**
   * Set privateStatus
   *
   * @param Entity\AnswerStatusType $privateStatus
   */
  public function setPrivateStatus(AnswerStatusType $privateStatus){
    $this->privateStatus = $privateStatus;
  }

  /**
   * Get privateStatus
   *
   * @return Entity\AnswerStatusType $privateStatus
   */
  public function getPrivateStatus(){
    return $this->privateStatus;
  }
  
  /**
   * Get the jazzeeAnswer
   * 
   * @return \Jazzee\Answer
   */
  public function getJazzeeAnswer(){
    if(is_null($this->jazzeeAnswer)){
      $pageClass = $this->page->getType()->getClass();
      $class = $pageClass::ANSWER_CLASS;
      if(!class_exists($class)) $class = 'Jazzee\Entity\Answer\Text';
      $this->jazzeeAnswer = new $class($this);
    }
    return $this->jazzeeAnswer;
  }
  
  /**
   * Mark the lastUpdate automatically 
   * @PrePersist
   */
  public function markLastUpdate(){
      $this->updatedAt = new \DateTime();
  }
}