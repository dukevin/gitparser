<html>
<head>
<?php
$url = $_REQUEST["url"];
$PAGE = "gerrit";
$title = "gerrit results";
$url = str_replace("/q", '', $url);
$urlP = explode("/",$url);
if(strpos($url,"chromium.googlesource") !== false) {
	$url .= "&format=JSON";
	$PAGE = "googlesource";
	$title = "Google Source";
	foreach($urlP as $u)
		if(is_numeric($u[0]))
			$title = explode("?",str_replace("..", " 🡒 ", $u))[0];
}
else if($url[strlen($url)-1] != '/' && !is_numeric($url[strlen($url)-1])) $url.="/";

?>
	<title><?=$title?></title>
	<link rel="icon" type="image/png" href="favicon.ico">
	<style>*{font-family:arial;text-align:center;transition:0.33s all;text-decoration: none} td{border:1px solid black; word-break: break-all} td:first-child{font-size:14pt; width:40%} td:last-child,td:nth-last-child(2) {width: 5%;} tr:hover {background-color:#eee} table{border-collapse:collapse; width:100%;} .message{font-size:11px; text-align:left;}</style>
</head>
<body>
<?php
$vars = "url=".urlencode($_REQUEST["url"])."&whitelist=".urlencode($_REQUEST["whitelist"])."&blacklist=".urlencode($_REQUEST["blacklist"]);
if($_REQUEST["save"])
{
	header("Location: index.php?".$vars);
	exit;
}
echo "<a style='float:left' href='index.php?".$vars."'>< Edit </a><br clear=both>";
$bugURL = "https://bugs.chromium.org/p/chromium/issues/detail?id=";
echo "<h1>".$title."</h1>";
try {
	$data = get_json($url);
} catch(Exception $e) {
	die($e->getMessage());
}

echo "<table>";

$skipped = $total = $foundf = 0;

if($PAGE == "googlesource")
foreach($data->log as $d)
{ 
	if(on_blacklist($d->message.$d->author->name))
		continue;
	$total = sizeof($data->log);
	echo "<tr>";
	$message = explode(PHP_EOL, $d->message);
	$message1 = htmlspecialchars($message[0]);
	array_shift($message);array_shift($message);
	$message = array_map('htmlspecialchars', $message);
	$message2 = implode("<br>\n",$message);
	$message2 = explode("Change-Id",$message2);
	$message2 = $message2[0];
	$message2 = preg_replace('/\d{6,9}/', "<a href='$bugURL$0'>$0</a>", $message2);
	echo "<td><a href='https://chromium.googlesource.com/chromium/src/+/".$d->commit."'>".$message1."</a></td><td class='message'>".$message2."</td><td>".$d->author->name."</td>"."<td>".(time_elapsed_string($d->author->time))."</td>";
	echo "</tr>";
}
if($PAGE == "gerrit") {
	if(empty($_REQUEST['amt']))
		$_REQUEST['amt'] = 0;
	if($_REQUEST['amt'] > 1000000 || !is_numeric($_REQUEST['amt']))
	{
		echo "gerrit amount was either higher than 1000000 or was not a number. Defaulting to 500<br>";
		$_REQUEST['amt'] = 500;
	}
	foreach($data as $d)
	{
		$total++;
		//$details = json_decode( substr(file_get_contents("https://chromium-review.googlesource.com/changes/".$d->_number."/detail/"),4) );
		if(on_blacklist($d->subject.$d->project.$d->status.$d->owner->_account_id)) 
			continue;
		echo "<tr>";
		$link="https://chromium-review.googlesource.com/q/".$d->_number;
		echo "<td><a href='$link'>".htmlspecialchars($d->subject)."</a></td><td>".htmlspecialchars($d->project)."</td><td>".htmlspecialchars($d->status)."</td><td><a href='https://chromium-review.googlesource.com/accounts/".$d->owner->_account_id."'>".$d->owner->_account_id."</a></td><td>".(convertTime(explode('.',$d->updated)[0]))."</td>";
		//time_elapsed_string(date("Y-m-d H:i:s",strtotime(explode('.',$d->updated)[0])),true)
		echo "</tr>";
		unset($details);
	}
	if($total < $_REQUEST['amt'])
	{
		for($i=0; $i<$_REQUEST['amt']; $i+=500)
		{
			try {
				$data = get_json($url."?O=881&S=".$i."&n=500&q=".$_REQUEST['q']."/");
			} catch(Exception $e) {
				echo($e->getMessage());
			}
			foreach($data as $d)
			{
				$total++;
				if(on_blacklist($d->subject.$d->project.$d->status.$d->owner->_account_id.$d->status)) 
					continue;
				echo "<tr>";
				$link="https://chromium-review.googlesource.com/q/".$d->_number;

				echo "<td><a href='$link'>".htmlspecialchars($d->subject)."</a></td><td>".htmlspecialchars($d->project)."</td><td>".$d->status."</td><td><a href='https://chromium-review.googlesource.com/accounts/".$d->owner->_account_id."'>".$d->owner->_account_id."</a></td><td>".(convertTime(explode('.',$d->updated)[0]))."</td>";
				echo "</tr>";
				unset($details);
			}
		}
	}
}

echo "</table><br>\n";
if($total == 0 && $findf == 0 && $skipped == 0)
	echo "Empty results from <br> $url <br> identified as $PAGE";
else
	echo "Skipped $skipped of ".$total." (".round($skipped/$total*100,2)."%) entries on blacklist. Ignored $foundf entries from blacklist";
?>
</pre>
</body>
</html>

<?php
//check givin compare string with blacklist. If it is on the blacklist and not whitelist, return true
function on_blacklist($compare)
{
	global $foundf, $skipped;
	$black = explode(PHP_EOL,$_REQUEST["blacklist"]);
	$white = explode(PHP_EOL,$_REQUEST["whitelist"]);
	foreach($black as $b)
		if(@stripos($compare,trim($b)) !== false)
		{
			foreach($white as $w)
				if(@stripos($compare,trim($w)) !== false)
				{
					$foundf++;
					return false;
				}
			$skipped++;
			return true;
		}
	return false;
}
function convertTime($time)
{
	$date = DateTime::createFromFormat("Y-m-d H:i:s", $time);
	$date->modify("-7 hours");
	return $date->format("Y-m-d H:i:s");
}
function get_json($url)
{
	if(! $file = file_get_contents($url)) 
		throw new Exception("Fatal: Invalid URL <br>$url");
	$data = json_decode(substr($file, 4));
	if(empty($data)) 
		throw new Exception("Fatal: Response was not JSON or was empty<br>$url<br>$data");
	return $data;
}
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}?>