<?php

$hasheading = ($PAGE->heading);
$hasnavbar = (empty($PAGE->layout_options['nonavbar']) && $PAGE->has_navbar());
$hasfooter = (empty($PAGE->layout_options['nofooter']));
$hassidepre = $PAGE->blocks->region_has_content('side-pre', $OUTPUT);
$hassidepost = $PAGE->blocks->region_has_content('side-post', $OUTPUT);
$showsidepre = $hassidepre && !$PAGE->blocks->region_completely_docked('side-pre', $OUTPUT);
$showsidepost = $hassidepost && !$PAGE->blocks->region_completely_docked('side-post', $OUTPUT);

//Allows theme to show custom menu items
$custommenu = $OUTPUT->custom_menu();
$hascustommenu = (empty($PAGE->layout_options['nocustommenu']) && !empty($custommenu));

$bodyclasses = array();
if ($showsidepre && !$showsidepost) {
    $bodyclasses[] = 'side-pre-only';
} else if ($showsidepost && !$showsidepre) {
    $bodyclasses[] = 'side-post-only';
} else if (!$showsidepost && !$showsidepre) {
    $bodyclasses[] = 'content-only';
}
if ($hascustommenu) {
    $bodyclasses[] = 'has_custom_menu';
}
echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes() ?>>
<head>
    <title><?php echo $PAGE->title ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme')?>" />
    <?php echo $OUTPUT->standard_head_html() ?>
</head>
<body id="<?php echo $PAGE->bodyid ?>" class="<?php echo $PAGE->bodyclasses.' '.join(' ', $bodyclasses) ?>">
<?php echo $OUTPUT->standard_top_of_body_html() ?>

<div id="page">
<?php if ($hasheading || $hasnavbar) { ?>
    <div id="page-header">
        <div class="rounded-corner top-left"></div>
        <div class="rounded-corner top-right"></div>
        <?php if ($hasheading) { ?>
        <h1 class="headermain"><?php echo $PAGE->heading ?></h1>
        <div class="headermenu"><?php
            echo $OUTPUT->login_info();
            if (!empty($PAGE->layout_options['langmenu'])) {
                echo $OUTPUT->lang_menu();
            }
            echo $PAGE->headingmenu
        ?></div><?php } ?>
        
        <?php if ($hascustommenu) { ?>
        <div id="custommenu"><?php echo $custommenu; ?></div>
        <?php } ?>
        
		<?php if ($hasnavbar) { ?>
            <div class="navbar clearfix">
                <div class="breadcrumb"><?php echo $OUTPUT->navbar(); ?></div>
                <div class="navbutton"><?php echo $PAGE->button; ?></div>
            </div>
        <?php } ?>
    </div>
<?php } ?>
<!-- END OF HEADER -->

    <div id="page-content">
        <div id="region-main-box">
            <div id="region-post-box">

                <div id="region-main-wrap">
                    <div id="region-main">
                        <div class="region-content">
                            <?php echo core_renderer::MAIN_CONTENT_TOKEN ?>
                        	<!--Adds a link to the main course page to the bottom of every page (or to My Moodle if not inside a course)-->
							<?php echo '<div class="BackToMainPage"><a href="' . $CFG->wwwroot . '/course/view.php?id=' . $COURSE->id . '">Back to Main Page</a></div>'; ?>
                        </div>
                    </div>
                </div>

                <?php if ($hassidepre) { ?>
                <div id="region-pre" class="block-region">
                    <div class="region-content">
                        <?php echo $OUTPUT->blocks_for_region('side-pre') ?>
                    </div>
                </div>
                <?php } ?>

                <?php if ($hassidepost) { ?>
                <div id="region-post" class="block-region">
                    <div class="region-content">
                        <?php echo $OUTPUT->blocks_for_region('side-post') ?>
                    </div>
                </div>
                <?php } ?>

            </div>
        </div>      
    </div>
    
	
	<script>//Make customized changes to jquery.expander here:
// you can override default options globally, so they apply to every .expander() call
$.expander.defaults.slicePoint = 120;

$(document).ready(function() {
  // simple example, using all default options unless overridden globally
  //$('div.expandable p').expander();

  // *** OR ***
  
  // override default options (also overrides global overrides)
  //$('div.expandable p:gt(0)').expander({
   // slicePoint:       80,  // default is 100
    //expandPrefix:     ' ', // default is '... '
    //expandText:       '[...]', // default is 'read more'
    //collapseTimer:    5000, // re-collapses after 5 seconds; default is 0, so no re-collapsing
    //userCollapseText: '[^]'  // default is 'read less'
 // });
  
   $('div#Announcements h1:gt(0)~*').expander({
    slicePoint:       80,  // default is 100 
  });
});</script>

<!-- START OF FOOTER -->
    <?php if ($hasfooter) { ?>
    <div id="page-footer" class="clearfix">
        <p class="helplink"><?php echo page_doc_link(get_string('moodledocslink')) ?>

</p>
    <?php
        echo $OUTPUT->login_info();
        echo $OUTPUT->home_link();
        //Moved echo $OUTPUT->standard_footer_html(); from here
        ?>
        <div class="rounded-corner bottom-left"></div>
        <div class="rounded-corner bottom-right"></div>
		
        <?php //To here
		echo $OUTPUT->standard_footer_html(); ?>
    </div>    
    <?php } ?>
</div>
<?php echo $OUTPUT->standard_end_of_body_html() ?>
</body>
</html>