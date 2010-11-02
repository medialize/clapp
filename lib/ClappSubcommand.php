<?php

/**
 * Subcommand to register with a Clapp instance
 * 
 * TODO: add more docs here
 * @example /examples/example03.php Defining Subcommands
 * @package Clapp
 * @author Rodney Rehm
 */
class ClappSubcommand extends Clapp
{
	/**
	 * short description of the subcommand's functionality
	 * @var string
	 */
	protected $abstract = null;
	
	/**
	 * Clapp container this subcommand is registered to
	 * @var Clapp
	 */
	protected $parent = null;
	
	/**
	 * Create a new Subcommand
	 * @param string $name readable name of the subcommand
	 * @uses $argumentParserClass to create a new ClappArgumentParser
	 */
	public function __construct( $name )
	{
		$this->name = $name;
		// parse from the second
		$this->args = new $this->argumentParserClass( 2 );
	}

	/**
	 * get the Subcommand's short description
	 * @return string the Subcommand's short description
	 */
	public function getAbstract()
	{
		return $this->abstract;
	}
	
	/**
	 * set the Subcommand's short description
	 * @param string $abstract the Subcommand's short description
	 * @return ClappSubcommand $this for chaining
	 */
	public function setAbstract( $abstract )
	{
		$this->abstract = $abstract;
		return $this;
	}

	/**
	 * get the Subcommand's parent container
	 * @return Clapp the Subcommand's parent container
	 */
	public function getParent()
	{
		return $this->parent;
	}

	/**
	 * set the Subcommand's parent container
	 * @param Clapp $parent the Subcommand's parent container
	 * @return ClappSubcommand $this for chaining
	 */
	protected function setParent( $parent )
	{
		$this->parent = $parent;
		return $this;
	}

	/**
	 * Initialize the Subcommand's ArgumentParser
	 *
	 * automatically display --help screen,
	 * do not act on --version,
	 * validate the parsed arguments
	 * @param boolean $validate flag to activate argument validation, defaults to true
	 * @return ClappSubcommand $this for chaining
	 * @uses help() to display --help screen
	 */
	public function initialize( $validate=true )
	{
		$args = $this->args->parse();
		
		// show help
		if( $args->value('help') )
		{
			$this->help();
		}
		
		// subcommands don't know --version automatically
		
		if( $validate )
			$args->validate();

		return $this;
	}

}

?>