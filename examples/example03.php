<?php

require_once( dirname( __FILE__ ) .'/../lib/Clapp.php' );

$clapp = new Clapp( 'Clapp', '0.1', Clapp::LICENSE_MIT );
$clapp->add( $z = new ClappArgument( 'z' ) );
$z->setDescription( 'simple #flag' );
	
$sub1 = new ClappSubcommand( 'Clapp Sub1' );
$sub1->setAbstract( 'do something to save the world' )
	->setCommand( 'sub-routine-one', array('sub1','s1') )
	->add( $a = new ClappArgument( 'a' ) )
	->add( $b = new ClappArgument( 'b') )
	->add( $c = new ClappArgument( 'c' ) );
$a->setDescription( 'simple #flag' );
$b->setDescription( 'simple #flag' );
$c->setDescription( 'simple #flag' );

$sub2 = new ClappSubcommand( 'Clapp Sub2' );
$sub2->setAbstract( 'hack the planet!' )
	->setCommand( 'sub-routine-two', array('sub2','s2') )
	->add( $a = new ClappArgument( 'a' ) )
	->add( $b = new ClappArgument( 'b') )
	->add( $d = new ClappArgument( 'd' ) );
$a->setDescription( 'simple #flag' );
$b->setDescription( 'simple #flag' );
$d->setDescription( 'simple #flag' );

$clapp->add( $sub1 )->add( $sub2 );

try
{
	$app = $clapp->initialize();
	
	// this is a demonstration, just display what we parsed
	$app->results();
}
catch( ClappException $e )
{
	if( $e->getMessage() )
		echo "FATAL ERROR: ", $e->getMessage(), "\n\n",
			'please see ', basename(__FILE__), ' --help', "\n";
	echo "\n";
	exit(1);
}

?>