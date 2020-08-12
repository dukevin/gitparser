<!doctype html>
<html>
<head>
	<meta chartype=utf-8>
	<title>google git scraper</title>
	<link rel="icon" type="image/png" href="favicon.ico">
	<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
	<script src="http://timeago.yarp.com/jquery.timeago.js" type="text/javascript"></script>
	<style>
		textarea {height: 500px; width: 177px;} #url{width: 543px;} h2,span,p,table{font-family:arial;} .prefill{color:blue;cursor:pointer;} form,#versions{float:left;} #versions{margin-top: 10%;} td:first-child{font-weight:bold;} th{font-weight:normal} #versions td{padding: 10px;} #time{font: italic 10pt arial; color: gray;} #amt{width:70px;} #q{width:395px;}
	</style>
	<script>$(document).ready(function(){
		$("#gs").click(function(){
			$("#url").val("https://chromium.googlesource.com/chromium/src/+log/?n=1000");
		});
		$("#gerrit").click(function(){
			$("#url").val("https://chromium-review.googlesource.com/changes/");
		});
		$("#help").click(function(){
			alert("url: \tUrl location of a resource returning json data\nAll list entries are case insensitive and separated by newlines. Whitespaces are stripped.\nA `backtick` at the end or beginning of a word is treated as a whole word. For ex: roll matches [roll, scroll, roller], `roll matches [roll, roller], roll` matches [roll, scroll], `roll` only matches [roll]\nblacklist: \tA result containing any text from the blacklist is excluded\nshow only: \tOnly results containing the text here will be shown\nwhitelist: \tResults containing any whitelist text will be shown even if it is on the blacklist or show-only list\nFilter button: \tFetch the results\nSave button: \tCreate a url saving all the entries on the page for bookmarking or linking\nGerrit only: amount: \tshow this many entries (in multiples of 500)\nGerrit only: search: \tpass search parameters to the Gerrit search query parameter");
		});
		$("#versions table tr td:first-child").each(function(i){
			var codename = $(this);
			$("#versions table th").each(function(j){
				var elem = $(this);
				$.ajax({
					url: "platformvn.php?latest=true&codename="+codename.text()+"&url=https://raw.githubusercontent.com/skylartaylor/cros-updates/master/src/data/cros-updates.json",
					context: document.body
				}).done(function(data){
					var json = JSON.parse(data);
					var channel = $(elem).text();
					$('#versions tr:eq('+(i+1)+') td:eq('+j+')').html(json[channel]);
				});
			})
		});
		$.ajax({
			url: "platformvn.php?date=true&url=https://api.github.com/repos/skylartaylor/cros-updates/branches/master",
			context: document.body
		}).done(function(data){
			$("time.timeago").text(data);
			$("time.timeago").attr("datetime",data);
			$("time.timeago").timeago();
		});
	});
	</script>
</head>
<body>
<h2>Google git parser</h2>
<form action="scrape.php" method="get" target="_blank">
	<p>Supports <span class="prefill" id="gs">googlesource</span> and <span class="prefill" id="gerrit">gerrit url</span></p>
	<input type="text" name="url" id="url" placeholder="url" value="<?=$_REQUEST["url"]?>"/><br clear="both">
	<p style="float:left;margin:0;font-size:10pt;padding-top:5px">Gerrit only:&nbsp;</p>
	<div style="float:left"><input type="number" name="amt" id="amt" placeholder="amount" value="<?=$_REQUEST["amt"]?>" min=0 step="500"/>
	<input type="text" name="q" id="q" placeholder="search" value="<?=empty($_REQUEST["q"]) ? "" : $_REQUEST["q"] ?>" placeholder="changes"/></div><br clear='both'>
	<textarea id="blacklist" name="blacklist" placeholder="blacklist"><?=urldecode($_REQUEST["blacklist"])?></textarea><textarea name="only" id="only" placeholder="show only"><?=urldecode($_REQUEST["only"])?></textarea><textarea name="whitelist" id="whitelist" placeholder="whitelist"><?=urldecode($_REQUEST["whitelist"])?></textarea>
	<br clear='both'>
	<input type="submit" id="scrape" name="scrape" value="Filter" style="float:left;font-weight:bold"/>
	<input type="submit" id="save" name="save" value="Save" style="float:left"/>
	<button type="button" style="float:left" id="help">Help</button>
	<br clear='both'>
</form>
<div id="versions">
	<table>
		<tr><th id='time'><time class="timeago">...</time></th><th>Stable</th><th>Beta</th><th>Dev</th><th>Canary</th>
		<tr><td>eve</td><td>...</td><td>...</td><td>...</td><td>...</td></tr>
		<tr><td>nocturne</td><td>...</td><td>...</td><td>...</td><td>...</td></tr>
	</table>
</div>
</body>
</html>
