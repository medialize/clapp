<?php

/**
 * Controller Class for Cursor Movement
 *
 * This class is a do-nothing wrapper which should silently be used when STDOUT is not a TTY.
 * @package Clapp
 * @subpackage Clapp-tty-control
 * @author Rodney Rehm
 */
class ClappTTYControl
{
	/**
	 * Move cursor up one cell
	 * @param int $offset number of cells to move
	 * @return ClappTTYControl $this for chaining
	 */
	public function up( $offset=1 )
	{
		return $this;
	}
	
	/**
	 * Move cursor down one cell
	 * @param int $offset number of cells to move
	 * @return ClappTTYControl $this for chaining
	 */
	public function down( $offset=1 )
	{
		return $this;
	}
	
	/**
	 * Move cursor right one cell
	 * @param int $offset number of cells to move
	 * @return ClappTTYControl $this for chaining
	 */
	public function right( $offset=1 )
	{
		return $this;
	}
	
	/**
	 * Move cursor left cell
	 * @param int $offset number of cells to move
	 * @return ClappTTYControl $this for chaining
	 */
	public function left( $offset=1 )
	{
		return $this;
	}

	/**
	 * move curser by rows
	 * @param int $offset number of rows to move by, will move up if negative
	 * @return ClappTTYControl $this for chaining
	 */
	public function line( $offset=0 )
	{
		if( $offset >= 0 )
			return $this->next( max( 1, $offset ) );
		
		return $this->previous( max( 1, abs($offset) ) );
	}

	/**
	 * Move cursor up one row, setting col=1
	 * @param int $offset number of rows to move
	 * @return ClappTTYControl $this for chaining
	 */
	public function previous( $offset=1 )
	{
		return $this;
	}
	
	/**
	 * Move cursor down one row, setting col=1
	 * @param int $offset number of rows to move
	 * @return ClappTTYControl $this for chaining
	 */
	public function next( $offset=1 )
	{
		return $this;
	}
	
	/**
	 * alias to {$link column()}
	 * @param int $offset number of columns to move
	 * @return ClappTTYControl $this for chaining
	 */
	public function col( $column=1 )
	{
		return $this->column( $column );
	}
	
	/**
	 * alias to {$link position()}
	 * @param int $offset number of rows to move
	 * @return ClappTTYControl $this for chaining
	 */
	public function row( $row=1 )
	{
		return $this->position( $row, 1 );
	}
	
	/**
	 * Move cursor to specified column
	 * @param int $column the column to move to (remember they start at 1)
	 * @return ClappTTYControl $this for chaining
	 */
	public function column( $column=1 )
	{
		return $this;
	}
	
	/**
	 * Move cursor to specified position
	 * @param string $row the row to move to (remember they start at 1)
	 * @param string $column the column to move to (remember they start at 1)
	 * @return ClappTTYControl $this for chaining
	 */
	public function position( $row=1, $column=1 )
	{
		return $this;
	}

	/**
	 * Output blank characters
	 * @param int $blanks number of blanks to print
	 * @return ClappTTYControl $this for chaining
	 */
	public function blanks( $blanks=1 )
	{
		return $this;
	}

	/**
	 * Clear entire Screen
	 * @return ClappTTYControl $this for chaining
	 */
	public function clear()
	{
		return $this;
	}
	
	/**
	 * Clear Screen before cursor position
	 * @note the cursor position does not change
	 * @return ClappTTYControl $this for chaining
	 */
	public function clearToCursor()
	{
		return $this;
	}
	
	/**
	 * Clear Screen after cursor position
	 * @note the cursor position does not change
	 * @return ClappTTYControl $this for chaining
	 */
	public function clearFromCursor()
	{
		return $this;
	}
	
	/**
	 * Clear Line
	 * @note the cursor position does not change
	 * @return ClappTTYControl $this for chaining
	 */
	public function clearLine()
	{
		return $this;
	}
	
	/**
	 * Clear Line before cursor position
	 * @note the cursor position does not change
	 * @return ClappTTYControl $this for chaining
	 */
	public function clearLineToCursor()
	{
		return $this;
	}
	
	/**
	 * Clear Line after cursor position
	 * @note the cursor position does not change
	 * @return ClappTTYControl $this for chaining
	 */	
	public function clearLineFromCursor()
	{
		return $this;
	}
	
	/**
	 * determine current cursor position
	 * @return array array( 'row' => null, 'col' => null )
	 */
	public function getPosition()
	{
		return array( 'row' => null, 'col' => null );
	}
	
	/**
	 * save the cursor position
	 * @return ClappTTYControl $this for chaining
	 */
	public function save()
	{
		return $this;
	}
	
	/**
	 * restore the cursor position
	 * @return ClappTTYControl $this for chaining
	 */
	public function restore()
	{
		return $this;
	}

	/**
	 * hide the cursor
	 * @return ClappTTYControl $this for chaining
	 */
	public function hide()
	{
		return $this;
	}
	
	/**
	 * hide the cursor
	 * @return ClappTTYControl $this for chaining
	 */
	public function show()
	{
		return $this;
	}
}

?>