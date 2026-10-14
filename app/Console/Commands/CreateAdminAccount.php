<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminAccount extends Command
{
    protected $signature = 'admin:create {username=admin : Username for the new administrator}';

    protected $description = 'Create an administrator account and require a password change at first login';

    public function handle(): int
    {
        $username = (string) $this->argument('username');
        $email = (string) $this->ask('Admin email address');
        $password = (string) $this->secret('Initial password');
        $confirmation = (string) $this->secret('Confirm initial password');

        $validator = Validator::make(
            ['username' => $username, 'email' => $email, 'password' => $password],
            [
                'username' => ['required', 'alpha_dash:ascii', 'min:3', 'max:40', 'unique:users,username'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                // The initial credential is temporary and the account is forced to replace it.
                'password' => ['required', 'string', 'min:7', 'max:255'],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        if (! hash_equals($password, $confirmation)) {
            $this->error('The password confirmation does not match.');

            return self::FAILURE;
        }

        $admin = User::create([
            'name' => $username,
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'admin',
            'is_active' => true,
            'must_change_password' => true,
        ]);
        AuditLog::create([
            'action' => 'user.created',
            'subject_type' => User::class,
            'subject_id' => $admin->id,
            'description' => "Created administrator account {$admin->username} from the command line.",
            'metadata' => ['role' => 'admin', 'must_change_password' => true],
        ]);

        $this->info("Administrator '{$admin->username}' created. Change the initial password after signing in.");

        return self::SUCCESS;
    }
}
