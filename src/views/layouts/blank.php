<?php
/**
 * Blank layout
 * Doesn't layout anythig, jut prints the view content
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header('Content-Type:text/html; charset=UTF-8');
header('X-FRAME-OPTIONS: SAMEORIGIN');
print $layoutContent;
?>