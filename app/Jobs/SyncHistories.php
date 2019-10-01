<?php

namespace App\Jobs;

use App\Exceptions\CoinHistoryAPIException;
use App\Exceptions\MissingCoinInformationException;
use App\Models\Coin;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncHistories implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $coin;

    /**
     * Create a new job instance.
     *
     * @param Coin $coin
     */
    public function __construct(Coin $coin = null)
    {
        $this->coin = $coin;
        $this->queue = "syncHistory";
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws MissingCoinInformationException
     * @throws CoinHistoryAPIException
     */
    public function handle()
    {
        if (!$this->coin) {
            $active_coin_ids = DB::table('jobs')->where('id', '!=', $this->job->getJobId())->where('queue', $this->queue)->get('payload')->map(function ($v) {
                return unserialize(json_decode($v->payload, true)["data"]["command"])->coin->id;
            });

            Coin::whereNotIn('id', $active_coin_ids)->notFiat()->where('enable', true)->get(["id", "key"])->each(function ($coin) {
                self::dispatch($coin);
            });
            return;
        }

        try {
            $client = new Client();
            $response = $client->get("http://api.coingecko.com/api/v3/coins/{$this->coin->key}?localization=false&tickers=false&market_data=true&community_data=false&developer_data=false&sparkline=false");
            $data = json_decode($response->getBody(), true);
            $price = $data["market_data"]["current_price"]["usd"];
            $change = $data["market_data"]["price_change_percentage_24h"];
            $created_at = strtotime($data["market_data"]["last_updated"]);

            $this->coin->histories()->create(compact("price", "change", "created_at"));

            self::dispatch($this->coin)->delay(60 * 5);
        } catch (ClientException $ex) {
            Log::error("[{$this->coin->id}|{$this->coin->name}] Server response invalid. " . $ex->getResponse()->getBody());
        }
    }
}
