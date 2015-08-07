<?php
/**
 * Author: Ted Bowman
 * Date: 7/28/15
 * Time: 2:53 PM
 */

namespace ConsoleCourses\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class StudentDelCommand extends Command {
  protected function configure() {
    $this->setName('student-del')
      ->setDescription('Delete All Student Evns')
      ->setHelp('This is the help for the test command.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $term_wrapper = new TerminusWrapper($output);
    $envs = $term_wrapper->getStudentEnvs($output);
    $term_wrapper->delEnvs($output, $this->getEnvNames($envs));
  }

}
