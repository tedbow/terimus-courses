<?php
namespace ConsoleCourses\Console\Command;

use GitWrapper\GitWrapper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Just a Test command...
 */
class StudentGitRebaseCommand extends Command {

  protected function configure() {
    $this->setName('student-git-push')
      ->setDescription('Push to Git Students')
      ->setHelp('This is the help for the test command.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $git = new GitWrapper();
    $out = $git->git('status');
    $output->writeln($out);
  }
}
