<?php

namespace App\Foundation\Clients;

use Illuminate\Support\Facades\Cache;

class Fake extends Base
{
    public $client;
    private $cache;

    public function __construct()
    {
        $this->client = $this;
        $this->cache = Cache::remember("coin.faker", 60 * 60, function () {
            $data = [
                "getinfo" => [
                    "version" => 3000000,
                    "protocolversion" => 70201,
                    "walletversion" => 61000,
                    "balance" => 0.0,
                    "obfuscation_balance" => 0.0,
                    "blocks" => 384555,
                    "timeoffset" => 0,
                    "connections" => 125,
                    "proxy" => "",
                    "difficulty" => 2012.79773099,
                    "testnet" => true,
                    "keypoololdest" => 1554325171,
                    "keypoolsize" => 1001,
                    "paytxfee" => 0.0,
                    "relayfee" => 0.0001,
                    "staking status" => "Staking Not Active",
                    "errors" => "",
                ],
                "listtransactions" => [
                    "" => []
                ],
                "getbalance" => [
                    "" => 0.0
                ],
                "getaddressesbyaccount" => [
                    "" => []
                ],
            ];
            return $data;
        });
    }

    public function __call($name, $arguments)
    {
        return $this->cache[$name] && $this->cache[$name][$arguments[0]] ? $this->cache[$name][$arguments[0]] : ($this->cache[$name] ?? null);
    }

    public function help()
    {
        return <<<Help
== FAKER ==
getpoolinfo
listmasternodes

== Blockchain ==
getbestblockhash
getblock "hash" ( verbose )
getblockchaininfo
getblockcount
getblockhash index
getblockheader "hash" ( verbose )
getchaintips
getdifficulty
getmempoolinfo
getrawmempool ( verbose )
gettxout "txid" n ( includemempool )
gettxoutsetinfo
verifychain ( checklevel numblocks )

== Control ==
getinfo
help ( "command" )

== Mining ==
getblocktemplate ( "jsonrequestobject" )
getmininginfo
getnetworkhashps ( blocks height )

== Network ==
getconnectioncount
getnettotals
getnetworkinfo
getpeerinfo

== Wallet ==
getaccount "acraddress"
getaccountaddress "account"
getaddressesbyaccount "account"
getbalance ( "account" minconf includeWatchonly )
getnewaddress ( "account" )
gettransaction "txid" ( includeWatchonly )
getwalletinfo
listaccounts ( minconf includeWatchonly)
sendfrom "fromaccount" "toacraddress" amount ( minconf "comment" "comment-to" )
Help;
    }

    public function getnewaddress($account)
    {
        $addresses = &$this->cache["getaddressesbyaccount"];

        srand(time());
        $address = implode(array_map(function ($i) {
            return chr($i % 3 == 0 ? rand(48, 57) : ($i % 3 == 1 ? rand(65, 90) : rand(97, 112)));
        }, range(0, 33)));

        if ($addresses[$account]) {
            $addresses[$account][] = $address;
        } else {
            $addresses[] = [$account => [$address]];
        }

        Cache::put("coin.faker", $this->cache, 60 * 60);

        return $address;
    }

    public function sendfrom()
    {
        return true;
    }
}
