<?php

// register ClappDialogError as error_handler
set_error_handler( array( 'ClappDialogError', 'handler' ) );

/**
 * ClappDialog based Error handler
 *
 * @package Clapp
 * @subpackage Clapp-util-dialog
 * @author Rodney Rehm
 */
class ClappDialogError extends ClappDialog
{
	/**
	 * Error number (type)
	 * @var int
	 */
	protected $_no = null;
	
	/**
	 * Error message
	 * @var string
	 */
	protected $_str = null;
	
	/**
	 * File the error occured in
	 * @var string
	 */
	protected $_file = null;
	
	/**
	 * Line number the error occured on 
	 * @var int
	 */
	protected $_line = null;
	
	/**
	 * available variables in the context the error happened in.
	 * <strong>DO NOT MODIFY!</strong>
	 * @var array
	 */
	protected $_context = null;
	
	/**
	 * Create a new Error Dialog
	 * @param int $errno Error number
	 * @param string $errstr Error message
	 * @param string $errfile File the error occured in
	 * @param int $errline Line number the error occured on 
	 * @param array $errcontext available variables in the context the error happened in
	 */
	public function __construct( $errno, $errstr, $errfile, $errline, $errcontext )
	{
		parent::__construct();
		
		$this->_no = $errno;
		$this->_str = $errstr;
		$this->_file = $errfile;
		$this->_line = $errline;
		$this->_context = $errcontext;
	}
	
	/**
	 * get marked up error message
	 *
	 * @uses ClappTTYFormat::format() to format message
	 * @uses ClappTTYFormat::wordwrap() to fit message to screen
	 * @return string formatted error message
	 */
	public function getMessage()
	{
		$t = array( "\n" );
		switch( $this->_no )
		{
			case E_USER_ERROR:
			case E_ERROR:
				$t[] = $this->ttyFormat->format( 'ERROR:', array( ClappTTYFormat::BOLD, ClappTTYFormat::FG_RED ) );
			break;

			case E_USER_WARNING:
			case E_WARNING:
				$t[] = $this->ttyFormat->format( 'WARNING:', array( ClappTTYFormat::BOLD, ClappTTYFormat::FG_MAGENTA ) );
			break;
			case E_USER_NOTICE:
			case E_NOTICE:
				$t[] = $this->ttyFormat->format( 'NOTICE:', array( ClappTTYFormat::BOLD, ClappTTYFormat::FG_BLUE ) );
			break;
		}
		
		$t[] = ' ';
		$t[] = $this->ttyFormat->wordwrap( $this->_str, $this->ttyWidth - 10);
		$t[] = "\n    in ";
		$t[] = $this->ttyFormat->wordwrap( $this->_file, $this->ttyWidth - 10 );
		$t[] = ' on line ';
		$t[] = $this->_line;
		$t[] = "\n";

		return join( '', $t );
	}
	
	/**
	 * display the error message and wait for user response.
	 * 
	 * depending on user input exit(1) or remove message from screen.
	 * @return void
	 */
	public function display()
	{
		$m = $this->getMessage();
		$this->out( $m );
		$this->out( "\n". $this->ttyFormat->format( "(c)ontinue or (a)bort?", ClappTTYFormat::BLINK ) );

		$c = $this->response( array( 'a', 'c', ' ', "\n", "\033" ) );
		if( in_array( $c, array( 'a', "\033", "\n" ) ) )
		{
			$this->ttyControl->clearLine()->column();
			echo "aborted...\n";
			exit(1);
		}

		$this->ttyControl->clearLine()->column();
		$this->reset();
	}
	
	/**
	 * Error Handler dispatcher for use with {@link set_error_handler()}
	 *
	 * {@see http://php.net/set_error_handler}
	 * @param int $errno Error number
	 * @param string $errstr Error message
	 * @param string $errfile File the error occured in
	 * @param int $errline Line number the error occured on 
	 * @param array $errcontext available variables in the context the error happened in
	 * @return boolean true if error was handled, false if PHP's error_handler should engage
	 */
	public static function handler( $errno, $errstr, $errfile, $errline, $errcontext )
	{
		// abort if error-level tells us to
		if( !(error_reporting() & $errno) )
			return true;
			
		$dialog = new ClappDialogError( $errno, $errstr, $errfile, $errline, $errcontext );
		switch( $errno )
		{
			case E_USER_ERROR:
			case E_ERROR:
				echo $dialog->getMessage();
				exit(1);
			break;

			case E_USER_WARNING:
			case E_WARNING:
			case E_USER_NOTICE:
			case E_NOTICE:
				// handled by ClappDialogError
			break;

			case 0:
				// ignore @function() errors
				return true;
			break;

			default:
				// let the default error_handler do the rest
				return false;
			break;
		}
		
		$dialog->display();
		return true;
	}
}


?>