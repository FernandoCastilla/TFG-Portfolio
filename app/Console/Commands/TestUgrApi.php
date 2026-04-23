<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestUgrApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ugr:test-api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = env('OPENPROJECT_URL') . '/api/v3/projects';
        $token = env('OPENPROJECT_TOKEN');

        $this->info("Conectando con proyectostic.ugr.es...");

        $response = \Illuminate\Support\Facades\Http::withBasicAuth('apikey', $token)
                        ->get($url);

        if ($response->successful()) {
            $total = $response->json()['total'] ?? 0;
            $this->info("¡Conexión exitosa!"); 
            $this->line("Actualmente trabajamos con {$total} proyectos en el Vicerrectorado.");
        } else {
            $this->error("Error de conexión: " . $response->status());
            $this->line($response->body());
        }
    }
}
