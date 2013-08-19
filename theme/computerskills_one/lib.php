<?php

/**
 * Functions needed by the computerskills_one theme should be put here.
 *
 * Any functions that get created here should ALWAYS contain the theme name
 * to reduce complications for other theme designers who may be copying this theme.
 */
 
  function theme_computerskills_one_page_init(moodle_page $page) {
    $page->requires->jquery();
    $page->requires->jquery_plugin('expander', 'theme_skonline');
	$page->requires->jquery_plugin('expander_custom', 'theme_skonline');
 }