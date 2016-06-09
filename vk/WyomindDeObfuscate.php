<?php

include '../Vic.php';


function formatCode($code)
{
	$code = str_replace(';', ";\n", $code);
	$code = str_replace('{', "{\n", $code);
	$code = str_replace('}', "}\n", $code);
	return nl2br(htmlentities($code));
}


$filename = @$_GET['file'];
if(!$filename): ?>
	
<form action="" method="GET">
	<label for="filename">File to deobfuscate:</label>
	<input id="filename" name='file' value="<?php echo dirname(getcwd()) ?>/app/code/local/Wyomind/" style="width:100%;" />
	<button type="submit">Go!</button>
</form>
<?php
	die;
endif;

$sourceText = file_get_contents($filename);
$text = $sourceText;

//$text = preg_replace("#(\\\x[0-9A-Fa-f]{2})#e", "chr(hexdec('\\1'))", $text);

for($i=255; $i>=32; $i--) {
	$text = str_replace('\\x'.(string)dechex($i), chr($i), $text);
	$text = str_replace('\\'.(string)decoct($i), chr($i), $text);
}

?>

<style>
	#src {
		float: left;
		width: 50%;
		/*padding: 20px;*/
	}
	#dest {
		float: right;
		width: 50%;
		/*padding: 20px;*/
	}
</style>
<div id="src"><?php echo formatCode($sourceText) ?></div>
<div id="dest"><?php echo formatCode($text) ?></div>
