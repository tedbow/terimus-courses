<?php
/**
 * Author: Ted Bowman
 * Date: 7/28/15
 * Time: 2:53 PM
 */

namespace ConsoleCourses\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ConsoleCourses\Console\TerminusWrapper;
use ConsoleCourses\Console\Command\CommandBase;

/**
 * Class StudentCreateCommand
 *
 * @package ConsoleCourses\Console\Command
 *
 */
class StudentCreateCommand extends CommandBase {
  protected function configure() {
    parent::configure();
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
      ->setHelp('Creates students multidev environments.');
  }

  /**
   * @param \Symfony\Component\Console\Input\InputInterface $input
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    parent::execute($input, $output);
    $count = $input->getArgument('count');
    $env = $input->getArgument('env');
    $env = $env ? $env : 'dev';
    $term_wrapper = new TerminusWrapper($output, $this->getSite(), $this->getHelper('dialog'));
    $term_wrapper->createEnvs($env, $count);
  }
}
