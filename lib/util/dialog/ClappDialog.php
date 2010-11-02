<?php

/**
 * ClappDialog - Facility for interaction with the user
 * 
 * @package Clapp
 * @subpackage Clapp-util-dialog
 * @author Rodney Rehm
 */
class ClappDialog
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
	 * Cursor's Position befor the dialog was shown: array( 'rows' => 123, 'columns' => 123 )
	 * @var array
	 */
	protected $position = null;
	
	/**
	 * Number of lines written to STDOUT
	 * @var int
	 */
	protected $lines = 0;

	/**
	 * Create a new Dialog
	 * @uses ClappTTY::dimensions() to determine the screens width, defaults to {@link ClappFormat::defaultWidth()}
	 */
	public function __construct()
	{
		$this->ttyControl = ClappTTY::control();
		$this->ttyFormat = ClappTTY::format();
		$this->position = $this->ttyControl->getPosition();

		$d = ClappTTY::dimensions();
		$this->ttyWidth = $d['columns'] ? $d['columns'] : ClappFormat::defaultWidth();
	}
	
	/**
	 * write message to STDOUT while remembering the number of lines written
	 * @param string $message 
	 * @return ClappDialog $this for chaining
	 */
	public function out( $message )
	{
		$m = explode( "\n", $message );
		$this->lines += count( $m );
		echo $message;
		return $this;
	}

	/**
	 * reset the TTY to the state before this dialog was shown.
	 *
	 * basically delete the written lines and set the cursor position to what it was before.
	 * @return ClappDialog $this for chaining
	 */
	public function reset()
	{
		if( $this->position['row'] !== null )
		{
			$t = $this->ttyControl->getPosition();
			$x = max( $t['row'] - $this->lines +1, 1 );
			
			
			// empty the lines we just littered with error messages
			for( $row = $x; $row <= $t['row']; $row++ )
				$this->ttyControl->position( $row )->clearLine();

			// re-position to where we were before the error occured
			$this->ttyControl->position( $x, $this->position['col'] );
		}
		
		return $this;
	}
	
	/**
	 * get (and wait for) user's response
	 *
	 * If a list of characters is specified, only these characters will be accepted as input.
	 * If no character limitation was specified any input will be accepted.
	 * @param array $characters list of characters to allow for input, defaults to all characters
	 * @return string the user's response
	 * @author Rodney Rehm
	 */
	public function response( $characters=array() )
	{
		while( true )
		{
			$t = ClappTTYInput::char( true );
			if( $characters && in_array( $t, $characters ) )
				return $t;
		}
	}
}

?>