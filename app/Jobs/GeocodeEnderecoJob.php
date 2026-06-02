<?php

namespace App\Jobs;

use App\Models\Endereco;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GeocodeEnderecoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $endereco;

    /**
     * Create a new job instance.
     */
    public function __construct(Endereco $endereco)
    {
        $this->endereco = $endereco;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $endereco = $this->endereco;

        // Se a localização já foi preenchida enquanto o job aguardava na fila
        if (!empty($endereco->localizacao)) {
            return;
        }

        $apiKey = config('services.google_maps.key');

        if (empty($apiKey)) {
            Log::warning("Job GeocodeEnderecoJob abortado: GOOGLE_MAPS_API_KEY não configurada.");
            return;
        }

        $parts = [];
        $parts[] = trim("{$endereco->logradouro}, {$endereco->numero}");
        if ($endereco->bairro) $parts[] = trim($endereco->bairro);
        $parts[] = trim($endereco->cidade);
        if ($endereco->estado) $parts[] = trim($endereco->estado);
        if ($endereco->cep) {
            // Remove o ponto do CEP (ex: 13.661-218 -> 13661-218)
            // O Google Maps costuma se confundir com CEPs formatados com ponto no Brasil
            $parts[] = preg_replace('/[^0-9-]/', '', $endereco->cep);
        }
        $parts[] = "Brasil";

        $addressString = implode(", ", $parts);

        // Log::debug("Iniciando geocodificação para Endereço {$endereco->id}", [
        //     'address_string' => $addressString,
        //     'endereco_dados_originais' => $endereco->only(['logradouro', 'numero', 'bairro', 'cidade', 'estado', 'cep'])
        // ]);

        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                'address' => $addressString,
                'components' => 'country:BR',
                'key' => $apiKey
            ]);

            if (!$response->successful()) {
                Log::error("Erro na API do Google Maps para Endereço {$endereco->id}: " . $response->body());
                return;
            }

            $data = $response->json();

            if (
                !isset($data['status'])
                ||
                $data['status'] !== 'OK'
                ||
                empty($data['results'])
            ) {
                $statusMsg = $data['status'] ?? 'Desconhecido';
                Log::warning("Google Maps Geocoding falhou para Endereço {$endereco->id}: Status {$statusMsg}");
                return;
            }

            $location = $data['results'][0]['geometry']['location'];
            $locationType = $data['results'][0]['geometry']['location_type'] ?? 'UNKNOWN';
            $formattedAddress = $data['results'][0]['formatted_address'] ?? 'UNKNOWN';

            $lat = $location['lat'];
            $lng = $location['lng'];

            // Log::debug("Resposta do Google Maps para Endereço {$endereco->id}", [
            //     'formatted_address' => $formattedAddress,
            //     'location_type' => $locationType,
            //     'lat' => $lat,
            //     'lng' => $lng
            // ]);

            // Em MySQL e Laravel, a longitude vem primeiro no POINT
            $endereco->update([
                'localizacao' => DB::raw("ST_GeomFromText('POINT({$lng} {$lat})')")
            ]);

            Log::info("Endereço {$endereco->id} geocodificado com sucesso: lat {$lat}, lng {$lng}");

        } catch (\Exception $e) {
            Log::error("Exceção ao geocodificar Endereço {$endereco->id}: " . $e->getMessage());
        }
    }
}
