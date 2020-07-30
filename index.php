<!doctype html>
<html>
<head>
	<meta chartype=utf-8>
	<title>google git scraper</title>
	<link rel="icon" type="image/png" href="favicon.ico">
	<script
			  src="https://code.jquery.com/jquery-3.5.1.slim.js"
			  integrity="sha256-DrT5NfxfbHvMHux31Lkhxg42LY6of8TaYyK50jnxRnM="
			  crossorigin="anonymous"></script>
	<script>
	$(document).ready(function(){


	});
	</script>
	<style>
		textarea {height: 500px;} input[type="text"]{width: 600px;} h2,span{font-family:arial;}
	</style>
</head>
<body>
<h2>Google git scraper</h2>
<form action="scrape.php" method="get" target="_blank">
	<input type="text" name="url" id="url" placeholder="url" value="<?=$_REQUEST["url"]?>"/><br clear='both'>
	<textarea id="blacklist" name="blacklist" placeholder="blacklist" height=50><?=urldecode($_REQUEST["blacklist"])?></textarea><textarea name="whitelist" id="whitelist" placeholder="whitelist"><?=urldecode($_REQUEST["whitelist"])?></textarea>
	<br clear='both'>
	<input type="submit" id="scrape" name="scrape" value="scrape" style="float:left"/>
	<input type="submit" id="save" name="save" value="save" style="float:left"/>
	<br clear='both'><span style="color: #6cf"><?if(!empty($_REQUEST["blacklist"])) echo "saved";?></span>
</form>
</body>
</html>
