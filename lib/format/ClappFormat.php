<?php

if( !isset( $_clappLibraryPath ) )
	$_clappLibraryPath = dirname(__FILE__) .'/..';

require_once( $_clappLibraryPath .'/format/ClappFormatHelp.php' );
require_once( $_clappLibraryPath .'/format/ClappFormatResults.php' );
require_once( $_clappLibraryPath .'/format/ClappFormatVersion.php' );


/**
 * Application Format Interface
 *
 * This class defines basic rendering helpers to be used by any extending Formatters
 * @package Clapp
 * @subpackage Clapp-format
 * @author Rodney Rehm
 */
abstract class ClappFormat
{
	/**
	 * full list of arguments
	 *
	 * @var string
	 */
	protected $arguments = null;
	
	/**
	 * sorting order for argument output
	 *
	 * @var int
	 */
	protected $order = null;

	/**
	 * filtered list of arguments for output
	 *
	 * @var array
	 */
	protected $args = array();

	/**
	 * list of argument groups
	 *
	 * @var array
	 */
	protected $groups = array();

	/**
	 * index of keys for sorting
	 *
	 * @var array
	 */
	protected $_keys = array();
	
	/**
	 * index of names for sorting
	 *
	 * @var string
	 */
	protected $_names = array();

	/**
	 * maximum width of Argument identifier part
	 *
	 * @var int
	 */
	protected $longestIdentifier = 0;

	/**
	 * minimum width of Argument identifer part
	 *
	 * @var int
	 */
	protected $nameCharacters = 19;

	/**
	 * width of the output in characters
	 *
	 * @var int
	 */
	protected $width = null;
	
	/**
	 * Initialize a new Format (output renderer)
	 *
	 * @param Clapp $app Clapp to render output for
	 */
	public function __construct( Clapp $app )
	{
		$this->app = $app;
		$this->arguments = $app->args;
		$this->setOrder();
		
		$d = ClappTTY::dimensions();
		$this->setWidth( $d['columns'] !== null ? $d['columns'] : self::defaultWidth() );
		$this->format = ClappTTY::format();
	}
	
	/**
	 * set the output width
	 *
	 * @param int $width number of characters to print in width, defaults to {$link defaultWidth()} if null is given
	 * @return ClappFormat $this for chaining
	 */
	public function setWidth( $width=null )
	{
		$this->width = $width ? $width : self::defaultWidth();
		return $this;
	}
	
	/**
	 * set the order the arguments should be output in
	 *
	 * @param int $order ORDER_* flag of ClappArgumentGroup
	 * @return ClappFormat $this for chaining
	 */
	public function setOrder( $order=ClappArgumentGroup::ORDER_IDENTIFIER )
	{
		$this->order = $order;
		return $this;
	}
	
	/**
	 * get the default output width
	 *
	 * Windows: 79 characters (because cursor counts as a character and would thus lead to undesired line breaks)
	 * Otherwise: 80 characters
	 * @return int width of output in characters
	 */
	public static function defaultWidth()
	{
		return substr( PHP_OS, 0, 3 ) == 'WIN' ? 79 : 80;
	}
	
	/**
	 * render the ouput screen
	 *
	 * @param int $width width of output
	 * @return ClappFormat $this for chaining
	 */
	public abstract function render( $width=null );
	
	/**
	 * render the arguments (OPTIONS part)
	 *
	 * Arguments are output sorted by group and $order
	 * @param int $width width of output
	 * @return ClappFormat $this for chaining
	 * @uses renderArgument() to render each ClappArgument
	 */
	protected function renderArguments( $width )
	{
		echo "OPTIONS:";
		foreach( $this->groups as $group )
		{
			echo "\n";
			if( $group->name )
				echo $group->name, "\n";

			foreach( $group->get( $this->order ) as $arg )
				$this->renderArgument( $arg, $width );
		}

		return $this;
	}
	
	/**
	 * Render a ClappArgument
	 *
	 * @param ClappArgument $arg ClappArgument to render
	 * @param int $width width of output
	 * @return ClappFormat $this for chaining
	 * @uses ClappArgument::getIdentifier(true,true) to identify the argument
	 * @uses ClappTTYFormat::rightPadd() to justify argument identificator
	 * @uses wrapArgumentDescription() to wrap and indent the description as necessary
	 */
	protected function renderArgument( ClappArgument $arg, $width )
	{
		echo '  ', $this->format->rightPadd( $arg->getIdentifier( true, true ), $this->nameCharacters ),
			$this->wrapArgumentDescription( $arg->description, $width ),
			"\n";

		return $this;
	}
	
	/**
	 * wrap and indent a string
	 *
	 * wrapping is done by PHP's wordwrap. Indentation is done by left-padding the lines (except for the first) with spaces
	 * @param string $string string to wrap and indent
	 * @param int $width width of output
	 * @param int $indent number of characters to indent (left-padd with spaces)
	 * @return void wrapped and indented string
	 * @uses wordwrap to break the string into separate lines
	 */
	protected function wrapIndent( $string, $width, $indent=0 )
	{
		// sanitize: remove linebraks, duplicate spaces
		// $string = preg_replace( '#\r\n#S', ' ', $string );
		// $string = preg_replace( '#\s+#S', ' ', $string );
		
		// break into lines
		$length = $width - $indent;
		$string = wordwrap( $string, $length, "\n" );

		// inject indent
		$spaces = str_repeat( ' ', $indent );
		$string = str_replace( "\n", "\n". $spaces, $string );
		return $string;
	}
	
	/**
	 * wrap ClappArgument's description
	 *
	 * @param string $string ClappArgument description to wrap and indent
	 * @param int $width width of output
	 * @return string (indented) wrapped ClappArgument description
	 * @uses wrapIndent() to actually indent and wrap the description
	 */
	protected function wrapArgumentDescription( $string, $width )
	{
		$indent = $this->nameCharacters +2;
		return $this->wrapIndent( $string, $width, $indent );
	}
	
	/**
	 * prepare output by filtering ClappArguments and building sorting indexes
	 *
	 * @param int $width width of output
	 * @return ClappFormat $this for chaining
	 * @author Rodney Rehm
	 */
	protected function prepare( $width )
	{
		$this->prepareGroups();
		$this->args = array_filter( $this->arguments->getArguments(), array( $this, 'filterArgs' ) );
		$this->nameCharacters = max( $this->longestIdentifier, $this->nameCharacters ) +2;
		return $this;
	}
	
	/**
	 * fill the group index with all App's groups and the default group
	 *
	 * @return ClappFormat $this for chaining
	 * @uses $_groups to build the group index
	 */
	protected function prepareGroups()
	{
		$this->groups = $this->app->groups;
		$this->groups[ '__default__' ] = new ClappArgumentGroup( '', '__default__' );
		return $this;
	}
	
	/**
	 * prepare sorting index
	 *
	 * @param ClappArgument $arg Argument to push into index
	 * @return ClappFormat $this for chaining
	 * @uses $_keys to build the key index
	 * @uses $_names to build the name index
	 * @uses $groups to build the default-group index
	 */
	protected function prepareSorting( ClappArgument $arg )
	{
		$n = $arg->name;
		$k = $arg->key;
		$k = is_numeric( $k ) ? ( '_'. $k ) : $k;

		if( $k !== null )
			$this->_keys[ $k ] = $arg;

		if( $n !== null )
			$this->_names[ $n ] = $arg;
		
		if( !$arg->groups )
			$this->groups[ '__default__' ]->add( $arg );
		
		return $this;
	}

	/**
	 * filter arguments to display
	 *
	 * @param ClappArgument $arg ClappArgument to filter
	 * @return boolean true to include, false to exclude
	 * @uses prepareSorting() to fill indexes
	 * @uses updateLongestIdentifier() to determine the max identifier length 
	 */
	public function filterArgs( $arg )
	{
		$this->prepareSorting( $arg );
		$this->updateLongestIdentifier( $arg->getIdentifier( true, true ) );
		return true;
	}
	
	/**
	 * remember the longest Identifier found (for proper linebreaking)
	 *
	 * @param string $string identifier to compare length with
	 * @return void
	 * @uses $longestIdentifier to store the number of characters
	 */
	protected function updateLongestIdentifier( $string )
	{
		$this->longestIdentifier = max( $this->longestIdentifier, ClappEncoding::strlen( $string ) );
	}
}



?>