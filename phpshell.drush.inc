<?php
// $Id: example.drush.inc,v 1.3 2009/04/15 01:13:45 weitzman Exp $

/**
 * @file
 *   Example drush command.
 *
 *   Shows how to make your own drush command.
 *
 *   You can copy this file to any of the following
 *     1. A .drush folder in your HOME folder.
 *     2. Anywhere in a folder tree below an active module on your site.
 *     3. In an arbitrary folder specified with the --include option.
 */

/**
 * Implementation of hook_drush_command().
 *
 * In this hook, you specify which commands your
 * drush module makes available, what it does and 
 * description.
 *
 * Notice how this structure closely resembles how 
 * you define menu hooks.
 * 
 * @See drush_parse_command() for a list of recognized keys.
 *
 * @return
 *   An associative array describing your command(s).
 */
function phpshell_drush_command() {
  $items = array();

  // the key in the $items array is the name of the command.
  $items['php-cli'] = array(
    // the name of the function implementing your command.
    'callback' => 'phpshell_php',
    // a short description of your command
    'description' => "Open a PHP command-line interface following a Drupal bootstrap.",
  );

  // more commands here ...

  return $items;
}

/**
 * Implementation of hook_drush_help().
 *
 * This function is called whenever a drush user calls
 * 'drush help <name-of-your-command>'
 *
 * @param
 *   A string with the help section (prepend with 'drush:')
 *
 * @return
 *   A string with the help text for your command.
 */
function phpshell_drush_help($section) {
  switch ($section) {
    case 'drush:php-cli':
      return dt("Opens an interactive PHP shell following a Drupal bootstrap.");
  }
}

/**
 * Example drush command callback.
 *
 * This is where the action takes place.
 *
 * In this function, all of Drupals API is (usually) available, including
 * any functions you have added in your own modules/themes.
 *
 * To print something to the terminal window, use drush_print().
 *
 */
function phpshell_php() {
  require "php-shell-custom.php";
}
