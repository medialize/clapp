<?php

require_once( dirname( __FILE__ ) .'/../lib/Clapp.php' );

// some formatting fun
$tty = ClappTTY::format();
echo $tty->format( "I'm a BOLD piece of text", ClappTTYFormat::BOLD ), "\n",
	$tty->format( "I'm an HALF_BRIGHT piece of text", ClappTTYFormat::HALF_BRIGHT ), "\n",
	$tty->format( "I'm an UNDERLINEd piece of text", ClappTTYFormat::UNDERLINE ), "\n",
	$tty->format( "I'm a BLINKing piece of text", ClappTTYFormat::BLINK ), "\n",
	$tty->format( "I'm a FAST BLINKing piece of text", ClappTTYFormat::BLINK_FAST ), "\n",
	$tty->format( "I'm a FAST CONCEALed piece of text", ClappTTYFormat::CONCEAL ), "\n",
	$tty->format( "I'm a FAST CROSSed piece of text", ClappTTYFormat::CROSS ), "\n",
	$tty->format( "I'm an INVERTed piece of text", ClappTTYFormat::INVERT ), "\n",
	$tty->format( "I'm a FONT1 piece of text", ClappTTYFormat::FONT1 ), "\n",
	$tty->format( "I'm a FONT2 piece of text", ClappTTYFormat::FONT2 ), "\n",
	$tty->format( "I'm a FONT3 piece of text", ClappTTYFormat::FONT3 ), "\n",
	$tty->format( "I'm an FRAKTUR piece of text", ClappTTYFormat::FRAKTUR ), "\n",
	$tty->format( "I'm an FRAME piece of text", ClappTTYFormat::FRAME ), "\n",
	$tty->format( "I'm an CIRCLE piece of text", ClappTTYFormat::CIRCLE ), "\n",
	$tty->format( "I'm an OVERLINE piece of text", ClappTTYFormat::OVERLINE ), "\n",
	$tty->format( "I'm an UNDERLINE_DOUBLE piece of text", ClappTTYFormat::UNDERLINE_DOUBLE ), "\n",
	$tty->format( "I'm an INTENSITY_NORMAL piece of text", ClappTTYFormat::INTENSITY_NORMAL ), "\n",
	$tty->format( "I'm a RED piece of text", ClappTTYFormat::FG_RED ), "\n",
	$tty->format( "I'm a piece of text on a GREEN background", ClappTTYFormat::BG_GREEN ), "\n",
	$tty->format( "I'm a piece of text on a HALF_BRIGHT GREEN background", 
		array( ClappTTYFormat::BG_GREEN, ClappTTYFormat::HALF_BRIGHT ) ), "\n",
	$tty->format( "I'm a BOLD and UNDERLINEd MAGENTA piece of text on a CYAN background", 
		array( ClappTTYFormat::FG_MAGENTA, ClappTTYFormat::BG_CYAN, ClappTTYFormat::BOLD, ClappTTYFormat::UNDERLINE ) ), "\n";
	

?>