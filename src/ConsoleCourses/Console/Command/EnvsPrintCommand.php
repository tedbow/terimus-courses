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


class EnvsPrintCommand extends CommandBase {
  protected function configure() {
    parent::configure();
    $this->setName('env-print')
      ->setDescription('Print out all environments.')
      ->setHelp('This is the help for the test command.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    parent::execute($input, $output);
    $term_wrapper = new TerminusWrapper($output, $this->getSite(), $this->getHelper('dialog'));
    if ($envs = $term_wrapper->getStudentEnvs()) {
      foreach ($envs as $env) {
        $output->writeln($env->domain);
      }


    }
    else {
      $output->writeln('<info>No environments to delete.</info>');
    }
  }
}
