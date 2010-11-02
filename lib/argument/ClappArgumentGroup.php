<?php

/**
 * Argment Group Container
 *
 * @package Clapp
 * @subpackage Clapp-argument
 * @author Rodney Rehm
 */
class ClappArgumentGroup
{
	/**
	 * Order by nothing
	 */
	const ORDER_NONE = 0;
	
	/**
	 * Order by Identifier ("-f, --foobar")
	 */
	const ORDER_IDENTIFIER = 1;
	
	/**
	 * Order by Relevance
	 */
	const ORDER_RELEVANCE = 2;
	
	/**
	 * ID of group
	 *
	 * @var string
	 */
	protected $id = null;
	
	/**
	 * Name of group
	 *
	 * @var string
	 */
	protected $name = null;
	
	/**
	 * ClappArguments of group
	 *
	 * @var array
	 */
	protected $arguments = array();
	
	/**
	 * Create new ArgumentGroup
	 *
	 * @param string $name name of the group
	 * @param string $id id of the group, if ommitted will be uniqid()
	 */
	public function __construct( $name, $id=null )
	{
		$this->name = $name;
		$this->id = $id ? $id : uniqid();
	}
	
	/**
	 * MagicMethod-getter
	 *
	 * @param string $name name of the attribute to get
	 * @return mixed attribute value
	 * @throws Exception if unknown attribute is read
	 */
	public function __get( $name )
	{
		$fn = 'get'. ucfirst( $name );
		if( is_callable( array( $this, $fn ) ) )
			return $this->$fn();
		
		throw new Exception( 'Undefined property: '. get_class($this) .'::$'. $name );
	}
	
	/**
	 * MagicMethod-setter
	 *
	 * @param string $name name of the attribute to get
	 * @param mixed $vlue value to set
	 * @return mixed setter's response (usually null)
	 * @throws Exception if unknown attribute is written
	 */
	public function __set( $name, $value )
	{
		$fn = 'set'. ucfirst( $name );
		if( is_callable( array( $this, $fn ) ) )
			return $this->$fn( $value );
		
		throw new Exception( 'Undefined property: '. get_class($this) .'::$'. $name );
	}
	
	/**
	 * get group ID
	 *
	 * @return string
	 * @property-read string $id the group id
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * get group name
	 *
	 * @return string
	 * @property-read string $name the group name
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * set group ID
	 *
	 * @return string
	 * @property-write string $id the group name
	 */
	public function setName( $name )
	{
		$this->name = $name;
		return $this;
	}
	
	/**
	 * get the registered ClappArguments
	 *
	 * @return array list of ClappArguments in the group
	 * @property-read array $arguments list of ClappArguments in the group
	 */
	public function getArguments()
	{
		return $this->arguments;
	}
	
	/**
	 * add an argument to the group
	 *
	 * @param ClappArgument $arg the Argument to add to the group
	 * @return ClappArgumentGroup $this for chaining
	 * @uses $arguments to store the added argument
	 */
	public function add( ClappArgument $arg )
	{
		$this->arguments[] = $arg;
		$arg->add( $this );
		return $this;
	}
	
	public function get( $order=self::ORDER_NONE )
	{
		$sort = array();
		foreach( $this->arguments as $arg )
			$arg->sortIndex( $sort );
			
		if( empty($sort[ $this->id ]) )
			return array();
		
		switch( $order )
		{
			case self::ORDER_NONE:
				return $this->arguments;
			break;
			
			case self::ORDER_RELEVANCE:
				$index = self::unique( $sort[ $this->id ]['relevances'] );
				ksort( $index );
				return $index;
			break;
			
			default:
			case self::ORDER_IDENTIFIER:
				$index = self::unique( $sort[ $this->id ]['identifiers'] );
				ksort( $index );
				return $index;
			break;
		}
	}
	
	/**
	 * array_unique for objects (PHP < 5.2.9 hack)
	 * 
	 * @param array $array input array
	 * @return array filtered array
	 */
	protected static function unique( $array )
	{
		// PHP < 5.2.9 did not know the sort-flag
		if( version_compare( PHP_VERSION, '5.2.9', '>=' ) )
			return array_unique( $array, SORT_REGULAR );
		else
			return array_unique( $array );
		
	}
}



?>