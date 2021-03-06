<?php 
/**
 * apply_page view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
?>
  <?php if($page->getJazzeePage()->getStatus() == \Jazzee\Interfaces\Page::SKIPPED){?>
    <p class="skip">You have selected to skip this page.  You can still change your mind and <a href='<?php print $this->controller->getActionPath() . '/do/unskip';?>' title='complete this page'>Complete This Page</a> if you wish.</p>
  <?php } else {
    if(!$page->isRequired() and !count($page->getJazzeePage()->getAnswers())){?>
      <p class="skip">This page is optional, if you do not have any information to enter you can <a href='<?php print $this->controller->getActionPath() . '/do/skip';?>' title='skip this page'>Skip This Page</a>.</p>
    <?php }?>
    <div id='counter'><?php 
      $answers = $page->getjazzeePage()->getAnswers();
      if($answers){
        $totalAnswers = count($answers);
        $completedAnswers = 0;
        foreach($answers as $answer)if($answer->isLocked()) $completedAnswers++; ?>
        <p>
        <?php 
        if(is_null($page->getMax())){
          if($completedAnswers >= $page->getMin()) print 'You may add as many additional recommenders as you wish to this page, but it is not required.';
          else print 'You have invited ' . $completedAnswers . ' of the ' . $page->getMin() . ' required recommenders on this page.';
        } else {
          if($page->getMax() - $completedAnswers == 0) print 'You cannot invite any more recommenders to this page.';
          else if($completedAnswers >= $page->getMin()) print 'You may invite an additional ' . ($page->getMax() - $completedAnswers) . ' recommenders on this page, but it is not required.';
          else print 'You have invited ' . $completedAnswers . ' of the ' . $page->getMin() . ' required recommenders on this page.';
        }
        ?>
        <?php 
        if($completedAnswers < $totalAnswers) print '&nbsp;You have not invited ' . ($totalAnswers - $completedAnswers) . ' of the recommenders you added to the page.  Click &lsquo;Send Invitation Email&rsquo; to invite each recommender.';
        ?>
        </p>
      <?php } ?>
  </div>
  <?php if($answers = $page->getJazzeePage()->getAnswers()){?>
    <div id='answers'>
      <?php foreach($answers as $answer){?>
        <div class='answer<?php if($currentAnswerID and $currentAnswerID == $answer->getID()) print ' active'; ?>'>
          <h5>Recommender</h5>
          <?php 
          foreach($answer->getPage()->getElements() as $element){
            $element->getJazzeeElement()->setController($this->controller);
            $value = $element->getJazzeeElement()->displayValue($answer);
            if($value){
              print '<p><strong>' . $element->getTitle() . ':</strong>&nbsp;' . $value . '</p>'; 
            }
          }
          ?>
          <p class='status'>
            <strong>Last Updated:</strong> <?php print $answer->getUpdatedAt()->format('M d Y g:i a');?><br />
            <?php if($child = $answer->getChildren()->first()){?>
              <br /><strong>Status:</strong> This recommendation was received on <?php print $child->getUpdatedAt()->format('l F jS Y g:ia');
            } else if($answer->isLocked()){?>
              <strong>Invitation Sent:</strong> <?php print $answer->getUpdatedAt()->format('l F jS Y g:ia'); ?><br />
              <strong>Status:</strong> You cannot make changes to this recommendation becuase the invitation has already been sent.
              <?php if($answer->getUpdatedAt()->diff(new DateTime('now'))->days < $answer->getPage()->getVar('lorWaitDays')){?> 
                You will be able to send the invitation to your recommender again in <?php print ($answer->getPage()->getVar('lorWaitDays') - $answer->getUpdatedAt()->diff(new DateTime('now'))->days);?> days.
              <?php }?>
            <?php }?>
          </p>
          <p class='controls'>
            <?php 
            if(!$answer->isLocked() and $currentAnswerID and $currentAnswerID == $answer->getID()){?>
              <a class='undo' href='<?php print $this->controller->getActionPath() ?>'>Undo</a>
            <?php } else if(!$answer->isLocked()){ ?>
              <a class='edit' href='<?php print $this->controller->getActionPath();?>/edit/<?php print $answer->getId()?>'>Edit</a>
              <a class='delete' href='<?php print $this->controller->getActionPath();?>/delete/<?php print $answer->getId()?>'>Delete</a>
              <a class='invite' href='<?php print $this->controller->getActionPath();?>/do/sendEmail/<?php print $answer->getId()?>'>Send Invitation Email</a>
            <?php } else if(!$answer->getChildren()->first() and $answer->getUpdatedAt()->diff(new DateTime('now'))->days >= $answer->getPage()->getVar('lorWaitDays')){ ?>
              <a class='invite' href='<?php print $this->controller->getActionPath();?>/do/sendEmail/<?php print $answer->getId()?>'>Send Reminder Email</a>
            <?php }?>
          </p>
        </div>
      <?php } //end foreach answers ?>
    </div>
  <?php } 
  if(!empty($currentAnswerID) or is_null($page->getMax()) or count($page->getJazzeePage()->getAnswers()) < $page->getMax()){?>
    <div id='leadingText'><?php print $page->getLeadingText()?></div>
      <?php $this->renderElement('form', array('form'=> $page->getJazzeePage()->getForm())); ?>
    <div id='trailingText'><?php print $page->getTrailingText()?></div>
  <?php }
}