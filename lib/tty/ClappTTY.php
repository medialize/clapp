<?php

if( !isset( $_clappLibraryPath ) )
	$_clappLibraryPath = dirname(__FILE__) .'/..';

/**
 * Load ClappEncoding
 */
require_once( $_clappLibraryPath .'/encoding/ClappEncoding.php' );

/**
 * Load ClappTTYFormat (Dummy TTY Formatter)
 */
require_once( $_clappLibraryPath .'/tty/format/ClappTTYFormat.php' );

/**
 * Load ClappTTYFormatAnsi (ECMA-48 / ANSI TTY Formatter)
 */
require_once( $_clappLibraryPath .'/tty/format/ClappTTYFormatAnsi.php' );

/**
 * Load ClappTTYControl (Dummy TTY Controller)
 */
require_once( $_clappLibraryPath .'/tty/control/ClappTTYControl.php' );

/**
 * Load ClappTTYControlAnsi (ECMA-48 / ANSI TTY Controller)
 */
require_once( $_clappLibraryPath .'/tty/control/ClappTTYControlAnsi.php' );

// make sure TTY is closed. PHP would do this by itself, though.
register_shutdown_function( array( 'ClappTTY', 'close' ) );

/**
 * Clapp TTY Interface
 *
 * @example /examples/example05.php TTY Formatting (ECMA-48 SGR: Select Graphic Rendition)
 * @example /examples/example06.php TTY Character Control
 * @package Clapp
 * @subpackage Clapp-tty
 * @author Rodney Rehm
 */
class ClappTTY
{
	/**
	 * Standard Input FileDescriptor
	 */
	const STDIN = 0;
	
	/**
	 * Standard Output FileDescriptor
	 */
	const STDOUT = 1;
	
	/**
	 * Standard Error FileDescriptor
	 */
	const STDERR = 2;
	
	/**
	 * List of TERMs not supporting ECMA-48 SGR
	 * @var array
	 */
	protected static $_blacklistFormat = array( 'xterm', 'vt100' );
	
	/**
	 * List of TERMs not supporting ECMA-48
	 * @var array
	 */
	protected static $_blacklistControl = array();

	/**
	 * Instance of TTY Formatting Interface
	 * @var ClappTTYFormat
	 */
	protected static $_format = null;
	
	/**
	 * Instance of TTY Controlling Interface
	 * @var ClappTTYControl
	 */
	protected static $_control = null;
	
	/**
	 * FilePointer to the current TTY
	 * @var resource
	 */
	protected static $_fp = null;
	
	/**
	 * paths to the respective TTYs
	 * @var array
	 */
	protected static $_tty = array( 
		0 => null,
		1 => null,
		2 => null,
	);
	
	/**
	 * get the TTY Formatter Instance
	 * @return ClappTTYFormat TTY Formatter Instance
	 * @uses $_format to cache the instance
	 * @uses name() to determine if STDOUT is a TTY
	 * @uses $_blacklistFormat to identify terminals incapable of formatting
	 */
	public static function format()
	{
		if( self::$_format !== null )
			return self::$_format;
		
		// no TTY, no format
		if( !ClappTTY::name( self::STDOUT ) )
			self::$_format = new ClappTTYFormat();
					
		// ANSI / ECMA-48 Escape Sequences
		if( !in_array( strtolower( getenv('TERM') ), self::$_blacklistFormat ) )
			self::$_format = new ClappTTYFormatAnsi();
		
		// Default (do-nothing) Wrapper
		else
			self::$_format = new ClappTTYFormat();
			
		return self::$_format;
	}
	
	/**
	 * get the TTY Controller Instance
	 * @note most controlling features work on a STDOUT-only TTY, 
	 * while determining the cursor's position requires an IN/OUT TTY
	 * @return ClappTTYControl TTY Controller Instance
	 * @uses $_control to cache the instance
	 * @uses name() to determine if STDOUT is a TTY
	 * @uses $_blacklistControl to identify terminals incapable of controlling
	 */
	public static function control()
	{
		if( self::$_control !== null )
			return self::$_control;
		
		// no TTY, no control
		if( !ClappTTY::name( self::STDOUT ) )
			self::$_control = new ClappTTYControl();
					
		// ANSI / ECMA-48 Escape Sequences
		if( !in_array( strtolower( getenv('TERM') ), self::$_blacklistControl ) )
			self::$_control = new ClappTTYControlAnsi();
		
		// Default (do-nothing) Wrapper
		else
			self::$_control = new ClappTTYControl();
			
		return self::$_control;
	}
	
	/**
	 * Open a FilePointer to the TTY itself.
	 *
	 * @note The FilePointer will be opened to the path returned by posix_ttyname().
	 * @param string $mode mode to open the FilePointer in, defaults to "r+"
	 * @return resource FilePointer to the TTY
	 * @uses name() to determine the TTY's path
	 * @uses io() to determine if the STDIN and STDOUT are connected to the same TTY
	 * @uses ClappEncoding::filter() to attach the transparent Encoding StreamFilters
	 * @uses $_fp to cache the FilePointer (in the mode first opened!)
	 * @throws ClappException if STDIN and STDOUT are not connected to the same TTY, or the FilePointer could not be opened
	 */
	public static function open( $mode='r+' )
	{
		if( !empty( self::$_fp ) )
			return self::$_fp;
			
		if( !self::io() )
			throw new ClappException( 'STDIN and STDOUT have are not the same TTY' );
			
		if( !is_resource( $fh = @fopen( self::name( self::STDIN ), $mode ) ) )
			throw new ClappException( self::name( self::STDIN ) .' could not be opened in mode '. $mode );
		
		//ClappEncoding::filter( $fh );
		return self::$_fp = $fh;
	}
	
	/**
	 * close the FilePointer to the TTY itself.
	 * @return void
	 * @uses ClappEncodung::unfilter() to detach transparent Encoding StreamFilters
	 */
	public static function close()
	{
		if( !self::$_fp )
			return;
			
		ClappEncoding::unfilter( self::$_fp );
		fclose( self::$_fp );
		self::$_fp = null;
	}
	
	/**
	 * test if both STDIN and STDOUT are connected to the same TTY
	 * @return boolean true if STDIN and STDOUT are connected to the same TTY, false else
	 * @uses name() for TTY detection
	 */
	public static function io()
	{
		return self::name( self::STDIN ) && self::name( self::STDIN ) === self::name(self::STDOUT );
	}
	
	/**
	 * test if STDIN is connected to a TTY
	 * @return boolean true if STDIN is a TTY, false else
	 * @uses name() for TTY detection
	 */
	public static function stdin()
	{
		return self::name( self::STDIN );
	}
	
	/**
	 * test if STDOUT is connected to a TTY
	 * @return boolean true if STDOUT is a TTY, false else
	 * @uses name() for TTY detection
	 */
	public static function stdout()
	{
		return self::name( self::STDOUT );
	}
	
	/**
	 * test if STDERR is connected to a TTY
	 * @return boolean true if STDERR is a TTY, false else
	 * @uses name() for TTY detection
	 */
	public static function stderr()
	{
		return self::name( self::STDERR );
	}
	
	/**
	 * determine a FileDescriptors TTY name
	 * @param int $fd FileDescriptor (0:STDIN, 1:STDOUT, 2:STDERR)
	 * @return string|boolean path to the TTY, false else
	 */
	protected static function name( $fd )
	{
		if( !function_exists('posix_ttyname') )
			return false;
		
		if( self::$_tty[ $fd ] !== null )
			return self::$_tty[ $fd ];
		
		return self::$_tty[ $fd ] = posix_ttyname( $fd );
	}

	/**
	 * determine dimensions of the TTY
	 * @return array array( 'rows' => 123, 'columns' => 123 ) or array( 'rows' => null, 'columns' => null ) if IN/OUT is not a TTY
	 * @uses io() to determine if STDIN and STDOUT are the same TTY
	 * @uses `stty -a` to retrieve information about the TTY
	 * @author Rodney Rehm
	 * @author Christian Seiler <christian.seiler@selfhtml.org>
	 */
	public static function dimensions()
	{
		// different systems yield different stty output...
		// gentoo: "; rows 20; columns 130;"
		// darwin: "; 20 rows; 130 columns;"
		
		$t = array( 'columns' => null, 'rows' => null );
		
		if( !self::io() )
			return $t;
		
		// TODO: `stty size` might work on some systems, too
		
		$stty = @`stty -a`;
		$matches = array();
		preg_match_all( '#; (?<leading>\d+ )?(?<type>(columns|rows))(?<trailing> \d+)?#', $stty, $matches, PREG_SET_ORDER );
		foreach( $matches as $m )
		{
			$t[ $m['type'] ] = intval( !empty( $m['leading'] ) ? $m['leading'] : $m['trailing'] );
		}
		
		return $t;
	}
	
	/**
	 * determine encoding of the TTY
	 * @return string encoding of the TTY, defaults to "ASCII"
	 * @uses `locale -k charmap` to actually detect encoding
	 * @author Rodney Rehm
	 * @author Christian Seiler <christian.seiler@selfhtml.org>
	 */
	public static function encoding()
	{
		$charmap = @`locale -k charmap`;
		if( !$charmap )
			return 'ASCII';
			
		$charmap = explode( '=', $charmap );
		$e = strtoupper( !empty( $charmap[1] ) ? trim( $charmap[1], "\n\"" ) : 'ASCII' );

		// Dawin gives us »US-ASCII«
		if( $e == 'US-ASCII' )
		{
			$e = 'ASCII';
		}
		else
		{
			// Darwin gives us »ISO8859-1«
			$e = preg_replace( '#^ISO_?(?=\d)#i', 'ISO-', $e );
		}	

		return $e;
	}
}

?>