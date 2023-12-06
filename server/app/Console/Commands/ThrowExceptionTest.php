<?php

namespace App\Console\Commands;

use App\Exceptions\ExtendedException;
use Exception;
use Illuminate\Console\Command;

class ThrowExceptionTest extends Command
{
    /**
     * The name and signature of the console command
     * @var string
     */
    protected $signature = 'exception:throw';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Throw an Exception.';

    /**
     * Create a new command instance
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command
     * @return mixed
     * @throws Exception
     */
    public function handle() {
        throw new Exception("This is a test exception.");
    }
}
