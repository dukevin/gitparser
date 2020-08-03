<?php
$version = $_REQUEST['num'];
$url = urldecode($_REQUEST['url']);
if(empty($version))
	die("Error: version was empty");
$csv = file_get_contents($url) or die("Unavailable: $url");
$lines = explode(PHP_EOL, $csv);
foreach($lines as $line)
{
	if(strpos($line,$version) !== false)
	{
		$words = explode(",",$line);
		foreach($words as $i=>$word)
		{
			if($word == $version)
				die($words[$i+1]);
		}
	}
}
die("?");
?>
