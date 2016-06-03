<?php

function normalizeYesNo($value)
{
	if ('no' == strtolower($value)) return false;
	else
		return (bool) (	is_numeric($value)
			?	(int) $value
			:	$value
		);
}