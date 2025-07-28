@extends('layouts.master')

@section('title', 'Ödeme Sayfası')

@section('content')
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/">
            <i class="fas fa-sim-card me-2"></i>eSIM Market
        </a>
    </div>
</nav>

<div class="container py-5">
    <div id="PaymentApp">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-3">💳 Kart Bilgileri</h5>
                        <div class="mb-3">
                            <label class="form-label">Kart Numarası</label>
                            <input v-model="payment.kartNo" class="form-control" placeholder="1234 5678 9012 3456">
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Son Kullanma Tarihi</label>
                                <input v-model="payment.kartSonKullanmaTarihi" class="form-control" placeholder="MM/YY">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">CVV</label>
                                <input v-model="payment.kartCvv" class="form-control" placeholder="123">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kart Sahibi</label>
                            <input v-model="payment.kartSahibi" class="form-control" >
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Taksit Sayısı</label>
                            <select v-model="payment.taksitSayisi" class="form-select">
                                <option value="1">Tek Çekim</option>
                                <option value="2">2 Taksit</option>
                                <option value="3">3 Taksit</option>
                            </select>
                        </div>
                        <div class="d-grid gap-2">
                            <button class="btn btn-success" @click="confirmPayment">
                                Ödemeyi Tamamla ($@{{ amount }})
                            </button>
                            <a href="/esim" class="btn btn-outline-secondary">
                                Geri Dön
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const { createApp } = Vue;

createApp({
    data() {
        return {
            esimId: '{{ $esimId }}',
            amount: 0,
            packageInfo: {},
            payment: {
                kartNo: '',
                kartCvv: '',
                kartSonKullanmaTarihi: '',
                kartSahibi: '',
                taksitSayisi: 1
            }
        }
    },
    async mounted() {
        await this.fetchEsimDetails();
    },
    methods: {
        // payment.blade.php içindeki fetchEsimDetails metodunu güncelleyin
async fetchEsimDetails() {
    try {
        const response = await axios.get(`/api/esim/details/${this.esimId}`);
        
        if (!response.data.status) {
            throw new Error(response.data.message || 'Geçersiz yanıt');
        }

        this.amount = response.data.data.package.price;
        // this.payment.kartSahibi = response.data.data.customer_name;
        this.packageInfo = response.data.data.package;
        
        // Stok durumu kontrolü
        if (response.data.data.status !== 'available') {
            this.redirectToEsimPageWithError(response.data.data.status);
        }
    } catch (error) {
        console.error("eSIM detayları yüklenirken hata:", error);
        this.showErrorModal(
            error.response?.data?.message || 
            'eSIM bilgileri alınırken hata oluştu. Lütfen sayfayı yenileyin.'
        );
    }
},
        validatePayment() {
            if (!this.payment.kartNo || this.payment.kartNo.length < 16) {
                alert("Geçerli bir kart numarası girin");
                return false;
            }
            if (!this.payment.kartCvv || this.payment.kartCvv.length < 3) {
                alert("Geçerli bir CVV numarası girin");
                return false;
            }
            if (!this.payment.kartSonKullanmaTarihi) {
                alert("Son kullanma tarihi gereklidir");
                return false;
            }
            return true;
        },
        async confirmPayment() {
            if (!this.validatePayment()) return;
            
            try {
                const response = await axios.post('/api/esim/confirm', {
                    id: this.esimId,
                    ...this.payment
                });
                
                if (response.data.success) {
                    window.location.href = `/payment-success?qr=${encodeURIComponent(response.data.qr_code)}`;
                } else {
                    throw new Error(response.data.message || "Ödeme işlemi başarısız");
                }
            } catch (error) {
                console.error("Ödeme işlemi hatası:", error);
                alert(`Ödeme işlemi sırasında hata: ${error.response?.data?.message || error.message}`);
            }
        }
    }
}).mount('#PaymentApp');
</script>
@endsection