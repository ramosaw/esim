<?php

namespace App\Http\Controllers;

use App\Models\EsimOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class EsimController extends Controller
{
    protected $baseUrl ;
    protected $token = '7c57f824afbac3bd4c60c60cd27eca35';

    // public function __construct()
    // {
    //     $this->baseUrl = env('ESIM_BASE_URL');
    //     $this->token = env('ESIM_API_KEY');
    // }
    public function getCountries() //TODO: ülke listesi
    {
        try {
            $response = Http::withOptions(['verify' => false])
                ->withHeaders([
                    'token' => $this->token,
                ])
                ->get($this->baseUrl . '/partner/v1/esim/countries');

            return $response->json();
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function getCoverages($code)
    {
        try {
            if ($code === 'ALL') {
                $response = Http::withOptions(['verify' => false])
                    ->withHeaders([
                        'token' => $this->token,
                    ])
                    ->get($this->baseUrl . "/partner/v1/esim/coverages");

                return $response->json();
            }

            $response = Http::withOptions(['verify' => false])
                ->withHeaders([
                    'token' => $this->token,
                ])
                ->get($this->baseUrl . "/partner/v1/esim/coverages/{$code}");

            return $response->json();
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function createEsim(Request $request)
    {   
        try {
            $response = Http::withOptions(['verify' => false])
                ->withHeaders([
                    'token' => $this->token,
                ])
                ->post($this->baseUrl . '/partner/v1/esim/create', [
                    'api_id' => $request->input('api_id'),
                    'gsm_no' => $request->input('gsm_no'),
                    'email' => $request->input('email'),
                    'tc_kimlik_no' => $request->input('tc_kimlik_no'),
                    'ad' => $request->input('ad'),
                    'soyad' => $request->input('soyad'),
                    'dogum_tarihi' => $request->input('dogum_tarihi'),
                ]);

            $data = $response->json();

            if ($data['status'] && isset($data['sold_esim'])) {
                $sold = $data['sold_esim'];

                EsimOrder::create([
                    'api_sold_id' => $sold['id'],
                    'gsm_no' => $request->input('gsm_no'),
                    'email' => $request->input('email'),
                    'tc_kimlik_no' => $request->input('tc_kimlik_no'),
                    'ad' => $request->input('ad'),
                    'soyad' => $request->input('soyad'),
                    'dogum_tarihi' => $request->input('dogum_tarihi'),
                    'paket_title' => $sold['title'],
                    'fiyat' => $sold['fiyat'],
                    'coverage' => $sold['coverage'],
                    'data_amount' => $sold['data_amount'],
                    'validity_period' => $sold['validity_period'],
                    'status' => 'pending',
                ]);
            }
            return $data;
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function confirmEsim(Request $request)
    {
        try {
            $response = Http::withOptions(['verify' => false])
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'token' => $this->token,
                ])->post($this->baseUrl . '/partner/v1/esim/confirm', [
                    'id' => $request->input('id'),
                    'kartCvv' => $request->input('kartCvv'),
                    'kartNo' => $request->input('kartNo'),
                    'kartSonKullanmaTarihi' => $request->input('kartSonKullanmaTarihi'),
                    'kartSahibi' => $request->input('kartSahibi'),
                    'taksitSayisi' => $request->input('taksitSayisi'),
                ]);
            $data = $response->json();

            if ($data['status'] && isset($data['sold_esim'])) {
                $sold = $data['sold_esim'];
                $parameters = $sold['parameters']['data'][0];
                $esim = $parameters['esimDetail'][0] ?? null;

                EsimOrder::where('api_sold_id', $sold['id'])->update([
                    'status' => 'sold',
                    'qr_code_url' => $esim['qr_code'] ?? null,
                    'iccid' => $esim['iccid'] ?? null,
                    'coverage' => $parameters['coverage'],
                    'start_date' => $parameters['start_date'],
                    'end_date' => $parameters['end_date'],
                ]);
            }

            return $data;
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function getEsimDetails($id)
    {
        try {
            
            if (!is_numeric($id) || $id <= 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Geçersiz eSIM ID formatı',
                ], 400);
            }
           
            $order = EsimOrder::with(['user', 'transactions'])
                        ->where('api_sold_id', $id)
                        ->first();
        
            if (!$order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Belirtilen eSIM bulunamadı veya erişim izniniz yok',
                ], 404);
            }
     
            if ($order->status === 'pending') {
                $apiResponse = Http::withHeaders(['token' => $this->token])
                    ->get($this->baseUrl . "/partner/v1/esim/status/{$id}");
                
                if ($apiResponse->successful() && $apiResponse->json()['status'] !== 'available') {
                    $order->update(['status' => $apiResponse->json()['status']]);
                }
            }
      
            return response()->json([
                'status' => true,
                'data' => [
                    'id' => $order->api_sold_id,
                    'package' => $order->paket_title,
                    'status' => $order->status,
                    'qr_code' => $order->qr_code_url,
                    'validity' => [
                        'start' => $order->start_date,
                        'end' => $order->end_date
                    ],
                    
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("eSIM detay hatası: " . $e->getMessage(), [
                'esim_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => false,
                'message' => 'eSIM bilgileri alınırken teknik bir hata oluştu',
            ], 500);
        }
    }

}




