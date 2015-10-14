<?php
/**
 * Author: Ted Bowman
 * Date: 7/28/15
 * Time: 10:39 AM
 */

namespace ConsoleCourses\Console;


use GitWrapper\GitWrapper;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class TerminusWrapper {
  /**
   * @var OutputInterface $output
   */
  protected $output;

  protected $site;

  protected $dialogHelper;

  protected $verbose;

  /**
   * @return mixed
   */
  public function getDialogHelper() {
    return $this->dialogHelper;
  }

  /**
   * @return mixed
   */
  protected function getSite() {
    return $this->site;
  }

  function __construct($output, $site, $dialogHelper) {
    $this->output = $output;
    $this->site = $site;
    $this->dialogHelper = $dialogHelper;
    $this->ensureLogin();
    $this->siteNotify();
  }


  protected function writeln($str) {
    $this->output->writeln($str);
  }

  private function ensureLogin() {

    if (!$this->isLoggedIn()) {
      /**
       * @var DialogHelper
       */
      $dialog = $this->getDialogHelper();
      $email = $dialog->ask(
        $this->output,
        "What is your Pantheon Email?"
      );
      $password = $dialog->askHiddenResponse(
        $this->output,
        'What is the Pantheon.io password?',
        FALSE
      );
      $process = new Process("terminus auth login $email --json --password=$password");
      $process->run();
      if (!$this->isLoggedIn()) {
        throw new \ErrorException("Login failed!");
      }
      else {
        $this->writeln("Login Successful!");
      }
    }
  }

  private function isLoggedIn() {
    $process = new Process('terminus auth whoami --json');
    $process->run();
    $return = trim($process->getOutput());
    return $return != 'You are not logged in.';
  }

  function getEnvs() {
    $this->ensureLogin();
    return $this->terminusCommand('site', 'environments');
  }

  /**
   * @param $base
   * @param $sub
   * @param array $options
   * @param string $suffix
   *
   * @return bool|mixed|string
   */
  function terminusCommand($base, $sub, $options = [], $suffix = '') {

    $cmd_str = 'terminus ' . $base;
    switch ($base) {
      case 'site':
      case 'drush':
        $cmd_str .= ' --site=' . $this->getSite();
        break;
    }

    $cmd_str .= " $sub ";
    if ($options) {
      foreach ($options as $option_key => $option_value) {
        $suffix .= " --$option_key";
        if ($option_value && $option_value !== TRUE) {
          $suffix .= "=$option_value";
        }

      }
      $suffix .= ' ';

    }
    $cmd_str .= " $suffix --json --yes";
    $this->writeln("Executing: $cmd_str");
    $process = new Process($cmd_str);
    $process->setTimeout(NULL);
    $process->run();
    if ($process->isSuccessful()) {
      $this->writeln('<info>Success!</info>');
      if ($returned = json_decode($process->getOutput())) {
        return $returned;
      }
      else {
        return $process->getOutput();
      }

    }
    else {
      $process->getExitCode();
      $this->debug($process->getErrorOutput());
      $this->writeln('<error>Failed</error>');
      return FALSE;
    }

  }

  protected function debug($msg) {
    if ($this->isVerbose()) {
      $this->output->writeln("<error>$msg</error>");
    }
  }

  protected function isVerbose() {
    $verbose_levels = [OutputInterface::VERBOSITY_DEBUG, OutputInterface::VERBOSITY_VERBOSE, OutputInterface::VERBOSITY_VERY_VERBOSE];
    if (in_array($this->output->getVerbosity(),$verbose_levels)) {
      return TRUE;
    }
    return FALSE;
  }

  function getStudentEnvs() {
    if ($envs = $this->getEnvs()) {
      foreach (array_keys($envs) as $key) {
        if (!$this->isStudentEnv($envs[$key])) {
          unset($envs[$key]);
        }
      }
    }
    return $envs;
  }

  function isStudentEnv(\stdClass $env) {
    if (in_array($env->name, array('dev', 'test', 'live'))) {
      return FALSE;
    }
    return TRUE;
  }

  function cloneToEnvs($from_env, $to_envs, $force, $options) {
    /**
     * @var DialogHelper $dialog
     */
    $dialog = $this->getDialogHelper();
    $confirm = $dialog->askConfirmation(
      $this->output,
      "About to clone env $from_env to " . implode(',', $to_envs) . ". <question>Are you sure?</question>"
    );
    if ($confirm) {
      $git_push = !empty($options['git-push']);
      // Don't pass on git-push option
      unset($options['git-push']);
      foreach ($to_envs as $to_env) {

        if ($force || $this->keepInSync($to_env)) {
          if ($git_push) {
            $this->rebaseEnv($to_env);
          }

          $this->terminusCommand(
            'site',
            'clone-content',
            [
              'from-env' => $from_env,
              'to-env' => $to_env,
            ] + $options
          );
        }
        else {
          $this->writeln("Env set not to sync: $to_env");
        }
      }
    }
  }

  protected function keepInSync($env) {
    return $this->getSiteVar($env, 'smt_sync', 1);
  }

  protected function getSiteVar($env, $var_name, $default = NULL) {
    $drush_output = $this->terminusCommand(
      'drush',
      "vget $var_name",
      ['env' => $env ]
    );
    $lines = explode("\n", $drush_output);
    foreach ($lines as $line) {
      if (strpos($line, "$var_name:") === 0) {
        $val = str_replace("$var_name: ", '', $line);
        $val = str_replace("\"", '', $val);
        return $val;
      }
    }
    return $default;
  }

  protected function rebaseEnv($to_env) {
    $this->writeln("Rebasing $to_env with master");
    $git_wrapper = new GitWrapper();
    $git_wrapper->git("checkout $to_env");
    $git_wrapper->git("rebase master");
    $git_wrapper->git('push --force');
  }

  protected function siteNotify() {
    $this->writeln("Working with: " . $this->getSite());
  }

  public function getEnvNames($envs) {
    return array_map(function ($e) {
      return $e->name;
    }, $envs);
  }

  public function delEnvs($envs) {
    $dialog = $this->getDialogHelper();
    $confirm = $dialog->askConfirmation(
      $this->output,
      "About to Delete envs " . implode(',', $envs) . ". Are you sure?"
    );
    if ($confirm) {
      foreach ($envs as $env) {
        $this->terminusCommand(
          'site',
          'delete-env',
          [ 'env' => $env ]
        );
      }

    }
  }

  public function createEnvs($env, $count) {
    $confirm = $this->confirm( "About to Create envs $count environments. Are you sure?");
    if ($confirm) {
      $start_index = $this->getNextSiteIndex();
      for ($c = $start_index; $c < $count + $start_index; $c++) {
        $env_name = $this->createEnvName($c);
        $this->terminusCommand(
          'site',
          'create-env',
          [
            'to-env' => $env_name,
            'from-env' => $env,
          ]
        );
      }
    }
  }

  protected function getNextSiteIndex() {
    $max = 0;
    if ($envs = $this->getStudentEnvs()) {
      $env_names = $this->getEnvNames($envs);
      $max = -1;
      foreach ($env_names as $env_name) {
        $parts = explode('-', $env_name);
        $index = (int)array_pop($parts);
        if ($index > $max) {
          $max = $index;
        }
      }
    }
    return $max + 1;
  }

  public function validateEnvName($env_name) {
    $envs = $this->getStudentEnvs();
    $env_names = $this->getEnvNames($envs);
    if (in_array($env_name, $env_names)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * @param $count
   *
   * @return mixed
   */
  protected function confirm($msg) {
    $dialog = $this->getDialogHelper();
    $confirm = $dialog->askConfirmation(
      $this->output, $msg
    );
    return $confirm;
  }

  /**
   * @param $c
   *
   * @return string
   */
  protected function createEnvName($index) {
    $site_name = $this->getSite();
    // Pantheon puts a 11 character limit on environment names.
    $site_name = substr($site_name,0, 8);
    $env_name = $site_name . "-" . $index;
    return $env_name;
  }
}
