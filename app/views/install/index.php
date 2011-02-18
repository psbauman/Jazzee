<?php
/**
 * InstallController build view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 */
?>
<h2>Configure Jazzee</h2>
<?php if(isset($fileContents)):?>
  <h5>Problem saving configuration</h5>
  <p>There was a problem saving your configuration file.  
  Please copy these contents into <?php print SRC_ROOT . InstallController::CONFIG_PATH ?> 
  and then visit the <a href='<?php print $setupPath; ?>'>Setup Page</a> to continue installation</p>
  <code>
    <?php print nl2br($fileContents); ?>
	</code>
<?php else:?>
  <?php $this->renderElement('form', array('form'=>$form)); ?>
<?php endif;?>