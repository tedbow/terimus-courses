<?php
namespace ConsoleCourses\Console\Command;

use ConsoleCourses\Console\TerminusWrapper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Just a Test command...
 */
class StudentPushCommand extends CommandBase {

  protected $passThruOptions = ['db-only', 'git-push'];

  protected function configure() {
    parent::configure();
    $this->setName('student-push')
      ->setDescription('Push to Students')
      ->addArgument(
        'env',
        InputArgument::OPTIONAL,
        'Which evn to push to. Otherwise all set to sync will be pushed to'
      )
      ->addOption(
        'force',
        NULL,
        InputOption::VALUE_NONE,
        'Force regardless of env setting'
      )
      ->addOption(
        'db-only',
        NULL,
        InputOption::VALUE_NONE
      )
      ->addOption(
        'git-push',
        FALSE,
        InputOption::VALUE_NONE
      )
      ->setHelp('This is the help for the test command.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    parent::execute($input, $output);
    $options = $this->getPassThruOptionsValues($input);
    $term_wrapper = new TerminusWrapper($output, $this->getSite(), $this->getHelper('dialog'));
    $force = $input->getOption('force');

    if ($env = $input->getArgument('env')) {
      if ($term_wrapper->validateEnvName($env)) {
        $term_wrapper->cloneToEnvs('dev', array($env), $force, $options);
      }
      return;

    }
    $envs = $term_wrapper->getStudentEnvs($output);

    $term_wrapper->cloneToEnvs('dev', $term_wrapper->getEnvNames($envs), $force, $options);
  }
}
