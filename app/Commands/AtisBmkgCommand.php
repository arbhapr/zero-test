<?php

namespace App\Commands;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use LaravelZero\Framework\Commands\Command;

class AtisBmkgCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'atis:info {icao : ICAO of airport}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = "Get METAR information of selected airport's ICAO from https://aviation.bmkg.go.id/latest/station.xml";

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $icao = $this->argument("icao");
        $bmkgUrl = "https://aviation.bmkg.go.id/latest/station.xml";
        $cacheKey = "atis:" . $icao;

        try {

            $xmlString = file_get_contents($bmkgUrl);
            $xmlObject = simplexml_load_string($xmlString);
            $rawJson = json_encode($xmlObject);
            $rawObject = json_decode($rawJson);
            $rawStations = collect(array_values($rawObject->report));

            $station = $rawStations->filter(function ($item) use ($icao) {
                return $item->icao_id == strtoupper($icao);
            })->first();

            $formatted_text = "--------------------- Report Time: " . Carbon::parse($rawObject->report_time)->format('d/m/Y H:i:s') . " ---------------------\n";
            $formatted_text .= "Station ICAO   : " . $station->icao_id . "\n";
            $formatted_text .= "Station Name   : " . $station->station_name . "\n";
            $formatted_text .= "Station METAR  : " . ($station->last_observation ?? '-') . "\n";

            $this->info($formatted_text);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
