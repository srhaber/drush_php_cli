<?php
@ob_end_clean();
error_reporting(E_ALL);
set_time_limit(0);

$include_path = get_include_path() . ':' . dirname(__FILE__) . '/lib/PHP_Shell-0.3.1';
set_include_path($include_path);

require_once "PHP/Shell.php";

$__shell = new PHP_Shell();
$__cmd = PHP_Shell_Commands::getInstance();

$f = <<<EOF
PHP-Barebone-Shell - Version %s%s
(c) 2006, Jan Kneschke <jan@kneschke.de>

>> use '?' to open the inline help 

EOF;

printf($f, 
  $__shell->getVersion(), 
  $__shell->hasReadline() ? ', with readline() support' : '');
unset($f);

while($__shell->input()) {
  try {
    if ($__shell->parse() == 0) {
      ## we have a full command, execute it
      $__shell_retval = eval($__shell->getCode()); 
      if (isset($__shell_retval)) {
        // WORKAROUND for var_export error "Nesting level too deep - recursive dependency":
        ob_start();
        var_dump($__shell_retval);
        $dataDump = ob_get_clean();
        echo $dataDump;        
      }
      $_ = (isset($__shell_retval)) ? $__shell_retval : $_;

      ## cleanup the variable namespace
      unset($__shell_retval);
      $__shell->resetCode();
    }
  } 
  catch(Exception $__shell_exception) {
    print $__shell_exception->getMessage();
    $__shell->resetCode();
    
    ## cleanup the variable namespace
    unset($__shell_exception);
  }
}
