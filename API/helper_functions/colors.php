<?php
// from http://www.if-not-true-then-false.com/2010/php-class-for-coloring-php-command-line-cli-scripts-output-php-output-colorizing-using-bash-shell-colors/

class Colors {
	private $foreground_colors = array();
	private $background_colors = array();

	public function __construct() {
		// Set up shell colors
		$this->foreground_colors[shell]['black'] = '0;30';
		$this->foreground_colors[shell]['dark_gray'] = '1;30';
		$this->foreground_colors[shell]['blue'] = '0;34';
		$this->foreground_colors[shell]['light_blue'] = '1;34';
		$this->foreground_colors[shell]['green'] = '0;32';
		$this->foreground_colors[shell]['light_green'] = '1;32';
		$this->foreground_colors[shell]['cyan'] = '0;36';
		$this->foreground_colors[shell]['light_cyan'] = '1;36';
		$this->foreground_colors[shell]['red'] = '0;31';
		$this->foreground_colors[shell]['light_red'] = '1;31';
		$this->foreground_colors[shell]['purple'] = '0;35';
		$this->foreground_colors[shell]['light_purple'] = '1;35';
		$this->foreground_colors[shell]['brown'] = '0;33';
		$this->foreground_colors[shell]['yellow'] = '1;33';
		$this->foreground_colors[shell]['light_gray'] = '0;37';
		$this->foreground_colors[shell]['white'] = '1;37';
		
		$this->foreground_colors[shell]['bold'] = '1;1';
		$this->foreground_colors[shell]['underline'] = '1;4';
		$this->foreground_colors[shell]['blink'] = '5;4';

		$this->background_colors[shell]['black'] = '40';
		$this->background_colors[shell]['red'] = '41';
		$this->background_colors[shell]['green'] = '42';
		$this->background_colors[shell]['yellow'] = '43';
		$this->background_colors[shell]['blue'] = '44';
		$this->background_colors[shell]['magenta'] = '45';
		$this->background_colors[shell]['cyan'] = '46';
		$this->background_colors[shell]['light_gray'] = '47';
		
		// Set up html colors (will be within a style= element of a span)
		$this->foreground_colors[html]['black'] = 'color:black;';
		$this->foreground_colors[html]['dark_gray'] = 'color:dark_gray;';
		$this->foreground_colors[html]['blue'] = 'color:blue;';
		$this->foreground_colors[html]['light_blue'] = 'color:light_blue;';
		$this->foreground_colors[html]['green'] = 'color:green;';
		$this->foreground_colors[html]['light_green'] = 'color:light_green;';
		$this->foreground_colors[html]['cyan'] = 'color:cyan;';
		$this->foreground_colors[html]['light_cyan'] = 'color:light_cyan;';
		$this->foreground_colors[html]['red'] = 'color:red;';
		$this->foreground_colors[html]['light_red'] = 'color:light_red;';
		$this->foreground_colors[html]['purple'] = 'color:purple;';
		$this->foreground_colors[html]['light_purple'] = 'color:light_purple;';
		$this->foreground_colors[html]['brown'] = 'color:brown;';
		$this->foreground_colors[html]['yellow'] = 'color:yellow;';
		$this->foreground_colors[html]['light_gray'] = 'color:light_gray;';
		$this->foreground_colors[html]['white'] = 'color:white;';
		
		$this->foreground_colors[html]['bold'] = 'font-weight:bold;';
		$this->foreground_colors[html]['underline'] = 'text-decoration:underline;';
		$this->foreground_colors[html]['blink'] = 'text-decoration:blink;';

		$this->background_colors[html]['black'] = 'background-color:black;';
		$this->background_colors[html]['red'] = 'background-color:red;';
		$this->background_colors[html]['green'] = 'background-color:green;';
		$this->background_colors[html]['yellow'] = 'background-color:yellow;';
		$this->background_colors[html]['blue'] = 'background-color:blue;';
		$this->background_colors[html]['magenta'] = 'background-color:magenta;';
		$this->background_colors[html]['cyan'] = 'background-color:cyan;';
		$this->background_colors[html]['light_gray'] = 'background-color:light_gray;';
	}

	// Returns colored string
	public function getColoredString($string, $foreground_color = null, $background_color = null) {
		$colored_string = "";
		
		$mode = "shell";
		if($this->is_server()){$mode = "html";}
		
		// begin
		if ($mode == "shell")
		{
			$colored_string .= "\033[";
		}
		if ($mode == "html")
		{$colored_string .= "<span style=\"";}

		// Check if given foreground color found
		if (isset($this->foreground_colors[$mode][$foreground_color])) {
			$colored_string .= $this->foreground_colors[$mode][$foreground_color];
		}
		// Check if given background color found
		if (isset($this->background_colors[$mode][$background_color])) {
			$colored_string .= $this->background_colors[$mode][$background_color];
		}
		
		// end
		if ($mode == "shell")
		{
			$colored_string .=  "m".$string."\033[0m";
		}
		if ($mode == "html")
		{
			$colored_string .=  "\">".$string."</span>";
		}		

		return $colored_string;
	}

	// Returns all foreground color names
	public function getForegroundColors() {
		return array_keys($this->foreground_colors);
	}

	// Returns all background color names
	public function getBackgroundColors() {
		return array_keys($this->background_colors);
	}
	
	function is_server()
	{
		if (isset($_SERVER['SERVER_ADDR'])) 
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}
 
?>