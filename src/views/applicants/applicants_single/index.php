<?php 
/**
 * applicants_single index view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * Create a blank canvas to draw the applicant on
 */
//$applicant = new \Jazzee\Entity\Applicant;
?>
<div id='ajaxstatus'></div>
<div id="container">
  <div id="bio">
    <h1>
      <?php print $applicant->getFullName(); ?>
      <?php if($this->controller->checkIsAllowed('applicants_single', 'updateBio')){ ?>
        <a id='updateBio' href="<?php print $this->path("applicants/single/{$applicant->getId()}/updateBio");?>">(edit)</a>
      <?php } ?>
    </h1>
    <h4><?php print $applicant->getEmail();?></h4>
  </div>
  <div id="status">
    <table id="statusTable">
      <thead>
        <tr>
          <th>Actions</th>
          <th>Account</th>
          <th>Admission Status</th>
          <th>Tags</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <?php if($this->controller->checkIsAllowed('applicants_single', 'pdf')){ ?>
              <a href="<?php print $this->path("applicants/single/{$applicant->getId()}/pdf/portrait");?>">Print Portrait PDF</a><br />
              <a href="<?php print $this->path("applicants/single/{$applicant->getId()}/pdf/landscape");?>">Print Landscape PDF</a><br />
            <?php } ?>
            <?php if($this->controller->checkIsAllowed('applicants_single', 'actas')){ ?>
              <a id='actas' href="<?php print $this->path("applicants/single/{$applicant->getId()}/actas");?>">Become this applicant</a><br />
            <?php } ?>
            <?php if($this->controller->checkIsAllowed('applicants_single', 'move')){ ?>
              <a id='move' href="<?php print $this->path("applicants/single/{$applicant->getId()}/move");?>">Move applicant</a>
            <?php }?>
          </td>
          <td id="actions">
            Account Created: <?php print $applicant->getCreatedAt()->format('c');?><br />
            Last Update: <?php print $applicant->getUpdatedAt()->format('c');?><br />
            Last Login: <?php print $applicant->getLastLogin()->format('c');?><br />
            Deadline Extension: 
            <?php $text = $applicant->getDeadlineExtension()?$applicant->getDeadlineExtension()->format('c'):'none'; ?>
            <?php if($this->controller->checkIsAllowed('applicants_single', 'extendDeadline')){ ?>
              <a href="<?php print $this->path("applicants/single/{$applicant->getId()}/extendDeadline");?>"><?php print $text;?></a>
            <?php } else {
              print $text;
            }?>
          </td>
          <td id="decisions">
            <?php
              $status = '';
              if($applicant->getDecision()) $status = $applicant->getDecision()->status();
              switch($status){
                case '': $status = 'No Decision'; break;
                case 'nominateAdmit': $status = 'Nominated for Admission'; break; 
                case 'nominateDeny': $status = 'Nominated for Deny'; break;       
                case 'finalDeny': 
                  $status = 'Denied ' . ($applicant->getDecision()->getDecisionViewed()?'(decision viewed ' . $applicant->getDecision()->getDecisionViewed()->format('c') . ')':'(decision not viewed)');
                  break; 
                case 'finalAdmit': 
                  $status = 'Admited ' . ($applicant->getDecision()->getDecisionViewed()?'(decision viewed ' . $applicant->getDecision()->getDecisionViewed()->format('c') . ')':'(decision not viewed)'); 
                  break; 
                case 'acceptOffer': $status = 'Accepted'; break; 
                case 'declineOffer': $status = 'Declined'; break;
              }
            ?>
            Status: <?php print $status; ?><br/>
            <?php 
              if($applicant->isLocked()){
                $actions = array(
                  array('action' => 'nominateAdmit','title'=>'Nominate for Admission','class'=>'action'),
                  array('action' => 'undoNominateAdmit','title'=>'Undo Nomination','class'=>'action'),
                  array('action' => 'nominateDeny','title'=>'Nominate for Deny','class'=>'action'),
                  array('action' => 'undoNominateDeny','title'=>'Undo Nomination','class'=>'action'),
                  array('action' => 'finalAdmit','title'=>'Admit Applicant','class'=>'actionForm'),
                  array('action' => 'undoFinalAdmit','title'=>'Undo Decision','class'=>'action'),
                  array('action' => 'finalDeny','title'=>'Deny Applicant','class'=>'actionForm'),
                  array('action' => 'undoFinalDeny','title'=>'Undo Decision','class'=>'action'),
                  array('action' => 'acceptOffer','title'=>'Accept Offer','class'=>'actionForm'),
                  array('action' => 'declineOffer','title'=>'Decline Offer','class'=>'actionForm'),
                  array('action' => 'undoAcceptOffer','title'=>'Undo Offer Response','class'=>'action'),
                  array('action' => 'undoDeclineOffer','title'=>'Undo Offer Response','class'=>'action')
                );
                foreach($actions as $arr){
                  if($this->controller->checkIsAllowed('applicants_single', $arr['action']) && $applicant->getDecision()->can($arr['action'])){?>
                    <a id="decision<?php print $arr['action']?>" class="<?php print $arr['class']?>" href="<?php print $this->path("applicants/single/{$applicant->getId()}/{$arr['action']}");?>"><?php print $arr['title'];?></a><br />
                  <?php }
                }
                if($this->controller->checkIsAllowed('applicants_single', 'unlock')){?>
                  <a class='action' href="<?php print $this->path("applicants/single/{$applicant->getId()}/unlock");?>">Unlock Application</a>
                <?php }
              } else if($this->controller->checkIsAllowed('applicants_single', 'lock')){?>
                <a class='action' href="<?php print $this->path("applicants/single/{$applicant->getId()}/lock");?>">Lock Application</a>
              <?php }
            ?>
          </td>
          <td id="tags"></td>
        </tr>
      </tbody>
    </table>
  </div>
    <div id="threads" class="discussion">
      <h4>Applicant Messages</h4>
      <?php $this->renderElement('applicants_messages_list', array('threads'=>$applicant->getThreads())); ?>  
      <a href="<?php print $this->path("applicants/messages/new/{$applicant->getId()}");?>">New Message</a>
    </div>
  <div id='sirPages'>
    <?php
    if($applicant->getDecision() and $applicant->getDecision()->getAcceptOffer() and $pages = $applicant->getApplication()->getApplicationPages(\Jazzee\Entity\ApplicationPage::SIR_ACCEPT)){
      $applicationPage = $pages[0];
      $class = $applicationPage->getPage()->getType()->getClass();
      $this->renderElement($class::sirApplicantsSingleElement(), array('page'=>$applicationPage, 'applicant'=>$applicant));
    }
    if($applicant->getDecision() and $applicant->getDecision()->getDeclineOffer() and $pages = $applicant->getApplication()->getApplicationPages(\Jazzee\Entity\ApplicationPage::SIR_DECLINE)){
      $applicationPage = $pages[0];
      $class = $applicationPage->getPage()->getType()->getClass();
      $this->renderElement($class::sirApplicantsSingleElement(), array('page'=>$applicationPage, 'applicant'=>$applicant));
    }
    ?>
  </div>
  <div id="duplicates">
    <?php 
      $duplicates = array();
      foreach($applicant->getDuplicates() as $duplicate){
        if(!$duplicate->isIgnored()){
          $duplicates[] = $duplicate;
        }
      }
    ?>
    <?php if(count($duplicates)){?>
      <fieldset>
        <legend>Possible Duplicate Applicants (<?php print count($duplicates); ?>)</legend>
        <ul>
          <?php foreach($duplicates as $duplicate){ ?>
            <li>
              <em><?php print $duplicate->getDuplicate()->getFullName(); ?></em> 
              <?php print $duplicate->getDuplicate()->getPercentComplete()*100; ?> % completed in <?php print $duplicate->getDuplicate()->getApplication()->getProgram()->getName(); ?>
              <?php if($this->controller->checkIsAllowed('applicants_single', 'ignoreDuplicate')){ ?>
                &nbsp;(<a class='ignoreDuplicate' href="<?php print $this->path("applicants/single/{$applicant->getId()}/ignoreDuplicate/{$duplicate->getId()}");?>">Ignore</a>)
              <?php } ?>
            </li>
          <?php } ?>
        </ul>
      </fieldset>
    <?php } ?>
  </div>
  <div id='pages'>
    <?php 
    foreach($applicant->getApplication()->getApplicationPages(\Jazzee\Entity\ApplicationPage::APPLICATION) as $applicationPage){
      if($applicationPage->getJazzeePage() instanceof \Jazzee\Interfaces\ReviewPage){
        $class = $applicationPage->getPage()->getType()->getClass();
        $this->renderElement($class::applicantsSingleElement(), array('page'=>$applicationPage, 'applicant'=>$applicant));
      }
    }
    ?>
  </div>
  <div id="attachments">
    <?php foreach($applicant->getAttachments() as $attachment){
      $pdfName = $applicant->getFullName() . '_attachment_' . $attachment->getId() . '.pdf';
      $pngName = $applicant->getFullName() . '_attachment_' . $attachment->getId() . 'preview.png';
      if(!$pdfFile = $this->controller->getStoredFile($pdfName) or $pdfFile->getLastModified() < $applicant->getUpdatedAt()){
        $this->controller->storeFile($pdfName, $attachment->getAttachment());
      }
      if(!$pngFile = $this->controller->getStoredFile($pngName) or $pngFile->getLastModified() < $applicant->getUpdatedAt()){
        $this->controller->storeFile($pngName, $attachment->getThumbnail());
      }
      ?>
      <div id='attachment<?php print $attachment->getId();?>'>
        <a href='<?php print $this->path('file/' . \urlencode($pdfName));?>'>
          <img src='<?php print $this->path('file/' . \urlencode($pngName));?>' /></a>
        <?php if($this->controller->checkIsAllowed('applicants_single', 'deleteApplicantPdf')){?>
          <a class='delete' href="<?php print $this->path("applicants/single/{$applicant->getId()}/deleteApplicantPdf/{$attachment->getId()}");?>">Delete PDF</a>
        <?php } ?>
      </div>
    <?php } ?>
    <?php if($this->controller->checkIsAllowed('applicants_single', 'attachApplicantPdf')){?>
      <a class='attach' href="<?php print $this->path("applicants/single/{$applicant->getId()}/attachApplicantPdf");?>">Attach PDF</a>
    <?php } ?>
  </div>
    
            <?php if($this->controller->checkIsAllowed('applicants_single', 'viewAuditLog')){ ?>
              <fieldset id='auditLog'>
                <legend>Audit Logs</legend>
                <?php
                  $result = array();
                  foreach($applicant->getAuditLogs() as $log){?>
                    <p>
                      <strong><?php print $log->getText();?></strong> <em>by <?php print $log->getUser()->getFirstName() . ' ' . $log->getUser()->getLastName();?> at <?php print $log->getCreatedAt()->format('c');?></em>
                    </p>
                  <?php }?>
              </fieldset>
            <?php }?>
</div><!-- /container-->