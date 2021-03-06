<?php
namespace Jazzee\Interfaces;
/**
 * Status Page interface
 * Pages which implement this can be viewed by applicants on the status screen
 * 
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage pages
 */
interface StatusPage 
{ 
  /**
   * Get the element for the apply status view
   * @return string 
   */
  public static function applyStatusElement();
}