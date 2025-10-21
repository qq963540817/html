<?php
error_reporting(0);
header('Content-type: text/json;charset=utf-8');

$v = $_GET['v'] ?? '';
if (!$v or $v == 'null') {
die ("{\n    \"code\": \"400\",\n    \"success\": \"0\",\n    \"msg\": \"url为空\"\n}");}

$ipInfo = 1; // 值为 1 则自动获取当前IP信息, 0 则是关闭
//$ipInfo = 'CMNET|BeiJing-36.137.240.30'; // 自定义示例：北京移动

$origin = 'https://jx.hls.one';
$apis = ['https://202.189.8.170/Api.js','https://cache.hls.one/xmflv.js'];
$area = ipInfo($ipInfo);
foreach ($apis as $api) {
	$time = round(microtime(true)*1000);
	$data = curl($api,'wap=0&url='.$v.'&time='.$time.'&key='.urlencode(sign(md5($time.$v))).$area,$origin);
	if ($data) {break;}
 }
$data = json_decode($data);

$ip = $data->ip;
$iptime = $data->iptime;
$key = $data->aes_key;
$iv = $data->aes_iv;

$vurl = openssl_decrypt($data->url,'AES-128-CBC',$key,0,$iv);
$html = openssl_decrypt($data->html,'AES-128-CBC',$key,0,$iv);

if($html){  preg_match_all('/<div class=\"title\">(.*?)<\/div>|<div class=\"title-info\">(.*?)<\/div>/is',$html,$html2); }

if(!$vurl[0][0] or $vurl == 'https://kjjsaas-sh.oss-cn-shanghai.aliyuncs.com/u/3401405881/20240818-936952-fc31b16575e80a7562cdb1f81a39c6b0.mp4'){
$msg = $data->msg;
$msg = preg_replace("/<br>/i"," ",$msg);
die ("{\n    \"code\": \"404\",\n    \"success\": \"0\",\n    \"msg\": \"解析失败\",\n    \"Server_msg\": \"".$msg."\"\n}");}
$vurl = preg_replace("/name=XMFLV&/i","",$vurl);

die(json_encode(["code" => 200,"success" => 1,"msg" => "解析成功","ip" => $ip,"iptime" => $iptime,"url" => $vurl,"name" => $html2[1][0] ,"synopsis" => $html2[2][1],"header" => ["Origin" => $origin,"User-Agent" => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36"]],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));

function sign($data) {
    $key = md5($data);
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 2, 'https://t.me/xmf'); 
    return $encrypted;
}

function encodeURI($url) {
    $encoded = rawurlencode($url);
    $revert = ['%21' => '!', '%2A' => '*', '%27' => "'", 
        '%28' => '(', '%29' => ')', '%3B' => ';', 
        '%2C' => ',', '%2F' => '/', '%3F' => '?', 
        '%3A' => ':', '%40' => '@', '%26' => '&', 
        '%3D' => '=', '%2B' => '+', '%24' => '$', 
        '%23' => '#'];
    return strtr($encoded, $revert);
}

function my_domain() {
    $protocol = 'http';
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {$protocol = 'https';}
    if (isset($_SERVER['HTTP_HOST'])) {$host = $_SERVER['HTTP_HOST'];}
    else {$host = '';}
    return $protocol . '://' . $host;
}

function curl($url,$postData=null,$origin=0){
$ch = curl_init();
curl_setopt_array($ch, [
	CURLOPT_URL => $url,
	CURLOPT_TIMEOUT => 30,
	CURLOPT_CONNECTTIMEOUT => 30,
	CURLOPT_ENCODING => '',
	CURLOPT_SSL_VERIFYPEER => 0,
	CURLOPT_SSL_VERIFYHOST => 0,
	CURLOPT_RETURNTRANSFER => 1,
	CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'
]);
if($postData){
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postData); }
if($origin){
	curl_setopt($ch, CURLOPT_HTTPHEADER, ['origin: '.$origin.'']); }
$response = curl_exec($ch);
curl_close($ch); 
return $response;
}

function ipInfo($ipInfo = 0){
    if($ipInfo == 1){
    $dat = curl('https://data.video.iqiyi.com/v.f4v');
    return '&area='.json_decode($dat)->t??'';}
    elseif($ipInfo){return '&area='.$ipInfo;}
    else{return '';}
}