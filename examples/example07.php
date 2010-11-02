<?php

require_once( dirname(__FILE__) .'/../lib/tty/ClappTTY.php' );
require_once( dirname(__FILE__) .'/../lib/tty/ClappTTYInput.php' );
require_once( dirname(__FILE__) .'/../lib/encoding/ClappEncoding.php' );
require_once( dirname(__FILE__) .'/../lib/util/dialog/ClappDialog.php' );
require_once( dirname(__FILE__) .'/../lib/util/dialog/ClappDialogErrors.php' );


$format = ClappTTY::format();
echo $format->wordwrap( "iamaverylong". $format->format("wordwihtout", ClappTTYFormat::FG_RED) ."anyspacesordotsorcommas". $format->format("justtosee", ClappTTYFormat::FG_RED) ."whatourlittlewordwrapperwilldotothislineaaabbbcccdddeeefffggghhhiiijjjkkklllmmmnnnooopppqqqrrrssstttuuuvvvwwwxxxyyyzzz" );

echo "\nlalalalal lalal foobar \n";

trigger_error( 'oops I did it again…', E_USER_NOTICE );
echo "lalalalal lalal foobar \n";

trigger_error( 'oops I did it again…', E_USER_WARNING );
echo "lalalalal lalal foobar \n";

//trigger_error( 'oops I did it again…', E_USER_ERROR );
trigger_error( 'oops I did it again…', E_USER_NOTICE );
echo "lalalalal lalal foobar \n";


?>