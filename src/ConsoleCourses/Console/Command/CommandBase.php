<?php
/**
 * Author: Ted Bowman
 * Date: 7/28/15
 * Time: 10:39 AM
 */

namespace ConsoleCourses\Console\Command;

use Symfony\Component\Console\Command\Command;


class CommandBase extends Command{

  protected function getSite() {
    $container = $this->getApplication()->getContainer();
    $site = $container->getParameter('site');
    return $site;
  }
  protected function getParameter($param) {
    $container = $this->getApplication()->getContainer();
    return $container->getParameter($param);
  }
}
