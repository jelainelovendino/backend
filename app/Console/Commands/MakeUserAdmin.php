<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class MakeUserAdmin extends Command
{
    protected $signature = 'make:admin {email?} {--create : Create a new admin user}';
    protected $description = 'Make a user an admin or create a new admin user';

    public function handle()
    {
        if ($this->option('create')) {
            $email = $this->argument('email') ?? $this->ask('Enter email for new admin');
            $name = $this->ask('Enter name for new admin');
            $password = $this->secret('Enter password for new admin');

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'admin'
            ]);

            $this->info("Admin user created successfully with email: {$email}");
            return;
        }

        $email = $this->argument('email') ?? $this->ask('Enter email of user to make admin');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email {$email} not found!");
            return;
        }

        $user->role = 'admin';
        $user->save();

        $this->info("User {$email} is now an admin!");
    }
} 