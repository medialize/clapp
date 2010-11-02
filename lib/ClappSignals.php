<?php

/**
 * Clapp Signal Handling
 *
 * This facility offers any application the capability of handling signals as they wish.
 * @example /examples/example04.php Signal Handling
 * @see http://www.gnu.org/software/libc/manual/html_node/Signal-Handling.html#Signal-Handling
 * @see http://www.gnu.org/software/libc/manual/html_node/Termination-Signals.html#Termination-Signals
 * @package Clapp
 * @author Rodney Rehm
 */
class ClappSignals
{
	/**
	 * flag denoting if ClappSignals has already initialized
	 * @var boolean
	 */
	protected static $_initialized = false;

	/**
	 * flag denoting if the application is currently in atomic mode 
	 * @var boolean
	 */
	protected static $atomic = false;
	
	/**
	 * cache for signals caught during atomic mode
	 * @var array()
	 */
	protected static $caught = array();
	
	/**
	 * container for signals and handlers
	 * @var array()
	 */
	protected static $signals = array();
	
	/**
	 * initialize signal handling
	 * @return boolean true if initialized, false else
	 * @uses register_tick_function() for PHP < 5.3
	 */
	public static function init()
	{
		if( self::$_initialized )
			return true;
		
		if( !self::possible() )
			return false;
			
		if( self::needTicks() )
			register_tick_function( array( 'ClappSignals', 'handleTick' ) );

		self::$_initialized = true;
		return true;
	}
	
	/**
	 * listen for a certain signal and register a handler
	 * @param int $signal Signal to listen for ({@see http://de.php.net/manual/en/pcntl.constants.php})
	 * @param mixed $callback function to execute when signal is caught, defaults to {@link handleSignal()}
	 * @return void
	 * @uses pcntl_signal() to register the signal-handler
	 * @uses $signals to remember handlers
	 */
	public static function listen( $signal, $callback=null )
	{
		if( !function_exists( 'pcntl_signal' ) )
			return;
			
		$cb = $callback ? $callback : array( 'ClappSignals', 'handleSignal' );
		self::$signals[ $signal ] = $cb;
		pcntl_signal( $signal, array( 'ClappSignals', 'dispatchSignal' ) );
	}
	
	/**
	 * Default Signal Handler
	 *
	 * printing the caught signal, but not exit()ing afterwards.
	 * This handler is for debugging purposes only.
	 * @param int $signal Signal that was caught
	 * @return void
	 */
	public static function handleSignal( $signal )
	{
		echo 'SIGNAL ', $signal, (self::$atomic ? ' IGNORED' : ' CAUGHT'), "\n";
	}
	
	/**
	 * Dispatch or remember a signal based on current atomic mode
	 * @param int $signal Signal that was caught
	 * @return void
	 */
	public static function dispatchSignal( $signal )
	{
		if( self::$atomic )
		{
			// remember signal for when atomic mode is finished
			self::$caught[] = $signal;
			return;
		}
		
		// run SignalHandler
		if( is_callable( self::$signals[ $signal ] ) )
			call_user_func( self::$signals[ $signal ], $signal );
	}
	
	/**
	 * Exiting Signal Handler
	 * @param string $signal 
	 * @return void
	 */
	public static function exitOnSignal( $signal )
	{
		echo "\nExiting because Signal ", $signal, " received\n";
		exit;
	}
	
	/**
	 * Dummy method to register as tickHandler
	 * @return void
	 */
	public static function handleTick()
	{
		// ignore ticks
	}

	/**
	 * start atomic mode
	 *
	 * While the application is running in atomic mode signals won't be handled. 
	 * Signals caught during atomic mode will be handled at the end of atomic mode
	 * @return void
	 */
	public static function beginAtomic()
	{
		self::$atomic = true;
	}
	
	/**
	 * end atomic mode
	 *
	 * after ending atomic mode all signals that were caught during atomic mode 
	 * are being dispatched (in order they were caught)
	 * @return void
	 */
	public static function endAtomic()
	{
		self::$atomic = false;
		
		// work through signals caught during atomic mode (PHP < 5.3)
		if( self::$caught )
		{
			foreach( self::$caught as $signal )
				if( is_callable( self::$signals[ $signal ] ) )
					call_user_func( self::$signals[ $signal ], $signal );
			
			self::$caught = array();
		}
		
		// consume new signals
		self::consume();
	}
	
	/**
	 * test if signal handling is possible in the current environment
	 * @return boolean true if signal handling is possible, false else
	 */
	public static function possible()
	{
		return function_exists( 'pcntl_signal' );
	}
	
	/**
	 * test if the tickHandler must be registered to enable signal handling
	 *
	 * Since PHP 5.3 pcntl_signal_dispatch() is available to query for signals. 
	 * PHP 5.2 and below requires tickHandlers to do the trick.
	 * @return boolean true if ticks are required to enable signal handling, false else
	 */
	public static function needTicks()
	{
		return function_exists( 'pcntl_signal' ) && !function_exists( 'pcntl_signal_dispatch' );
	}
	
	/**
	 * consume pending signals
	 * @return void
	 */
	public static function consume()
	{
		if( function_exists( 'pcntl_signal_dispatch' ) )
			pcntl_signal_dispatch();
	}
}

// define signals so code won't break when pcntl is not available
if( !defined( 'SIGTERM' ) )
{
	define( 'SIGTERM', null );
	define( 'SIGINT', null );
	define( 'SIGQUIT', null );
	define( 'SIGHUP', null );
	define( 'SIG_DFL', null );
	define( 'SIG_IGN', null );
}

?>