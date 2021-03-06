<?php  // $Id: play.php,v 1.27 2012/07/25 11:16:05 bdaloukas Exp $

// This file plays the game hangman

function game_hangman_continue( $id, $game, $attempt, $hangman, $newletter, $action, $context)
{
	global $DB, $USER;

	if( $attempt != false and $hangman != false){
		if( ($action == 'nextword') and ($hangman->finishedword != 0)){
			//finish with one word and continue to another
			if( !$DB->set_field( 'game_hangman', 'finishedword', 0, array( 'id' => $hangman->id))){
				error( "game_hangman_continue: Can't update game_hangman");
			}
		}else
		{
			return game_hangman_play( $id, $game, $attempt, $hangman, false, false, $context);
		}
	}
	
	$updatehangman = (($attempt != false) and ($hangman != false));
		
	//new game
    srand ((double)microtime()*1000003);
    
    //I try 10 times to find a new question
    $found = false;
    $min_num = 0;
    $unchanged = 0;
    for($i=1; $i <= 10; $i++)
    {
		$rec = game_question_shortanswer( $game, $game->param7, false);
		if( $rec === false){
			continue;
		}

        $answer = game_upper( $rec->answertext, $game->language);
        if( $game->language == '')
        {
            $game->language = game_detectlanguage( $answer);
            $answer = game_upper( $rec->answertext, $game->language);
        }
        
        $answer2 = $answer;
        if( $game->param7){
            //Have to delete space
            $answer2 = str_replace( ' ', '', $answer2);
        }
        if( $game->param8){
            //Have to delete -
            $answer2 = str_replace( '-', '', $answer2);
        }

        $allletters = game_getallletters( $answer2, $game->language);
		
        if( $allletters == ''){
            continue;
        }

        if( $game->param7){
            $allletters .= '_';
        }        
        if( $game->param8){
            $allletters .= '-';
        }        
		
		if( $game->param7 == false){   
		    //I don't allow spaces
    		if( strpos( $answer, " ")){
	    		continue;
	    	}
	   }
	   
	    $copy = false;
        $select2 = 'gameid=? AND userid=? AND questionid=? AND glossaryentryid=?';
        if( ($rec2 = $DB->get_record_select( 'game_repetitions', $select2, array( $game->id, $USER->id, $rec->questionid, $rec->glossaryentryid), 'id,repetitions r')) != false){
            if( ($rec2->r < $min_num) or ($min_num == 0)){
                $min_num = $rec2->r;
                $copy = true;
            }
        }else
        {
            $min_num = 0;
            $copy = true;
        }
       
        if( $copy){
            $found = true;
            
            $min = new stdClass();
            $min->questionid = $rec->questionid;
            $min->glossaryentryid = $rec->glossaryentryid;
            $min->attachment = $rec->attachment;
            $min->questiontext = $rec->questiontext;
            $min->answerid = $rec->answerid;
            $min->answer = $answer;
            $min->language = $game->language;
            $min->allletters = $allletters;
            if( $min_num == 0)
                break;  //We found an unused word
        }else
            $unchanged++;
            
        if( $unchanged > 2)
        {
            if( $found)
                break;
        }
	}
	
	if( $found == false){
	    print_error( get_string( 'no_words', 'game'));
	}
	
	//Found one word for hangman
    if( $attempt == false){
        $attempt = game_addattempt( $game);
    }
    if( !$DB->set_field( 'game_attempts', 'language', $min->language, array( 'id' => $attempt->id))){
       print_error( "game_hangman_continue: Can't set language");
    }
		 
    $query = new stdClass();		        
    $query->attemptid = $attempt->id;
	$query->gameid = $game->id;
	$query->userid = $USER->id;
	$query->sourcemodule = $game->sourcemodule;
	$query->questionid = $min->questionid;
	$query->glossaryentryid = $min->glossaryentryid;
	$query->attachment = $min->attachment;
	$query->questiontext = addslashes( $min->questiontext);
	$query->score = 0;
	$query->timelastattempt = time();
	$query->answertext = $min->answer;
	$query->answerid = $min->answerid;
	if( !($query->id = $DB->insert_record( 'game_queries', $query))){
		print_object( $query);
		print_error( "game_hangman_continue: Can't insert to table game_queries");
	}
		
	$newrec = new stdClass();
	$newrec->id = $attempt->id;
	$newrec->queryid = $query->id;
	if( $updatehangman == false){
		$newrec->maxtries = $game->param4;
		if( $newrec->maxtries == 0){
			$newrec->maxtries = 1;
		}
		$newrec->finishedword = 0;
		$newrec->corrects = 0;
	}
		
	$newrec->allletters = $min->allletters;
		
	$letters = '';
	if( $game->param1){
		$letters .= textlib::substr( $min->answer, 0, 1);
	}
	if( $game->param2){
		$letters .= textlib::substr( $min->answer, -1, 1);
	}
	$newrec->letters = $letters;

	if( $updatehangman == false){
		if( !game_insert_record(  'game_hangman', $newrec)){
			print_error( 'game_hangman_continue: error inserting in game_hangman');
		}	
	}else
	{
		if( !$DB->update_record(  'game_hangman', $newrec)){
			print_error( 'game_hangman_continue: error updating in game_hangman');
		}
		$newrec = $DB->get_record( 'game_hangman', array( 'id' => $newrec->id));
	}
		
	game_update_repetitions( $game->id, $USER->id, $query->questionid, $query->glossaryentryid);
		
    game_hangman_play( $id, $game, $attempt, $newrec, false, false, $context);
}

function game_hangman_onfinishgame( $game, $attempt, $hangman)
{
    global $DB;

	$score = $hangman->corrects / $hangman->maxtries;

	game_updateattempts( $game, $attempt, $score, true);

	if( !$DB->set_field( 'game_hangman', 'finishedword', 0, array( 'id' => $hangman->id))){
		print_error( "game_hangman_onfinishgame: Can't update game_hangman");
	}
}

function game_hangman_play( $id, $game, $attempt, $hangman, $onlyshow, $showsolution, $context)
{
	global $CFG, $DB, $OUTPUT;
	
	$query = $DB->get_record( 'game_queries', array( 'id' => $hangman->queryid));
	
    if( $attempt->language != '')
        $wordrtl = game_right_to_left( $attempt->language);
    else
        $wordrtl = right_to_left();
    $reverseprint = ($wordrtl != right_to_left());

	if( $game->toptext != ''){
		echo $game->toptext.'<br>';
	}

    $max=$game->param10;		// maximum number of wrong
    if( $max <= 0)
        $max = 6;
    hangman_showpage( $done, $correct, $wrong, $max, $word_line, $word_line2, $links,  $game, $attempt, $hangman, $query, $onlyshow, $showsolution, $context);

    if (!$done)
    {
        if ($wrong > $max){
            $wrong = $max;
        }
		if( $game->param3 == 0){
			$game->param3 = 1;
		}
        echo "\r\n<br/><img src=\"".$OUTPUT->pix_url('hangman/'.$game->param3.'/hangman_'.$wrong, 'mod_game')."\"";
		$message  = sprintf( get_string( 'hangman_wrongnum', 'game'), $wrong, $max);
		echo ' ALIGN="MIDDLE" BORDER="0" HEIGHT="100" alt="'.$message.'"/>';

        if ($wrong >= $max){

			//This word is incorrect. If reach the max number of word I have to finish else continue with next word
			hangman_onincorrect( $id, $word_line, $query->answertext, $game, $attempt, $hangman, $onlyshow, $showsolution);
        }else
		{
            $i = $max-$wrong;
            if( $i > 1)
    			echo ' '.get_string( 'hangman_restletters_many', 'game', $i);
            else        
    			echo ' '.get_string( 'hangman_restletters_one', 'game');

            if( $reverseprint){
                echo '<SPAN dir="'.($wordrtl ? 'rtl' : 'ltr').'">';
            }

            echo "<br/><font size=\"5\">\n$word_line</font>\r\n";
			if( $word_line2 != ''){
				echo "<br/><font size=\"5\">\n$word_line2</font>\r\n";
			}

            if( $reverseprint){
                echo "</SPAN>";
            }

			if( $hangman->finishedword == false){
				echo "<br/><br/><BR/>".get_string( 'hangman_letters', 'game').$links."\r\n";
			}
        }
	}else
	{
		//This word is correct. If reach the max number of word I have to finish else continue with next word
		hangman_oncorrect( $id, $word_line, $game, $attempt, $hangman, $query);
	}
		    
	echo "<br/><br/>".get_string( 'grade', 'game').' : '.round( $query->percent * 100).' %';
	if( $hangman->maxtries > 1){
		echo '<br/><br/>'.get_string( 'hangman_gradeinstance', 'game').' : '.round( $hangman->corrects / $hangman->maxtries * 100).' %';
	}
	
	if( $game->bottomtext != ''){
		echo '<br><br>'.$game->bottomtext;
	}
}

function hangman_showpage(&$done, &$correct, &$wrong, $max, &$word_line, &$word_line2, &$links, $game, &$attempt, &$hangman, &$query, $onlyshow, $showsolution, $context)
{
	global	$USER, $CFG, $DB;

    $id = optional_param('id', 0, PARAM_INT); // Course Module ID, or    
	
	$word = $query->answertext;
	
    $newletter  = optional_param('newletter', "", PARAM_TEXT);
	if( $newletter == '_'){
	    $newletter = ' ';
	}

    $letters = $hangman->letters;
    if( $newletter != NULL)
    {
		if( textlib::strpos( $letters,$newletter) === false){
			$letters .= $newletter;
		}
    }

    $links="";

    $alpha = $hangman->allletters;
    $wrong = 0;
		
	if( $query->questionid)
	{
        $query->questiontext = game_filterquestion(str_replace( '\"', '"', $query->questiontext), $query->questionid, $context->id, $game->course);
    }else
    {
        $cmglossary = get_coursemodule_from_instance('glossary', $game->glossaryid, $game->course);
        $contextglossary = get_context_instance(CONTEXT_MODULE, $cmglossary->id);        
        $query->questiontext = game_filterglossary(str_replace( '\"', '"', $query->questiontext), $query->glossaryentryid, $contextglossary->id, $game->course);
    }
		
    if( $game->param5){
        $s = trim( game_filtertext( $query->questiontext, $game->course));
        if( $s != '.' and $s <> ''){
    		echo "<br/><b>".$s.'</b>';
        }
		if( $query->attachment != ''){
            $file = "{$CFG->wwwroot}/file.php/$game->course/moddata/$query->attachment";
		    echo "<img src=\"$file\" />";
		}
		echo "<br/><br/>";
	}

    $word_line = $word_line2 = "";
	
	$len = textlib::strlen( $word);
	
	$done = 1;
	$answer = '';
    $correct = 0;
    for ($x=0; $x < $len; $x++)
    {
		$char = textlib::substr( $word, $x, 1);
		
		if( $showsolution){
			$word_line2 .= ( $char == " " ? '&nbsp; ' : $char);
			$done = 0;
		}
		
		if ( textlib::strpos($letters, $char)  === false){
			$word_line.="_<font size=\"1\">&nbsp;</font>\r\n";
			$done = 0;
			$answer .= '_';
		}else		
		{
			$word_line .= ( $char == " " ? '&nbsp; ' : $char);
			$answer .= $char;
			$correct++;
		}
    }

    $len_alpha = textlib::strlen($alpha);
	$fontsize = 5;

    for ($c=0; $c < $len_alpha; $c++)
    {
		$char = textlib::substr( $alpha, $c, 1);
		
		if ( textlib::strpos($letters, $char) === false)
		{
			//User doesn't select this character
			$params = 'id='.$id.'&amp;newletter='.urlencode( $char);
			if( $onlyshow or $showsolution){
				$links .= $char;
			}else
			{
				$links .= "<font size=\"$fontsize\"><a href=\"attempt.php?$params\">$char</a></font>\r\n";
			}
			continue;
		}
		
		if ( textlib::strpos($word, $char) === false)
		{
			$links .= "\r\n<font size=\"$fontsize\" color=\"red\">$char </font>";
			$wrong++;
		}else
		{
			$links .= "\r\n<B><font size=\"$fontsize\">$char </font></B> ";
		}
	}
	$finishedword = ($done or $wrong >= $max);
	$finished = false;

    $updrec = new stdClass();
	$updrec->id = $hangman->id;
	$updrec->letters = $letters;
	if( $finishedword){
		if( $hangman->finishedword == 0){
			//only one time per word increace the variable try
			$hangman->try = $hangman->try + 1;
			if( $hangman->try > $hangman->maxtries){
				$finished = true;
			}
			if( $done){
				$hangman->corrects = $hangman->corrects + 1;
				$updrec->corrects = $hangman->corrects;
			}
		}

		$updrec->try = $hangman->try;
		$updrec->finishedword = 1;
		
	}

	$query->percent = ($correct -$wrong/$max) /  textlib::strlen( $word);
	if( $query->percent < 0){
		$query->percent = 0;
	}

	if( $onlyshow or $showsolution){
		return;
	}
	
	if( !$DB->update_record( 'game_hangman', $updrec)){
		print_error( "hangman_showpage: Can't update game_hangman id=$updrec->id");
	}
	
	if( $done){
		$score = 1;
	}else if( $wrong >= $max){
		$score = 0;
	}else
	{
		$score = -1;
	}
	
	game_updateattempts( $game, $attempt, $score, $finished);
	game_update_queries( $game, $attempt, $query, $score, $answer);
}

//This word is correct. If reach the max number of words I have to finish else continue with next word
function hangman_oncorrect( $id, $word_line, $game, $attempt, $hangman, $query)
{
    global $DB;

	echo "<BR/><BR/><font size=\"5\">\n$word_line</font>\r\n";
	
	echo '<p><BR/><font size="5" color="green">'.get_string( 'win', 'game').'</font><BR/><BR/></p>';
	if( $query->answerid){
		$feedback = $DB->get_field( 'question_answers', 'feedback', array( 'id' => $query->answerid));
		if( $feedback != ''){
			echo "$feedback<br>";
		}
	}

	game_hangman_show_nextword( $id, $game, $attempt, $hangman);
}

function hangman_onincorrect( $id, $word_line, $word, $game, $attempt, $hangman, $onlyshow, $showsolution)
{
	echo "\r\n<BR/><BR/><font size=\"5\">\n$word_line</font>\r\n";
	
    if( $onlyshow or $showsolution)
	    return;
	    
	echo '<p><BR/><font size="5" color="red">'.get_string( 'hangman_loose', 'game').'</font><BR/><BR/></p>';
	
	if( $game->param6){
		//show the correct answer
		if( textlib::strpos($word, ' ') != false)
		    echo '<br/>'.get_string( 'hangman_correct_phrase', 'game');
        else
		    echo '<br/>'.get_string( 'hangman_correct_word', 'game');
        		
		echo '<B>'.$word."</B><BR/><BR/>\r\n";
	}
	
	game_hangman_show_nextword( $id, $game, $attempt, $hangman, true);	
}

function game_hangman_show_nextword( $id, $game, $attempt, $hangman)
{
	global $CFG, $DB;
	
	echo '<br/>';
	if( ($hangman->try < $hangman->maxtries) or ($hangman->maxtries == 0)){
		//continue to next word
		$params = "id=$id&action2=nextword\">".get_string( 'nextword', 'game').'</a> &nbsp; &nbsp; &nbsp; &nbsp;'; 
		echo "<a href=\"{$CFG->wwwroot}/mod/game/attempt.php?$params";
	}else
	{
		game_hangman_onfinishgame( $game, $attempt, $hangman);
		echo "<a href=\"{$CFG->wwwroot}/mod/game/attempt.php?id=$id\">".get_string( 'nextgame', 'game').'</a> &nbsp; &nbsp; &nbsp; &nbsp; ';
	}
	
	if (! $cm = $DB->get_record('course_modules', array( 'id' => $id))) {
		print_error( "Course Module ID was incorrect id=$id");
	}

	echo "<a href=\"{$CFG->wwwroot}/course/view.php?id=$cm->course\">".get_string( 'finish', 'game').'</a> ';
}
