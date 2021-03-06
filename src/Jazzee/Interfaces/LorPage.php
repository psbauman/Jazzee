<?php
namespace Jazzee\Interfaces;
/**
 * LORPage interface
 * Allows us to define interface for pages which can be used to compelted
 * letters of recommendation
 * 
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage pages
 */
interface LorPage 
{

  /**
   * Create a new LOR answer from user input
   * @param \Foundation\Form\Input $input
   * @param \Jazzee\Entity\Answer $answer
   * @return bool
   */
  function newLorAnswer(\Foundation\Form\Input $input, \Jazzee\Entity\Answer $answer);

  /**
   * Update a LOR Answer
   * @param \Foundation\Form\Input $input
   * @param \Jazzee\Entity\Answer $answer
   * @return bool
   */
  function updateLorAnswer(\Foundation\Form\Input $input, \Jazzee\Entity\Answer $answer);

  /**
   * Delete LOR answer
   * @param \Jazzee\Entity\Answer $answer
   * @return bool
   */
  function deleteLorAnswer(\Jazzee\Entity\Answer $answer);

  /**
   * Fill Lor form from answer
   * @param \Jazzee\Entity\Answer $answer
   */
  function fillLorForm(\Jazzee\Entity\Answer $answer);
  
  /**
   * Get the element for the LOR page view
   * @return string 
   */
  public static function lorPageElement();
  
  /**
   * Get the element for the LOR page view
   * @return string 
   */
  public static function lorReviewElement();
  
  /**
   * Get the element for the LOR page view
   * @return string 
   */
  public static function lorApplicantsSingleElement();
}