<?php
/**
 * Allows thrid party transactions to be posted to the system
 * Like admin API except the format is unstructured and apssed whole cloth to
 * another class to parse.  The class is specified in the transaction and called statically
 */
class TransactionController extends \Jazzee\Controller 
{ 
  public function actionPost($class){
    $class::transaction($this);
    exit();
  }
}
?>