<?php

require_once('vendor/autoload.php');


date_default_timezone_set('Europe/Bratislava');


function sendAlert($msg) {
    $webhook_uri        = 'sem si prdni svoj webhook na slack alebo mattermost';
    $user               = NULL;
    $client             = new \GuzzleHttp\Client(['base_uri' => $webhook_uri]);
    $mattermost_webhook = new \Nutama\MattermostWebhook\MattermostWebhook($client);
    $message            = new \Nutama\MattermostWebhook\Message($msg);
    if (isset($user)) {
        $message->setChannel('@'.$user);
    }

    $mattermost_webhook->send($message);
    return 1;
}


$url='https://mojeezdravie.nczisk.sk/api/v1/web/get_all_drivein_times_vacc';


$f1=file_get_contents($url);
$f2=file_get_contents('./get_all_drivein_times_vacc.old');
if ($f1!=$f2){
    echo "BACHA ZMENA\n";
    // zaloha pre buduce porovnanie
    file_put_contents('./get_all_drivein_times_vacc.old',$f1);
    // zaloha pre archivaciu a casovu rekonstrukciu
    file_put_contents('./get_all_drivein_times_vacc.'.date("Ymd-His").'.bak',$f1);
}else{
    echo "NIVL STÁBL. bez-peremen\n";
    die();
}

$data=json_decode($f1)->payload;
$spolu=0;
$miesta='';

foreach($data as $centrum){
    $x=$centrum->title."\n";
    $free=0;
    foreach($centrum->calendar_data as $cd){
	$free=$free+$cd->free_capacity;
    }
    $x=$x."\tVolne: [$free]\n";
    if ($free!=0){
	echo $x;
	$spolu=$spolu+$free;
	$miesta.="| ".$centrum->title." | ".$free." |\n";
    }
}

    $msg="## Pozor zmena vo volnych miestach";
    $msg.= "\n\n| Miesto | Volných slotov |
| :------------ |  ---------:|
".$miesta."\n\n** Spolu volnych slotov: $spolu ** \n\nRegistrácia je možná na [www.old.korona.gov.sk](https://www.old.korona.gov.sk/covid-19-vaccination-form.php)";
    sendAlert($msg);

