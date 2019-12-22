<?php

namespace App\Foundation\Clients;

use Exception;
use JsonRPC\Client;

/**
 * Class Base
 * @package App\Foundation\Clients
 *
 * @method Client withPositionalArguments()
 * @method HttpClient getHttpClient()
 * @method Client authentication($username, $password)
 * @method Client batch()
 * @method Exception|Client send()
 * @method Exception|Client execute($procedure, array $params = [], array $reqattrs = [], $requestId = null, array $headers = [])
 * @method mixed getbalance(string $account, int $minconf = 6, bool $include_watchonly = false)
 * @method mixed listtransactions(string $account, int $count = 100, int $skip = 0, bool $include_watchonly = false)
 * @method mixed sendfrom(string $fromaccount, string $toaddress, int $amount, string $comment = null, string $comment_to = null)
 * @method mixed getnewaddress(string $account)
 * @method mixed getaddressesbyaccount(string $account)
 * @method mixed getinfo()
 * @method int getblockcount()
 * @method string getblockhash(int $height)
 * @method mixed getblock(string $blockhash, bool $verbose = true)
 * @method mixed getrawtransaction(string $txid, int $verbose = 1)
 * @method mixed sendtoaddress(string $address, float $amount, string $comment = "", string $comment_to = "", bool $subtractfeefromamount = false)
 * @method mixed validateaddress(string $address)
 *
 */
abstract class Base
{
    public $client;
    protected $schema;
    protected $host;
    protected $port;
    protected $user;
    protected $password;

    public function __construct($rpc_info)
    {
        if (is_string($rpc_info)) {
            $url = parse_url($rpc_info);
            $this->schema = ($url["scheme"] ?? "http") . "://";
            $this->host = $url["host"] ?? $url["path"] ?? "127.0.0.1";
            $this->port = $url["port"] ?? 80;
            $this->user = $url["user"] ?? "username";
            $this->password = $url["pass"] ?? "password";
        } else if (is_array($rpc_info)) {
            $url = parse_url($rpc_info["host"]);
            $this->schema = ($url["scheme"] ?? "http") . "://";
            $this->host = $url["host"] ?? $url["path"] ?? "127.0.0.1";
            $this->port = $url["port"] ?? $rpc_info["port"] ?? 80;
            $this->user = $url["user"] ?? $rpc_info["user"] ?? "username";
            $this->password = $url["pass"] ?? $rpc_info["pass"] ?? "password";
        }
        $url = "$this->schema$this->host:$this->port";
        $this->client = new Client($url, false, new HttpClient($url));
        $this->client->authentication($this->user, $this->password);
    }

    public function __call($name, $arguments)
    {
        return $this->client->$name(...$arguments);
    }
}
