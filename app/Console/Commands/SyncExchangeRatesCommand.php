<?php

namespace App\Console\Commands;

use App\Services\Financial\CurrencyExchangeService;
use Illuminate\Console\Command;

class SyncExchangeRatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and synchronize foreign currency exchange rates relative to the base currency';

    /**
     * Execute the console command.
     */
    public function handle(CurrencyExchangeService $service): int
    {
        $this->info('Fetching live foreign exchange rates...');

        $result = $service->syncLiveRates();

        if ($result['success']) {
            $this->info($result['message']);
            $this->table(
                ['Currency', 'Rate to Base'],
                collect($result['synced'])->map(fn ($rate, $code) => [$code, number_format($rate, 6)])->values()
            );

            return Command::SUCCESS;
        }

        $this->error($result['message']);

        return Command::FAILURE;
    }
}
