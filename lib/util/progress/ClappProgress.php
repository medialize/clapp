<?php

/**
 * Load TTY interface
 */
require_once( dirname( __FILE__ ) .'/../../tty/ClappTTY.php' );

/**
 * Load ClappProgressFormat Interface
 */
require_once( $_clappLibraryPath .'/util/progress/ClappProgressFormat.php' );

/**
 * Load ClappProgressFormatBar for rendering progress as a bar
 */
require_once( $_clappLibraryPath .'/util/progress/ClappProgressFormatBar.php' );

/**
 * ClappProgress - Facility for visualizing the application's progress 
 *
 * The ClappProgress offers a simple interface for rendering the application's progress on the TTY.
 * @example /examples/example08.php Progress Bar Example
 * @package Clapp
 * @subpackage Clapp-util-progress
 * @author Rodney Rehm
 */
class ClappProgress
{
	/**
	 * The Formatter to be used by this progress controller
	 * @var ClappProgressFormat
	 */
	protected $format = null;
	
	/**
	 * total number of steps (denominator of percentage calculation)
	 * @var int
	 */
	protected $stepsTotal = 0;
	
	/**
	 * number of steps completed (numerator of percentage calculation)
	 * @var int
	 */
	protected $stepsComplete = 0;
	
	/**
	 * percentage completed
	 * @var float
	 */
	protected $percentage = 0;
	
	/**
	 * microtime the execution started
	 * @var flaot
	 */
	protected $started = null;
	
	/**
	 * microtime the execution will probably take (estimated time of arrival)
	 * @var flaot
	 */
	protected $eta = null;
	
	/**
	 * microtime the execution already took (estimated time of progress)
	 * @var flaot
	 */
	protected $etp = null;
	
	/**
	 * name of class to initialize Formatter
	 * @var string
	 */
	protected $formatClass = 'ClappProgressFormatBar';
	
	/**
	 * Initialize a new Progress controller
	 * @param int $steps total number of steps (denominator of percentage calculation)
	 * @param ClappProgressFormat $format Formatter to render progress with, defaults to a new instance of {@link $formatClass} being ClappProgressFormatBar
	 * @uses reset() to set internal counters and values
	 */
	public function __construct( $steps=null, ClappProgressFormat $format=null )
	{
		$this->format = $format ? $format : new $this->formatClass();
		$this->reset( $steps );
	}
	
	/**
	 * reset the current progress states (counters and timers) and redraw the progress
	 * @param int $total total number of steps (denominator of percentage calculation)
	 * @param int $complete number of steps completed (numerator of percentage calculation)
	 * @return ClappProgress $this for chaining
	 * @uses draw() to redraw the progress
	 */
	public function reset( $total=null, $complete=null )
	{
		$this->stepsTotal = $total;
		$this->stepsComplete = $complete;
		$this->started = null; //microtime( true );
		$this->draw();
		return $this;
	}
	
	/**
	 * set the (absolute) progress and redraw the progress
	 * @param float $percentage percentage completed, float between [0,1] inclusively
	 * @return ClappProgress $this for chaining
	 * @uses draw() to redraw the progress
	 */
	public function progress( $percentage )
	{
		// percentage is a float between [0,1] inclusively
		$this->percentage = max( 0, min( 1, $percentage ) );
		$this->draw();
		return $this;
	}
	
	/**
	 * increment the stepsCompleted counter and redraw the progress
	 * @param int $steps number of steps completed (defaults to 1)
	 * @return ClappProgress $this for chaining
	 * @uses draw() to redraw the progress
	 */
	public function proceed( $steps=1 )
	{
		if( !$this->stepsTotal )
			throw new ClappException( 'totalSteps has not been set!' );
		
		$this->stepsComplete += $steps;

		// we might already be at 100%, but someone might keep going...
		$this->stepsComplete = min( $this->stepsTotal, $this->stepsComplete );
		$this->percentage = max( $this->stepsComplete / $this->stepsTotal, 0);

		$this->draw();
		return $this;
	}
	
	/**
	 * redraw the progress 
	 * based on the current internal values of {@link $stepsComplete}, {@link $stepsTotal}, {@link $percentage}.
	 * @return ClappProgress $this for chaining
	 */
	public function draw()
	{
		$now = microtime( true );
		if( !$this->started )
		{
			$this->started = $now;
		}
		else if( $this->percentage )
		{
			$this->etp = $now - $this->started;
			$_percentage = $this->percentage * 100;
			$_timePerPoint = $this->etp / $_percentage;
			$this->eta = $_timePerPoint * ( 100 - $_percentage );
		}
	
		$this->format->draw( $this->percentage, $this->eta, $this->etp, $this->stepsComplete, $this->stepsTotal );
		return $this;
	}
}

?>