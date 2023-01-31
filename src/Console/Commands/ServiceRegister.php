<?php

namespace KieranFYI\Services\Core\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use KieranFYI\Services\Core\Models\Service;
use KieranFYI\Services\Core\Services\RegistrationService;
use KieranFYI\Services\Core\Traits\ServiceHTTPRequest;

class ServiceRegister extends Command
{
    use ServiceHTTPRequest;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service:register {token}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register a service';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $data = json_decode(base64_decode($this->argument('token')), true);
            $service = Service::where('endpoint', $data['endpoint'])
                ->firstOr(function () use ($data) {
                    return new Service([
                        'endpoint' => $data['endpoint']
                    ]);
                });

            $symmetricKey = random_bytes(32);

            $service->fill([
                'name' => $data['name'],
                'endpoint' => $data['endpoint'],
                'key' => $data['identifier'],
                'asymmetric_key' => base64_decode($data['asymmetric_key']),
                'symmetric_key' => $symmetricKey
            ])
                ->save();

            $result = $this->servicePost($service, new RegistrationService($symmetricKey));
            if ($result) {
                $this->info('Registration Complete');
                return Command::SUCCESS;
            }

        } catch (Exception $e) {
            Log::error($e);
        }
        $this->error('Failed to Register');
        return Command::FAILURE;
    }
}
