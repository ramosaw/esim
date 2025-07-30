<?php

namespace App\Http\Controllers;

use App\Models\EsimOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class EsimController extends Controller
{
    protected $baseUrl;
    protected $token;

    public function __construct()
    {
        $this->baseUrl = config('services.esim.base_url');
        $this->token = config('services.esim.token');
    }

    protected function client()
    {
        return Http::withOptions(['verify' => false])
            ->withHeaders(['token' => $this->token]);
    }

    protected function callApi($method, $endpoint, $data = [])
    {
        try {
            $response = $this->client()->$method($this->baseUrl.$endpoint, $data);
            return $response->json();
        } catch (\Exception $e) {
            Log::error("API Error - $endpoint: ".$e->getMessage());
            throw $e;
        }
    }

    protected function errorResponse($message = 'Bilinmeyen bir hata oluştu', $code = 500)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
        ], $code);
    }

    public function getCountries()
    {
        try {
            return $this->callApi('get', '/partner/v1/esim/countries');
        } catch (\Exception $e) {
            return $this->errorResponse('Ülke listesi alınırken hata oluştu: '.$e->getMessage());
        }
    }

    public function getCoverages($code)
    {
        try {
            $endpoint = $code === 'ALL' 
                ? '/partner/v1/esim/coverages' 
                : "/partner/v1/esim/coverages/{$code}";
            
            return $this->callApi('get', $endpoint);
        } catch (\Exception $e) {
            return $this->errorResponse('Kapsama bilgisi alınamadı: '.$e->getMessage());
        }
    }

    public function createEsim(Request $request)
    {   
        try {
            $data = $this->callApi('post', '/partner/v1/esim/create', [
                'api_id' => $request->input('api_id'),
                'gsm_no' => $request->input('gsm_no'),
                'email' => $request->input('email'),
                'tc_kimlik_no' => $request->input('tc_kimlik_no'),
                'ad' => $request->input('ad'),
                'soyad' => $request->input('soyad'),
                'dogum_tarihi' => $request->input('dogum_tarihi'),
            ]);

            if ($data['status'] && isset($data['sold_esim'])) {
                $this->createEsimOrder($request, $data['sold_esim']);
            }

            return $data;
        } catch (\Exception $e) {
            return $this->errorResponse('eSIM oluşturulurken hata oluştu: ' . $e->getMessage());
        }
    }

    protected function createEsimOrder(Request $request, array $soldEsim)
    {
        return EsimOrder::create([
            'api_sold_id' => $soldEsim['id'],
            'gsm_no' => $request->input('gsm_no'),
            'email' => $request->input('email'),
            'tc_kimlik_no' => $request->input('tc_kimlik_no'),
            'ad' => $request->input('ad'),
            'soyad' => $request->input('soyad'),
            'dogum_tarihi' => $request->input('dogum_tarihi'),
            'paket_title' => $soldEsim['title'],
            'fiyat' => $soldEsim['fiyat'],
            'coverage' => $soldEsim['coverage'],
            'data_amount' => $soldEsim['data_amount'],
            'validity_period' => $soldEsim['validity_period'],
            'status' => 'pending',
        ]);
    }

    public function confirmEsim(Request $request)
    {
        try {
            $data = $this->callApi('post', '/partner/v1/esim/confirm', [
                'id' => $request->input('id'),
                'kartCvv' => $request->input('kartCvv'),
                'kartNo' => $request->input('kartNo'),
                'kartSonKullanmaTarihi' => $request->input('kartSonKullanmaTarihi'),
                'kartSahibi' => $request->input('kartSahibi'),
                'taksitSayisi' => $request->input('taksitSayisi'),
            ]);

            if ($data['status'] && isset($data['sold_esim'])) {
                $this->updateEsimOrder($data['sold_esim']);
            }

            return $data;
        } catch (\Exception $e) {
            return $this->errorResponse('eSIM onaylanırken hata oluştu: ' . $e->getMessage());
        }
    }

    protected function updateEsimOrder(array $soldEsim)
    {
        $parameters = $soldEsim['parameters']['data'][0] ?? [];
        $esim = $parameters['esimDetail'][0] ?? null;

        return EsimOrder::where('api_sold_id', $soldEsim['id'])->update([
            'status' => 'sold',
            'qr_code_url' => $esim['qr_code'] ?? null,
            'iccid' => $esim['iccid'] ?? null,
            'coverage' => $parameters['coverage'] ?? null,
            'start_date' => $parameters['start_date'] ?? null,
            'end_date' => $parameters['end_date'] ?? null,
        ]);
    }

    public function getEsimDetails($id)
    {
        try {
            if (!is_numeric($id) || $id <= 0) {
                return $this->errorResponse('Geçersiz eSIM ID formatı', 400);
            }
           
            $order = EsimOrder::with(['user', 'transactions'])
                        ->where('api_sold_id', $id)
                        ->firstOrFail();
     
            if ($order->status === 'pending') {
                $this->checkEsimStatus($order, $id);
            }

            return response()->json([
                'status' => true,
                'data' => $this->formatEsimDetails($order)
            ]);

        } catch (\Exception $e) {
            return $this->errorResponse('eSIM bilgileri alınırken teknik bir hata oluştu');
        }
    }

    protected function checkEsimStatus(EsimOrder $order, $id)
    {
        try {
            $apiResponse = $this->callApi('get', "/partner/v1/esim/status/{$id}");
            
            if (isset($apiResponse['status']) && $apiResponse['status'] !== 'available') {
                $order->update(['status' => $apiResponse['status']]);
            }
        } catch (\Exception $e) {
            Log::error("Esim status check failed: " . $e->getMessage());
        }
    }

    protected function formatEsimDetails(EsimOrder $order)
    {
        return [
            'id' => $order->api_sold_id,
            'package' => $order->paket_title,
            'status' => $order->status,
            'qr_code' => $order->qr_code_url,
            'validity' => [
                'start' => $order->start_date,
                'end' => $order->end_date
            ],
        ];
    }
}