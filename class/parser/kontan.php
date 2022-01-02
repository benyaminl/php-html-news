<?php

class KontanParser {
  private $baseUrl;

  private $html;
  private $parsedList;
  private $xpathSelector;

  public function __construct(string $url) {
    $this->baseUrl = $url;
    $this->xpathSelector = '//ul[@id="list-news"]';
  }

  public function fetch() {
    $ch = curl_init($this->baseUrl);
    $useragent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0';
    
    curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
    curl_setopt($ch, CURLOPT_REFERER, 'https://www.kontan.co.id/');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $this->html = curl_exec($ch);
    
  }

  public function parseToList() {
    $html = new DOMDocument();
    @$html->loadHTML($this->html);
    $dom = new DOMXPath($html);
    $ulList = $dom->query($this->xpathSelector);
    $newsDom = $ulList->item(0)->childNodes;
    $data = [];
    $i = 0; 
    foreach ($newsDom as $child)
    {
        if (strlen($child->textContent) > 1 AND strpos($child->textContent, "(document)") === false AND $child->childNodes->count() > 0) {
          $tmp_dom = new DOMDocument();
          $tmp_dom->appendChild($tmp_dom->importNode($child->childNodes->item(1), true));
          $url = $child->childNodes->item(1)->attributes->item(0)->value;
          $title = $child->childNodes->item(1)
                    ->childNodes->item(1)
                        ->childNodes->item(1);
          if(isset($title->attributes)) {
            $title = $title->attributes->getNamedItem("alt")->value;
            $data[] = [
              "title" => $title,
              "url" => $this->baseUrl.$url."?page=all"
            ];
          }
        }
    }
    $this->parsedList = $data;

  }

  public function getList() {
    $this->fetch();
    $this->parseToList();

    return $this->parsedList;
  }

  public function getParsedPage(string $url) {
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
    $title = $xpath->query("//*[@class='detail-desk']")->item(0)->nodeValue;
    $innerHTML = "";
    $children = $article->item(0)->childNodes;
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
    return [
      "title" => $title,
      "article" => $innerHTML
    ];
  }
}

# $coba = new KontanParser("https://investasi.kontan.co.id");
# $hasil = $coba->getList();
# var_dump($hasil);
# var_dump($coba->getParsedPage("https://investasi.kontan.co.id/news/ihsg-diramal-berbalik-menguat-simak-pergerakan-asii-bbri-dan-towr-pada-senin-31?page=all"));
