<?php

if( function_exists( 'posix_getpid' ) )
	echo 'My PID is ', posix_getpid(), "\n";

require_once( dirname( __FILE__ ) .'/../lib/Clapp.php' );

// PHP < 5.3 needs ticks, PHP >= 5.3 doesn't!
if( ClappSignals::needTicks() )
	declare(ticks = 1);

$clapp = new Clapp( 'Clapp Signals' );
$clapp->signals();

// FIXME: Signal Handling seems to fail when unknown signals (eg kill -5 $pid) are sent and afterwards ^C is requested
// ^CIllegal instruction
// ^CTrace/BPT trap

/*
// setup signal listeners
ClappSignals::init();

// please terminate gracefully
ClappSignals::listen( SIGTERM, array( 'ClappSignals', 'exitOnSignal' ) );

// user asked to interrupt program
ClappSignals::listen( SIGINT, array( 'ClappSignals', 'exitOnSignal' ) );

// user asked to interrupt program (and write core-dump)
ClappSignals::listen( SIGQUIT, array( 'ClappSignals', 'exitOnSignal' ) );

// something hung up, call back? :)
ClappSignals::listen( SIGHUP, array( 'ClappSignals', 'exitOnSignal' ) );
*/
// do some work
while( true )
{
	// buffer signals until atomic part is done
	ClappSignals::beginAtomic();
	
	// u/sleep()s can be interrupted by signals
	sleep( 1 );
	
	// consume signals caught while in atomic mode
	ClappSignals::endAtomic();
}



?>