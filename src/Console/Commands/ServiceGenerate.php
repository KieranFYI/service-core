<?php

namespace KieranFYI\Services\Core\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use KieranFYI\Services\Core\Models\Service;
use KieranFYI\Services\Core\Services\RegistrationService;
use KieranFYI\Services\Core\Traits\ServiceHTTPRequest;

class ServiceGenerate extends Command
{
    use ServiceHTTPRequest;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service:generate {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate an access token';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws Exception
     */
    public function handle()
    {
        $res = openssl_pkey_new([
            "digest_alg" => "sha512",
            "private_key_bits" => 4096,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ]);

        openssl_pkey_export($res, $privateKey);
        $publicKey = openssl_pkey_get_details($res);
        $publicKey = $publicKey["key"];

        $service = Service::create([
            'name' => $this->argument('name'),
            'asymmetric_key' => $privateKey,
        ]);

        $data = [
            'name' => config('app.name'),
            'endpoint' => route('service'),
            'identifier' => $service->key,
            'public_key' => base64_encode($publicKey)
        ];
        $this->info(base64_encode(json_encode($data)));
        return Command::SUCCESS;
    }
}
