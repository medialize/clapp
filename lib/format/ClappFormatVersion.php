<?php

/**
 * Version Screen Formatter (--version)
 *
 * @see http://www.gnu.org/prep/standards/html_node/_002d_002dversion.html#g_t_002d_002dversion
 * @package Clapp
 * @subpackage Clapp-format
 * @author Rodney Rehm
 */
class ClappFormatVersion extends ClappFormat
{
	/**
	 * render the --help screen
	 *
	 * @param int $width width of output, defaults to {@link ClappFormat::$width}
	 * @return ClappFormatHelp $this for chaining
	 */
	public function render( $width=null )
	{
		$width = $width !== null ? $width : $this->width;

		$this->renderApplication( $width )
			->renderCopyright( $width )
			->renderLicense( $width )
			->renderAuthors( $width )
			->renderComments( $width );
		
		return $this;
	}

	/**
	 * render application and version part
	 *
	 * @param int $width width of output
	 * @return ClappFormat $this for chaining
	 */
	protected function renderApplication( $width )
	{
		echo $this->app->name;
		if( $v = $this->app->version )
	 		echo ' version ', $v;
		
		echo "\n";
		
		foreach( $this->app->versions as $v )
			echo ' with ', $v, "\n";
		
		return $this;
	}

	/**
	 * render copyright part
	 *
	 * @param int $width width of output
	 * @return ClappFormat $this for chaining
	 */
	protected function renderCopyright( $width )
	{
		if( $c = $this->app->copyright )
		{
			echo $c, "\n";

			foreach( $this->app->copyrights as $c )
				echo ' with ', $this->wrapIndent( $c, $width, 6 ), "\n";
		}

		return $this;
	}
	
	/**
	 * render authors part
	 *
	 * @param int $width width of output
	 * @return ClappFormat $this for chaining
	 */
	protected function renderAuthors( $width )
	{
		if( $authors = $this->app->authors )
		{
			echo "AUTHORS:\n";
			$a = join( ', ', $authors );
			echo ' ', $this->wrapIndent( $a, $width, 1 );
		}

		return $this;
	}
	
	/**
	 * render license part
	 *
	 * @param int $width width of output
	 * @return ClappFormat $this for chaining
	 */
	protected function renderLicense( $width )
	{
		if( $l = $this->app->license )
			echo $this->wrapIndent( $l, $width ), "\n";

		return $this;
	}
	
	/**
	 * render comments part
	 *
	 * @param int $width width of output
	 * @return ClappFormat $this for chaining
	 */
	protected function renderComments( $width )
	{
		if( $c = $this->app->comments )
		{
			echo "\n";
			foreach( $c as $l )
				echo $this->wrapIndent( $l, $width ), "\n";
		}

		return $this;
	}
}



?>