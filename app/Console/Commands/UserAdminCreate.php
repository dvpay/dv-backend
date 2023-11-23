<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Dto\Models\UserDto;
use App\Enums\UserRole;
use App\Services\User\UserService;
use Illuminate\Console\Command;

class UserAdminCreate extends Command
{
    public function __construct(private readonly UserService $userService)
    {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "user:admin:create {email} {password} {name?}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create user with admin access.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $time = time();

        $email = $this->argument('email');
        $password = $this->argument('password');
        $name = $this->argument('name') ?? '';

        $dto = new UserDto([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'isAdmin' => true,
        ]);

        $this->userService->create($dto, UserRole::Root->value);

        $this->info('The command was successful! ' . time() - $time . ' s.');
    }
}