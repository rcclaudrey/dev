<?php

class Vikont_OEM_Conversion_Abstract
{
	protected static $_table = array();


	public static function table()
	{
		return static::$_table;
	}



	public static function convert($key)
	{
		return isset(static::$_table[$key])
			?	static::$_table[$key]
			:	null;
	}



	public static function check()
	{
		$b = array_keys(static::$_table);
		echo 'Key count: ' . (int) count($b) . '<br/>';

		$c = array_unique($b);
		echo 'Unique count: ' . (int) count($c) . '<br/>';
	}



	public static function clearYear()
	{
		foreach(static::$_table as $k => $v) {
			echo "'" . preg_replace("/\s\([0-9]{4}\)/", "", $k) ."' => '$v',<br/>";
		}
	}

}