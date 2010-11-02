<?php

/**
 * Progress Bar Formatter
 *
 * This class will render the progress as a bar in the form of either:
 * <pre>00:00:01  980/1000 [==================================== ]  98.10% ETA 00:00:01</pre>
 * or if no steps were specified:
 * <pre>00:00:01 [============================================== ]  98.10% ETA 00:00:01</pre>
 * @package Clapp
 * @subpackage Clapp-util-progress
 * @author Rodney Rehm
 */
class ClappProgressFormatBar extends ClappProgressFormat
{
	/**
	 * render and draw the progress bar
	 * @param float $percentage percentage as float between [0,1] inclusively
	 * @param float $eta estimated time of arrival, microsconds as float
	 * @param float $etp estimated time of progress, microsconds as float
	 * @param int $stepsComplete number of steps completed
	 * @param int $stepsTotal total number of steps
	 * @return void
	 */
	public function draw( $percentage, $eta, $etp, $stepsComplete, $stepsTotal )
	{
		$head = $this->etp( $etp ) . $this->steps( $stepsComplete, $stepsTotal );
		$tail = $this->percentage( $percentage ) . $this->eta( $eta );
		$width = $this->ttyWidth - $this->ttyFormat->strlen( $head )  - $this->ttyFormat->strlen( $tail ) -1;

		$this->reset();
		echo $head, $this->bar( $width, $percentage ), $tail;
	}
}

?>