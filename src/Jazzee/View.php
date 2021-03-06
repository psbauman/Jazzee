<?php
namespace Jazzee;
/**
 * The Jazzee View class
 * provides a path for adding global functionality to layouts, views, and elements
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage views
 */
class View extends \Foundation\VC\View{
  
  protected function path($path){
    return $this->controller->path($path);
  }
}

?>