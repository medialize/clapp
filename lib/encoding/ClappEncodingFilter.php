<?php

// register StreamFilter
stream_filter_register( "Clapp.*", "ClappEncodingFilter" );

/**
 * Wrapper for ClappEncoding to register on streams.
 *
 * @warning If filters are applied to a FileDescriptor they need to be removed *before* the FD is closed!
 * If filters are not removed before fclose() is called, a SegmentationFault may be expected.
 * registering a shutdown_function to remove filters has no effect, since STDIN/STDOUT/STDERR are all closed
 * by PHP *before* user-defined shutdown_functions are executed.
 * @see http://php.net/stream_filter_register
 * @package Clapp
 * @subpackage Clapp-encoding
 * @author Rodney Rehm
 */
class ClappEncodingFilter extends php_user_filter
{
	/**
	 * Flag denoting the charset conversion should be internal to external
	 */
	const ENCODE = 1;
	
	/**
	 * Flag denoting the charset conversion should be external to internal
	 */
	const DECODE = 2;
	
	/**
	 * mode of charset conversion
	 * @var int
	 */
	protected $mode = null;

	/**
	 * filter stream data
	 * @param resource $in input bucket brigade
	 * @param resource $out output bucket brigade
	 * @param int $consumed number of consumed bytes
	 * @param string $closing true to tell the filter to close
	 * @return int filter status
	 */
	function filter( $in, $out, &$consumed, $closing )
	{
		while( $bucket = stream_bucket_make_writeable( $in ) )
		{
			switch( $this->mode )
			{
				case self::DECODE:
					$bucket->data = ClappEncoding::decode( $bucket->data );
				break;
				
				case self::ENCODE:
					$bucket->data = ClappEncoding::encode( $bucket->data );				
				break;
			}

			$consumed += $bucket->datalen;
			stream_bucket_append( $out, $bucket );
		}
		
		return PSFS_PASS_ON;
	}

	/**
	 * initialize filter
	 * @return boolean true if filter could be created, false else
	 */
	function onCreate()
	{
		// allocate resources
		switch( $this->filtername )
		{
			case 'Clapp.decode':
				$this->mode = self::DECODE;
				return true;			
			break;
			
			case 'Clapp.encode':
				$this->mode = self::ENCODE;
				return true;			
			break;
			
			default:
				return false;
			break;
		}
	}
	
	/**
	 * destruct filter
	 * @return void
	 */
	function onClose()
	{
		// clean up
	}
}


?>