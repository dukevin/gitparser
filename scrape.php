<html>
<head>
<?php
$url = $_REQUEST["url"];
$PAGE = "gerrit";
if(strpos($url,"chromium-review") === false) {
	$url .= "&format=JSON";
	$PAGE = "googlesource";
}
$urlP = explode("/",$url);
foreach($urlP as $u)
        if(is_numeric($u[0]))
                $title = explode("?",str_replace("..", " 🡒 ", $u))[0];
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
echo "<a style='float:left' href='index.php?".$vars."'>< Edit </a>";
$bugURL = "https://bugs.chromium.org/p/chromium/issues/detail?id=";
echo "<h1>".$title."</h1>";
$file = file_get_contents($url) or die("Fatal: Invalid URL <br>$url");

$data = json_decode(substr($file, 4));
$black = explode(PHP_EOL,$_REQUEST["blacklist"]);
$white = explode(PHP_EOL,$_REQUEST["whitelist"]);
echo "<table>";

$skipped = $total = $foundf = 0;

if($PAGE == "googlesource")
foreach($data->log as $d)
{
	$compare = $d->message.$d->author->name;
	foreach($black as $b)
		if(@stripos($compare,trim($b)) !== false)
		{
			foreach($white as $w)
				if(@stripos($compare,trim($w)) !== false)
				{
					$foundf++;
					break 2;
				}
			$skipped++;
			continue 2;
		}
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
if($PAGE == "gerrit")
foreach($data as $d)
{
	echo "<tr>";
	echo "<td>".$d->subject."</td><td>".$d->submit_type."</td><td>".$d->updated."</td>";
	echo "</tr>";
}

echo "</table><br>\n";
echo "Skipped $skipped of ".sizeof($data->log)." (".round($skipped/sizeof($data->log)*100,2)."%) entries on blacklist. Ignored $foundf entries from blacklist";
?>
</pre>
</body>
</html>



<?php
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
