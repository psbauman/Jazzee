<?php
namespace Jazzee\Console;
ini_set('memory_limit',-1);
/**
 * Scrambles applicants for testing
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage console
 *
 */
class Scramble extends \Symfony\Component\Console\Command\Command
{
    /**
     * @see Console\Command\Command
     */
    protected function configure(){
        $this->setName('scramble')->setDescription('Scramble Applicants for testing.');
    }
    protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output){
      $jazzeeConfiguration = new \Jazzee\Configuration;
      if($jazzeeConfiguration->getStatus() == 'PRODUCTION'){
        $output->write('<error>You cannot scramble in production.</error>' . PHP_EOL);
      } else {
        $start = time();
        $em = $this->getHelper('em')->getEntityManager();
        $offset = 0;
        $maxResults = 100;
        $count = 0;
        $query = $em->createQuery("SELECT a.firstName, a.lastName FROM \Jazzee\Entity\Applicant a");
        $query->setMaxResults(500);
        $firstNames = array();
        $lastNames = array();
        foreach($query->getResult() as $arr){
          $firstNames[] = $arr['firstName'];
          $lastNames[] = $arr['lastName'];
        }
        do{
          $query = $em->createQuery("SELECT a FROM \Jazzee\Entity\Applicant a");
          $query->setMaxResults($maxResults);
          $query->setFirstResult($offset);
          $applicants = $query->getResult();
          $continue = count($applicants);
          foreach($applicants as $applicant){
            $count++;
            $applicant->setFirstName($firstNames[array_rand($firstNames)]);
            $applicant->setLastName($lastNames[array_rand($lastNames)]);
            $applicant->setEmail("nobody{$count}@example.com");
            $em->persist($applicant);
          }
          $offset = $offset+$maxResults;
          $em->flush();
          $em->clear();
          gc_collect_cycles();
        } while ($continue);
        $total = time() - $start;
        $avg = $count?(round($total/$count,2) . 's/applicant'):'';
        $usedMemory = round(memory_get_peak_usage()/1048576,2);
        $output->write("<info>{$count} applicant's information scrambled successfuly in {$total} seconds {$avg} using {$usedMemory}MB</info>" . PHP_EOL);
      }

    }
}