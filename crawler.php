<?php
include("simple_html_dom.php");
$crawled_urls=array();
$found_urls=array();
set_time_limit(0);
ini_set('max_execution_time', 3600);
ini_set ('allow_url_fopen', '1');
// Site to crawl
$base1 = 'http://www.visions.ca';
$curl = curl_init();
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1" );
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($curl, CURLOPT_URL, $base1);
curl_setopt($curl, CURLOPT_REFERER, $base1);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($curl, CURLOPT_CONNECTTIMEOUT ,0); 
curl_setopt($curl, CURLOPT_TIMEOUT, 400);
curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
$str = curl_exec($curl);

curl_close($curl);

$html_base = new simple_html_dom();
// Load HTML from a string
$html_base->load($str);

global $crawled_urls, $found_urls;
$uen=urlencode($base1);
if((array_key_exists($uen,$crawled_urls)==0 || $crawled_urls[$uen] < date("YmdHis",strtotime('-25 seconds', time())))){
  
	$crawled_urls[$uen]=date("YmdHis");
	echo "Product Categories: <br/><br/>";
	foreach($html_base->find('a') as $li){
	// Fix URLs to add the visions.ca to it
	$url=perfect_url($li->href,$base1);
	$enurl=urlencode($url);
	if($url!='' && substr($url,0,4)!="mail" && substr($url,0,4)!="java" && array_key_exists($enurl,$found_urls)==0){
    $found_urls[$enurl]=1;
	
	if (strpos($url, 'Category')){
		// Display Product Category
		echo $li->innertext."<br/>";
		// Crawl each Product Category for Products		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1" );
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_REFERER, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT ,0); 
		curl_setopt($curl, CURLOPT_TIMEOUT, 400);
		curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
		$str = curl_exec($curl);

		curl_close($curl);
		$html_base1 = new simple_html_dom();
		// Load HTML from a product URL
		$html_base1->load($str);
		// Counter so you only get 1 Prodict
		$counter = 0;
		
		foreach($html_base1->find('div[class=contentright]') as $prod)
		{ 			
			if($counter==0){
				echo "Product Details: <br/><br/>";
				echo $prod->find('h2',0)->innertext."<br/>";
				echo "Price: ";
				echo $prod->find('span[class=price]',0)->innertext."<br/>";
				$cart = $prod->find('a[class=addToCart]',0)->innertext;
					if($cart = ""){
						echo "<br/>Out of Stock<br/>";
					}
					else{
						echo "<br/>In Stock<br/>";
					}
				echo "<br/>";
				$counter++;			
			}			
		}
		$html_base1->clear(); 
		unset($html_base1);
	}
   }
  }
 }

$html_base->clear(); 
unset($html_base);

// Below are the URL fixing functions
function perfect_url($u,$b){
	 $bp=parse_url($b);
	 if((@$bp['path']!="/" && @$bp['path']!="") || @$bp['path']==''){
	  if($bp['scheme']==""){
	   $scheme="http";
	  }else{
	   $scheme=$bp['scheme'];
	  }
	  $b=$scheme."://".$bp['host']."/";
	 }
	 if(substr($u,0,2)=="//"){
	  $u="http:".$u;
	 }
	 if(substr($u,0,4)!="http"){
	  $u=rel2abs($u,$b);
	 }
	 return $u;
}

function rel2abs($rel, $base){
	 if (parse_url($rel, PHP_URL_SCHEME) != ''){
	  return $rel;
	 }
	 if ($rel[0]=='#' || $rel[0]=='?'){
	  return $base.$rel;
	 }
	 extract(parse_url($base));
	 $path = preg_replace('#/[^/]*$#', '', $path);
	 if ($rel[0] == '/'){
	  $path = '';
	 }
	 $abs = "$host$path/$rel";
	 $re = array('#(/.?/)#', '#/(?!..)[^/]+/../#');
	 for($n=1; $n>0;$abs=preg_replace($re,'/', $abs,-1,$n)){}
	 $abs=str_replace("../","",$abs);
	 return $scheme.'://'.$abs;
}
?>



