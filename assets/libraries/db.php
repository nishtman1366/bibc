<?php

if (!isset($_SERVER["HTTP_HOST"])) {

	$_SERVER["HTTP_HOST"] = "k68.ir";
}

if($_SERVER["HTTP_HOST"] == "localhost")
{
	define( 'TSITE_SERVER','localhost');
	define( 'TSITE_DB','bibc');
	define( 'TSITE_USERNAME','root');
	define( 'TSITE_PASS','Nil00f@r1869');
}
else
{
	define( 'TSITE_SERVER','localhost');
	define( 'TSITE_DB','bibc');
	define( 'TSITE_USERNAME','root');
	define( 'TSITE_PASS','Nil00f@r1869');
}

?>
