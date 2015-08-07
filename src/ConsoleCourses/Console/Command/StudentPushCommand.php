<?php
namespace ConsoleCourses\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use ConsoleCourses\Console\Command\BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Just a Test command...
 */
class StudentPushCommand extends Command {

  /**
   * @var ContainerBuilder
   */
  private $container;

  protected function configure() {
    $this->setName('student-push')
      ->setDescription('Push to Students')
      ->addArgument(
        'env',
        InputArgument::OPTIONAL,
        'Which evn to push to. Otherwise all set to sync will be pushed to'
      )
      ->addOption(
        'force',
        null,
        InputOption::VALUE_NONE,
        'Force regardless of env setting'
      )
      ->setHelp('This is the help for the test command.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $term_wrapper = new TerminusWrapper($output);
    $force = $input->getOption('force');
    if ($env = $input->getArgument('env')) {
      if ($term_wrapper->validateEnvName($env)) {
        $term_wrapper->cloneToEnvs('dev', array($env), $force);
      }
      return;

    }
    $envs = $term_wrapper->getStudentEnvs($output);

    $term_wrapper->cloneToEnvs('dev', $term_wrapper->getEnvNames($envs), $force);
  }
}
