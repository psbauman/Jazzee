<?php
namespace Jazzee\Interfaces;
/**
 * Query Page interface
 * Pages which implement this can be queried
 * 
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage pages
 */
interface QueryPage 
{
  
  /**
   * Test a query
   * Checks if the applicant meets the query parameters
   * @param \stdClass $query
   * @returns boolean
   */
  public function testQuery(\stdClass $obj);
}