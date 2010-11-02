<?php

// FIXME: phpdoc ClappArgumentParser
// TODO: remember the index of token in $argv?
// TODO: should --wtf:lalala -? be supported? they're not GNU/POSIX compatible

// NOTE: the following yield the same result when read from CLI - but differ when passed as php strings
//$args->parse( '-a"b" -c \'value\' --delta="xy"' ); // source
//$args->parse( '-ab -c value --delta=xy' ); // result

/*
 * Rules / Conventions derived from »Program Argument Syntax Conventions«
 * (POSIX-1) Arguments are options if they begin with a hyphen delimiter (`-').
 * (POSIX-2) Multiple options may follow a hyphen delimiter in a single token if the options do not take arguments. Thus, `-abc' is equivalent to `-a -b -c'.
 * (POSIX-3) Option names are single alphanumeric characters (as for isalnum; see section Classification of Characters).
 * (POSIX-4) Certain options require an argument. For example, the `-o' command of the ld command requires an argument--an output file name.
 * (POSIX-5) An option and its argument may or may not appear as separate tokens. (In other words, the whitespace separating them is optional.) Thus, `-o foo' and `-ofoo' are equivalent.
 * (POSIX-6) Options typically precede other non-option arguments.
 * (POSIX-7) The argument `--' terminates all options; any following arguments are treated as non-option arguments, even if they begin with a hyphen.
 * (POSIX-8) A token consisting of a single hyphen character is interpreted as an ordinary non-option argument. By convention, it is used to specify input from or output to the standard input and output streams.
 * (POSIX-9) Options may be supplied in any order, or appear multiple times. The interpretation is left up to the particular application program.
 * (GNU-1) Long options consist of `--' followed by a name made of alphanumeric characters and dashes. Option names are typically one to three words long, with hyphens to separate words. Users can abbreviate the option names as long as the abbreviations are unique. 
 * (GNU-2) To specify an argument for a long option, write `--name=value'. This syntax enables a long option to accept an argument that is itself optional. 
 * (CLI-1) a long option name must be at least 2 characters long to not interfere with short option names when leading hyphens are stripped
 *
 * ClappArgumentParser complies with all rules but does not enforce (POSIX-6)
 */


class ClappArgumentParser
{
	const PATTERN_KEY = '#^[a-zA-Z0-9_-]$#S';
	const PATTERN_NAME = '#^[a-zA-Z0-9_-]{2,}$#S';
	
	protected $args = array();
	protected $allArgs = array();
	protected $keyArgs = array();
	protected $nameArgs = array();
	protected $undefinedArgs = array();
	protected $undefinedKeyArgs = array();
	protected $undefinedNameArgs = array();
	protected $defaultArg = null; // contains all non-option arguments
	
	protected $undefinedArgumentClass = 'ClappArgumentUndefined';
	protected $defaultArgumentClass = 'ClappArgumentDefault';

	protected $_previous = null;
	protected $_defaultsOnly = null;
	
	protected $argumnetOffset = null;
	
	public function __construct( $argumnetOffset=1 )
	{
		$this->argumnetOffset = $argumnetOffset;
	}
	
	public function __get( $name )
	{
		return $this->getArgument( $name );
	}


	/*
	 * parser
	 */

	public function parse( $argv=null )
	{
		if( !$argv )	
			$argv = $this->argv();
		
		if( is_string( $argv ) )
			$argv = explode( ' ', $argv );
		
		$_defaultsOnly = false;

		foreach( $argv as $i => $v )
		{
			// Argument might be given in some absurd charset
			$v = ClappEncoding::decode( $v );
			
			switch( true )
			{
				case $v == '-':						// Rules: (POSIX-7)
				case $this->_defaultsOnly:			// Rules: (POSIX-7)
					$this->parseDefault( $v );
				break;

				case $v == '--':					// Rules: (POSIX-7)
					$this->_defaultsOnly = true;
				break;
								
				case $v[0] == '-' && $v[1] == '-':	// Rules: (GNU-1), (GNU-2)
					$this->parseNameArgument( $v );
				break;
				
				case $v[0] == '-' && $v[1] != '-':	// Rules: most other rules
					$this->parseKeyArgument( $v );
				break;
				
				case $this->_previous:				// Rules: (POSIX-5)
					$this->parseValue( $v );
				break;
				
				default:							// non-option ("default") data 
					$this->parseDefault( $v );
				break;
			}
		}

		return $this;
	}
	
	public function parseCommand( $argv=null )
	{
		if( !$argv )
			$argv = $this->argv();
		
		if( is_string( $argv ) )
			$argv = explode( ' ', $argv );
		
		$command = array_shift( $argv );
		if( $command[0] != '-' )
			return ClappEncoding::decode( $command );
		
		return null;
	}
	
	// Rules: (CLI-1)
	protected function parseNameArgument( $v )
	{
		$v = substr( $v, 2 );
		$v = explode( '=', $v, 2 );
		
		$this->validateName( $v[0] );
		
		$arg = $this->getNameArgument( $v[0], true );
		// register a hit
		$arg->addOccurence();
		// save the argument's value
		if( isset($v[1]) )
			$arg->addValue( $v[1] );
		
		// Name Arguments have their value attached via the = delimitor
		$this->_previous = null;
	}
	
	protected function parseKeyArgument( $v )
	{
		$v = substr( $v, 1 );
		$vLength = ClappEncoding::strlen( $v );
		for( $vi=0; $vi < $vLength; $vi++ )
		{
			$this->validateKey( $v[$vi] );
			
			$arg = $this->getKeyArgument( $v[$vi], true );
			$arg->addOccurence();
			if( $arg->takesValue )
			{
				// in case $v is at its end treat the next token as the value of this arg
				$this->_previous = $arg;
				
				// starting with the next character we treat the rest as the value
				$vi += 1;
				
				// make sure we're within bounds
				if( $vi < $vLength )
				{
					$arg->addValue( substr( $v, $vi ) );
					// whatever token comes next is of no interest to the current arg
					$this->_previous = null;
					break;
				}
			}
			else
				$this->_previous = null;
		}
	}
	
	protected function parseValue( $v )
	{
		$this->_previous->addValue( $v );
		$this->_previous = null;
	}
	
	protected function parseDefault( $v )
	{
		$arg = $this->getDefaultArgument( true );
		$arg->addOccurence()->addValue( $v );
	}
	
	protected function validateName( $name )
	{
		if( !preg_match( self::PATTERN_NAME, $name) )
			throw new ClappException( 'Argument Names must be alpha-numeric [a-zA-Z0-9_-] with at least two characters, "'. $name .'" is not!' );
		
	}
	
	protected function validateKey( $key )
	{
		if( !preg_match( self::PATTERN_KEY, $key) )
			throw new ClappException( 'Argument Keys must be alpha-numeric [a-zA-Z0-9_-] with exactly one character, "'. $key .'" is not!' );
	}
	
	
	
	/*
	 * argument list mutation / access
	 */
	
	public function add( ClappArgument $arg )
	{
		if( $arg->key !== null && !empty( $this->keyArgs[ $arg->key ] ) )
			throw new ClappException( 'Argument Keys must be unique. "'. $arg->key .'" was specified twice' );
		
		if( $arg->name !== null && !empty( $this->nameArgs[ $arg->name ] ) )
			throw new ClappException( 'Argument Names must be unique. "'. $arg->name .'" was specified twice' );
		
		$this->args[] = $arg;
		if( $arg->name !== null )
		{
			$this->nameArgs[ $arg->name ] = $arg;
			$this->allArgs[ $arg->name ] = $arg;
		}
		if( $arg->key !== null )
		{
			$this->keyArgs[ $arg->key ] = $arg;
			$this->allArgs[ $arg->key ] = $arg;
		}
		
		return $this;
	}
	
	public function getDefaultArgument( $create=false )
	{
		if( $this->defaultArg )
			return $this->defaultArg;
	
		if( !$create )
			return null;
		
		return $this->args[] = $this->defaultArg = new $this->defaultArgumentClass();
	}
	
	public function getKeyArgument( $key, $create=false )
	{
		if( !empty( $this->keyArgs[ $key ] ) )
			return $this->keyArgs[ $key ];
		
		if( !$create )
			return null;
		
		$this->add( $arg = new $this->undefinedArgumentClass( $key ) );
		$this->undefinedArgs[ $arg->key ] = $this->undefinedKeyArgs[ $arg->key ] = $arg;
		
		return $arg;
	}
	
	public function getNameArgument( $name, $create=false )
	{
		if( !empty( $this->nameArgs[ $name ] ) )
			return $this->nameArgs[ $name ];
		
		if( !$create )
			return null;
		
		$this->add( $arg = new $this->undefinedArgumentClass( null, $name ) );
		$this->undefinedArgs[ $arg->name ] = $this->undefinedNameArgs[ $arg->name ] = $arg;
		
		return $arg;
	}
	
	public function getArguments()
	{
		return $this->args;
	}
	
	public function getArgument( $k )
	{
		if( !empty( $this->allArgs[ $k ] ) )
			return $this->allArgs[ $k ];
			
		return null;
	}


	/*
	 * facade
	 */

	public function firstValue( $k )
	{
		if( $arg = $this->getArgument( $k ) )
			return $arg->firstValue();
		
		return null;
	}
	
	public function lastValue( $k )
	{
		if( $arg = $this->getArgument( $k ) )
			return $arg->lastValue();
		
		return null;
	}
	
	public function value( $k, $type=null )
	{
		if( $arg = $this->getArgument( $k ) )
			return $arg->value( $type );
		
		return null;
	}
	
	public function values( $k )
	{
		if( $arg = $this->getArgument( $k ) )
			return $arg->values();
		
		return null;
	}
	
	public function occurences( $k )
	{
		if( $arg = $this->getArgument( $k ) )
			return $arg->occurences();
		
		return null;
	}
	
	public function nonOptions()
	{
		if( $arg = $this->getDefaultArgument() )
			return $arg->values();
		
		return null;
	}
	
	public function specified()
	{
		foreach( $this->args as $arg )
			if( $arg->specified() )
				return true;
				
		return false;
	}
	
	
	/*
	 * helpers
	 */
	
	public function validate( $allowUndefinedArgs=false )
	{
		if( !$allowUndefinedArgs && ( $this->undefinedKeyArgs || $this->undefinedNameArgs ) )
		{
			$t = array_merge( $this->undefinedKeyArgs, $this->undefinedNameArgs );
			$t = array_map( array( $this, 'extractIdentifier' ), $t );
			throw new ClappException( 'Unknown Argument(s): '. join( ', ', $t ) );
		}
		
		foreach( $this->args as $arg )
			$arg->validate();
		
		return $this;
	}
	
	public function command()
	{
		global $argv;

		if( !empty( $argv ) && is_array( $argv ) )
			return basename( ClappEncoding::decode( $argv[ $this->argumnetOffset -1 ] ) );
		
		if( !empty( $_SERVER['argv'] ) && is_array( $_SERVER['argv'] ) )
			return basename( ClappEncoding::decode( $_SERVER['argv'][ $this->argumnetOffset -1 ] ) );
		
		throw new ClappException( 'neither $argv nor $_SERVER[argv] are populated' );
	}
	
	public function argv()
	{
		global $argv;

		if( !empty( $argv ) && is_array( $argv ) )
			return array_slice( $argv, $this->argumnetOffset );
		
		if( !empty( $_SERVER['argv'] ) && is_array( $_SERVER['argv'] ) )
			return array_slice( $_SERVER['argv'], $this->argumnetOffset );
		
		throw new ClappException( 'neither $argv nor $_SERVER[argv] are populated' );
	}

	public function extractIdentifier( $arg )
	{
		return $arg->identifier;
	}
}


?>