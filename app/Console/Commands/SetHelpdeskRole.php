<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SetHelpdeskRole extends Command
{
    protected $signature = 'helpdesk:set-role {email : Email address of the existing user} {role : admin, agent, or customer}';

    protected $description = 'Set the role for an existing Helpdesk user';

    public function handle(): int
    {
        $role = $this->argument('role');
        if (! in_array($role, ['admin', 'agent', 'customer'], true)) {
            $this->error('Role must be admin, agent, or customer.');

            return self::FAILURE;
        }

        $user = User::where('email', $this->argument('email'))->first();
        if (! $user) {
            $this->error('No user was found with that email address.');

            return self::FAILURE;
        }

        $user->update(['role' => $role]);
        $this->info("{$user->email} is now a {$user->role}.");

        return self::SUCCESS;
    }
}
