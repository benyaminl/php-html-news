<?php
include("../class/parser/load.php");
// Process the URL that's not parsed
if (!isset($_GET['url'])) {
    header("Location: ../ ");
    exit;
}

if (realpath(__DIR__."/".urlencode($_GET['url']))) {
    header("Location: ".urlencode(urlencode($_GET['url'])));
    exit;
}

$url = substr($_GET['url'],0,strlen($_GET['url'])-5) ;

$helper = [
    "investasi.kontan" => new KontanParser("https://investasi.kontan.co.id"),
    "jagatreview" => new JagatReviewParser(),
    "nasional.kontan"=> new KontanParser("https://nasional.kontan.co.id")
];

$head = ""; $body = "";

foreach($helper as $h => $ob) {
    if (strpos($url, $h) !== false) {
        $res = $ob->getParsedPage($url);
        $head =  $res["title"];
        $body = $res["article"];
    }
}

ob_start();
echo <<<BODY
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 2.0//EN">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<html>
    <head>
        <title>$head</title>
    </head>
    <body>
    <style>
    img {
        max-width:100% !important;
    }
    </style>
BODY;
echo "<a href='../'>&lt; Back</a>";
echo "<h1>".$head."</h1>";

echo $body; 
echo "<a href='$url'>Source URL</a>";
echo "</body></html>";
$html = ob_get_contents();
ob_end_clean();

// Print the body
echo $html;
// Tulis sebagai Cache
$file = new SplFileObject(__DIR__."/".urlencode($_GET["url"]), "w+");
$file->fwrite($html);
$file = null;
