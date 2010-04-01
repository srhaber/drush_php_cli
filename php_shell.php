<?php
@ob_end_clean();
error_reporting(E_ALL);
set_time_limit(0);

$include_path = get_include_path() . ':' . dirname(__FILE__) . '/lib/PHP_Shell-0.3.1';
set_include_path($include_path);

require_once "PHP/Shell.php";
//require_once "PHP/Shell/Extensions/Autoload.php";
//require_once "PHP/Shell/Extensions/AutoloadDebug.php";
require_once "PHP/Shell/Extensions/Colour.php";
require_once "PHP/Shell/Extensions/ExecutionTime.php";
//require_once "PHP/Shell/Extensions/InlineHelp.php";
//require_once "PHP/Shell/Extensions/VerbosePrint.php"

$__shell = new PHP_Shell();
$__cmd = PHP_Shell_Commands::getInstance();
//$__cmd->registerCommand('#^exit;?$#', $__shell, 'cmdQuit', 'exit', 'leaves the shell (same as quit)');

$__shell_exts = PHP_Shell_Extensions::getInstance();
$__shell_exts->registerExtensions(array(
  "options"        => PHP_Shell_Options::getInstance(), /* the :set command */
  // "autoload"       => new PHP_Shell_Extensions_Autoload(),
  // "autoload_debug" => new PHP_Shell_Extensions_AutoloadDebug(),
  "colour"         => new PHP_Shell_Extensions_Colour(),
  "exectime"       => new PHP_Shell_Extensions_ExecutionTime(),
  // "inlinehelp"     => new PHP_Shell_Extensions_InlineHelp(),
  // "verboseprint"   => new PHP_Shell_Extensions_VerbosePrint(),
));

/*
$__shell_exts->colour->registerColourScheme(
"custom", array( 
  "default"   => PHP_Shell_Extensions_Colour::C_YELLOW,
  "prompt"    => PHP_Shell_Extensions_Colour::C_WHITE,
  "value"     => PHP_Shell_Extensions_Colour::C_CYAN,
  "exception" => PHP_Shell_Extensions_Colour::C_RED));

$__shell_exts->colour->applyColourScheme("custom");
*/

$f = <<<EOF
PHP-Barebone-Shell - Version %s%s
(c) 2006, Jan Kneschke <jan@kneschke.de>

>> use '?' to open the inline help 

EOF;

printf($f, 
  $__shell->getVersion(), 
  $__shell->hasReadline() ? ', with readline() support' : '');
unset($f);

print $__shell_exts->colour->getColour("prompt");

while($__shell->input()) {
  try {
    $__shell_exts->exectime->startParseTime();
    if ($__shell->parse() == 0) {
      ## we have a full command, execute it
      $__shell_exts->exectime->startExecTime();
      print $__shell_exts->colour->getColour("value");              

      $__shell_retval = eval($__shell->getCode()); 
      if (isset($__shell_retval)) {
        print $__shell_exts->colour->getColour("default");              
        var_export($__shell_retval);
      }
      $_ = (isset($__shell_retval)) ? $__shell_retval : $_;

      ## cleanup the variable namespace
      unset($__shell_retval);
      $__shell->resetCode();
    }
  } catch(Exception $__shell_exception) {
    print $__shell_exts->colour->getColour("exception");
    print $__shell_exception->getMessage();

    $__shell->resetCode();

    ## cleanup the variable namespace
    unset($__shell_exception);
  }
  print $__shell_exts->colour->getColour("default");                
  $__shell_exts->exectime->stopTime();
  if ($__shell_exts->exectime->isShow()) {
      printf(" (parse: %.4fms, exec: %.4fms)", 
          $__shell_exts->exectime->getParseTime(),
          $__shell_exts->exectime->getExecTime()
      );
  }
  print $__shell_exts->colour->getColour("prompt");    
}

print $__shell_exts->colour->getColour("reset");

