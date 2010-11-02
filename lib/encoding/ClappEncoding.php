<?php

if( !isset( $_clappLibraryPath ) )
	$_clappLibraryPath = dirname(__FILE__) .'/..';

/**
 * Load StreamFilter for Encoding
 */
require_once( $_clappLibraryPath .'/encoding/ClappEncodingFilter.php' );

/**
 * Character Encoding Controller
 * 
 * This facility lets you convert strings from one encoding to another. 
 * Internal and external charsets can be detected an managed for convinient access.
 * @package Clapp
 * @subpackage Clapp-encoding
 * @author Rodney Rehm
 */
class ClappEncoding
{
	/**
	 * internal charset 
	 * @var string
	 */
	protected static $_internal = null;
	
	/**
	 * external charset 
	 * @var string
	 */
	protected static $_external = null;
	
	/**
	 * attached StreamFilters 
	 * @var array
	 */
	protected static $_filters = array();
	
	/**
	 * transcode string from internal to external charset 
	 * @param string $string string to encode
	 * @return string encoded string
	 * @uses convert() for actual transcoding
	 * @uses external() to determine external charset
	 * @uses internal() to determine internal charset
	 */
	public static function encode( $string )
	{
		return self::convert( $string, self::external(), self::internal() );
	}
	
	/**
	 * transcode string from external to internal charset 
	 * @param string $string string to decode
	 * @return string decoded string
	 * @uses convert() for actual transcoding
	 * @uses external() to determine external charset
	 * @uses internal() to determine internal charset
	 */
	public static function decode( $string )
	{
		return self::convert( $string, self::internal(), ClappTTY::encoding() );
	}
	
	/**
	 * convert string from one charset to another
	 *
	 * @note If either charset is not specified the original string is returned without conversion.
	 * @note If $to and $from are the same charset the original string is returned without conversion.
	 * @param string $string string to convert
	 * @param string $to charset to convert to
	 * @param string $from charset to convert from, defaults to {@link internal()} if not specified
	 * @return string converted string
	 * @uses internal() to determine internal charset
	 * @uses mb_convert_encoding() to convert if MBString is available
	 * @uses iconv() to convert if iconv is available
	 */
	public static function convert( $string, $to, $from=null )
	{
		$from = $from ? $from : self::internal();
		
		// in/out use the same encoding
		if( $from == $to )
			return $string;

		// at least one encoding is unknown		
		if( !$from || !$to )
			return $string;
		
		// php.net/mbstring
		if( function_exists( 'mb_convert_encoding' ) )
			return mb_convert_encoding( $string, $to, $from );
		
		// php.net/iconv
		else if( function_exists( 'iconv' ) )
			return iconv( $from, $to, $string );
		
		return $string;
	}
	
	/**
	 * get the length of a string
	 * @param string $string strint to count characters from
	 * @return int number of characters in $string
	 * @uses mb_strlen() to convert if MBString is available
	 * @uses iconv_strlen() to convert if iconv is available
	 */
	public static function strlen( $string )
	{
		if( function_exists( 'mb_strlen' ) )
			return mb_strlen( $string, self::internal() );
			
		if( function_exists( 'iconv_strlen' ) )
			return iconv_strlen( $string, self::internal() );
		
		return strlen( $string );
	}
	
	/**
	 * get or set external encoding
	 *
	 * If $encoding was specified, it is set as the external encoding used throughout the application's charset conversions.
	 * If $encoding was not specified, the currently set external encoding will be returned. 
	 * If it wasn't set yet, it will be detected via {@link ClippTTY::encoding()}.
	 * @param string $encoding external encoding to set
	 * @return string|boolean external encoding, true if $encoding was specified
	 * @uses ClappTTY::encoding() to determine TTY's encoding
	 */
	public static function external( $encoding=null )
	{
		if( $encoding !== null )
		{
			self::$_external = $encoding;
			return true;
		}
		
		if( self::$_external === null )
		{
			self::$_external = ClappTTY::encoding();
		}
			
		
		return self::$_external;
	}
	
	/**
	 * get or set internal encoding
	 *
	 * If $encoding was specified, it is set as the internal encoding used throughout the application's charset conversions.
	 * If $encoding was not specified, the currently set internal encoding will be returned. 
	 * If it wasn't set yet, it will be detected via {@link mb_internal_encoding()} or {@link iconv_get_encoding()}.
	 * If the internal encoding could not be determined, it defaults to UTF-8.
	 * @param string $encoding internal encoding to set
	 * @return string|boolean internal encoding, true if $encoding was specified and could be set via {@link mb_internal_encoding()} or {@link iconv_get_encoding()}.
	 * @uses mb_internal_encoding() to set internal encoding if MBString is available
	 * @uses iconv_set_encoding() to set internal encoding if iconv is available
	 * @uses mb_internal_encoding() to get internal encoding if MBString is available
	 * @uses iconv_get_encoding() to get internal encoding if iconv is available
	 */
	public static function internal( $encoding=null )
	{
		if( $encoding !== null )
		{
			self::$_internal = null;
			
			if( function_exists( 'mb_internal_encoding' ) )
				return mb_internal_encoding( $encoding );
			
			else if( function_exists( 'iconv_set_encoding' ) )
				return iconv_set_encoding( 'internal_encoding', $encoding );
			
			return null;
		}
		
		if( self::$_internal !== null )
			return self::$_internal;
		
		else if( function_exists( 'mb_internal_encoding' ) )
			return self::$_internal = mb_internal_encoding();
		
		else if( function_exists( 'iconv_get_encoding' ) )
			return self::$_internal = iconv_get_encoding( 'internal_encoding' );
		
		return self::$_internal = 'UTF-8';
	}
	
	/**
	 * attach StreamFilters for decoding on reading and encoding on writing to a FilePointer
	 *
	 * @warning If filters are applied to a FileDescriptor they need to be removed *before* the FD is closed!
	 * If filters are not removed before fclose() is called, a SegmentationFaults may be expected.
	 * registering a shutdown_function to remove filters has no effect, since STDIN/STDOUT/STDERR are all closed
	 * by PHP *before* user-defined shutdown_functions are executed.
	 * @param resource $fp FilePointer to attach StreamFilters to
	 * @return void
	 */
	public static function filter( $fp )
	{
		self::$_filters[ $fp + 0 ] = array(
			'Clapp.encode' => stream_filter_append( $fp, "Clapp.encode", STREAM_FILTER_WRITE ),
			'Clapp.decode' => stream_filter_append( $fp, "Clapp.decode", STREAM_FILTER_READ ),
		);
 	}

	/**
	 * detach StreamFilters from FilePointer
	 * @param resource $fp FilePointer to detach StreamFilters from
	 * @return void
	 */
	public static function unfilter( $fp=null )
	{
		// remove filters on specified FilePointer
		if( $fp !== null )
		{
			$fp += 0;
			if( empty( self::$_filters[ $fp ] ) )
				return;
			
			foreach( self::$_filters[ $fp ] as $filter )
				stream_filter_remove( $filter );
				
			return;
		}
		
		// remove all filters
		foreach( self::$_filters as $fp )
			foreach( $fp as $filter )
				stream_filter_remove( $filter );
	}

	/**
	 * output buffer handler
	 * 
	 * Ouput all data received from OB handler to STDOUT 
	 * (to make use of the possibly attached StreamFilters for encoding).
	 * Detaches all StreamFilters from all resources when PHP_OUTPUT_HANDLER_END is specified.
	 * @param string $string chunk sent from PHP's OB handler
	 * @param int $flags type of call (START|CONT|END)
	 * @return string always empty-string
	 */
	public static function outputBuffer( $string, $flags )
	{
		/*
			if( $flags & PHP_OUTPUT_HANDLER_START )
				$flags_sent[] = "PHP_OUTPUT_HANDLER_START";

			if( $flags & PHP_OUTPUT_HANDLER_CONT )
				$flags_sent[] = "PHP_OUTPUT_HANDLER_CONT";

			if( $flags & PHP_OUTPUT_HANDLER_END )
				$flags_sent[] = "PHP_OUTPUT_HANDLER_END";
		*/
		
		// send data over the possibly filtered STDOUT FilePointer
		fwrite( STDOUT, $string );
		
		// detach Filters to avoid Segfaults
		if( $flags & PHP_OUTPUT_HANDLER_END )
			self::unfilter();
		
		// empty the buffer so PHP won't send data twice
		return '';
	}
	
	/**
	 * attach encoding StreamFilters to STDIN and STDOUT, register outputBuffer().
	 * @return void
	 */
	public static function attach()
	{
		// register transcoding filter for in/out
		self::filter( STDIN );
		self::filter( STDOUT );

		// register outputBuffer re-routing
		ob_implicit_flush( true );
		ob_start( array( 'ClappEncoding', 'outputBuffer' ), 2 );
	}
	
	/**
	 * detach encoding StreamFilters from STDIN and STDOUT, ending outputBuffer()
	 * @return void
	 */
	public static function detach()
	{
		// unregister filters is done by the outputBuffer handler
		// self::unfilter();
		// stop OutputBuffering (but flush whatever's left)
		ob_end_flush();
	}
}



?>