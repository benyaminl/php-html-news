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
        // Load the RSS of Kontan
        $kontan = [
            'investasi' => "http://rss.kontan.co.id/news/investasi",
            'nasional'  => "http://rss.kontan.co.id/news/nasional"
        ];

        foreach($kontan as $n => $u) {
            $result = "";
            $file = new SPLFileInfo(__DIR__."/rss/{$n}.xml");
            if (!$file->getRealPath()){
                $ch = curl_init($u);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $result = curl_exec($ch);
                curl_close($ch);
                $file = $file->openFile("w+");
                $file->fwrite($result);
            } else {
                $file = $file->openFile("r");
                $result = "";
                while (!$file->eof()) {
                    $result .= $file->fgets();
                }
            }
            $time = DateTime::createFromFormat("U", $file->getMTime());
            $time->setTimeZone(new DateTimeZone("Asia/Jakarta"));
            // Read the XML 
            $file = null; // close file
            $xml = new XMLReader();
            $xml->XML($result);
            $ttl = false;
            echo "<h3 style='margin-bottom:0px;'>Kontan ".ucfirst($n)."</h3>";
            echo "<h6 style='margin-top:2px;margin-bottom:1px;'><i>Last Update : ".$time->format("d-M-Y H:i T")."</i></h6>";
            $i = 0;
            echo "<ol>";
            $rssData = []; $temp = [];
            while ($xml->read()) {
                if (!$ttl) {
                    if ($xml->name == "ttl") {
                        $ttl = true;
                    }
                }
                if (($xml->name == "title" AND $ttl 
                    AND $xml->nodeType != XMLReader::END_ELEMENT)
                    OR 
                    ($xml->name == "link" AND $ttl 
                    AND $xml->nodeType != XMLReader::END_ELEMENT)) {
                    // echo $xml->name." : ";
                    $xml->read();
                    // echo $xml->value;
                    // echo "<br/>";
                    $i++;
                    $temp[] = $xml->value;
                }

                if ($i == 2) {
                    $rssData[] = $temp;
                    $url = "news/index.php?url=".urlencode("{$temp[1]}?page=all").".html";
                    echo "<li><a href='$url'>{$temp[0]}</a></li>";
                    $i = 0; $temp = [];
                }
            }
            echo "</ol>";
        }
    ?>
    </body>
</html>
