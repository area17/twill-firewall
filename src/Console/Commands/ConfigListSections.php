<?php

namespace A17\TwillFirewall\Console\Commands;

use Illuminate\Console\Command;
use A17\TwillFirewall\Exceptions\PackageException;
use A17\TwillFirewall\Support\Facades\TwillFirewall;

class ConfigListSections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twill-firewall:config:list-sections';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all sections that can be merged into the published config file';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->table(
            ['Section', 'Merge command'],
            collect(TwillFirewall::config('package.sections'))->sort()->map(fn($value) => [$value, "php artisan twill-firewall:config:merge {$value}"])->toArray()
        );
    }
}
