<?php

/**
 * Result Screen Formatter (debugging)
 *
 * The Result Screen Formatter outputs all arguments of an application as a table consisting
 * of identifier and parsed values, states and defaults.
 * @package Clapp
 * @subpackage Clapp-format
 * @author Rodney Rehm
 */
class ClappFormatResults extends ClappFormat
{
	/**
	 * Initialize a new Format (output renderer)
	 *
	 * Order output by relevance.
	 * @param Clapp $app Clapp to render output for
	 */
	public function __construct( Clapp $app )
	{
		parent::__construct( $app );
		$this->setOrder( ClappArgumentGroup::ORDER_RELEVANCE );
	}

	/**
	 * render the results screen
	 *
	 * @param int $width width of output, defaults to {@link ClappFormat::$width}
	 * @return ClappFormatHelp $this for chaining
	 */
	public function render( $width=null )
	{
		$width = $width !== null ? $width : $this->width;
		
	 	$this->prepare( $width )
			->renderArguments( $width )
			->renderNonOptions( $width );
	}
	
	/**
	 * render the arguments
	 *
	 * Arguments are output sorted by relevancy
	 * @param int $width width of output
	 * @return ClappFormat $this for chaining
	 * @uses renderArgument() to render each ClappArgument
	 */
	protected function renderArguments( $width )
	{
		parent::renderArguments( $width );
		echo "Legend: [Identifier], [Mandatory|Optional|Undefined], [Specified], [Type], [Value]\n";
		return $this;
	}
	
	/**
	 * Render a ClappArgument
	 *
	 * Ouput Argument as table row, cells containing: 
	 * identifier, 
	 * [Mandatory, Optional, Undefined]-Flag, 
	 * hint if the argument was specified,
	 * data type of the parsed value
	 * parsed value
	 * @param ClappArgument $arg ClappArgument to render
	 * @param int $width width of output
	 * @return ClappFormat $this for chaining
	 */
	protected function renderArgument( ClappArgument $arg, $width )
	{
		if( $arg instanceof ClappArgumentDefault )
			return $this;
		
		echo '  ', $this->format->rightPadd( $arg->getIdentifier( true, true ), $this->nameCharacters );
		
		$t = array( ' | ' );
		
		if( $arg instanceof ClappArgumentUndefined )
			$t[] = 'U | S';
		else
		{
			$t[] = $arg->mandatory ? 'M |' : 'O |';
			$t[] = $arg->specified() ? ' S' : '  ';
		}
		$t[] = ' | ';
		
		$v = $arg->value();
		if( is_array( $v ) )
		{
			if( $arg->type > ClappArgument::VALUES )
			{
				$t[] = 'keyval | ['. join( ', ', $arg->value( ClappArgument::VALUES ) ) .']';
			}
			else
				$t[] = 'list   | ['. join( ', ', $v ) .']';
		}
		else
		{
			switch( true )
			{
				case is_bool( $v ):
					$t[] = 'bool   | ';
					$t[] = $v ? 'true' : 'false';
				break;
				
				case is_int( $v ):
					$t[] = 'int    | '. $v;
				break;
				
				case is_numeric( $v ):
					$t[] = 'float   | '. $v;
				break;
				
				case is_string( $v ):
					$t[] = 'string | '. $v;
				break;
				
				default:
					$t[] = '       | ';
				break;
			}
		}
		
		//echo $this->wrapArgumentDescription( join($t), $width );
		echo join($t);
		
		echo "\n";
		
		return $this;
	}
	
	/**
	 * Render non-Argument arguments (usually called non-options)
	 *
	 * @param ClappArgument $arg ClappArgument to render
	 * @param int $width width of output
	 * @return ClappFormat $this for chaining
	 */
	protected function renderNonOptions( $width )
	{
		if( $arg = $this->arguments->getDefaultArgument() )
		{
			$values = '['. join( ', ', $arg->value() ) .']';
			
			echo "\nARGUMENTS:\n",
				' ', $this->wrapIndent( $values, $width, 2 ),
				"\n";
		}
		
		return $this;
	}
}



?>