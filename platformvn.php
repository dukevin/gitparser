<?php
$version = $_REQUEST['num'];
$url = urldecode($_REQUEST['url']);
if(empty($version))
	die("!!!");
$u = explode(".",$url);
if($u[sizeof($u)-1]=="json")
{
	$json = json_decode(file_get_contents($url), JSON_OBJECT_AS_ARRAY);
	foreach($json as $j)
		foreach($j as $i => $k)
		{
			if($i == "Recovery" || $i == "Brand names" || $i=="Codename")
				continue;
			if(strpos($k,$version)!==false)
			{
				$d = explode("<br>",$k);
				die($d[0]);
			}
		}
	die("?");
}
$csv = file_get_contents($url) or die("!!!");
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