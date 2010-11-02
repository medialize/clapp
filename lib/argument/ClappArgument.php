<?php

if( !isset( $_clappLibraryPath ) )
	$_clappLibraryPath = dirname(__FILE__) .'/..';

require_once( $_clappLibraryPath .'/argument/ClappArgumentParser.php' );
require_once( $_clappLibraryPath .'/argument/ClappArgumentGroup.php' );
require_once( $_clappLibraryPath .'/argument/ClappArgumentDefault.php' );
require_once( $_clappLibraryPath .'/argument/ClappArgumentUndefined.php' );

// FIXME: phpdoc ClappArgument

class ClappArgument
{
	const NON_OPTION = 0;
	const FLAG = 1; // will not accept values, thus -abc translates to -a -b -c, additionally exception for occurences > 1
	const FLAGS = 2; // will not accept values, thus -abc translates to -a -b -c
	const VALUE = 4; // exception for values > 1
	const VALUE_FIRST = 8;
	const VALUE_LAST = 16;
	const VALUE_ALL = 32;
	const VALUES = 64;
	const KEYVALUES = 128;	// like VALUE_ALL but values are split by a delimiter into a (unqiue key) array
	const KEYVALUES_ALL = 256; // like VALUE_ALL but values are split by a delimiter into an 2d array
	
	const REQUIRED = 512; // Required value
	
	protected $key = null;
	protected $name = null;
	protected $type = null;
	protected $default = null;
	protected $description = null;
	protected $mandatory = false;
	protected $dependencies = array();
	protected $conflicts = array();
	protected $expected = null;
	
	protected $groups = array();
	
	protected $values = array();
	protected $occurences = 0;
	
	protected $keyvalueDelimiter = '=';
	
	public function __construct( $key=null, $name=null, $type=self::FLAGS, $desc=NULL, $expected=NULL )
	{
		$this->key = $key;
		$this->name = $name;
		
		if( $key === null && $name === null )
			throw new ClappException( 'Arguments must either have a key, a name or both!' );
		
		if( $type & self::REQUIRED )
		{
			$this->setMandatory();
			$type = $type & ~self::REQUIRED;
		}
		
		if( $desc !== NULL )
		{
			$this->setDescription( $desc );
		}
		
		if ( $expected !== NULL )
		{
			$this->setExpected( $expected );
		}
		
		$this->setType( $type );
	}
	
	public function __get( $name )
	{
		$fn = 'get'. ucfirst( $name );
		if( is_callable( array( $this, $fn ) ) )
			return $this->$fn();
		
		trigger_error( 'Undefined property: '. get_class($this) .'::$'. $name, E_USER_NOTICE );
	}
	
	public function __set( $name, $value )
	{
		$fn = 'set'. ucfirst( $name );
		if( is_callable( array( $this, $fn ) ) )
			return $this->$fn( $value );
		
		trigger_error( 'Undefined property: '. get_class($this) .'::$'. $name, E_USER_NOTICE );
	}
	
	public function __toString()
	{
		return $this->getIdentifier();
	}


	/*
	 * settings
	 */
	
	public function setKeyvalueDelimiter( $delimiter='=' )
	{
		$this->keyvalueDelimiter = $delimiter;
		return $this;
	}
	
	
	/*
	 * attribute mutation and access
	 */
	
	public function getIdentifier( $paddKey=false, $addExpected=false )
	{
		$t = '';
		
		if( $this->key !== null )
			$t = $this->getKey( true );
			
		else if( $paddKey )
			$t = '  ';
		
		if( $this->name !== null )
		{
			if( $this->key )
				$t .= ', ';
			
			else if( $paddKey )
				$t .= '  ';
				
			$t .= $this->getName( true );
		}
		
		if( $addExpected )
		{
			if( $e = $this->getExpected() )
				$t .= ' '. ClappTTY::format()->format( $e, ClappTTYFormat::UNDERLINE );
		}
		
		return $t;
	}
	
	public function getKey( $hyphen=false )
	{
		return $hyphen && $this->key !== null ? ('-'. $this->key) : $this->key;
	}
	
	public function getName( $hyphen=false )
	{
		return $hyphen && $this->name !== null ? ('--'. $this->name) : $this->name;
	}
	
	public function getType()
	{
		return $this->type;
	}
	
	public function setType( $type=self::FLAGS )
	{
		$this->type = $type;
		return $this;
	}
	
	public function getDefault()
	{
		return $this->default;
	}
	
	public function setDefault( $default=null )
	{
		$this->default = $default;
		return $this;
	}
	
	public function getDescription()
	{
		return $this->description;
	}
	
	public function setDescription( $description )
	{
		$this->description = $description;
		return $this;
	}
	
	public function getMandatory()
	{
		return $this->mandatory;
	}
	
	public function setMandatory( $mandatory=true )
	{
		$this->mandatory = $mandatory;
		return $this;
	}
	
	public function getExpected()
	{
		// flags don't have a value
		if( $this->type < self::VALUE )
			return null;
		
		if( $this->type >= self::KEYVALUES )
			return $this->expected ? $this->expected : 'KEY=VALUE';
			
		return $this->expected ? $this->expected : 'ARG';
	}
	
	public function setExpected( $expected )
	{
		$this->expected = $expected;
		return $this;
	}
	
	public function addDependency( ClappArgument $arg )
	{
		$this->dependencies[] = $arg;
		return $this;
	}
	
	public function addConflict( ClappArgument $arg )
	{
		$this->conflicts[] = $arg;
		return $this;
	}
	
	public function getGroups()
	{
		return $this->groups;
	}
	
	public function sortIndex( &$sort, $group=null )
	{
		$m = $this instanceof ClappArgumentUndefined ? 'C__' : ($this->mandatory ? 'A__' : 'B__');
		$n = $this->name;
		$k = is_numeric( $this->key ) ? ( '_'. $this->key ) : $this->key;
		$mn = $n . $n;
		$mk = $n . $k;
		
		$groups = array();

		// file into a specific group
		if( $group )
		{
			$groups[] = $group;
		}
		// file into default group
		else if( !$this->groups )
		{
			$groups[] = '__default__';
		}
		// file all associated groups
		else
		{
			foreach( $this->groups as $group )
				$groups[] = $group->id;
		}
		
		foreach( $groups as $g )
		{
			if( empty( $sort[ $g ] ) )
			{
				$sort[ $g ] = array(
					'keys' => array(),
					'names' => array(),
					'identifiers' => array(),
					'relevances' => array(),
				);
			}
			
			if( $this->key !== null )
			{
				$sort[ $g ]['keys'][ $k ] = $this;
				$sort[ $g ]['identifiers'][ $k ] = $this;
				$sort[ $g ]['relevances'][ $mk ] = $this;
			}
			
			if( $this->name !== null )
			{
				$sort[ $g ]['names'][ $n ] = $this;
				$sort[ $g ]['identifiers'][ $n ] = $this;
				$sort[ $g ]['relevances'][ $mn ] = $this;
			}
		}
		
		return $this;
	}
	
	
	/*
	 * validation and tests
	 */
	
	public function getTakesValue()
	{
		return $this->type > self::FLAGS;
	}
	
	public function validate()
	{
		if( $this->mandatory && !$this->occurences )
			throw new ClappException( 'The Argument "'. $this->getIdentifier() .'" is mandatory but was not specified!' );
		
		foreach( $this->dependencies as $d )
			if( !$d->specified() )
				throw new ClappException( 'The Argument "'. $this->getIdentifier() .'" depends on Argument "'. $d->getIdentifier() .'" being specified!' );

		foreach( $this->conflicts as $c )
			if( $c->specified() )
				throw new ClappException( 'The Argument "'. $this->getIdentifier() .'" conflicts with Argument "'. $c->getIdentifier() .'", remove either one!' );
		
		if( ( $this->type == self::VALUE || $this->type == self::FLAG ) && $this->occurences > 1 )
			throw new ClappException( 'The Argument "'. $this->getIdentifier() .'" must be unique, but has been specified '. $this->occurences .' times!' );
	}
	
	public function specified()
	{
		return !!$this->occurences;
	}
	
	
	/*
	 * value accessors
	 */
	
	public function firstValue()
	{
		return $this->value( self::VALUE_FIRST );
	}
	
	public function lastValue()
	{
		return $this->value( self::VALUE_LAST );
	}

	public function values()
	{
		return $this->value( self::VALUE_ALL );
	}
	
	public function value( $type=null )
	{
		switch( $type ? $type : $this->type )
		{
			case self::KEYVALUES:
				// return key=value types as indexed array
				return $this->valuePairs( $this->keyvalueDelimiter, true);
			break;
			
			case self::KEYVALUES_ALL:
				// return key=value types as 2d array
				return $this->valuePairs( $this->keyvalueDelimiter, false );
			break;
			
			case self::VALUE_ALL:
				// return default only if it is non-null
				return !$this->values && $this->default !== null ? $this->default : $this->values;
			break;
			
			case self::VALUE:
			case self::VALUE_FIRST:
				// return first value encountered
				foreach( $this->values as $v )
					return $v;
				
				return $this->default;
			break;
			
			case self::VALUE_LAST:
				// return last value encountered
				if( !$this->values )
					return $this->default;
				
				// Note: this would break string keys but doesn't harm (sequential) numeric indexes
				array_push( $this->values, $v = array_pop( $this->values ) );
				return $v;
			break;
			
			case self::FLAG:
				// return true if flag was sepcified, false else
				return $this->occurences === 1;
			break;
			
			case self::FLAGS:
			default:
				// return the number of occurences
				return $this->occurences;
			break;
		}
	}
	
	// note: since (GNU-2) long options cannot take separate values, so --opt key=val will not work, but --opt=key=val does!
	public function valuePairs( $delimiter='=', $uniqueKeys=true )
	{
		// note: works as $type == VALUE_ALL 
		$t = array();
		foreach( $this->values as $value )
		{
			$v = explode( $delimiter, $value, 2 );
			if( empty( $v[1] ) )
				$v[1] = null;
				
			if( $uniqueKeys )
				$t[ $v[0] ] = $v[1];
			else
				$t[] = array( 'key' => $v[0], 'value' => $v[1] );
		}
		
		return $t;
	}


	public function add( $o )
	{
		switch( true )
		{
			case $o instanceof ClappArgumentGroup:
				$this->groups[ $o->id ] = $o;
			break;

			default:
				if( !is_object( $o ) )
					throw new ClappException( 'Only objects can be handled by ClappArgument!' );
									
				throw new ClappException( 'Objects of class '. get_class( $o ) .' cannot be added to ClappArgument!' );
			break;
		}
		
		return $this;
	}

	/*
	 * value mutation
	 */

	public function addOccurence()
	{
		$this->occurences++;
		return $this;
	}

	public function addValue( $v )
	{
		$this->values[] = $v;
		return $this;
	}
}



?>