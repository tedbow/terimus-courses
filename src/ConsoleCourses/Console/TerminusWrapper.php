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

  function terminusCommand($base, $sub, $suffix = '') {

    $cmd_str = 'terminus ' . $base;
    switch ($base) {
      case 'site':
      case 'drush':
        $cmd_str .= ' --site=' . $this->getSite();
        break;
    }
    $cmd_str .= " $sub ";
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
      $this->writeln('<error>Failed</error>');
      return FALSE;
    }

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
    if (in_array($env->Name, array('dev', 'test', 'live'))) {
      return FALSE;
    }
    return TRUE;
  }

  function cloneToEnvs($from_env, $to_envs, $force) {
    /**
     * @var DialogHelper $dialog
     */
    $dialog = $this->getDialogHelper();
    $confirm = $dialog->askConfirmation(
      $this->output,
      "About to clone env $from_env to " . implode(',', $to_envs) . ". <question>Are you sure?</question>"
    );
    if ($confirm) {
      foreach ($to_envs as $to_env) {

        if ($force || $this->keepInSync($to_env)) {
          $this->rebaseEnv($to_env);
          $this->terminusCommand(
            'site',
            'clone-env',
            " --from-env=$from_env --to-env={$to_env} --db=yes --files=yes"
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
      " --env=$env"
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
      return $e->Name;
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
          " --env={$env}"
        );
      }

    }
  }

  public function createEnvs($env, $count) {
    for ($c = 0; $c < $count; $c++) {
      $env_name = $this->getSite() . "-" . $c;
      $this->terminusCommand(
        'site',
        'create-env',
        " --env=$env_name --from-env=$env"
      );
    }
  }

  public function validateEnvName($env_name) {
    $envs = $this->getStudentEnvs();
    $env_names = $this->getEnvNames($envs);
    if (in_array($env_name, $env_names)) {
      return TRUE;
    }
    return FALSE;
  }
}
