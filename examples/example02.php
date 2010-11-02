<?php

require_once( dirname( __FILE__ ) .'/../lib/Clapp.php' );

$clapp = new Clapp( 'Clapp', '0.1', Clapp::LICENSE_MIT );
$clapp->addComment( 'Im a comment' )->addComment( 'And this is another comment' )
	->addVersion( 'some library', '12.3' )->addVersion( 'some single line version thing' )
	->setCopyright( 2010, 2015, 'medialize.de' )
	->addCopyright( 'PHP', 1955, 2035, 'The PHP Group' )
	->addCopyright( 'Mac OS X', 1999, 2004, 'Steve Jobs' )
	->addBugsURL( 'http://code.google.com/p/clapp/issues/list' )
	->addHelpURL( 'http://code.google.com/p/clapp/w/list' )
	->addHomeURL( 'http://code.google.com/p/clapp/' )
	->addHeader( 'Header line 1' )
	->addHeader( 'Header line 2' )
	->addFooter( 'Footer line 1' )
	->addFooter( 'Footer line 2' )
	->addExample( 'lala blubber', 'lala blubber' )
	->addAuthor( 'Rodney Rehm', 'rodney.rehm@medialize.de' )
	->addAuthor( 'Superman' );

$clapp->add( $a = new ClappArgument( 'a' ) )
	->add( $b = new ClappArgument( 'b', 'bravo' ) )
	->add( $c = new ClappArgument( 'c', 'charlie' ) )
	// -abc -a -b -c
	
	->add( $d = new ClappArgument( 'd', 'default', ClappArgument::VALUE ) )
	->add( $m = new ClappArgument( 'm', 'mandatory', ClappArgument::VALUE ) )
	
	->add( $da = new ClappArgument( null, 'depend-on-a' ) )
	->add( $dab = new ClappArgument( null, 'depend-on-a-and-b' ) )
	->add( $dd = new ClappArgument( null, 'depend-on-dependency' ) )
	
	->add( $ca = new ClappArgument( null, 'conflict-a' ) )
	
	->add( $f = new ClappArgument( 'f', 'foxtrot', ClappArgument::FLAGS ) )
	
	->add( $vf = new ClappArgument( '0', 'value-first', ClappArgument::VALUE_FIRST ) )
	->add( $vl = new ClappArgument( '1', 'value-last', ClappArgument::VALUE_LAST ) )
	->add( $va = new ClappArgument( '2', 'value-all', ClappArgument::VALUE_ALL ) )
	
	->add( $kv = new ClappArgument( '3', 'keyvalues', ClappArgument::KEYVALUES ) )
	->add( $kva = new ClappArgument( '4', 'keyvalues-all', ClappArgument::KEYVALUES_ALL ) )
	->add( $vld = new ClappArgument( null, 'very-long-description' ) )
	;
	
	$a->setDescription( 'simple #flag' );
	$b->setDescription( 'simple #flag' );
	$c->setDescription( 'simple #flag' );
	$d->setDescription( '#value defaulting to "DefaultFoobar"' )->setDefault( 'DefaultFoobar' );
	$m->setDescription( 'a #value that must be specified' )->setMandatory();
	
	$da->setDescription( '#flag depending on -a being specified' );
	$dab->setDescription( '#flag depending on -a and -b being specified' );
	$dd->setDescription( '#flag depending on --depend-on-dependency (and thus -a) being specified' );

	$ca->setDescription( '#flag conflicting with -a' );
	
	$f->setDescription( '#flags (counting #flag occurences)' );
	
	$vf->setDescription( '#value_first (first of all #value occurences)' );
	$vl->setDescription( '#value_last (last of all #value occurences)' );
	$va->setDescription( '#values (list of all #value occurences)' );
	
	$kv->setDescription( '#keyvalues (list of #value each split by = resulting in {key=val, key2=val, …})' );
	$kva->setDescription( '#keyvalues_all (list of #value each split by = resulting in [[key,val],[key2,val]]' );
	
	$vld->setDescription( 'Im a very long description. I should do an appropriate linebreak and indenting as you expect me to do. Now this seems to be working just fine for the moment. nice, very nice.' );

$clapp->add( $valueGroup = new ClappArgumentGroup( 'Value Options' ) );
	$valueGroup->add( $d )->add( $m )->add( $vf )->add( $vl )->add( $va );
	
$clapp->add( $kvGroup = new ClappArgumentGroup( 'KeyValue Options' ) );
	$kvGroup->add( $kv )->add( $kva );

$clapp->add( $mandatoryGroup = new ClappArgumentGroup( 'Mandatory Options' ) );
	$mandatoryGroup->add( $m );

$clapp->add( $dcGroup = new ClappArgumentGroup( 'Dependent/Conflicting Options' ) );
	$dcGroup->add( $da )->add( $dab )->add( $dd )->add( $ca );
	
$clapp->add( $flagGroup = new ClappArgumentGroup( 'Flag Options' ) );
	$flagGroup->add( $a )->add( $b )->add( $c )->add( $ca )->add( $f )->add( $vld );
	
try
{
	//$args->parse();
	$clapp->initialize( true );
	
	// show examples
	if( !$clapp->args->specified() )
	{
		echo "Clapp 0.1 - Hassle Free CommandLine Arguments\n",
			"(for the sake of these examples the mandatory switches are disabled)\n\n",
			"list of available arguments:\n",
			" ClappArgumentParser.php --help\n",
			
			"\nsimple flags (true if specified) arguments:\n",
			" ClappArgumentParser.php -abc\n",
			" ClappArgumentParser.php -a -b -c\n",

			"\ncounting occurences of flag arguments:\n",
			" ClappArgumentParser.php -fff -f -f -fffff\n",

			"\nmultiple occurences of value arguments:\n",
			" ClappArgumentParser.php --value-first=one --value-first=two --value-first=three\n",
			" ClappArgumentParser.php --value-last=one --value-last=two --value-last=three\n",
			" ClappArgumentParser.php --value-all=one --value-all=two --value-all=three\n",

			"\nmultiple occurences of key/value arguments:\n",
			" ClappArgumentParser.php --keyvalues=one=val1 --keyvalues=two=val2 --keyvalues=three=val3\n",
			" ClappArgumentParser.php --keyvalues_all=one=val1 --keyvalues_all=two=val2 --keyvalues_all=three=val3\n",
			
			"\ndepending arguments:\n",
			" ClappArgumentParser.php --depend-on-a\n",
			" ClappArgumentParser.php -a --depend-on-a\n",

			"\nconflicting arguments:\n",
			" ClappArgumentParser.php --conflict-a\n",			
			" ClappArgumentParser.php -a --conflict-a\n";
			
		exit;
	}
	
	// this is a demonstration, just display what we parsed
	$clapp->results();

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