<!doctype html>
<html>
<head>
<?php
$url = $_REQUEST["url"];
$PAGE = "gerrit";
$title = "gerrit results";
$url = str_replace("/q", '', $url);
$urlP = explode("/",$url);
if(strpos($url,"chromium.googlesource") !== false) {
	$PAGE = "googlesource";
	$title = "Google Source";
	$url = preg_replace('/[?&]+n=[^&]+(&|$)/','$1',$url); //remove amount (n=) from url
	foreach($urlP as $u)
		if(is_numeric($u[0]))
		{
			$vn = explode("..", $u);
			$title = $vn[0]." 🡒 ".$vn[1];
		}
}
else if($url[strlen($url)-1] != '/' && !is_numeric($url[strlen($url)-1])) $url.="/";

?>
	<title><?=$title?></title>
	<link rel="icon" type="image/png" href="favicon.ico">
	<style>*{font-family:arial;text-align:center;transition:0.33s all;text-decoration: none} td{border:1px solid black; word-break: break-all} td:first-child{font-size:14pt; width:40%} td:last-child,td:nth-last-child(2) {width: 5%;} tr:hover {background-color:#eee} table{border-collapse:collapse; width:100%;} .message{font-size:11px; text-align:left;} .med{width:8%} .green{background-color:#afa}.blue{background-color:#aff}.red{background-color:#faa}.purple{background-color:#f0e5fa}.purple:hover{background-color:#cface8}.red:hover{background-color:#d88}.found{font-style: italic}#url{}</style>
	<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
	<script>
	$(document).ready(function(){
		$("tr").mousedown(function(){
			$(this).toggleClass("purple");
		});
		<?= ($PAGE == "gerrit" || (empty($vn[0])&&empty($vn[1]))) ? "return;\n" : "" ?>
		var vn = new Array();
		getVN("#platformvn", "<?=$vn[0]?>", 0, 0, vn);
		getVN("#platformvn2", "<?=$vn[1]?>", 0, 1, vn);
	});
	function getVN(dom, num, index, i, vn)
	{
		var url=["https://raw.githubusercontent.com/skylartaylor/cros-updates/master/src/data/cros-updates.json",
		"https://cros-updates-serving.appspot.com/csv", "https://cros-updates-serving.appspot.com/all"];
		console.log("Searching "+url[index]+" for "+num);
		$(dom).fadeIn();
		if(index == 0)
			$(dom).html("...");
		$.ajax({
			url: "platformvn.php?num="+num+"&url="+url[index],
			context: document.body
		}).done(function(d){
			if(d == "?" || d == "!") {
				if(index < url.length) {
					$(dom).html($(dom).html().substr(1)+d);
					getVN(dom, num, index+1);
				}
				else
					$(dom).css("color","#c00");
			}
			else {
				$(dom).html(d);
				$("#platformb").html(" 🡒 ");
				vn[i] = d;
				makeVNclick(vn); 
			}
		});
	}
	function makeVNclick(vn)
	{
		if(vn.length < 2)
			return false;
		$("#platcont").wrap('<a href=https://chromeos.google.com/partner/console/a/1/changelog?from='+vn[0]+'&to='+vn[1]+' target=_blank>');
	}
	</script>
</head>
<body>
<?php
$vars = "url=".urlencode($_REQUEST["url"])."&whitelist=".urlencode($_REQUEST["whitelist"])."&blacklist=".urlencode($_REQUEST["blacklist"])."&only=".urlencode($_REQUEST["only"])."&amt=".urlencode($_REQUEST["amt"])."&q=".urlencode($_REQUEST["q"]);
if($_REQUEST["save"])
{
	header("Location: index.php?".$vars);
	exit;
}
echo "<a style='float:left' href='index.php?".$vars."'>&lt; Edit </a><br clear=both>\n";
$bugURL = "https://bugs.chromium.org/p/chromium/issues/detail?id=";
$CL = "https://chromeos.google.com/partner/console/a/1/clFinder?query=";
echo "<h1>".$title."</h1>\n";
echo "<span id='platcont'><span id='platformvn' style='display:none'>".(empty($vn[0]) ? "" : $vn[0])."</span><span id='platformb'>&nbsp</span><span id='platformvn2' style='display:none'>".(empty($vn[1]) ? "" : $vn[1])."</span></span>\n";
echo "<table>";

$skipped = $total = $foundf = 0;

if($PAGE == "googlesource")
{
	if(empty($_REQUEST['amt']))
		$_REQUEST['amt'] = 1000;
	if($_REQUEST['amt'] > 1000000 || !is_numeric($_REQUEST['amt']) || $_REQUEST['amt'] < 1)
	{
		echo "Google Source amount was not valid, defaulting to 1000<br>";
		$_REQUEST['amt'] = 1000;
	}
	$inc = $_REQUEST['amt'] > 10000 ? 10000 : $_REQUEST['amt'];
	for($i=0; $i<$_REQUEST['amt']; $i+=$inc)
	{
		$inc = $_REQUEST['amt'] - $i > 10000 ? 10000 : $_REQUEST['amt'];
		$n = "?n=".$inc;
		if($i==0)
			$last = $last_p = '';
		else
			$last_p = "?s=".$last;
		try {
			$data = get_json($url.$n.$last_p."&format=JSON");
		} catch(Exception $e) {
			echo($e->getMessage());
		}
		foreach($data->log as $d)
		{ 
			$debug = $subject = '';
			$total++;
			if(on_blacklist($d->message.$d->author->name))
				if($_REQUEST["debug"] == "on")
					$debug = "class='red'";
				else 
					continue;
			echo "<tr ".$debug.">";
			$message = explode(PHP_EOL, $d->message);
			$message1 = htmlspecialchars($message[0]);
			array_shift($message);array_shift($message);
			$message = array_map('htmlspecialchars', $message);
			$message2 = implode("<br>\n",$message);
			$message2 = explode("Change-Id",$message2);
			$message2 = $message2[0];
			$message2 = preg_replace('/\d{6,9}/', "<a href='$bugURL$0' target='_blank'>$0</a>", $message2);
			echo "<td><a href='https://chromium.googlesource.com/chromium/src/+/".$d->commit."' target='_blank'>".$message1."</a><a href='$CL".$d->commit."' target='_blank'>&nbsp;ⓘ&nbsp;</a>".$subject."</td><td class='message'>".$message2."</td><td>".$d->author->name."</td>"."<td>".(time_elapsed_string($d->author->time,true))."</td>";
			echo "</tr>";
			$commit = $data->next;
		}
		if($last == $commit) {
			//echo "<script>console.log('last: ". $commit ."');</script>";
			break;
		}
		$last = $commit;
	}
}
if($PAGE == "gerrit") {
	if(empty($_REQUEST['amt']))
		$_REQUEST['amt'] = 500;
	if($_REQUEST['amt'] > 1000000 || !is_numeric($_REQUEST['amt']) || $_REQUEST['amt'] < 1)
	{
		echo "Gerrit amount was not valid, defaulting to 500<br>";
		$_REQUEST['amt'] = 500;
	}
	for($i=0; $i<$_REQUEST['amt']; $i+=500)
	{
		$q = empty($_REQUEST['q']) ? "" : "&q=".$_REQUEST['q'];
		try {
			$data = get_json($url."?O=881&S=".$i."&n=500".$q);
		} catch(Exception $e) {
			echo($e->getMessage());
		}
		foreach($data as $d)
		{
			$total++;
			$debug = $subject = '';
			if(on_blacklist($d->subject.$d->project.$d->status.$d->owner->_account_id.$d->status)) 
				if($_REQUEST["debug"] == "on")
					$debug = "class='red'";
				else
					continue;
			echo "<tr ".$debug.">";
			$link="https://chromium-review.googlesource.com/q/".$d->_number;
			echo "<td><a href='$link' target='_blank'>".htmlspecialchars($d->subject)."</a>".$subject."</td><td class='med'>".htmlspecialchars($d->project)."</td>".colorcell($d->status)."<td><a href='https://chromium-review.googlesource.com/accounts/".$d->owner->_account_id."'>".$d->owner->_account_id."</a></td><td>".(convertTime(explode('.',$d->updated)[0]))."</td>";
			echo "</tr>";
			unset($details);
		}
	}
}

echo "</table><br>\n";
if($total == 0 && $findf == 0 && $skipped == 0)
	echo "Empty results from <br> $url <br> identified as $PAGE";
else
	echo $total." changes in total. Skipped ".$skipped." of ".$total." (".round($skipped/$total*100,2)."%) except for ".$foundf." entries<br><br>";
echo "Source: <input id='url' onClick='this.select();' value='".$url."'>";
?>

</body>
</html>

<?php
//check givin compare string with blacklist. If it is on the blacklist and not whitelist, return true. If it is on show only return true
function on_blacklist($compare)
{
	global $foundf, $skipped, $subject;
	$black = explode(PHP_EOL,$_REQUEST["blacklist"]);
	$white = explode(PHP_EOL,$_REQUEST["whitelist"]);
	$only = explode(PHP_EOL,$_REQUEST["only"]);
	if(count($only) > 0 && !empty($only[0]))
		foreach($only as $o)
		{
			if(check_whole_words($compare, $o)) {
				if($w = on_whitelist($compare, $white)) {
					$subject = "<br><span class='found'>Found: ".$o.", whitelist: ".$w."</span>";
					return false;
				}
				$skipped++;
				return true;
			}
			if(@stripos($compare,trim($o)) === false) //not on show only list
			{
				if($w = on_whitelist($compare, $white)) {
					$subject = "<br><span class='found'>Found: ".$o.", whitelist: ".$w."</span>";
					return false;
				}
				$skipped++;
				return true;
			}
		}
	foreach($black as $b)
	{
		if(check_whole_words($compare,$b)) {
			if($w = on_whitelist($compare, $white)){
				$subject = "<br><span class='found'>Found: ".$b.", whitelist: ".$w."</span>";
				return false;
			}
			$skipped++;
			$subject = "<br><span class='found'>Found: ".$b."</span>";
			return true;
		}
		if(@stripos($compare,trim($b)) !== false)
		{
			if($w = on_whitelist($compare, $white)) {
				$subject = "<br><span class='found'>Found: ".$b.", whitelist: ".$w."</span>";
				return false;
			}
			$subject = "<br><span class='found'>Found: ".$b."</span>";
			$skipped++;
			return true;
		}
	}
	return false;
}
function on_whitelist($compare, $white)
{
	global $foundf, $skipped;
	foreach($white as $w)
	{
		if(check_whole_words($compare,$w)) {
			$foundf++;
			return $w;
		}
		if(@stripos($compare,trim($w)) !== false) {
			$foundf++;
			return $w;
		}
	}
	return false;
}
function check_whole_words($compare, $word) //find whole words token and the whole word returns true
{
	$word = trim($word);
	if($word[0] == "`" || $word[strlen($word)-1] == "`")
	{
		$find = str_replace("`","\b",$word);
		if(preg_match("/$find/i", $compare)) {
			return true;
		}
	}
	return false;
}
function colorcell($str)
{
	if($str == "NEW")
		$color="white";
	if($str == "MERGED")
		$color="green";
	if($str == "ABANDONED")
		$color="red";
	return "<td class='$color med'>".$str."</td>";

}
function convertTime($time)
{
	$date = DateTime::createFromFormat("Y-m-d H:i:s", $time);
	$date->modify("-5 hours");
	return time_elapsed_string($date->format("Y-m-d H:i:s"),true);
}
function get_json($url)
{
	if(! $file = file_get_contents($url)) 
		throw new Exception("Fatal: Invalid URL <br>$url");
	$data = json_decode(substr($file, 4));
	if(empty($data)) 
		throw new Exception("Error: Empty or invalid JSON");
	echo "<script>console.log('Queried for JSON data: ". $url ."');</script>";
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
