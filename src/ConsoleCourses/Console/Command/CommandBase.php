<?php
/**
 * Author: Ted Bowman
 * Date: 7/28/15
 * Time: 10:39 AM
 */

namespace ConsoleCourses\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class CommandBase extends Command{
  protected $site;
  protected function configure() {
    parent::configure();
    $this->addOption(
      'site',
      null,
      InputOption::VALUE_REQUIRED,
      'Set Pantheon Site'
    );
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    if ($site = $input->getOption('site')) {
      $this->site = $site;
    }
    else {
      $this->site = $this->getParameter('site');
    }
  }

  protected function getSite() {
    return $this->site;
  }
  protected function getParameter($param) {
    $container = $this->getApplication()->getContainer();
    return $container->getParameter($param);
  }

}
