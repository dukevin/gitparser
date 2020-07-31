<!doctype html>
<html>
<head>
	<meta chartype=utf-8>
	<title>google git scraper</title>
	<link rel="icon" type="image/png" href="favicon.ico">
	<style>
		textarea {height: 500px;} input[type="text"]{width: 600px;} h2,span,p{font-family:arial;}
	</style>
</head>
<body>
<h2>Google git parser</h2>
<form action="scrape.php" method="get" target="_blank">
	<p>Supports googlesource and gerrit results</p>
	<input type="text" name="url" id="url" placeholder="url" value="<?=$_REQUEST["url"]?>"/><input type="number" name="amt" id="amt" placeholder="gerrit amount" value="<?=$_REQUEST["amt"]?>" min=0 step=100/><br clear='both'>
	<textarea id="blacklist" name="blacklist" placeholder="blacklist" height=50><?=urldecode($_REQUEST["blacklist"])?></textarea><textarea name="whitelist" id="whitelist" placeholder="whitelist"><?=urldecode($_REQUEST["whitelist"])?></textarea>
	<br clear='both'>
	<input type="submit" id="scrape" name="scrape" value="filter" style="float:left"/>
	<input type="submit" id="save" name="save" value="save" style="float:left"/>
	<br clear='both'><span style="color: #3cf"><?if(!empty($_REQUEST["blacklist"])) echo "saved";?></span>
</form>
</body>
</html>
