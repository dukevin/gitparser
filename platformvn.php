<?php
$version = $_REQUEST['num'];
if(empty($version))
	die("Error: version was empty");
$csv = file_get_contents('https://cros-updates-serving.appspot.com/csv') or die("Unavailable");
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
unset($csv, $lines, $version);
$csv = file_get_contents('https://cros-omahaproxy.appspot.com/all');
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
die("???");
?>
