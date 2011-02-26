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
defined('IN_PLUCK') or exit('Access denied!');

/**
 * Get the page title.
 *
 * @return string The page title.
 */
function get_pagetitle() {
	global $lang, $module;

	//Check if we want to get the title for a page, and check whether the page exists.
	if (defined('CURRENT_PAGE_FILENAME')) {
		if (strpos(CURRENT_PAGE_FILENAME, '/') !== false) {
			$parts = explode('/', CURRENT_PAGE_FILENAME);
			$count = count($parts);
			unset($parts[$count -1]);

			$pages = $parts;
			include ('data/settings/pages/'.CURRENT_PAGE_FILENAME);
			$titles[] = $title;

			foreach ($parts as $part) {
				$page = implode('/', $pages);
				include ('data/settings/pages/'.get_page_filename($page));
				$titles[] = $title;
				$pages = explode('/', $page);
				$count = count($pages);
				unset($pages[$count -1]);
			}
			unset($part);

			//Reverse array for more logical breadcrumb-order.
			$titles = array_reverse($titles);

			$page_title = trim(implode(' &middot; ', $titles));
		}

		else {
			include ('data/settings/pages/'.CURRENT_PAGE_FILENAME);
			$page_title = $title;
		}

		//Get the title if we are looking at a module page
		if (defined('CURRENT_MODULE_DIR') && module_is_compatible(CURRENT_MODULE_DIR) && function_exists(CURRENT_MODULE_DIR.'_pages_site')) {
			$module_page_site = call_user_func(CURRENT_MODULE_DIR.'_pages_site');
			foreach ($module_page_site as $module_page) {
				if ($module_page['func'] == CURRENT_MODULE_PAGE) {
					$page_title = $page_title.' &middot; '.$module_page['title'];
					break;
				}
			}
			unset($module_page);
		}
	}

	//If page doesn't exist, and we don't want to display a module; display error.
	elseif (!defined('CURRENT_PAGE_FILENAME') && !isset($module))
		$page_title = $lang['general']['404'];

	return $page_title;
}

//[THEME] FUNCTION TO INCLUDE META-DATA IN THE PAGE
//---------------------------------
function theme_meta($reset_css = false) {
	//Get page-info (for meta-information)
	if (defined('CURRENT_PAGE_FILENAME')) {
		if (file_exists('data/settings/pages/'.CURRENT_PAGE_FILENAME))
			include ('data/settings/pages/'.CURRENT_PAGE_FILENAME);
	}
	$stylefile = 'style';


	//Allow modules to manipulate theme
	$page_theme = THEME;
	run_hook('site_theme', array(&$page_theme));

	//Allow modules to manipulate CSS-filename
	run_hook('site_theme_css', array(&$stylefile));

	//Check which CSS-file we need to use (LTR or RTL)
	if (DIRECTION_RTL)
		$cssfile = 'data/themes/'.$page_theme.'/'.$stylefile.'-rtl.css';
	else
		$cssfile = 'data/themes/'.$page_theme.'/'.$stylefile.'.css';

	echo '<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />'."\n";
	echo '<meta name="generator" content="pluck '.PLUCK_VERSION.'" />'."\n";
	echo '<title>'.PAGE_TITLE.' - '.SITE_TITLE.'</title>'."\n";
	if ($reset_css)
		echo '<link href="data/reset.css" rel="stylesheet" type="text/css" media="screen" />'."\n";
	echo '<link href="'.$cssfile.'" rel="stylesheet" type="text/css" media="screen" />'."\n";
	echo '<meta name="language" content="'.LANG.'" />'."\n";

	//If we are not looking at a module: include metatag information
	if (defined('CURRENT_PAGE_FILENAME') && file_exists('data/settings/pages/'.CURRENT_PAGE_FILENAME)) {
		echo '<meta name="title" content="'.PAGE_TITLE.'" />'."\n";
		if (isset($keywords) && !empty($keywords))
			echo '<meta name="keywords" content="'.$keywords.'" />'."\n";
		if (isset($description) && !empty($description))
			echo '<meta name="description" content="'.$description.'" />'."\n";
	}

	//If RTL, set direction to RTL in CSS
	if (DIRECTION_RTL)
		echo '<style type="text/css">body {direction:rtl;}</style>'."\n";

	run_hook('theme_meta');
}

//[THEME] FUNCTION TO SHOW SITE TITLE
//---------------------------------
function theme_sitetitle() {
	echo SITE_TITLE;
}

//[THEME] FUNCTION TO SHOW THE MENU
//---------------------------------
function theme_menu($block, $inline, $active_id = null, $level = 0, $only_subpages = false) {
	if ($only_subpages)
		$dir = 'data/settings/pages/'.CURRENT_PAGE_SEONAME;
	else
		$dir = 'data/settings/pages';

	theme_menu_data($block, $inline, $active_id, $level, $dir);
}

function theme_menu_data($block, $inline, $active_id, $level, $dir) {
	//if there is no sub-pages, just return.
	if (!is_dir($dir))
		return;

	$files = read_dir_contents($dir, 'files');

	if ($files) {
		//Sort the array.
		natcasesort($files);

		echo '<'.$block.'>';

		foreach ($files as $file) {
			include ($dir.'/'.$file);

			$file = get_page_seoname($dir.'/'.$file);
			//Only display in menu if page isn't hidden by user.
			if (isset($hidden) && $hidden == 'no') {
				//Check if we need to show an active link.
				if (defined('CURRENT_PAGE_SEONAME') && CURRENT_PAGE_SEONAME == $file && $active_id)
					echo '<'.$inline.' id="'.$active_id.'">';

				else
					echo '<'.$inline.'>';

				echo '<a href="?file='.$file.'" title="'.$title.'">'.$title.'</a>';

				preg_match_all('|\/|', $file, $page_level);
				$page_level = count($page_level[0]);

				if ($level > $page_level && is_dir('data/settings/pages/'.$file))
					theme_menu_data($block, $inline, $active_id, $level, 'data/settings/pages/'.$file);

				echo '</'.$inline.'>';
	    	}
		}
		unset($file);

		echo '</'.$block.'>';
	}
}

//[THEME] FUNCTION TO SHOW PAGE TITLE
//---------------------------------
function theme_pagetitle() {
	echo PAGE_TITLE;
}

//[THEME] FUNCTION TO SHOW PAGE CONTENTS
//---------------------------------
function theme_content() {
	//Get needed variables
	global $lang;

	//Get the contents only if we are looking at a normal page.
	if (defined('CURRENT_PAGE_SEONAME') && !defined('CURRENT_MODULE_DIR')) {
		//Check if page exists
		if (defined('CURRENT_PAGE_FILENAME') && file_exists('data/settings/pages/'.CURRENT_PAGE_FILENAME)) {
			include ('data/settings/pages/'.CURRENT_PAGE_FILENAME);
			run_hook('theme_content_before');
			run_hook('theme_content', array(&$content));

			//Check for module tags in content
			$regex = '/\{pluck (.*?)\}/';
			if (preg_match($regex, $content)) {
				//Split content in chunks.
				$content = preg_split($regex, $content, null, PREG_SPLIT_DELIM_CAPTURE);
				foreach ($content as $value) {
					//Check if chunk is a show_module command
					if (preg_match('/show_module\((.*?)\)/', $value, $matches)) {
						$module_to_include = $matches[1];
						unset ($matches);

						//Check if we need to pass a variable to the module.
						if (strpos($module_to_include, ',')) {
							$module_to_include = explode(',', $module_to_include);
							if (module_is_compatible($module_to_include[0]) && function_exists($module_to_include[0].'_theme_main'))
								call_user_func_array($module_to_include[0].'_theme_main', array(&$module_to_include[1]));
							unset($module_to_include);
						}
						//If we don't need to pass a variable, include module in regular way.
						else {
							//Check if module is compatible, and the function exists.
							if (module_is_compatible($module_to_include) && function_exists($module_to_include.'_theme_main'))
								call_user_func($module_to_include.'_theme_main');
							unset($module_to_include);
						}
					}
					//If chunk is not any module command, just display it.
					else
						echo $value;
				}
			}
			//No module tags? Display content without any change.
			else
				echo $content;

			run_hook('theme_content_after');
		}

		//If page doesn't exist, show error message
		else
			echo $lang['general']['not_found'];
	}

	//If we are looking at a module page, call the module function.
	elseif (defined('CURRENT_PAGE_SEONAME') && defined('CURRENT_MODULE_DIR')) {
			$module_page_site = call_user_func(CURRENT_MODULE_DIR.'_pages_site');
			foreach ($module_page_site as $module_page) {
				if ($module_page['func'] == CURRENT_MODULE_PAGE)
					call_user_func(CURRENT_MODULE_DIR.'_page_site_'.$module_page['func']);
			}
			unset($module_page);
		}
}

//[THEME] FUNCTION TO INCLUDE SITE-WIDE MODULES
//---------------------------------
function theme_area($place) {
	//Include info of theme (to see which modules we should include etc), but only if file exists.
	if (file_exists('data/settings/themes/'.THEME.'/moduleconf.php') && !defined('CURRENT_MODULE_DIR')) {
		include ('data/settings/themes/'.THEME.'/moduleconf.php');

		//Get the array and sort it.
		foreach ($space as $area => $number) {
			//Sort the array, so that the modules will be displayed in correct order.
			natcasesort($number);
			foreach ($number as $module => $order) {
				//If the area where the module should be displayed is the same as the area we're currently...
				//...processing: include the module.
				if ($area == $place) {
					//Check if module is compatible, and the function exists.
					if (module_is_compatible($module) && function_exists($module.'_theme_main'))
							call_user_func($module.'_theme_main');
				}
			}
		}
		unset($area);
	}
}
?>