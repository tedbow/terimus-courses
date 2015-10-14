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


class CommandBase extends Command {
  protected $site;

  protected $passThruOptions = [];

  protected function configure() {
    parent::configure();
    $this->addOption(
      'site',
      NULL,
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

  protected function getPassThruOptionsValues(InputInterface $input) {
    $options = [];
    if ($this->passThruOptions) {
      foreach ($this->passThruOptions as $option) {
        if ($input->hasOption($option)) {
          $option_value = $input->getOption($option);
          if ($option_value !== FALSE) {
            $options[$option] = $input->getOption($option);
          }

        }
      }
    }
    return $options;
  }

}
