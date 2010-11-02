<?php

// TODO: xml-file-based app configuration?

$_clappLibraryPath = dirname(__FILE__);
/**
 * Load ClappEncoding
 */
require_once( $_clappLibraryPath .'/encoding/ClappEncoding.php' );

/**
 * Load ClappException
 */
require_once( $_clappLibraryPath .'/ClappException.php' );

/**
 * Load ClappSubcommand
 */
require_once( $_clappLibraryPath .'/ClappSubcommand.php' );

/**
 * Load ClappSignals for Signal Handling
 */
require_once( $_clappLibraryPath .'/ClappSignals.php' );

/**
 * Load ClappTTY for TTY Interaction
 */
require_once( $_clappLibraryPath .'/tty/ClappTTY.php' );

/**
 * Load ClappArgument for Argument Parsing
 */
require_once( $_clappLibraryPath .'/argument/ClappArgument.php' );

/**
 * Load ClappFormat for Argument and Version information screens
 */
require_once( $_clappLibraryPath .'/format/ClappFormat.php' );


/**
 * Clapp - Command Line Application
 * 
 * Facility for properly exposing a PHP Script to the CLI
 * @todo: describe magic-getters and magic-setters
 * @package Clapp
 * @author Rodney Rehm
 */
class Clapp
{
	// see more licenses at http://www.gnu.org/licenses/license-list.html
	
	/**
	 * GNU GPL License
	 */
	const LICENSE_GPL = 'GNU General Public License, http://www.gnu.org/licenses/gpl.html.';
	
	/**
	 * GNU Lesser GPL License
	 */
	const LICENSE_LGPL = 'GNU Lesser General Public License, http://www.gnu.org/licenses/lgpl.html.';
	
	/**
	 * Apache License
	 */
	const LICENSE_APACHE = 'The Apache Software Foundation license, http://www.apache.org/licenses.';
	
	/**
	 * Artistic License (Perl)
	 */
	const LICENSE_ARTISTIC = 'The Artistic license used for Perl, http://www.perlfoundation.org/legal.';
	
	/**
	 * Expat License
	 */
	const LICENSE_EXPAT = 'The Expat license, http://www.jclark.com/xml/copying.txt.';
	
	/**
	 * Mozilla Public License
	 */
	const LICENSE_MPL = 'The Mozilla Public License, http://www.mozilla.org/MPL/.';
	
	/**
	 * Original BSD License
	 */
	const LICENSE_OBSD = 'The original (4-clause) BSD license, incompatible with the GNU GPL http://www.xfree86.org/3.3.6/COPYRIGHT2.html#6.';
	
	/**
	 * PHP License
	 */
	const LICENSE_PHP = 'The license used for PHP, http://www.php.net/license/.';

	/**
	 * Public Domain License
	 */
	const LICENSE_PUBLIC_DOMAIN = 'The non-license that is being in the public domain, http://www.gnu.org/licenses/license-list.html#PublicDomain.';
	
	/**
	 * Python License
	 */
	const LICENSE_PYTHON = 'The license for Python, http://www.python.org/2.0.1/license.html.';
	
	/**
	 * Revised BSD License
	 */
	const LICENSE_RBSD = 'The revised (3-clause) BSD, compatible with the GNU GPL, http://www.xfree86.org/3.3.6/COPYRIGHT2.html#5.';

	/**
	 * X11 License
	 */
	const LICENSE_X11 = 'The simple non-copyleft license used for most versions of the X Window System, http://www.xfree86.org/3.3.6/COPYRIGHT2.html#3.';

	/**
	 * X11 License
	 */
	const LICENSE_ZLIB = 'The license for Zlib, http://www.gzip.org/zlib/zlib_license.html.';	

	/**
	 * Eclipse License
	 */
	const LICENSE_ECLIPSE = 'Eclipse Public License, http://www.eclipse.org/legal/epl-v10.html';

	/**
	 * MIT License
	 */
	const LICENSE_MIT = 'MIT License, http://www.opensource.org/licenses/mit-license.php';
	
	/**
	 * The command the (sub) Application is known by to be shown in --help screen
	 * @var string
	 */
	protected $command = null;
	
	/**
	 * List of aliases to $command to be shown in --help screen
	 * @var array
	 */
	protected $commands = array();
	
	/**
	 * Human Readable Name of the Application to be shown in --version screen
	 * @var string
	 */
	protected $name = null;
	
	/**
	 * Human Readable Version of the Application to be shown in --version screen
	 * @var string
	 */
	protected $version = null;
	
	/**
	 * Versions of (sub) modules contained in the Application to be shown in --version screen
	 * @var array
	 */
	protected $versions = array();
	
	/**
	 * License the Application is published under to be shown in --version screen
	 * @var string
	 */
	protected $license = null;
	
	/**
	 * Copyright notice of the Application to be shown in --version screen
	 * @var string
	 */
	protected $copyright = null;
	
	/**
	 * Copyright notices of (sub) modules contained in the Application to be shown in --version screen
	 * @var array
	 */
	protected $copyrights = array();
	
	/**
	 * List of Authors of the Application to be shown in --version screen
	 * @var array
	 */
	protected $authors = array();
	
	/**
	 * Comment-lines to be shown in --version screen
	 * @var array
	 */
	protected $comments = array();
	
	/**
	 * Header-lines to be shown in --help screen
	 * @var array
	 */
	protected $header = array();
	
	/**
	 * Footer-lines to be shown in --help scren
	 * @var array
	 */
	protected $footer = array();
	
	/**
	 * Example Application Calls ("USAGE Examples") to be shown in --help screen
	 * @var array
	 */
	protected $examples = array();
	
	/**
	 * ArgumentParser instance for the Application or Subcommand
	 * @var ClappArgumentParser
	 */
	protected $args = null;
	
	/**
	 * List of {@link ArgumentGroup}s registered to organize --help screen
	 * @var array
	 */
	protected $groups = array();
	
	/**
	 * List of {@link ClappSubcommand}s registered to the Application or Subcommand
	 * @var array
	 */
	protected $subcommands = array();
	
	/**
	 * name of class to initialize for formatting the --help screen
	 * @var string
	 */
	protected $formatHelpClass = 'ClappFormatHelp';
	
	/**
	 * name of class to initialize for formatting the --version screen
	 * @var string
	 */
	protected $formatVersionClass = 'ClappFormatVersion';
	
	/**
	 * name of class to initialize for formatting the results screen (debugging helper)
	 * @var string
	 */
	protected $formatResultsClass = 'ClappFormatResults';
	
	/**
	 * name of class to initialize for parsing arguments
	 * @var string
	 */
	protected $argumentParserClass = 'ClappArgumentParser';
	
	/**
	 * Create a new Command Line Application
	 * @param string $name Human Readable Name of the Application
	 * @param string $version Human Readable Version of the Application
	 * @param string $license License the Application is published under (see LICENSE_* Constants)
	 * @uses $argumentParserClass to initialize a new ArgumentParser
	 */
	public function __construct( $name, $version=null, $license=null )
	{
		$this->name = $name;
		$this->version = $version;
		$this->license = $license;
		$this->args = new $this->argumentParserClass();
	}
	
	/**
	 * Magic Getter
	 *
	 * @note Issues an E_USER_NOTICE if the requested attribute does not exist
	 * @param string $name name of attribute to read
	 * @return mixed value of the attribute
	 */
	public function __get( $name )
	{
		$fn = 'get'. ucfirst( $name );
		if( is_callable( array( $this, $fn ) ) )
			return $this->$fn();
		
		trigger_error( 'Undefined property: '. get_class($this) .'::$'. $name, E_USER_NOTICE );
	}
	
	/**
	 * Magic Setter
	 *
	 * @note Issues an E_USER_NOTICE if the requested attribute does not exist
	 * @param string $name name of the attribute to write
	 * @param mixed $value value to set for the attribute
	 * @return void
	 */
	public function __set( $name, $value )
	{
		$fn = 'set'. ucfirst( $name );
		if( is_callable( array( $this, $fn ) ) )
			return $this->$fn( $value );
		
		trigger_error( 'Undefined property: '. get_class($this) .'::$'. $name, E_USER_NOTICE );
	}
	
	/**
	 * get Human Readable Name of the Application
	 * @return string name of the application
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * get Human Readable Version of the Application
	 * @return string version of the application
	 */
	public function getVersion()
	{
		return $this->version;
	}
	
	/**
	 * set Human Readable Version of the Application
	 *
	 * see the LICENSE_* Constants for an idea - any string will do, though!
	 * @see http://www.gnu.org/prep/standards/html_node/_002d_002dversion.html#g_t_002d_002dversion
	 * @param string $version version to set for the application
	 * @return Clapp $this for chaining
	 */
	public function setVersion( $version )
	{
		$this->version = $version;
		return $this;
	}
	
	/**
	 * get the Versions of (sub) modules contained in the Application
	 * @return array list of modules and versions
	 */
	public function getVersions()
	{
		return $this->versions;
	}
	
	/**
	 * add a Version of a (sub) module contained in the Application
	 * @see http://www.gnu.org/prep/standards/html_node/_002d_002dversion.html#g_t_002d_002dversion
	 * @param string $module name of the module, or name and version in combined form
	 * @param string $version version of the module
	 * @return Clapp $this for chaining
	 */
	public function addVersion( $module, $version=null )
	{
		if( $version === null )
		{
			$this->versions[] = $module;
			return $this;
		}
		
		$this->versions[] = $module .' '. $version;
		return $this;
	}
	
	/**
	 * get Comment-lines to be shown in --version screen
	 * @return array list of Comments
	 */
	public function getComments()
	{
		return $this->comments;
	}
	
	/**
	 * add a Comment-line to be shown in --version screen
	 *
	 * @note this is not defined by GNU/POSIX
	 * @param string $line comment (may contain linebreaks and TTYFormats)
	 * @return Clapp $this for chaining
	 */
	public function addComment( $line )
	{
		$this->comments[] = $line;
		return $this;
	}
	
	/**
	 * get License the Application is published under
	 * @return string license of the application
	 */
	public function getLicense()
	{
		return $this->license;
	}
	
	/**
	 * set License the Application is published under
	 *
	 * see LICENSE_* Constants for an idea
	 * @see http://www.gnu.org/prep/standards/html_node/_002d_002dversion.html#g_t_002d_002dversion
	 * @param string $license license the application is published under
	 * @return Clapp $this for chaining 
	 */
	public function setLicense( $license )
	{
		$this->license = $license;
		return $this;
	}
	
	/**
	 * get Copyright notice of the Application
	 * @return string copyright notice of the application
	 */
	public function getCopyright()
	{
		return $this->copyright;
	}
	
	/**
	 * set Copyright notice of the Application
	 *
	 * @see http://www.gnu.org/prep/standards/html_node/_002d_002dversion.html#g_t_002d_002dversion
	 * @param string $from year the copyright began, or the whole notice as a single line
	 * @param string $till year until the copyright lasts
	 * @param string $who Person or Company owning the copyright
	 * @return Clapp $this for chaining
	 */
	public function setCopyright( $from, $till=null, $who=null )
	{
		if( $till === null && $who === null )
		{
			$this->copyright = $from;
			return $this;
		}
		
		$this->copyright = 'Copyright (C) '. $from .'-'. $till .' '. $who;
		return $this;
	}
	
	/**
	 * get Copyright notices of (sub) modules contained in the Application
	 * @return array list of copyright notices of contained (sub) modules
	 */
	public function getCopyrights()
	{
		return $this->copyrights;
	}
	
	/**
	 * add Copyright notice of a (sub) module contained in the Application
	 *
	 * @see http://www.gnu.org/prep/standards/html_node/_002d_002dversion.html#g_t_002d_002dversion
	 * @param string $module name of the module, or whole copyright notice as a single line
	 * @param string $from year the copyright began, or the whole notice as a single line
	 * @param string $till year until the copyright lasts
	 * @param string $who Person or Company owning the copyright
	 * @return Clapp $this for chaining
	 */
	public function addCopyright( $module, $from=null, $till=null, $who=null )
	{
		if( $from === null && $till === null && $who === null )
		{
			$this->copyrights[] = $module;
			return $this;
		}
		
		$this->copyrights[] =  $module .' Copyright (C) '. $from .'-'. $till .' '. $who;
		return $this;
	}
	
	/**
	 * get list of Authors of the Application
	 * @return array authors of the application
	 */
	public function getAuthors()
	{
		return $this->authors;
	}
	
	/**
	 * add an author of the Application
	 *
	 * @see http://www.gnu.org/prep/standards/html_node/_002d_002dversion.html#g_t_002d_002dversion
	 * @param string $name name of the author, or whole author as single line
	 * @param string $email E-Mail Address of the author
	 * @return Clapp $this for chaining
	 */
	public function addAuthor( $name, $email=null )
	{
		if( $email === null )
		{
			$this->authors[] = $name;
			return $this;
		}
		$this->authors[] = $name .' <'. $email .'>';
		return $this;
	}

	/**
	 * get Example Application Calls ("USAGE Examples")
	 * @return array list of exmaple calls
	 */
	public function getExamples()
	{
		return $this->examples;
	}
	
	/**
	 * add an Example Application Call
	 *
	 * @note this is not defined by GNU/POSIX
	 * @param string $description description / title of the example (may contain linebreaks and TTYFormats)
	 * @param string $arguments cli call (clapputil --some-args=123) (may contain linebreaks and TTYFormats)
	 * @return Clapp $this for chaining
	 */
	public function addExample( $description, $arguments )
	{
		$this->examples[] = array( $description, $arguments );
		return $this;
	}
	
	/**
	 * get header-lines of --help screen
	 * @return array header-lines
	 */
	public function getHeader()
	{
		return $this->header;
	}
	
	/**
	 * add a header-line for the --help screen
	 *
	 * @note this is not defined by GNU/POSIX
	 * @param string $line header-line (may contain linebreaks and TTYFormats)
	 * @return Clapp $this for chaining
	 */
	public function addHeader( $line )
	{
		$this->header[] = $line;
		return $this;
	}
	
	/**
	 * get footer-lines of --help screen
	 * @return array footer-lines
	 */
	public function getFooter()
	{
		return $this->footer;
	}
	
	/**
	 * add a footer-line for the --help screen
	 *
	 * @note this is not defined by GNU/POSIX
	 * @param string $line footer-line (may contain linebreaks and TTYFormats)
	 * @return Clapp $this for chaining
	 */
	public function addFooter( $line )
	{
		$this->footer[] = $line;
		return $this;
	}
	
	/**
	 * add url where bugs should be reported to
	 * @see http://www.gnu.org/prep/standards/html_node/_002d_002dhelp.html#g_t_002d_002dhelp
	 * @param string $url url to report bugs to
	 * @return Clapp $this for chaining
	 */
	public function addBugsURL( $url )
	{
		return $this->addFooter( 'Report bugs to: '. $url );
	}
	
	/**
	 * add url to website of the application
	 * @see http://www.gnu.org/prep/standards/html_node/_002d_002dhelp.html#g_t_002d_002dhelp
	 * @param string $url url of website
	 * @return Clapp $this for chaining
	 */
	public function addHomeURL( $url )
	{
		return $this->addFooter( 'Home page: '. $url );
	}
	
	/**
	 * add url where the user can get further help
	 * @see http://www.gnu.org/prep/standards/html_node/_002d_002dhelp.html#g_t_002d_002dhelp
	 * @param string $url url of help / wiki / forum
	 * @return Clapp $this for chaining
	 */
	public function addHelpURL( $url )
	{
		return $this->addFooter( 'General Help: '. $url );
	}
	
	/**
	 * Add a ClappArgument | ClappArgumentGroup | Clapp instance to the application
	 *
	 * {@link ClappArgument}s are registered with the ArgumentParser of the application.
	 * {@link Clapp}s are registered with the ArgumentParser.
	 * {@link ClappArgumentGroup}s are remembered for --help screen rendering.
	 * @param ClappArgument|ClappArgumentGroup|Clapp $o object to add to the application
	 * @return Clapp $this for chaining
	 * @throws ClappException if given value in $o is neither ClappArgument, ClappArgumentGroup nor Clapp
	 */
	public function add( $o )
	{
		switch( true )
		{
			case $o instanceof ClappArgumentGroup:
				$this->groups[ $o->id ] = $o;
			break;
			
			case $o instanceof ClappArgument:
				$this->args->add( $o );
			break;
			
			case $o instanceof Clapp:
				$o->setParent( $this );
				$this->subcommands[ $o->command ] = $o;
				foreach( $o->commands as $c )
					$this->subcommands[ $c ] = $o;
			break;
			
			default:
				if( !is_object( $o ) )
					throw new ClappException( 'Only objects can be added to Clapp!' );
									
				throw new ClappException( 'Objects of class '. get_class( $o ) .' cannot be added to Clapp!' );
			break;
		}
		
		return $this;
	}
	
	/**
	 * get the ArgumentGroups registered for the application
	 * @return array list of ArgumentGroups
	 */
	public function getGroups()
	{
		return $this->groups;
	}
	
	/**
	 * get the ArgumentParser instance of the application
	 * @return ClappArgumentParser ArgumentParser instance used by the application
	 */
	public function getArgs()
	{
		return $this->args;
	}
	
	/**
	 * get the command the application is known by
	 * @return string command of the application, or command determined by the ArgumentParser
	 */
	public function getCommand()
	{
		if( $this->command !== null )
			return $this->command;

		return $this->args->command();
	}
	
	/**
	 * set the command (and aliases) the application is known by
	 * @param string $command command of the application
	 * @param array $commands aliases to the command
	 * @return Clapp $this for chaining
	 */
	public function setCommand( $command, $commands=array() )
	{
		$this->command = $command;
		$this->commands = $commands;
		return $this;
	}
	
	/**
	 * get the alias commands the application is known by
	 * @return array alias commands of the application
	 */
	public function getCommands()
	{
		return $this->commands;
	}
	
	/**
	 * get the list of Subcommands registered with the application
	 * @return array list of {@link ClappSubcommand}s
	 */
	public function getSubcommands()
	{
		return $this->subcommands;
	}
	
	/**
	 * Initialize the ArgumentParser
	 *
	 * test if a registered subcommand was called,
	 * automatically display --help screen,
	 * automatically display --version screen,
	 * validate the parsed arguments
	 * @param boolean $validate flag to activate argument validation, defaults to true
	 * @return Clapp $this for chaining
	 * @uses help() to display --help screen
	 * @uses version() to display --version screen
	 */
	public function initialize( $validate=true )
	{
		if( $this->subcommands && ($command = $this->args->parseCommand()) && !empty( $this->subcommands[ $command ] ) )
		{
			// check here:
			// manage that parser to start at index+1
			// $this->subcommands[ $command ]
			return $this->subcommands[ $command ]->initialize( $validate );
		}
		
		$args = $this->args->parse();
		
		// show help
		if( $args->value('help') )
		{
			$this->help();
		}

		// show version
		if( $args->value('version') )
		{
			$this->version();
		}
		
		if( $validate )
			$args->validate();

		return $this;
	}
	
	/**
	 * render the --help screen and exit(0)
	 * @return void
	 * @uses $formatHelpClass to initialize the help renderer
	 */
	public function help()
	{
		$format = new $this->formatHelpClass( $this );
		$format->render();
		exit;
	}
	
	/**
	 * render the --version screen and exit(0)
	 * @return void
	 * @uses $formatVersionClass to initialize the version renderer
	 */
	public function version()
	{
		$format = new $this->formatVersionClass( $this );
		$format->render();
		exit;
	}
	
	/**
	 * render the results screen and exit(0)
	 * @return void
	 * @uses $formatResultsClass to initialize the results renderer
	 */
	public function results()
	{
		$format = new $this->formatResultsClass( $this );
		$format->render();
		exit;
	}
	
	/**
	 * Flag stating to listen for signals and register {@link signal()} as callback
	 */
	const SIGNALS_LISTEN = 0;
	
	/**
	 * Flag stating to ignore signals
	 */
	const SIGNALS_IGNORE = 1;
	
	/**
	 * Flag stating to use default PCNTL handlers
	 */
	const SIGNALS_DEFAULT = 2;
	
	/**
	 * setup Signal Handling for SIGTERM, SIGINT, SIGQUIT, SIGHUP
	 *
	 * @note PCNTL is required for Signal Handling to work
	 * @param int $type listening mode, see SIGNALS_* Constants
	 * @return Clapp $this for chaining
	 */
	public function signals( $type=self::SIGNALS_LISTEN )
	{
		ClappSignals::init();
		$signals = array( SIGTERM, SIGINT, SIGQUIT, SIGHUP );
		switch( $type )
		{
			case self::SIGNALS_LISTEN:
				foreach( $signals as $signal )
					ClappSignals::listen( $signal, array( $this, 'signal' ) );
			break;

			case self::SIGNALS_IGNORE:
				foreach( $signals as $signal )
					ClappSignals::listen( $signal, SIG_IGN );
			break;
						
			case self::SIGNALS_DEFAULT:
				foreach( $signals as $signal )
					ClappSignals::listen( $signal, SIG_DFL );
			break;
			
			default:
				throw new ClappException( 'Cannot handle $type '. $type .'! use SIGNAL_LISTEN|SIGNAL_IGNORE|SIGNAL_DEFAULT' );
			break;
		}

		return $this;
	}
	
	/**
	 * default clapp signal handler
	 *
	 * will output which signal was called and exit(0)
	 * @param int $signal signal caught
	 * @return void
	 */
	public function signal( $signal )
	{
		$tty = ClappTTY::format();
		switch( $signal )
		{
			case SIGTERM:
				echo "\n\nCaught Signal ", $tty->format( 'SIGTERM', ClappTTYFormat::UNDERLINE ), ", Terminating NOW.\n";
			break;
			
			case SIGINT:
				echo "\n\nCaught Signal ", $tty->format( 'SIGINT', ClappTTYFormat::UNDERLINE ), ", Terminating NOW.\n";
			break;
			
			case SIGQUIT:
				echo "\n\nCaught Signal ", $tty->format( 'SIGQUIT', ClappTTYFormat::UNDERLINE ), ", Terminating NOW.\n";
			break;
			
			case SIGHUP:
				echo "\n\nCaught Signal ", $tty->format( 'SIGHUP', ClappTTYFormat::UNDERLINE ), ", Terminating NOW.\n";
			break;
		}
		
		exit;
	}

}

?>