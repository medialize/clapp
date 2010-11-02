<?php

/**
 * Controller Class for Text Formatting 
 *
 * This class is a do-nothing wrapper which should silently be used when STDOUT is not a TTY.
 * For convinience reasons this class holds the format constants (used by ECMA-48)
 * @package Clapp
 * @subpackage Clapp-tty-format
 * @author Rodney Rehm
 */
class ClappTTYFormat
{
	/**
	 * Formatting Code to Reset all previous Formats
	 */
	const RESET = 0;
	
	/**
	 * Formatting Code to make text bold
	 */
	const BOLD = 1;
	
	/**
	 * Formatting Code to make text show at 50% opacity
	 */
	const HALF_BRIGHT = 2;
	
	/**
	 * Formatting Code to make text show at normal intensity (not bold, not half_bright)
	 */
	const INTENSITY_NORMAL = 22;
	
	/**
	 * Formatting Code to make text italic (ignored on most terminals)
	 */
	const ITALIC = 3;
	
	/**
	 * Formatting Code to end italic text (ignored on most terminals)
	 */
	const ITALIC_OFF = 23;
	
	/**
	 * Formatting Code to make text underlined
	 */
	const UNDERLINE = 4;

	/**
	 * Formatting Code to end underlined text
	 */
	const UNDERLINE_OFF = 24;
	
	/**
	 * Formatting Code to make text blink
	 */
	const BLINK = 5;
	
	/**
	 * Formatting Code to make text blink fast (ignored on most terminals)
	 */
	const BLINK_FAST = 6;
	
	/**
	 * Formatting Code to end blinking text
	 */
	const BLINK_OFF = 25;
	
	/**
	 * Formattic Code to invert Background and Foreground colors
	 */
	const INVERT = 7;
	
	/**
	 * Formattic Code to end inverted Background and Foreground colors
	 */
	const INVERT_OFF = 27;
		
	/**
	 * Formattic Code to hide text
	 */
	const CONCEAL = 8;
	
	/**
	 * Formatting Code to make text crossed out (ignored on most terminals)
	 */
	const CROSS = 9;
	
	/**
	 * Formatting Code to make end crossed out text (ignored on most terminals)
	 */
	const CROSS_OFF = 29;
	
	/**
	 * Formatting Code to use terminal font 1 (ignored if unavailable)
	 */
	const FONT1 = 10;
	
	/**
	 * Formatting Code to use terminal font 2 (ignored if unavailable)
	 */
	const FONT2 = 11;
	
	/**
	 * Formatting Code to use terminal font 3 (ignored if unavailable)
	 */
	const FONT3	 = 12;
	
	/**
	 * Formatting Code to make text black-letter (ignored on most terminals)
	 */
	const FRAKTUR = 20;
	
	/**
	 * Formatting Code to make text double underlined (ignored on most terminals)
	 */
	const UNDERLINE_DOUBLE = 21;

	/**
	 * Formatting Code to make text framed (ignored on most terminals)
	 */
	const FRAME = 51;
	
	/**
	 * Formatting Code to end framed text (ignored on most terminals)
	 */
	const FRAME_OFF = 54;
	
	/**
	 * Formatting Code to make text circled (ignored on most terminals)
	 */
	const CIRCLE = 52;
	
	/**
	 * Formatting Code to end circled text (ignored on most terminals)
	 */
	const CIRCLE_OFF = 54;
	
	/**
	 * Formatting Code to make text overlined (ignored on most terminals)
	 */
	const OVERLINE = 53;
	
	/**
	 * Formatting Code to end overlined text (ignored on most terminals)
	 */
	const OVERLINE_OFF = 55;
	
	/**
	 * Foreground Color Black
	 */
	const FG_BLACK = 30;
	
	/**
	 * Foreground Color Red
	 */
	const FG_RED = 31;
	
	/**
	 * Foreground Color Green
	 */
	const FG_GREEN = 32;
	
	/**
	 * Foreground Color Brown
	 */
	const FG_BROWN = 33;
	
	/**
	 * Foreground Color Blue
	 */
	const FG_BLUE = 34;
	
	/**
	 * Foreground Color Magenta
	 */
	const FG_MAGENTA = 35;
	
	/**
	 * Foreground Color Cyan
	 */
	const FG_CYAN = 36;
	
	/**
	 * Foreground Color White
	 */
	const FG_WHITE = 37;
	
	/**
	 * Foreground Color Default
	 */
	const FG_DEFAULT = 39;
	
	/**
	 * Background Color Black
	 */
	const BG_BLACK = 40;
	
	/**
	 * Background Color Red
	 */
	const BG_RED = 41;
	
	/**
	 * Background Color Green
	 */
	const BG_GREEN = 42;
	
	/**
	 * Background Color Brown
	 */
	const BG_BROWN = 43;
	
	/**
	 * Background Color Blue
	 */
	const BG_BLUE = 44;
	
	/**
	 * Background Color Magenta
	 */
	const BG_MAGENTA = 45;
	
	/**
	 * Background Color Cyan
	 */
	const BG_CYAN = 46;
	
	/**
	 * Background Color White
	 */
	const BG_WHITE = 47;
	
	/**
	 * Background Color Default
	 */
	const BG_DEFAULT = 49;
	
	/**
	 * Format a string with SGR Escape sequences
	 *
	 * The class constants of {@link ClappTTYFormat} translate the format-codes to readable names.
	 * @param int|array $formats FormatID or array of FormatIDs see constants of {@link ClappTTYFormat}
	 * @param boolean $reset append a format-reset to the string if true
	 * @return string formatted string
	 * @uses format() to do the actual formatting
	 */
	public function __invoke( $string, $formats=null, $reset=true )
	{
		return $this->format( $string, $formats, $reset );
	}
	
	/**
	 * Format a string with SGR Escape sequences
	 *
	 * The class constants of {@link ClappTTYFormat} translate the format-codes to readable names.
	 * @param string $string the string to wrap (or prepend) with format
	 * @param int|array $formats FormatID or array of FormatIDs see constants of {@link ClappTTYFormat}
	 * @param boolean $reset append a format-reset to the string if true
	 * @return string formatted string
	 */
	public function format( $string, $formats=null, $reset=true )
	{
		return $string;
	}
	
	/**
	 * remove formats from string
	 * @param string $string text to remove format codes from
	 * @return string format free text
	 */
	public function strip( $string )
	{
		return $string;
	}
	
	/**
	 * split a very long word into tokens (while ignoring and preserving format codes)
	 * @param string $string word to split
	 * @param int $length maximum word length
	 * @return array list of tokens
	 */
	protected function wordwrapSplit( $string, $length )
	{
		// hard split after $length of characters
		return preg_split( '#(.{'. $length .'})#u', $string, -1, PREG_SPLIT_NO_EMPTY + PREG_SPLIT_DELIM_CAPTURE );
	}
	
	/**
	 * Wraps a string to a given number of characters
	 * @see http://php.net/manual/en/function.wordwrap.php#94452 for inspiration
	 * @see http://php.net/manual/en/function.wordwrap.php for similarity
	 * @param string $str the string to wrap
	 * @param int $width the width of the output
	 * @param string $break the character used to break the line
	 * @param boolean $cut ignored parameter, just for the sake of 
	 * @return string wrapped string
	 */
	public function wordwrap( $str, $width=75, $break="\n", $cut=false )
	{
		$tokens = preg_split('#([\x20\r\n\t]++|\xc2\xa0)#sSX', $str, -1, PREG_SPLIT_NO_EMPTY);
		$length = 0;
		$t = array();
		
		foreach( $tokens as $_token )
		{
			$token_length = $this->strlen( $_token );
			$_tokens = $token_length > $width ? $this->wordwrapSplit( $_token, $width ) : array( $_token );

			foreach( $_tokens as $token )	
			{
				$token_length = $this->strlen( $token );
				$length += $token_length;
			
				if( $length >= $width )
				{
					$t[] = $break;
					$length = $token_length;
				}
				else
				{
					$t[] = ' ';
					$length++;
				}
			
				$t[] = $token;
			}
		}
		
		// re-inject spaces lost by preg_split
		return join( '', $t );
	}
	
	/**
	 * get the string's length (without counting formatting code)
	 * @param string $string string to determine length of
	 * @return int length of string without formatting code
	 * @uses CLappEncoding::strlen() to determine length
	 */
	public function strlen( $string )
	{
		return ClappEncoding::strlen( $string );
	}
	
	/**
	 * blow up string to a certain length by appending a number of $char to the string
	 * @param string $string string to padd
	 * @param int $width number of characters the string should be long
	 * @param string $char character to append to the end of the string to fit the desired length
	 * @return string right-padded string
	 */
	public function rightPadd( $string, $width, $char=' ' )
	{
		if( ( $right = $width - $this->strlen( $string ) ) > 0 )
			return $string . str_repeat( $char, $right );
		
		return $string;
	}
}

?>