<?php

//require_once( dirname( __FILE__ ) .'/../lib/Clapp.php' );

require_once( dirname( __FILE__ ) .'/../lib/util/progress/ClappProgress.php' );


echo "Progress->proceed()\n";
$progress = new ClappProgress( 150 );
usleep( 500000 );
for( $i=0; $i < 153; $i++ )
{
	$progress->proceed();
	usleep( 10000 );
}

echo "\nProgress->progress()\n";
$progress = new ClappProgress();
usleep( 500000 );
for( $i=0; $i < 153; $i++ )
{
	$progress->progress( $i / 152 );
	usleep( 10000 );
}

echo "\n";

?>