<?php

/**
 * Functions needed by the comm_skills theme should be put here.
 *
 * Any functions that get created here should ALWAYS contain the theme name
 * to reduce complications for other theme designers who may be copying this theme.
 */
 function theme_comm_skills_page_init(moodle_page $page) {
    $page->requires->jquery();
    $page->requires->jquery_plugin('expander', 'theme_skonline');
	$page->requires->jquery_plugin('expander_custom', 'theme_skonline');
 }
 ?>