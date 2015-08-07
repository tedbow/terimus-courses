<?php
/**
 * Author: Ted Bowman
 * Date: 7/28/15
 * Time: 2:53 PM
 */

namespace ConsoleCourses\Console\Command;

use ConsoleCourses\Console\TerminusWrapper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class StudentDelCommand extends CommandBase {
  protected function configure() {
    parent::configure();
    $this->setName('student-del')
      ->setDescription('Delete  Student Environments')
      ->setHelp('This is the help for the test command.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    parent::execute($input, $output);
    $term_wrapper = new TerminusWrapper($output, $this->getSite(), $this->getHelper('dialog'));
    if ($envs = $term_wrapper->getStudentEnvs()) {
      $env_names = $term_wrapper->getEnvNames($envs);
      $term_wrapper->delEnvs($env_names);
    }
    else {
      $output->writeln('<info>No environments to delete.</info>');
    }
  }
}
