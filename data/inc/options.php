<?php
/*
 * This file is part of pluck, the easy content management system
 * Copyright (c) somp (www.somp.nl)
 * http://www.pluck-cms.org

 * Pluck is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.

 * See docs/COPYING for the complete license.
*/

//Make sure the file isn't accessed directly.
if (!strpos($_SERVER['SCRIPT_FILENAME'], 'index.php') && !strpos($_SERVER['SCRIPT_FILENAME'], 'admin.php') && !strpos($_SERVER['SCRIPT_FILENAME'], 'install.php') && !strpos($_SERVER['SCRIPT_FILENAME'], 'login.php')) {
	//Give out an "Access denied!" error.
	echo 'Access denied!';
	//Block all other code.
	exit;
}
?>
	<p>
		<strong><?php echo $lang_options1; ?></strong>
	</p>
<?php
run_hook('admin_options_before');
showmenudiv($lang['settings']['title'], $lang_settings3, 'data/image/page.png', '?action=settings');
showmenudiv($lang_modules3, $lang_modules4, 'data/image/modules.png', '?action=managemodules');
showmenudiv($lang['theme']['title'], $lang_options3, 'data/image/themes.png', '?action=theme');
showmenudiv($lang['language']['title'], $lang_options8, 'data/image/language.png', '?action=language');
showmenudiv($lang['changepass']['title'], $lang_options5, 'data/image/password.png', '?action=changepass');
run_hook('admin_options_after');
?>