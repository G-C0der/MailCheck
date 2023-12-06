<?php

namespace App\Console\Commands;

use Adldap\Laravel\Facades\Adldap;
use App\Mail\Alert\Notification;
use App\Models\Api\Alert\Message;
use App\Models\Users\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class LdapLogin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ldap:login {username} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test ldap login';

    /**
     * Executor
     * @return bool
     * @throws \Adldap\Auth\BindException
     * @throws \Adldap\Auth\PasswordRequiredException
     * @throws \Adldap\Auth\UsernameRequiredException
     */
    public function handle() {
        $username = $this->argument("username");
        $password = $this->argument("password");

        if(Adldap::auth()->attempt($username, $password, $bindAsUser = true)) {
            $this->info("Login successfull");
        } else {
            $this->error("Login failed");
        }
        return true;
    }
}
