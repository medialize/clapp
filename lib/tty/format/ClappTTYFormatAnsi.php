<?php

/**
 * Controller Class for SGR: Select Graphic Rendition
 *
 * ECMA-48 / ANSI Escape Codes - SGR
 * @see http://en.wikipedia.org/wiki/ANSI_escape_code#Codes
 * @see http://linux.die.net/man/4/console_codes
 * @package Clapp
 * @subpackage Clapp-tty-format
 * @author Rodney Rehm
 */
class ClappTTYFormatAnsi extends ClappTTYFormat
{
	/**
	 * Pattern to match SGR escape sequences
	 */
	const FORMAT_PATTERN = "#(\033\[[0-9;]+m)#S";
	
	/**
	 * Format a string with SGR Escape sequences
	 *
	 * The class constants of {@link ClappTTYFormat} translate the format-codes to readable names.
	 * @param string $string the string to wrap (or prepend) with format
	 * @param int|array $formats FormatID or array of FormatIDs see constants of {@link ClappTTYFormat}
	 * @param boolean $reset append a format-reset to the string if true
	 * @return string SGR formatted string
	 */
	public function format( $string, $formats=null, $reset=true )
	{
		if( $formats === null )
			return $string;
		
		if( !is_array( $formats ) )
			$formats = array( $formats );
			
		$t = "\033[". join( ';', $formats ) ."m". $string;
		
		if( $reset ) 
			$t .= "\033[0m";
		
		return $t;
	}
	
	/**
	 * remove SGR formats from string
	 * @param string $string text to remove SGR codes from
	 * @return string SGR Escape Sequence free text
	 */
	public function strip( $string )
	{
		return preg_replace( self::FORMAT_PATTERN, '', $string );
	}

	/**
	 * get the string's length (without counting SGR Escape Sequences)
	 * @param string $string string to determine length of
	 * @return int length of string without SGR Escape Sequences
	 * @uses strip() to remove SGR Escape Sequences
	 * @uses CLappEncoding::strlen() to determine length
	 */
	public function strlen( $string )
	{
		return ClappEncoding::strlen( $this->strip( $string ) );
	}
	
	/**
	 * split a very long word into tokens (while ignoring and preserving SGR Escape Sequences)
	 * @param string $string word to split
	 * @param int $length maximum word length
	 * @return array list of tokens
	 */
	protected function wordwrapSplit( $string, $length )
	{
		$tokens = array();
		$buffer = '';
		
		// split into splittable tokens
		$_tokens = preg_split( self::FORMAT_PATTERN, $string, -1, PREG_SPLIT_NO_EMPTY + PREG_SPLIT_DELIM_CAPTURE );
		foreach( $_tokens as $token )
		{
			if( preg_match( self::FORMAT_PATTERN, $token ) )
			{
				$buffer .= $token;
				continue;
			}
			
			if( strlen( $string ) > $length )
			{
				// err.. yeah... what should we do with a drunken sailor?
				$t = parent::wordwrapSplit( $token, $length );
				foreach( $t as $_t )
				{
					// for indentation purposes wrap each token individually
					$tokens[] = $buffer . $_t . ( $buffer ? "\033[0m" : '');
				}
				
				$buffer = '';
				continue;
			}
			
			// token is to short for splitting
			$tokens[] = $buffer . $token;
			$buffer = '';
		}
		
		return $tokens;
	}
}

?>