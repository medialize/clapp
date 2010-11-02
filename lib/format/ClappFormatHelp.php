<?php

/**
 * Help Screen Formatter (--help)
 *
 * @see http://www.gnu.org/prep/standards/html_node/_002d_002dhelp.html#g_t_002d_002dhelp
 * @package Clapp
 * @subpackage Clapp-format
 * @author Rodney Rehm
 */
class ClappFormatHelp extends ClappFormat
{
	/**
	 * flag stating if --help should be output as a regular argument
	 *
	 * @var boolean
	 */
	protected $injectHelp = true;
	
	/**
	 * flag stating if --version should be output as a regular argument
	 *
	 * @var boolean
	 */
	protected $injectVersion = true;

	/**
	 * set if --help should be ouput as a regular argument
	 *
	 * @param boolean $injectHelp true to output --help
	 * @return ClappFormatHelp $this for chaining
	 */
	public function setInjectHelp( $injectHelp=true )
	{
		$this->injectHelp = $injectHelp;
		return $this;
	}
	
	/**
	 * set if --version should be ouput as a regular argument
	 *
	 * @param boolean $injectVersion true to output --version
	 * @return ClappFormatHelp $this for chaining
	 */
	public function setInjectVersion( $injectVersion=true )
	{
		$this->injectVersion = $injectVersion;
		return $this;
	}

	/**
	 * render the --help screen
	 *
	 * @param int $width width of output, defaults to {@link ClappFormat::$width}
	 * @return ClappFormatHelp $this for chaining
	 */
	public function render( $width=null )
	{
		$width = $width !== null ? $width : $this->width;

	 	$this->prepare( $width )
			->injectHelpArgument()
			->injectVersionArgument()
			->renderSubcommand( $width )
			->renderHeader( $width )
			->renderSynopsis( $width )
			->renderExamples( $width )
			->renderSubcommands( $width )
			->renderArguments( $width )
			->renderFooter( $width );
		
		return $this;
	}
	
	/**
	 * render commands of the current sub-command
	 *
	 * @param int $width width of output
	 * @return ClappFormatHelp $this for chaining
	 */
	protected function renderSubcommand( $width )
	{
		// the current app is a subcommand
		if( $this->app instanceof ClappSubcommand )
		{
			echo $this->app->command;
			if( $s = $this->app->commands )
				echo ' (', join( ', ', $s ), ')';
			
			if( $a = $this->app->abstract )
				echo ': ', $a;
			
			echo "\n\n";
		}
		
		return $this;
	}
	
	/**
	 * render the applications sub-commands
	 *
	 * @param int $width width of output
	 * @return ClappFormatHelp $this for chaining
	 */
	protected function renderSubcommands( $width )
	{
		// the current app is a subcommand
		if( $subs = $this->app->subcommands )
		{
			echo "SUBCOMMANDS:\n";
			$max = 0;
			$commands = array();
			foreach( $subs as $sub )
			{
				$t = $sub->command;
				if( $s = $sub->commands )
					$t .= ' ('. join( ', ', $s ) .')';
				
				$commands[ $sub->command ] = array( $t, $sub->abstract );
				$max = max( $max, ClappEncoding::strlen( $t ) );
			}
			
			$max = max( $max, $this->nameCharacters );
			
			foreach( $commands as $c )
			{
				echo '  ', $this->format->rightPadd( $c[0], $max ), ' ';
				if( $c[1] )
					echo ' ', $this->wrapIndent( $c[1], $width, $max +3 );
					
				echo "\n";
			}

			echo "\n";
		}
		
		return $this;
	}
	
	/**
	 * render the --help screen header
	 *
	 * @param int $width width of output
	 * @return ClappFormatHelp $this for chaining
	 */
	protected function renderHeader( $width )
	{
		if( $header = $this->app->header )
		{
			foreach( $header as $line )
				echo $this->wrapIndent( $line, $width ), "\n";
		
			echo "\n";
		}
		
		return $this;
	}
	
	/**
	 * render the --help screen footer
	 *
	 * @param int $width width of output
	 * @return ClappFormatHelp $this for chaining
	 */
	protected function renderFooter( $width )
	{
		if( $footer = $this->app->footer )
		{
			echo "\n";
			foreach( $footer as $line )
				echo $this->wrapIndent( $line, $width ), "\n";
		}
	
		return $this;
	}
	
	/**
	 * render the SYNOPSIS part 
	 *
	 * @param int $width width of output
	 * @return ClappFormatHelp $this for chaining
	 */
	protected function renderSynopsis( $width )
	{
		// TODO: auto generated synopsis
		// http://www.opengroup.org/onlinepubs/009695399/basedefs/xbd_chap12.html
		// utility_name [-abcDxyz][-p arg][operand]		
		// utility_name [options][operands]				-- for many options
		// [] denotes optional arguments, NOT a regex-range
		// | denotes mutually-exclusive arguments (conflicts)
		
		echo "SYNOPSIS\n";
		
		if( $this->app->subcommands )
		{
			echo '  ', $this->app->command, " <subcommand> [options] [args]\n";
			echo '  ', $this->app->command, " <subcommand> --help\n";
		}
		
		if( $this->app instanceof ClappSubcommand )
		{
			echo '  ', $this->app->parent->command, ' ', $this->app->command, " [options] [args]\n";
			echo '  ', $this->app->parent->command, ' ', $this->app->command, " --help\n";
		}
		
		if( count( $this->_names ) > 1 )
		{
			echo '  ', $this->app->command, " [options] [args]\n";
			echo '  ', $this->app->command, " --help\n";
			echo '  ', $this->app->command, " --version\n";
		}
		
		echo "\n";
		return $this;
	}
	
	/**
	 * render the EXAMPLES part
	 *
	 * @param int $width width of output
	 * @return ClappFormatHelp $this for chaining
	 */
	protected function renderExamples( $width )
	{
		if( $examples = $this->app->examples )
		{
			$command = $this->app->command;
			echo "EXAMPLES\n";
			foreach( $examples as $line )
				echo '  ', $this->wrapIndent( $line[0], $width, 2 ), ":\n    ", 
					$command, ' ', $this->wrapIndent( $line[1], $width, 5 + ClappEncoding::strlen($command) ), "\n";

			echo "\n";
		}

		return $this;
	}
	
	/**
	 * inject the --help argument
	 *
	 * @return ClappFormatHelp $this for chaining
	 */
	protected function injectHelpArgument()
	{
		if( !$this->injectHelp || !empty( $this->_names['help'] ) )
			return $this;
		
		$h = empty( $this->_keys['h'] );
		
		$arg = new ClappArgument( $h ? 'h' : null, 'help', ClappArgument::FLAG );
		$arg->setDescription( 'Print this help menu.' );
		$this->prepareSorting( $arg );
		
		return $this;
	}
	
	/**
	 * inject the --version argument
	 *
	 * @return ClappFormatHelp $this for chaining
	 */
	protected function injectVersionArgument()
	{
		if( !$this->injectVersion || !empty( $this->_names['version'] ) )
			return $this;

		$v = empty( $this->_keys['v'] );
		
		$arg = new ClappArgument( $v ? 'v' : null, 'version', ClappArgument::FLAG );
		$arg->setDescription( 'Print version information.' );
		$this->prepareSorting( $arg );
		
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
		if( $t = !($arg instanceof ClappArgumentUndefined ) && !($arg instanceof ClappArgumentDefault ) )
		{
			$this->prepareSorting( $arg );
			$this->updateLongestIdentifier( $arg->getIdentifier( true, true ) );
		}
		
		return $t;
	}
}



?>