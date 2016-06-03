<?php

function __()
{
	$args = func_get_args();
	$text = array_shift($args);
	return htmlspecialchars(@vsprintf($text, $args));
}

?>