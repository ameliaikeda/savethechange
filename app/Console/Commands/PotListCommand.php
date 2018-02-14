<?php

namespace App\Console\Commands;

use Amelia\Monzo\Monzo;
use App\User;
use Illuminate\Console\Command;

class PotListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pot:list {email : Email for the user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get a user\'s pots.';

    /**
     * The monzo client for this command.
     *
     * @var \Amelia\Monzo\Monzo
     */
    protected $monzo;

    /**
     * Create a new command instance.
     *
     * @param \Amelia\Monzo\Monzo $monzo
     */
    public function __construct(Monzo $monzo)
    {
        parent::__construct();

        $this->monzo = $monzo;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $user = User::where('email', $this->argument('email'))->firstOrFail();

        $pots = $this->monzo->as($user)->pots();

        /** @var \Illuminate\Support\Collection $output */
        $output = $pots->map->toArray();

        if ($output->count() === 0) {
            $this->warn('No pots found');
        }

        $headers = array_keys($output->first());

        $this->table($headers, $output->all());
    }
}
