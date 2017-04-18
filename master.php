<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

require('config.php');

$mintconfig = file_get_contents($mintpooldir.'config.json');
$mintyconfig = json_decode($mintconfig, true);

require_once($dependenciesdir.'easybitcoin.php');
require_once($dependenciesdir.'cryptsyapi.php');
require_once($dependenciesdir.'bittrexapi.php');
require_once($dependenciesdir.'poloniexapi.php');
require_once($dependenciesdir.'mintpalapi.php');
require_once($dependenciesdir.'dependencies.php');

$tradingsuccess = "<table><thead><th>Coin Name</th><th>Algo</th><th>Amount</th><th>Exchange</th><th>TX ID</th></thead>";
$tradingerror = "<table><thead><th>Coin Name</th><th>Algo</th><th>Error</th><th>Amount</th><th>Exchange</th></thead>";
$coinexchangetrades = "<table><thead><th>Coin Name</th><th>Algo</th><th>Amount</th><th>Exchange</th><th>ID</th></thead>";

foreach ($poolconfig as $key => $value) {
	if ($value['enabled'] == true) {
		$algorithm = $coinconfigs[$key]['algorithm'];
		$coinname = $coinconfigs[$key]['name'];
		${"${coinname}rpc"} = new Bitcoin($value['wallet']['user'], $value['wallet']['password'], $value['wallet']['host'], $value['wallet']['port']);
		${"${coinname}trading"} = ${"${coinname}rpc"}->getbalance($mintyconfig['shiftProcessing'][$algorithm]['accounts']['trading']);
		$coinsymbol = $coinconfigs[$key]['symbol'];
		$payoutcoin = ProfitData(0)['coins'][$coinsymbol]['trading'][0]['market'];
		if (ProfitData(0)['coins'][$coinsymbol]['trading'][0]['exchange'] == "Cryptsy" && $encryptsy == true) {
			$cryptsyaddress = Cryptsy("getmydepositaddresses");
			$coinaddress = $cryptsyaddress['return'][$coinsymbol];
			if (empty($cryptsyaddress)) { print("Cryptsy address for ".$coinname." has not been generated and has a balance of ".${"${coinname}trading"}."\r\n\r\n");
			} elseif (${"${coinname}trading"} > 0 && $coinaddress) { 
				$txid = ${"${coinname}rpc"}->sendmany($mintyconfig['shiftProcessing'][$algorithm]['accounts']['trading'], array($coinaddress => ${"${coinname}trading"}));
				if (isset(${"${coinname}rpc"}->error)) {
					$tradingerror .= "<tr><td>".$coinname."</td><td>".$algorithm."</td><td>".${"${coinname}rpc"}->error."</td><td>".${"${coinname}trading"}."</td><td>Cryptsy</td></tr>";
					$info = 1;
				} else {
					$redis->zAdd("algoSent", $timenow, '{"symbol":"'.$coinsymbol.'","coinname":"'.$coinname.'","algorithm":"'.$algorithm.'","txid":"'.$txid.'","exchange":"Cryptsy","time":'.time().'}}');
				}
			}
		} elseif (ProfitData(0)['coins'][$coinsymbol]['trading'][0]['exchange'] == "Bittrex" && $enbittrex == true) {
			$bittrexaddress = Bittrex("account/getdepositaddress", array("currency" => $coinsymbol))['result']['Address'];
			if (empty($bittrexaddress)) { print("Bittrex address for ".$coinname." has not been generated and has a balance of ".${"${coinname}trading"}."\r\n\r\n");
			} elseif (${"${coinname}trading"} > 0 && $bittrexaddress) { 
				$txid = ${"${coinname}rpc"}->sendmany($mintyconfig['shiftProcessing'][$algorithm]['accounts']['trading'], array($bittrexaddress => ${"${coinname}trading"}));
				if (isset(${"${coinname}rpc"}->error)) {
					$tradingerror .= "<tr><td>".$coinname."</td><td>".$algorithm."</td><td>".${"${coinname}rpc"}->error."</td><td>".${"${coinname}trading"}."</td><td>Bittrex</td></tr>";
					$info = 1;
				} else {
					$redis->zAdd("algoSent", $timenow, '{"symbol":"'.$coinsymbol.'","coinname":"'.$coinname.'","algorithm":"'.$algorithm.'","txid":"'.$txid.'","exchange":"Bittrex","time":'.time().'}}');
				}
			}
		} elseif (ProfitData(0)['coins'][$coinsymbol]['trading'][0]['exchange'] == "Mintpal" && $enmintpal == true) {
			$mintpaladdress = Mintpal("wallet/depositaddress/".$coinsymbol)['data'];
			if (empty($mintpaladdress)) { print("Mintpal address for ".$coinname." has not been generated and has a balance of ".${"${coinname}trading"}."\r\n\r\n");
			} elseif (${"${coinname}trading"} > 0 && $mintpaladdress) { 
				$txid = ${"${coinname}rpc"}->sendmany($mintyconfig['shiftProcessing'][$algorithm]['accounts']['trading'], array($mintpaladdress => ${"${coinname}trading"}));
				if (isset(${"${coinname}rpc"}->error)) {
					$tradingerror .= "<tr><td>".$coinname."</td><td>".$algorithm."</td><td>".${"${coinname}rpc"}->error."</td><td>".${"${coinname}trading"}."</td><td>Mintpal</td></tr>";
					$info = 1;
				} else {
					$redis->zAdd("algoSent", $timenow, '{"symbol":"'.$coinsymbol.'","coinname":"'.$coinname.'","algorithm":"'.$algorithm.'","txid":"'.$txid.'","exchange":"Mintpal","time":'.time().'}}');
				}
			}
			
		} elseif (ProfitData(0)['coins'][$coinsymbol]['trading'][0]['exchange'] == "Poloniex" && $enpoloniex == true) {
			$poloniexaddress = Poloniex("tradingApi", array("command" => "returnDepositAddresses"))[$coinsymbol];
			if (empty($poloniexaddress)) { print("Poloniex address for ".$coinname." has not been generated and has a balance of ".${"${coinname}trading"}."\r\n\r\n");
			} elseif (${"${coinname}trading"} > 0 && $poloniexaddress) {
				$txid = ${"${coinname}rpc"}->sendmany($mintyconfig['shiftProcessing'][$algorithm]['accounts']['trading'], array($poloniexaddress => ${"${coinname}trading"}));
				if (isset(${"${coinname}rpc"}->error)) {
					$tradingerror .= "<tr><td>".$coinname."</td><td>".$algorithm."</td><td>".${"${coinname}rpc"}->error."</td><td>".${"${coinname}trading"}."</td><td>Poloniex</td></tr>";
					$info = 1;
				} else {
					$redis->zAdd("algoSent", $timenow, '{"symbol":"'.$coinsymbol.'","coinname":"'.$coinname.'","algorithm":"'.$algorithm.'","txid":"'.$txid.'","exchange":"Poloniex","time":'.time().'}}');
				}
			}
		}
		if (!empty($ckey) && $encryptsy == true && $coinsymbol !== "LTC") {
			if (Cryptsy('getinfo')['return']['balances_available'][$coinsymbol] > 0.0000015) {
				$cryptsybalance = Cryptsy("getinfo")['return']['balances_available'][$coinsymbol];
				$cryptmarketid = $payoutcoin.$coinsymbol;
				$cryptsysell = Cryptsy("createorder", array("marketid" => $$cryptmarketid, "ordertype" => "Sell", "quantity" => $cryptsybalance, "price" => ProfitData(0)['coins'][$coinsymbol]['trading'][0]['weightedBid']));
				if ($cryptsysell['success'] == 0) { $cryptsysellresult = $cryptsysell['error']; } else { $cryptsysellresult = $cryptsysell['orderid']; }
				if ($cryptsysell['success'] == 0) { $coinexchangetrades .= "<tr><td>".$coinname."</td><td>".$algorithm."</td><td>".$cryptsybalance."</td><td>Cryptsy</td><td>".$cryptsysellresult."</td></tr>";	$info = 1; }
				if ($cryptsysell['success'] == 1) { $redis->sAdd('algoTrading:'.$algorithm, '{"symbol":"'.$coinsymbol.'","coinname":"'.$coinname.'","algorithm":"'.$algorithm.'","orderid":"'.$cryptsysellresult.'","exchange":"Cryptsy","time":'.$timenow.',"marketid":'.$$cryptmarketid.',"payoutcoin":"'.$payoutcoin.'"}'); }
			}
		}
		if (!empty($bkey) && $enbittrex = true && $coinsymbol !== "LTC") {
			if (Bittrex("account/getbalance", array("currency" => $coinsymbol))['result']['Available'] > 0.0000015) {
				$bittrexbalance = Bittrex("account/getbalance", array("currency" => $coinsymbol))['result']['Available'];
				$bittrexcoinsymbol = $payoutcoin.'-'.$coinsymbol;
				$bittrexsell = Bittrex("market/selllimit", array("market" => $bittrexcoinsymbol, "quantity" => $bittrexbalance, "rate" => ProfitData(0)['coins'][$coinsymbol]['trading'][0]['weightedBid']));
				if ($bittrexsell['success'] !== true) { $coinexchangetrades .= "<tr><td>".$coinname."</td><td>".$algorithm."</td><td>".$bittrexbalance."</td><td>Bittrex</td><td>".$bittrexsell['message']."</td></tr>"; $info = 1; }
				if ($bittrexsell['success'] == true) { $redis->sAdd('algoTrading:'.$algorithm, '{"symbol":"'.$coinsymbol.'","coinname":"'.$coinname.'","algorithm":"'.$algorithm.'","orderid":"'.$bittrexsell['result']['uuid'].'","exchange":"Bittrex","time":'.$timenow.',"payoutcoin":"'.$payoutcoin.'"}'); }
			}
		}
		if (!empty($mkey) && $enmintpal == true && $coinsymbol !== "LTC") {
			if (Mintpal('wallet/balances/'.$coinsymbol)['data'][0]['balance_available'] > 0.0000015) {
				$mintpalbalance = Mintpal('wallet/balances/'.$coinsymbol)['data'][0]['balance_available'];
				if ((ProfitData(0)['coins'][$coinsymbol]['trading'][0]['weightedBid'] * $mintpalbalance) > 0.0001) {
					$mintpalsell = Mintpal("trading/order", array("coin" => $coinsymbol, "exchange" => $payoutcoin, "price" => ProfitData(0)['coins'][$coinsymbol]['trading'][0]['weightedBid'], "amount" => $mintpalbalance, "type" => 1), "POST");
					if ($mintpalsell['status'] == "success") { $mintpalsellresult = $mintpalsell['data']['order_id']; } else { $mintpalsellresult = $mintpalsell['message']; }
					if ($mintpalsell['status'] !== "success") { $coinexchangetrades .= "<tr><td>".$coinname."</td><td>".$algorithm."</td><td>".$mintpalbalance."</td><td>Mintpal</td><td>".$mintpalsellresult."</td></tr>"; $info = 1; }
					if ($mintpalsell['status'] == "success") { $redis->sAdd('algoTrading:'.$algorithm, '{"symbol":"'.$coinsymbol.'","coinname":"'.$coinname.'","algorithm":"'.$algorithm.'","orderid":"'.$mintpalsellresult.'","exchange":"Mintpal","time":'.$timenow.',"payoutcoin":"'.$payoutcoin.'"}'); }
				}
			}
		}
		if (!empty($pkey) && $enpoloniex == true && $coinsymbol !== "LTC") {
			if (Poloniex("tradingApi", array("command" => "returnBalances"))[$coinsymbol] > 0.0000015) {
				$poloniexbalance = Poloniex("tradingApi", array("command" => "returnBalances"))[$coinsymbol];
				$polocoinsymbol = $payoutcoin.'_'.$coinsymbol;
				$polorate = Poloniex("public", array("command" => "returnTicker"))[$polocoinsymbol]['highestBid'];
				if (($polorate * $poloniexbalance) > 0.0001) {
				$polosellcoin = Poloniex("tradingApi", array("command" => "sell", "currencyPair" => $polocoinsymbol, "rate" => $polorate, "amount" => $poloniexbalance));
				if (empty($polosellcoin['orderNumber']) == true) { $coinexchangetrades .= "<tr><td>".$coinname."</td><td>".$algorithm."</td><td>".$poloniexbalance."</td><td>Poloniex</td><td>".$polosellcoin['error']."</td></tr>"; $info = 1; }
				if (! empty($polosellcoin['orderNumber'])) { $redis->sAdd('algoTrading:'.$algorithm, '{"symbol":"'.$coinsymbol.'","coinname":"'.$coinname.'","algorithm":"'.$algorithm.'","orderid":"'.$polosellcoin['orderNumber'].'","exchange":"Poloniex","time":'.$timenow.',"payoutcoin":"'.$payoutcoin.'"}'); }
				}
			}
		}
	}
}
//Get Order Balances IF they are complete This should be accurate right down to the last 0.0000000000000000000001 statoshi.
foreach ($mintyconfig['proxy'] as $key => $value) {
	if ($value['enabled'] == true) {
		foreach ($redis->sMembers("algoTrading:".$key) as $key => $value) {
			$rawsvalue = $value;
			$decoded = json_decode($value, true);
			if ($decoded['exchange'] == "Cryptsy") {
				$activeorder = null;
				foreach (Cryptsy("myorders", array("marketid" => $decoded['marketid']))['return'] as $key => $value) {
					if ($value['orderid'] === $decoded['orderid']) {
						$activeorder = 1;
					} elseif (empty($decoded['orderid']) == true) {
						//HERE FOR VALIDATION OF DATABASE DATA, there must be an order ID otherwise balances are going to be off majorly...
						$activeorder = 1;
					}
				}
				if ($activeorder == null) {
					$amount = 0;
					$i = 0;
					foreach (Cryptsy("mytrades", array("marketid" => $decoded['marketid']))['return'] as $key => $value) {
						if ($value['order_id'] == $decoded['orderid']) {
							$amount = $amount + ($value['total'] - $value['fee']);
							$present = 1;
						}
					}
					if ($present = 1) {
						$redis->zAdd("algoTraded:".$decoded['algorithm'], $timenow,'{"symbol":"'.$decoded['symbol'].'","coinname":"'.$decoded['coinname'].'","algorithm":"'.$decoded['algorithm'].'","orderid":"'.$decoded['orderid'].'","exchange":"Cryptsy","time":'.$timenow.',"amount":'.$amount.',"payoutcoin":"'.$decoded['payoutcoin'].'"}');
						$redis->incrBy("exchBals:".$decoded['algorithm'].":".$decoded['payoutcoin'].":Cryptsy", $amount * 100000000);
						$redis->sRem("algoTrading:".$decoded['algorithm'], $rawsvalue);
					}
				}
			}
			elseif ($decoded['exchange'] == "Mintpal") {
				$activeorder = null;
				if (Mintpal("trading/order/".$decoded['orderid'])['status'] == "success") {
						$activeorder = 1;
				} elseif (empty($decoded['orderid']) == true) {
					//HERE FOR VALIDATION OF DATABASE DATA, there must be an order ID otherwise balances are going to be off majorly...
					$activeorder = 1;
				}
				if ($activeorder == null) {
					$amount = 0;
					$present = 0;
					$i = 0;
					foreach (Mintpal("trading/trades/".$decoded['symbol']."/0/99")['data'] as $key => $value) {
						if ($value['order_id'] === $decoded['orderid']) {
							$amount = $amount + $value['total'];
							$present = 1;
						}
					}
					if ($present = 1) {
						$redis->zAdd("algoTraded:".$decoded['algorithm'], $timenow, '{"symbol":"'.$decoded['symbol'].'","coinname":"'.$decoded['coinname'].'","algorithm":"'.$decoded['algorithm'].'","orderid":"'.$decoded['orderid'].'","exchange":"Mintpal","time":'.$timenow.',"amount":'.$amount.',"payoutcoin":"'.$decoded['payoutcoin'].'"}');
						$redis->incrBy("exchBals:".$decoded['algorithm'].":".$decoded['payoutcoin'].":Mintpal", $amount * 100000000);
						$redis->sRem("algoTrading:".$decoded['algorithm'], $rawsvalue);
					}
				}
			}
			elseif ($decoded['exchange'] == "Bittrex") {
				foreach (Bittrex("account/getorderhistory", array("market" => $decoded['payoutcoin']."-".$decoded['symbol'], "count" => 100))['result'] as $key => $value) {
					if ($value['OrderUuid'] === $decoded['orderid'] && $value['QuantityRemaining'] == 0) {
						$amount = $value['Price'] - $value['Commission'];
						$redis->zAdd("algoTraded:".$decoded['algorithm'], $timenow, '{"symbol":"'.$decoded['symbol'].'","coinname":"'.$decoded['coinname'].'","algorithm":"'.$decoded['algorithm'].'","orderid":"'.$decoded['orderid'].'","exchange":"Bittrex","time":'.$timenow.',"amount":'.$amount.',"payoutcoin":"'.$decoded['payoutcoin'].'"}');
						$redis->incrBy("exchBals:".$decoded['algorithm'].":".$decoded['payoutcoin'].":Bittrex", $amount * 100000000);
						$redis->sRem("algoTrading:".$decoded['algorithm'], $rawsvalue);
					}
				}
			}
			elseif ($decoded['exchange'] == "Poloniex") {
				$activeorder = null;
				foreach (Poloniex("tradingApi", array("command" => "returnOpenOrders", "currencyPair" => $decoded['payoutcoin'].'_'.$decoded['symbol'])) as $key => $value) {
					if ($value['orderNumber'] === $decoded['orderid']) {
						$activeorder = 1;
					} elseif (empty($decoded['orderid']) == true) {
						//THIS IS AGAIN FOR DATABASE VALIDATION.
						$activeorder = 1;
					}
				}
				if ($activeorder == null) {
					$i = 0;
					$amount = 0;
					foreach (Poloniex("tradingApi", array("command" => "returnTradeHistory", "currencyPair" => $decoded['payoutcoin'].'_'.$decoded['symbol'])) as $key => $value) {
						if ($value['orderNumber'] === $decoded['orderid']) {
							$amount = $amount + $value['total'];
							$present = 1;
						}
					}
					if ($present = 1) {
						$amount = $amount * 0.998;
						$redis->zAdd("algoTraded:".$decoded['algorithm'], $timenow, '{"symbol":"'.$decoded['symbol'].'","coinname":"'.$decoded['coinname'].'","algorithm":"'.$decoded['algorithm'].'","orderid":"'.$decoded['orderid'].'","exchange":"Poloniex","time":'.$timenow.',"amount":'.$amount.',"payoutcoin":"'.$decoded['payoutcoin'].'"}');
						$redis->incrBy("exchBals:".$decoded['algorithm'].":".$decoded['payoutcoin'].":Poloniex", $amount * 100000000);
						$redis->sRem("algoTrading:".$decoded['algorithm'], $rawsvalue);
					}
				}
			}
		}
	}
}

foreach ($mintyconfig['proxy'] as $key => $value) {
	if ($value['enabled'] == true) {
		if ($redis->Get('exchBals:'.$key.':LTC:Cryptsy') > 0) {
			$redisbalance = $redis->Get('exchBals:'.$key.':LTC:Cryptsy');
			$coinbalance = $redis->Get('exchBals:'.$key.':LTC:Cryptsy') / 100000000;
			$priceper = $BTCLTCraw['last_trade'] * 0.8; //I've done this because I found my LTC was not selling as quickly, you still get the best price for it though.
			$cryptsysell = Cryptsy("createorder", array("marketid" => $BTCLTC, "ordertype" => "Sell", "quantity" => $coinbalance, "price" => $priceper));
			if ($cryptsysell['success'] == 0) { $cryptsysellresult = $cryptsysell['error']; } else { $cryptsysellresult = $cryptsysell['orderid']; }
			if ($cryptsysell['success'] == 0) { $coinexchangetrades .= "<tr><td>Litecoin</td><td>".$key."</td><td>".$coinbalance."</td><td>Cryptsy</td><td>".$cryptsysellresult."</td></tr>";	$info = 1; }
			if ($cryptsysell['success'] == 1) { 
			$redis->decrBy('exchBals:'.$key.':LTC:Cryptsy', $redisbalance);
			$redis->sAdd('algoTrading:'.$key, '{"symbol":"LTC","coinname":"Litecoin","algorithm":"'.$key.'","orderid":"'.$cryptsysellresult.'","exchange":"Cryptsy","time":'.$timenow.',"marketid":'.$BTCLTC.',"payoutcoin":"BTC"}'); 
			}
		}
		if ($redis->Get('exchBals:'.$key.':LTC:Bittrex') > 0) {
			$redisbalance = $redis->Get('exchBals:'.$key.':LTC:Bittrex');
			$coinbalance = $redis->Get('exchBals:'.$key.':LTC:Bittrex') / 100000000;
			$priceper = Bittrex("public/getorderbook", array("market" => "BTC-LTC", "type" => "sell", "depth" => 4))['result'][4]['Rate'];
			$bittrexsell = Bittrex("market/selllimit", array("market" => "BTC-LTC", "quantity" => $coinbalance, "rate" => $priceper));
			if ($bittrexsell['success'] !== true) { $coinexchangetrades .= "<tr><td>Litecoin</td><td>".$key."</td><td>".$coinbalance."</td><td>Bittrex</td><td>".$bittrexsell['message']."</td></tr>"; $info = 1; }
			if ($bittrexsell['success'] == true) { 
			$redis->decrBy('exchBals:'.$key.':LTC:Bittrex', $redisbalance);
			$redis->sAdd('algoTrading:'.$key, '{"symbol":"LTC","coinname":"Litecoin","algorithm":"'.$key.'","orderid":"'.$bittrexsell['result']['uuid'].'","exchange":"Bittrex","time":'.timenow.',"payoutcoin":"BTC"}');
			}
		}
		if ($redis->Get('exchBals:'.$key.':LTC:Mintpal') > 0) {
			$redisbalance = $redis->Get('exchBals:'.$key.':LTC:Mintpal');
			$coinbalance = $redis->Get('exchBals:'.$key.':LTC:Mintpal') / 100000000;
			$priceper = Mintpal("market/stats/LTC/BTC")['data']['top_bid'];
			$mintpalsell = Mintpal("trading/order", array("coin" => "LTC", "exchange" => "BTC", "price" => $priceper, "amount" => $coinbalance, "type" => 1), "POST");
			if ($mintpalsell['status'] == "success") { $mintpalsellresult = $mintpalsell['data']['order_id']; } else { $mintpalsellresult = $mintpalsell['message']; }
			if ($mintpalsell['status'] !== "success") { $coinexchangetrades .= "<tr><td>Litecoin</td><td>".$key."</td><td>".$coinbalance."</td><td>Mintpal</td><td>".$mintpalsellresult."</td></tr>"; $info = 1; }
			if ($mintpalsell['status'] == "success") { 
			$redis->decrBy('exchBals:'.$key.':LTC:Mintpal', $redisbalance);
			$redis->sAdd('algoTrading:'.$key, '{"symbol":"LTC","coinname":"Litecoin","algorithm":"'.$key.'","orderid":"'.$mintpalsellresult.'","exchange":"Mintpal","time":'.timenow.',"payoutcoin":"BTC"}');
			}
		}
		if ($redis->Get('exchBals:'.$key.':LTC:Poloniex') > 0) {
			$redisbalance = $redis->Get('exchBals:'.$key.':LTC:Poloniex');
			$coinbalance = $redis->Get('exchBals:'.$key.':LTC:Poloniex') / 100000000;
			$polocoinsymbol = "BTC_LTC";
			$polorate = Poloniex("public", array("command" => "returnTicker"))['LTC']['highestBid'];
			$polosellcoin = Poloniex("tradingApi", array("command" => "sell", "currencyPair" => $polocoinsymbol, "rate" => $polorate, "amount" => $coinbalance));
			if (empty($polosellcoin['orderNumber']) == true) { $coinexchangetrades .= "<tr><td>Litecoin</td><td>".$key."</td><td>".$coinbalance."</td><td>Poloniex</td><td>".$polosellcoin['error']."</td></tr>"; $info = 1; }
			if (! empty($polosellcoin['orderNumber'])) { 
			$redis->decrBy('exchBals:'.$key.':LTC:Poloniex', $redisbalance);
			$redis->sAdd('algoTrading:'.$key, '{"symbol":"LTC","coinname":"Litecoin","algorithm":"'.$key.'","orderid":"'.$polosellcoin['orderNumber'].'","exchange":"Poloniex","time":'.timenow.',"payoutcoin":"BTC"}');
			}
		}
	}
}

$tradingsuccess .= "</table>";
$tradingerror .= "</table>";
$coinexchangetrades .= "</table>";
$headers = 'From: CoinTrader' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();
$headers .= "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
$message = "
<!DOCTYPE html>
        <html lang='en-us'>
            <head>
                <meta charset='utf-8'>
                <title>Information of Latest Trades</title>
                <style type='text/css'>
                    table {
						width: 100%;
						border: 1px solid black;
					}
					thead {
						background: #eee;
					td {
						padding: 3px;
					}
					tr:nth-child(even) {
						background: #eee;
					}
                </style>
            </head>
            <body>
			Successful Trades<br>".$tradingsuccess."<br>Trading Errors<br>".$tradingerror."<br>Exchange Coins to BTC/LTC Results<br>".$coinexchangetrades."
			</body>
        </html>";
$message = wordwrap($message, 70, "\r\n");
if (!empty($info)) {
mail($emailaddr, 'Information on Latest Trades', $message, $headers);
}
?>
