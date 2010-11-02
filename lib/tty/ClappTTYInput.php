<?php

/**
 * TTY Input Interface
 *
 * @package Clapp
 * @subpackage Clapp-tty
 * @author Rodney Rehm
 */
class ClappTTYInput
{
	/**
	 * read a character from TTY
	 * @param boolean $hide true to hide user input from TTY, false to show (default)
	 * @return string character read from TTY
	 * @uses `stty` to configure and reset TTY
	 * @throws ClappException if STDIN and STDOUT are not connected to the same TTY
	 * @author Rodney Rehm
	 * @author Christian Seiler <christian.seiler@selfhtml.org>
	 */
	public static function char( $hide=false )
	{
		if( !ClappTTY::io() )
			throw new ClappException( 'STDIN and STDOUT have are not the same TTY' );
			
		$hide = $hide ? '-echo' : 'echo';
		$old = @`stty -g`;
		@system( "stty $hide -icanon min 1 time 0" );
		$c = fgetc( STDIN );
		@system( "stty $old" );
		return $c;
	}
	
	/**
	 * read a line (delimited by \n) from TTY
	 * @param boolean $hide true to hide user input from TTY, false to show (default)
	 * @return string line read from TTY (excluding trailing \n)
	 * @uses `stty` to configure and reset TTY
	 * @throws ClappException if STDIN and STDOUT are not connected to the same TTY
	 * @author Rodney Rehm
	 * @author Christian Seiler <christian.seiler@selfhtml.org>
	 */
	public static function line( $hide=false )
	{
		$hide = $hide ? '-echo' : 'echo';
		$old = @`stty -g`;
		@system( "stty $hide -icanon min 1 time 0" );
		$c = fgets( STDIN );
		@system( "stty $old" );
		return trim( $c, "\n" );
	}
	
	/**
	 * read a line (delimited by \n) from TTY unless timeout was reached first
	 * @param int $timeout number of seconds to wait for input
	 * @param string $default default value to return if timeout was reached
	 * @return string line read from TTY (excluding trailing \n)
	 * @uses `read` to read from TTY with a timeout
	 * @see http://de.php.net/manual/de/function.readline.php#91643
	 */
	public static function lineTimeout( $timeout=null, $default=null )
	{
		if( $timeout = intval( $timeout ) )
		{
			$default = escapeshellarg( $default );
			$t = `clapptmp=$default; read -t $timeout clapptmp; echo \$clapptmp`;
		}
		else
			$t = `read clapptmp; echo \$clapptmp`;
			
		return trim( $t, "\n" );
	}
}
 

?>