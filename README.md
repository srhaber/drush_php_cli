Drush PHP-CLI
=============

This Drush command opens an interactive PHP shell for a Drupal site.  It uses 
the Readline library if available.

This command is useful for quickly testing code snippets, making DB queries, 
and so forth.  For  more robust scripts, use the php-script or write a 
custom drush command.

Example usage:

	> drush php-cli
	
	>> $u = user_load(1);
	(stores user object into variable $u)
	
	>> print_r($u);
	(prints user object)

	>> $count = db_result(db_query("SELECT COUNT(*) FROM users"));
	(stores value into variable $count)
	

The PHP\_Shell library is available here:
<http://jan.kneschke.de/projects/php-shell>