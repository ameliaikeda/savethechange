<?php

namespace App\Console\Commands;

use Amelia\Monzo\Monzo;
use App\User;
use Illuminate\Console\Command;

class CreateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "create:user
                                {email : Email for the user.}
                                {--p|pot= : The pot_id to give this user.}
                                {--w|webhook : Register a webhook against Monzo's API.}
                                {--c|code= : Short-circuit auth if you've done it already.}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new monzo user, complete with webhook.';

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
        $user = new User([
            'email' => $this->argument('email'),
            'pot_id' => $this->option('pot'),
            'password' => bcrypt(str_random(64)),
        ]);

        $user->save();

        if ($code = $this->option('code')) {
            $this->info('Short-circuiting auth.');

            if (method_exists($this->monzo, 'completeAuth')) {
                $this->monzo->completeAuth($user, $code);
            }
        }

        if ($this->option('webhook')) {
            $this->info('Registering webhook for user.');

            $user->registerWebhook();
        }

        $this->info('User created successfully.');
    }
}
