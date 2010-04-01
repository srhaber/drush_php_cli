<?php

/**
 * Implementation of hook_drush_command().
 */
function php_cli_drush_command() {
  $items = array();

  $items['php-cli'] = array(
    'callback' => 'drush_php_cli_callback',
    'description' => "Open a PHP command-line interface following a Drupal bootstrap.",
  );

  return $items;
}

/**
 * Implementation of hook_drush_help().
 */
function php_cli_drush_help($section) {
  switch ($section) {
    case 'drush:php-cli':
      return dt("Opens an interactive PHP shell following a Drupal bootstrap.");
  }
}

/**
 * Callback for php-cli command.
 *
 * This loads a file which opens a php shell.
 */
function drush_php_cli_callback() {
  include "php_shell.php";
}
