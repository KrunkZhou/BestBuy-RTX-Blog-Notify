<?php

// BestBuy 30系显卡爬虫
// 使用 UptimeRobot 定时访问此 PHP
// Powered by KRUNK.CN

// Config
$url = "https://blog.bestbuy.ca/best-buy/nvidia";
$bbxpath = "/html/body/div[6]/div[2]/div/article/div[1]/div/div/header/div/span/time";

// 邮件API
// Define Email Api in this function
function sendMail($date){
	$receiver="";
	$pass="";
	$token = file_get_contents("https://api.krunk.cn/token/?pass=".$pass); //get token
	$head="BestBuy_Restock";
	$content=str_replace(' ', '_', $date);
	$url="https://api.krunk.cn/sendmail/mail.php?token=".$token."&receiver=".$receiver."&head=".$head."&content=".$content; //set url
	$send = file_get_contents($url); //send and get feedback

	if ($send[0]=='1')
		return true;
	else
		return false;
}

include('kdb.class.php');
$db = new kdb();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

$html = file_get_contents($url);

$date_db = $db->find_one('bestbuy',array('date' => "1"));

if ($date_db){
	$currentDate = $date_db[key($date_db)]['current'];
}else{
	$currentDate = "December 0, 1998";
}

if ($html){
	libxml_use_internal_errors(true);
	$html=str_replace('&nbsp;','',$html);
	$html=str_replace('<br/>','-kbr-',$html);
	$dom = new DOMDocument();
	$dom->loadHTML($html);
	$xpath = new DOMXPath($dom);

	$date = $xpath->query($bbxpath)->item(0)->nodeValue;

	if (!strcmp($date, $currentDate)){
		echo "None";
	}else{
		echo "Updated " . $date;

		//邮件API
		$sent = sendMail($date);

		if ($sent){
			echo "<br>Mail Success";
			$data = array("date"=>"1",
						"current"=>$date);

			if ($date_db){
				$db->update('bestbuy',$data,key($date_db));
			}else{
				$db->insert('bestbuy',$data);
			}
		}else{
			echo "<br>Mail Failed: ";
			echo $send;
		}
	}
}

?>

