<?php

/**
 * Controller Class for Cursor Movement
 *
 * ECMA-48 / ANSI Escape Codes
 * @see http://en.wikipedia.org/wiki/ANSI_escape_code#Codes
 * @see http://linux.die.net/man/4/console_codes
 * @package Clapp
 * @subpackage Clapp-tty-control
 * @author Rodney Rehm
 */
class ClappTTYControlAnsi extends ClappTTYControl
{
	/**
	 * Move cursor up one cell
	 *
	 * ECMA-48 calls this CUU: CUrsor Up
	 * @param int $offset number of cells to move
	 * @return ClappTTYControlAnsi $this for chaining
	 */
	public function up( $offset=1 )
	{
		echo "\033[". $offset ."A";
		return $this;
	}
	
	/**
	 * Move cursor down one cell
	 *
	 * ECMA-48 calls this CUD: CUrsor Down
	 * @param int $offset number of cells to move
	 * @return ClappTTYControlAnsi $this for chaining
	 */
	public function down( $offset=1 )
	{
		echo "\033[". $offset ."B";
		return $this;
	}
	
	/**
	 * Move cursor right one cell
	 *
	 * ECMA-48 calls this CUF: CUrsor Forward
	 * @param int $offset number of cells to move
	 * @return ClappTTYControlAnsi $this for chaining
	 */
	public function right( $offset=1 )
	{
		echo "\033[". $offset ."C";
		return $this;
	}
	
	/**
	 * Move cursor left one cell
	 *
	 * ECMA-48 calls this CUB: CUrsor Back
	 * @param int $offset number of cells to move
	 * @return ClappTTYControlAnsi $this for chaining
	 */
	public function left( $offset=1 )
	{
		echo "\033[". $offset ."D";
		return $this;
	}

	/**
	 * Move cursor up one row, setting col=1
	 *
	 * ECMA-48 calls this CPL: Cursor Previous Line
	 * @param int $offset number of rows to move
	 * @return ClappTTYControlAnsi $this for chaining
	 */
	public function previous( $offset=1 )
	{
		echo "\033[". $offset ."F";
		return $this;
	}
	
	/**
	 * Move cursor up down row, setting col=1
	 *
	 * ECMA-48 calls this CNL: Cursor Next Line
	 * @param int $offset number of rows to move
	 * @return ClappTTYControlAnsi $this for chaining
	 */
	public function next( $offset=1 )
	{
		echo "\033[". $offset ."E";
		return $this;
	}
	
	/**
	 * Move cursor to specified column
	 *
	 * ECMA-48 calls this CHA: Cursor Horizontal Absolute
	 * @param int $column the column to move to (remember they start at 1)
	 * @return ClappTTYControlAnsi $this for chaining
	 */
	public function column( $column=1 )
	{
		echo "\033[". $column ."G";
		return $this;
	}
	
	/**
	 * Move cursor to specified position
	 *
	 * ECMA-48 calls this CUP: CUrsor Position
	 * @param string $row the row to move to (remember they start at 1)
	 * @param string $column the column to move to (remember they start at 1)
	 * @return ClappTTYControlAnsi $this for chaining
	 */
	public function position( $row=1, $column=1 ) // CUP: CUrsor Position
	{
		echo "\033[". $row .";". $column ."H";
		// same as: HVP – Horizontal and Vertical Position
		// echo "\033[". $row .";". $column ."f";
		return $this;
	}

	/**
	 * Output blank characters
	 *
	 * ECMA-48 calls this ICH
	 * @param int $blanks number of blanks to print
	 * @return ClappTTYControlAnsi $this for chaining
	 */
	public function blanks( $blanks=1 )
	{
		echo "\033[". $blanks ."@";
		return $this;
	}

	/**
	 * Clear entire Screen
	 *
	 * ECMA-48 calls this ED: Erase Data
	 * @return ClappTTYControlAnsi $this for chaining
	 */
	public function clear()
	{
		echo "\033[2J";
		return $this;
	}
	
	/**
	 * Clear Screen before cursor position
	 *
	 * ECMA-48 calls this ED: Erase Data
	 * @note the cursor position does not change
	 * @return ClappTTYControlAnsi $this for chaining
	 */
	public function clearToCursor()
	{
		echo "\033[1J";
		return $this;
	}
	
	/**
	 * Clear Screen after cursor position
	 *
	 * ECMA-48 calls this ED: Erase Data
	 * @note the cursor position does not change
	 * @return ClappTTYControlAnsi $this for chaining
	 */
	public function clearFromCursor()
	{
		echo "\033[0J";
		return $this;
	}
	
	/**
	 * Clear Line
	 *
	 * ECMA-48 calls this EL: Erase Line
	 * @note the cursor position does not change
	 * @return ClappTTYControlAnsi $this for chaining
	 */
	public function clearLine()
	{
		echo "\033[2K";
		return $this;
	}
	
	/**
	 * Clear Line before cursor position
	 *
	 * ECMA-48 calls this EL: Erase Line
	 * @note the cursor position does not change
	 * @return ClappTTYControlAnsi $this for chaining
	 */
	public function clearLineToCursor()
	{
		echo "\033[1K";
		return $this;
	}
	
	/**
	 * Clear Line after cursor position
	 *
	 * ECMA-48 calls this EL: Erase Line
	 * @note the cursor position does not change
	 * @return ClappTTYControlAnsi $this for chaining
	 */
	public function clearLineFromCursor()
	{
		echo "\033[0K";
		return $this;
	}
	
	/**
	 * determine current cursor position
	 *
	 * @return array array( 'row' => 123, 'col' => 123 ), values will be null if not connected to a TTY
	 * @uses ClappTTY::open() to open a FilePointer to the TTY
	 * @uses `stty` to configure and reset TTY
	 */
	public function getPosition()
	{
		$pos = array( 'row' => null, 'col' => null );

		try
		{
			// open rw handle to (the I/O) TTY device
			$tty = ClappTTY::open();

			// save current TTY settings
			$old = @`stty -g`;
			// disable automatic output
			system( "stty -echo" );
			// request cursor position
			fprintf( $tty, "\033[6n" );
			fflush( $tty );
			// setup TTY read timeout
			system( "stty -icanon min 0 time 1" );
			// read response, e.g: ^[[2;19R
			$result = stream_get_line( $tty, 20, "R" );
			// restore TTY settings
			system( "stty $old" );

			// position was not returned
			if( !is_string($result) || !$result || $result[0] != "\033" )
				return $pos;

			$result = explode( ';', substr( $result, 2 ) );
			$pos['row'] = $result[0];
			$pos['col'] = $result[1];
		}
		catch( ClappException $e )
		{
			// ignore
			return $pos;
		}

		return $pos;
	}
	
	/**
	 * save the cursor position
	 *
	 * ECMA-48 calls this SCP: Save Cursor Position
	 * @return ClappTTYControlAnsi $this for chaining
	 */
	public function save()
	{
		echo "\033[s";
		return $this;
	}
	
	/**
	 * restore the cursor position
	 *
	 * ECMA-48 calls this RCP: Restore Cursor Position
	 * @return ClappTTYControlAnsi $this for chaining
	 */
	public function restore()
	{
		echo "\033[u";
		return $this;
	}

	/**
	 * hide the cursor
	 *
	 * ECMA-48 calls this DECTCEM
	 * @return ClappTTYControlAnsi $this for chaining
	 */
	public function hide()
	{
		echo "\033[?25l";
		return $this;
	}
	
	/**
	 * hide the cursor
	 *
	 * ECMA-48 calls this DECTCEM
	 * @return ClappTTYControlAnsi $this for chaining
	 */
	public function show()
	{
		echo "\033[?25h";
		return $this;
	}
}

?>