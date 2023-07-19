<?php

namespace App\Console\Commands\Adverts;

use App\Models\Adverts\Property;
use Illuminate\Console\Command;
use MeiliSearch\Client;

class UpdatePropertyIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'adverts:update-property-index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command update adverts properties index';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $client = new Client(config('scout.meilisearch.host'), config('scout.meilisearch.key'));

        $this->updateFilterableProperties($client);
        $this->updateSortableProperties($client);

        return Command::SUCCESS;
    }

    /**
     * Method updateFilterableProperties
     *
     * @param Client $client 
     *
     * @return void
     */
    protected function updateFilterableProperties(Client $client): void
    {
        $filterableProperties = Property::filterable()->get()->map(function (Property $property) {
            return 'property_values.' . $property->id;
        })->toArray();

        $client->index('adverts')->updateFilterableAttributes($filterableProperties);

        $this->info('Updated filterable attributes...');
    }

    /**
     * Method updateSortableProperties
     *
     * @param Client $client 
     *
     * @return void
     */
    protected function updateSortableProperties(Client $client): void
    {
        $sortableProperties = Property::sortable()->get()->map(function (Property $property) {
            return 'property_values.' . $property->id;
        })->toArray();

        $client->index('adverts')->updateSortableAttributes($sortableProperties);

        $this->info('Updated sortable attributes...');
    }
}
