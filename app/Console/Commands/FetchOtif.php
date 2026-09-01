<?php

namespace App\Console\Commands;

use App\Services\OtifFetcher;
use Illuminate\Console\Command;

class FetchOtif extends Command
{
    protected $signature = 'otif:fetch';

    protected $description = 'Pull the current order backlog from Epicor into a new OTIF snapshot';

    public function handle(OtifFetcher $fetcher): int
    {
        if (config('otif.source') !== 'epicor') {
            $this->warn("OTIF_SOURCE is not 'epicor' — skipping live fetch (using sample/seeded data).");

            return self::SUCCESS;
        }

        $snapshot = $fetcher->fetch();

        if ($snapshot->error) {
            $this->error('OTIF fetch failed: '.$snapshot->error);

            return self::FAILURE;
        }

        $this->info("OTIF snapshot #{$snapshot->id} captured with {$snapshot->companies->count()} business units.");

        return self::SUCCESS;
    }
}
