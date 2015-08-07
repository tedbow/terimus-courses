<?php
/**
 * Author: Ted Bowman
 * Date: 7/28/15
 * Time: 2:53 PM
 */

namespace ConsoleCourses\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;


class StudentCreateCommand extends Command {
  protected function configure() {
    $this->setName('student-create')
      ->setDescription('Create Env')
      ->addArgument(
        'count',
        InputArgument::REQUIRED,
        'How many?'
      )
      ->addArgument(
        'env',
        InputArgument::OPTIONAL,
        'Which env to clone from?'
      )
      ->setHelp('This is the help for the test command.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $count = $input->getArgument('count');
    $env = $input->getArgument('env');
    $env = $env ? $env : 'dev';
    $term_wrapper = new TerminusWrapper($output);
    $term_wrapper->createEnvs($output, $env, $count);
  }

}
