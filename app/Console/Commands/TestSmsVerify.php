<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;
use Vonage\Client;
use Vonage\Verify2\Request\SMSRequest;

class TestSmsVerify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vonage:test-verify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Example Command to test the outgoing message';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $credentials = new Client\Credentials\Keypair(
            file_get_contents(base_path(env('VONAGE_PRIVATE_KEY_PATH'))),
            env('VONAGE_APPLICATION_ID')
        );

        $client = new Client($credentials);

        $phoneNumber = '+447738066610';
        $smsRequest = new SMSRequest($phoneNumber, 'VONAGE-LARAVEL');

        try {
            $response = $client->verify2()->startVerification($smsRequest);
        } catch (Client\Exception\Request $e) {
            dd($e);
        }

        dd($response);
    }
}
