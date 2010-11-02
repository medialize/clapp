<?php

/**
 * Progress Format Interface
 *
 * This class defines basic rendering helpers to be used by any extending Formatters
 * @package Clapp
 * @subpackage Clapp-util-progress
 * @author Rodney Rehm
 */
abstract class ClappProgressFormat
{
	/**
	 * TTY Format Wrapper
	 * @var ClappTTYFormat
	 */
	protected $ttyFormat = null;
	
	/**
	 * TTY Control Wrapper
	 * @var ClappTTYControl
	 */
	protected $ttyControl = null;
	
	/**
	 * Width of Screen
	 * @var int
	 */
	protected $ttyWidth = null;

	/**
	 * Initialize a new Formatter
	 * @uses ClappTTY::dimensions() to determine the screens width, defaults to {@link ClappFormat::defaultWidth()}
	 */
	public function __construct()
	{
		$this->ttyFormat = ClappTTY::format();
		$this->ttyControl = ClappTTY::control();

		$d = ClappTTY::dimensions();
		$this->ttyWidth = $d['columns'] ? $d['columns'] : ClappFormat::defaultWidth();
	}
	
	/**
	 * reset whatever the Formatter might've changed to the cursor
	 * @return void
	 */
	public function __destruct()
	{
		// (re-)show cursor after we're done
		$this->ttyControl->show();
	}
	
	/**
	 * format timestamp
	 * @param int|float $ts timestamp in seconds (int) or microseconds (float)
	 * @return string time formatted as "01:23:45" (HH:MM:SS)
	 */
	protected function formatTime( $ts )
	{
		$seconds = intval( $ts );
		return sprintf( '%02d', $seconds / 3600 ) 
			.':'. sprintf( '%02d', ( $seconds % 3600 ) / 60 ) 
			.':'. sprintf( '%02d', ( $seconds % 60 ) );
	}
	
	/**
	 * format percentage
	 * @param float $p percentage as float between [0,1] inclusively
	 * @return string percentage as "09.10%"
	 */
	protected function formatPercentage( $p )
	{
		$t = sprintf( '%0.2f%%', $p * 100 );
		
		if( $p < 0.1 )
			return '  '. $t;
		
		if( $p < 1)
			return ' '. $t;
		
		return $t;
	}
	
	/**
	 * clear the Formatter's output
	 * @return ClappProgressFormat $this for chaining
	 */
	protected function reset()
	{
		$this->ttyControl->clearLine()->column()->hide();
		return $this;
	}	
	
	/**
	 * render and draw the progress
	 * @param float $percentage percentage as float between [0,1] inclusively
	 * @param float $eta estimated time of arrival, microsconds as float
	 * @param float $etp estimated time of progress, microsconds as float
	 * @param int $stepsComplete number of steps completed
	 * @param int $stepsTotal total number of steps
	 * @return void
	 */
	public abstract function draw( $percentage, $eta, $etp, $stepsComplete, $stepsTotal );
	
	/**
	 * render percentage
	 * @param float $p percentage as float between [0,1] inclusively
	 * @return string rendered percentage as " 09.10%"
	 */
	protected function percentage( $percentage )
	{
		return ' '. $this->formatPercentage( $percentage );
	}
	
	/**
	 * render steps completed / total
	 * @param int $c number of steps completed
	 * @param int $t total number of steps
	 * @return string rendered steps as "  123 / 1234"
	 */
	protected function steps( $c, $t )
	{
		if( !$t )
			return '';

		$lt = ClappEncoding::strlen( $t );
		$lc = ClappEncoding::strlen( $c );
		return ' '. str_repeat( ' ', $lt - $lc ) . $c .' / '. $t;
	}
	
	/**
	 * render estimated time of progress
	 * @param float $etp estimated time of progress in microseconds (float)
	 * @return string rendered etp as " 01:23:45"
	 */
	protected function etp( $etp )
	{
		return ' '. $this->formatTime( $etp );
	}
	
	/**
	 * render estimated time of arrival
	 * @param float $eta estimated time of arrival 
	 * @return string rendered eta as " ETA 01:23:45"
	 */
	protected function eta( $eta )
	{
		return ' ETA '. $this->formatTime( $eta );
	}
	
	/**
	 * render the bar
	 * @param int $width number of characters to consume by the bar, (should be >=13)
	 * @param float $percentage percentage as float between [0,1] inclusively
	 * @return string bar as " [========  ]"
	 */
	protected function bar( $width, $percentage )
	{
		$width -= 3; // leading space + brackets
		$bw = intval($width * $percentage);
		$sw = $width - $bw;
		
		return ' ['. str_repeat( '=', $bw ) . str_repeat( ' ', $sw ) .']'; 
	}
}

?>