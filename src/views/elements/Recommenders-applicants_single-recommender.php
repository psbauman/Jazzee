<?php
/**
 * Recommenders page LOR single recommender info element
 */
?>
<?php print $answer->getPage()->getElementByFixedId(\Jazzee\Page\Recommenders::FID_FIRST_NAME)->getJazzeeElement()->displayValue($answer); ?>;
<?php print $answer->getPage()->getElementByFixedId(\Jazzee\Page\Recommenders::FID_LAST_NAME)->getJazzeeElement()->displayValue($answer); ?><br />
<?php print $answer->getPage()->getElementByFixedId(\Jazzee\Page\Recommenders::FID_INSTITUTION)->getJazzeeElement()->displayValue($answer); ?><br />
<?php print $answer->getPage()->getElementByFixedId(\Jazzee\Page\Recommenders::FID_EMAIL)->getJazzeeElement()->displayValue($answer); ?><br />
<?php print $answer->getPage()->getElementByFixedId(\Jazzee\Page\Recommenders::FID_PHONE)->getJazzeeElement()->displayValue($answer); ?><br />
Has <?php if($answer->getPage()->getElementByFixedId(\Jazzee\Page\Recommenders::FID_WAIVE_RIGHT)->getJazzeeElement()->displayValue($answer) == 'No')print '<strong>not </strong>'; ?> waived right to view<br />