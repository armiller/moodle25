<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

// link color setting
$name = 'theme_bbytefusionmod/linkcolor';
$title = get_string('linkcolor','theme_bbytefusionmod');
$description = get_string('linkcolordesc', 'theme_bbytefusionmod');
$default = '#2d83d5';
$previewconfig = NULL;
$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
$settings->add($setting);


// Tag line setting
$name = 'theme_bbytefusionmod/tagline';
$title = get_string('tagline','theme_bbytefusionmod');
$description = get_string('taglinedesc', 'theme_bbytefusionmod');
$setting = new admin_setting_configtextarea($name, $title, $description, '');
$settings->add($setting);

// Foot note setting
$name = 'theme_bbytefusionmod/footertext';
$title = get_string('footertext','theme_bbytefusionmod');
$description = get_string('footertextdesc', 'theme_bbytefusionmod');
$setting = new admin_setting_confightmleditor($name, $title, $description, '');
$settings->add($setting);

// Custom CSS file
$name = 'theme_bbytefusionmod/customcss';
$title = get_string('customcss','theme_bbytefusionmod');
$description = get_string('customcssdesc', 'theme_bbytefusionmod');
$setting = new admin_setting_configtextarea($name, $title, $description, '');
$settings->add($setting);

}