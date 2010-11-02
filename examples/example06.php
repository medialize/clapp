<?php

require_once( dirname( __FILE__ ) .'/../lib/Clapp.php' );

$control = ClappTTY::control();

$d = ClappTTY::dimensions();
$control->position( $d['rows'], $d['columns'] )->position();
echo "The Screen will look pretty messy when this example is done. ",
	"Don't worry, this is an *expected* result. The thing you should ",
	"learn from this example: YOU are responsible to clean up after yourself.\n\n\n\n";

$i = 0;
while( true )
{
	if( $i % 10 == 0 )
		echo "\n";
		
	if( $i < 10 )
	{
		$control->clearLine()->column();
		echo 'clearLine()->column() ', $i++;
	}
	else if( $i < 20 )
	{
		$control->left(8);
		echo '        left(5) ', $i++;
		$p = $control->getPosition();
		echo ' POS[', $p['row'], ',', $p['col'], ']';
	}
	else if( $i < 21 )
	{
		$i++;
		$control->line(-3);
		echo 'up we go -- ';
		$p = $control->getPosition();
		echo ' POS[', $p['row'], ',', $p['col'], ']';		
	}
	else
		break;
	
	usleep( 500000 );
}

echo "\n";

?>