<?php
mb_language('Japanese');

$url = 'https://www.google.co.jp/search?q=%E6%B2%96%E7%B8%84%E3%80%80%E9%AB%98%E7%B4%9A%E3%83%9B%E3%83%86%E3%83%AB';
$searchPath = '//*/h3[@class="r"]/a';

$html = file_get_contents($url);
$html = mb_convert_encoding($html,"utf8","auto");

$dom = new DOMDocument();
@$dom->loadHTML( mb_convert_encoding($html,'HTML-ENTITIES','UTF-8'));
$xpath = new DOMXPath($dom);

$nodeList = $xpath->query($searchPath);

foreach($nodeList as $node) {
  echo '<<< ' . $node->nodeValue . " >>>\n";
  $url = "https://www.google.co.jp" . $node->attributes['href']->nodeValue;
  $decodeUrl = parse_url($url);
  parse_str($decodeUrl['query'],$path);
  echo $path['q'] . "\n";
  echo "---------------\n";
}

