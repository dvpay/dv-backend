<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Processing\Contracts\OwnerContract;
use Illuminate\Console\Command;

class UserOwnerIdCreate extends Command
{
    public function __construct(private readonly OwnerContract $owner)
    {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "user:owner:create";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Created processing owner id to old users.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $time = time();

        $users = User::where('processing_owner_id', null)->get();

        foreach ($users as $user) {
            $user->processing_owner_id = $this->owner->createOwner('user-' . $user->id);
            $user->save();
        }

        $this->info('The command was successful! ' . time() - $time . ' s.');
    }
}