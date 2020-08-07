<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
$version = $_REQUEST['num'];
$url = urldecode($_REQUEST['url']);
$u = explode(".",$url);
if($_REQUEST['latest'])
{
	$json = json_decode(file_get_contents($url)) or die("!");
	foreach($json as $j)
	{
		if($j->Codename == $_REQUEST["codename"])
			die(json_encode($j));
	}
	die("?");
}
if($_REQUEST['date'])
{
	$context = stream_context_create(
		array(
			"http" => array(
				"header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.103 Safari/537.37"
			)
		)
	);
	$json = json_decode(file_get_contents($url, false, $context)) or die("!!!");
	die($json->commit->commit->author->date);
}
if(empty($version))
	die("!!!");
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
$csv = file_get_contents($url) or die("!");
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