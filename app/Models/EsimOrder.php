<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EsimOrder extends Model
{
    use HasFactory;
    protected $fillable = [
        'api_sold_id',
        'ad',
        'soyad',
        'email',
        'gsm_no',
        'tc_kimlik_no',
        'dogum_tarihi',
        'esim_code',
        'title',
        'amount',
        'data_amount',
        'validity_period',
        'qr_code',
        'paket_title',
        'fiyat',
        // diğer gerekli alanlar...
    ];
}
