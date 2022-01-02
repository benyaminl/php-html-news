<?php

class JagatReviewParser {
  private $baseUrl = "https://www.jagatreview.com/category/mobile-computing/";

  private $html;
  private $parsedList;
  private $xpathSelector;

  public function __construct(string $url = "") {
    $this->baseUrl = $url != "" ? $url : $this->baseUrl;
    $this->xpathSelector = '//div[@class="ct__main"]';
  }

  public function fetch() {
    $ch = curl_init($this->baseUrl);
    $useragent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0';
    
    curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
    curl_setopt($ch, CURLOPT_REFERER, 'https://www.jagatreview.com/');
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
    /*
        <div class="ct__main">
            <div class="ct__main">
                <section id="article-xxxx">
                    <a href=""></a>
                </section>
            </div>
        </div>
    */
    $newsDom = $ulList->item(1)->childNodes;
    
    $data = [];
    
    foreach ($newsDom as $child)
    {
        if (strlen($child->textContent) > 1 
            AND strpos($child->textContent, "(document)") === false 
            AND $child->childNodes->count() > 0) {
          $tmp_dom = new DOMDocument();
          $tmp_dom->appendChild($tmp_dom->importNode($child->childNodes->item(1), true));
          
          $urlDOM = $tmp_dom
          ->childNodes->item(0)
              ->childNodes->item(1)
                  ->childNodes->item(1);
            if ($urlDOM != null) {
                $url = $urlDOM->attributes->item(0)->nodeValue;
                $title = $urlDOM->attributes->item(1)->nodeValue;
          
                $data[] = [
                    "title" => $title,
                    "url" => $url
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

  public function getParsedPage(string $url, $page = 0, $totalPage = 0) {
    $htmlDOM = "";

    $ch = curl_init($url);
    $useragent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0';
    curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
    curl_setopt($ch, CURLOPT_REFERER, 'https://www.jagatreview.com/');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);

    $dom = new DOMDocument();
    @$dom->loadHTML($result);

    $xpath = new DOMXPath($dom);
    $title = $xpath->query("//title")->item(0)->nodeValue;
    $article = $xpath->query("//article")
        ->item(0)->childNodes
        ->item(1)->childNodes;
    
    foreach ($article as $child)
    {
        /** @var \DOMElement|\DOMText $child */ 
        $tmp_dom = new DOMDocument();
        if ($child instanceof DOMElement) {
            $class = $child->attributes->getNamedItem("class")->nodeValue;
            if (strpos($class, "content") !== false) {
                $tmp_dom->appendChild($tmp_dom->importNode($child, true));
                
                $advSearch = $tmp_dom->getElementById("advanced-search");
                // var_dump($advSearch);
                if ($advSearch == null) {
                    var_dump($url);
                    var_dump($tmp_dom);
                }
                $pageXPATH = new DOMXPath($tmp_dom);
                $tocDOM = new DOMDocument(); 
                $tocDOM->appendChild($tocDOM->importNode($advSearch, true));
                // remove Daftar Isi 
                $tmp_dom->childNodes
                    ->item(0)->removeChild($advSearch);
                // remove share button 
                $shareDOM = $pageXPATH->query("//div[contains(@class, 'sharedaddy')]")
                    ->item(0);
                $tmp_dom->childNodes->item(0)->removeChild($shareDOM);
                // remove baca juga
                $bacaJugaDOM = $pageXPATH->query("//*[text()[contains(.,'Baca Juga')]]");
                foreach ($bacaJugaDOM as $bj) {
                    /** @var \DOMNode $bj */
                    $tmp_dom->childNodes->item(0)->removeChild($bj->parentNode);
                }
                // remove tags
                $tags = $pageXPATH->query("//ul[@class='jgtags']")
                ->item(0);
                $tmp_dom->childNodes->item(0)->removeChild($tags->parentNode);
                
                if ($totalPage > 0 AND $totalPage > $page) {
                    $nextPage = $page+1;
                    $strRemove = strlen("/".$nextPage-1);
                    $length = strlen($url);
                    $url = substr($url, 0, $length-$strRemove);
                    // remove author for 2nd page and beyond
                    
                    $authorDOM = $pageXPATH->query("//div[@class='jgauthor breakout']")
                        ->item(0);
                    
                    $tmp_dom->childNodes
                        ->item(0)->removeChild($authorDOM);
                    $htmlDOM .= trim($tmp_dom->saveHTML());
                    $htmlDOM .= "<hr>".$this->getParsedPage($url."/".$nextPage, $nextPage, $totalPage);
                } else if ($totalPage == 0) {
                    $tocXPATH = new DOMXPath($tocDOM);
                    // Get total Page
                    $pageCountDOM = $tocXPATH->query("//ul[@class='panel__list']")->item(0)
                    ->childNodes;
                    $page = 0;
                    foreach($pageCountDOM as $p) {
                        if ($p instanceof DOMElement) {
                            $page++;
                        }
                    }
                    $totalPage = $page;
                    $nextPage = 2;
                    $htmlDOM .= trim($tmp_dom->saveHTML());
                    $htmlDOM .= $this->getParsedPage($url."/".$nextPage, $nextPage, $totalPage);
                    $htmlDOM = [
                        "title" => $title,
                        "article" => $htmlDOM
                    ];
                }
            }
        }
    }
    // $head = $xpath->query("//*[@class='detail-desk']")->item(0)->nodeValue;
    return $htmlDOM;
  }
}

// $coba = new JagatReviewParser("https://www.jagatreview.com/category/mobile-computing/");
// $hasil = $coba->getList();
// // var_dump($hasil);
// echo "<pre>";
// $coba->getParsedPage("https://www.jagatreview.com/2021/11/review-intel-nuc-element-laptop-dan-pc-desktop-bisa-tukaran-prosesor/"));