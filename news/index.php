<?php
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

$ch = curl_init($url);
$useragent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0';
curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
curl_setopt($ch, CURLOPT_REFERER, 'https://www.kontan.co.id/');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$result = curl_exec($ch);

$dom = new DOMDocument();
@$dom->loadHTML($result);
// $temp = $dom->getElementsByTagName("div");
// var_dump($temp);
// foreach($temp as $a){
//     echo $a->nodeValue;
// }
$xpath = new DOMXPath($dom);
//$results = $xpath->query("//*[@class='" . $classname . "']");
$article = $xpath->query("//*[@itemprop='articleBody']");
$head = $xpath->query("//*[@class='detail-desk']")->item(0)->nodeValue;
$innerHTML = "";
$children = $article->item(0)->childNodes;
ob_start();
echo <<<BODY
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 2.0//EN">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<html>
    <head>
        <title>$head</title>
    </head>
    <body>
BODY;
echo "<a href='../'>&lt; Back</a>";
echo "<h1>".$head."</h1>";
foreach ($children as $child)
{
    $tmp_dom = new DOMDocument();
    if (strpos($child->nodeValue,"Baca Juga") === false AND
        strpos($child->nodeValue,"Selanjutnya") === false AND
        $child->nodeName == "p") {

    $tmp_dom->appendChild($tmp_dom->importNode($child, true));
    $innerHTML .= trim($tmp_dom->saveHTML());
    }
}
echo $innerHTML; 
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
