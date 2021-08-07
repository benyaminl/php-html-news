<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 2.0//EN">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<html>
    <head> 
        <title>Indonesian HTML News Viewer</title>
    </head>
    <body>
    <center>
    <h1 style="margin-bottom: 0px;">Berita Indonesia</h1>
    <h5 style="margin-top: 0px;">dalam HTML</h5>
    </center>
    <hr/>
    <?php
        include_once("class/parser/load.php");
        // Load the RSS of Kontan
        $kontan = [
            'investasi' => new KontanParser("https://investasi.kontan.co.id/"),
            'nasional'  => new KontanParser("https://nasional.kontan.co.id/")
        ];

        foreach($kontan as $n => $u) {
            $result = "";
            $file = new SPLFileInfo(__DIR__."/rss/{$n}.json");
            if (!$file->getRealPath()){
                $result = $u->getList();
                $file = $file->openFile("w+");
                $file->fwrite(json_encode($result));
            } else {
                $file = $file->openFile("r");
                $result = "";
                while (!$file->eof()) {
                    $result .= $file->fgets();
                }
                $result = json_decode($result, true);
            }
            $time = DateTime::createFromFormat("U", $file->getMTime());
            $time->setTimeZone(new DateTimeZone("Asia/Jakarta"));
            $file = null; // close file
            
            echo "<h3 style='margin-bottom:0px;'>Kontan ".ucfirst($n)."</h3>";
            echo "<h6 style='margin-top:2px;margin-bottom:1px;'><i>Last Update : ".$time->format("d-M-Y H:i T")."</i></h6>";
            $i = 0;
            echo "<ol>";
            foreach($result as $r) {
              echo "<li><a href='/news/index.php?url=".urlencode($r['url']).".html'>{$r['title']}</a></li>";
            }
            echo "</ol>";
        }
    ?>
    <hr/>
    <center>Supported by PHP7, PHP-XML, PHP-cURL, PHP-mbstring | (c) 2021 - Benyamin Limanto</center>
    </body>
</html>
