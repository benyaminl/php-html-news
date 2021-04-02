<html>
    <head> 
        <title>HTML News Agregator</title>
    </head>
    <body>
    <?php
        // Load the RSS of Kontan
        $kontan = [
            0 => "http://rss.kontan.co.id/news/investasi" 
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
            // Read the XML 
            $file = null; // close file
            $xml = new XMLReader();
            $xml->XML($result);
            $ttl = false;
            $i = 0;
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
                    echo "<a href='$url'>{$temp[0]}</a><br/>";
                    $i = 0; $temp = [];
                }
            }
        }
    ?>
    </body>
</html>
