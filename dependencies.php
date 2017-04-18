<?php
$redis = new Redis();
$redis->connect('127.0.0.1');
$profitdata = $redis->sMembers('profitData');
function ProfitData($key) {
        global $profitdata;
        $decoded = json_decode($profitdata[$key], true);
        return $decoded;
}
$poolconfigs = array_diff(scandir($mintpooldir.'pool_configs'), array('..', '.')); //get all the config files from mintypool pool_configs dir and remove the . and .. array entries
$i = 0; //here for array rewriting

//get pool configs and remake the array to remove the . and .. entries from scandir
foreach ($poolconfigs as $key => $value) {
	$contents = file_get_contents($mintpooldir.'pool_configs/'.$value);
	$poolconfig[$i++] = json_decode($contents, true);
}
$i = 0; //here for array rewriting
foreach ($poolconfigs as $key => $value) {
	$coincontents = $contents = file_get_contents($mintpooldir.'coins/'.$value);
	$coinconfigs[$i++] = json_decode($coincontents, true);
}
if ($encryptsy == true) {
	foreach(Cryptsy('getmarkets')['return'] as $key => $value) {
		$marketsymbols = $value['secondary_currency_code'].$value['primary_currency_code'];
		$$marketsymbols = $value['marketid'];
		${"${marketsymbols}raw"} = $value;
	}
}
$timenow = time();
?>
