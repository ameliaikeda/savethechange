<?php

namespace App\Console\Commands;

use Amelia\Monzo\Monzo;
use App\User;
use Illuminate\Console\Command;

class PotLinkCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pot:set
                                {email : Email for the user}
                                {pot : The pot_id to set for this user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign a pot to a user.';

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

        $user->pot_id = $pot = $this->argument('pot');
        $user->save();

        $this->info("Pot {$pot} assigned.");
    }
}
