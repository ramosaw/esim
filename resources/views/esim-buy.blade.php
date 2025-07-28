@extends('layouts.master')

@section('title', 'eSIM Satın Al')

@section('content')
<div class="container py-5">
    <h2 class="mb-4 text-center">eSIM Satın Al</h2>
    <div id="EsimApp">
        <!-- Step 1: Paket Seçimi -->
        <div v-if="step === 1" class="row g-4">
            <!-- Sol Panel -->
            <div class="col-md-8">
                <!-- Filtreler -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-2">
                        <label class="form-label fw-semibold">🌍 Ülke Seç</label>
                        <select class="form-select" v-model="selectedCountry" @change="fetchPackages">
                            <option v-for="country in countries" :key="country.ulkeKodu" :value="country.ulkeKodu">
                                @{{ country.ulkeAd }}
                            </option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label fw-semibold">🗕️ Gün Sayısı</label>
                        <select class="form-select" v-model="selectedDays">
                            <option value="">Tümü</option>
                            <option v-for="day in uniqueDays" :key="day" :value="day">@{{ day }} Gün</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label fw-semibold">🚀 Kota</label>
                        <select class="form-select" v-model="selectedQuota">
                            <option value="">Tümü</option>
                            <option v-for="quota in uniqueQuotas" :key="quota" :value="quota">@{{ quota }} GB</option>
                        </select>
                    </div>
                </div>

                <!-- Yükleme Animasyonu -->
                <div v-if="loading" class="skeleton-loader">
                    <div class="card mb-3" v-for="n in 5" :key="n">
                        <div class="card-body">
                            <div class="placeholder-glow">
                                <span class="placeholder col-7"></span>
                                <span class="placeholder col-4"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Paket Listesi -->
                <div v-else>
                    <h5 class="mb-3">📂 Paketler</h5>
                    <div class="card mb-3" v-for="pack in filteredPackages" :key="pack.id">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <strong>@{{ pack.title }}</strong><br>
                                <small>@{{ pack.data_amount }} GB / @{{ pack.validity_period }} Gün</small>
                            </div>
                            <div>
                                <span class="text-danger fw-bold">$@{{ pack.amount }}</span>
                                <button class="btn btn-sm btn-success ms-3" @click="selectPackage(pack)">Seç</button>
                            </div>
                        </div>
                    </div>
                    <div v-if="filteredPackages.length === 0" class="alert alert-info">Uygun paket bulunamadı.</div>
                </div>
            </div>

            <!-- Sağ Panel -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-3">💼 Seçilen Paket</h5>
                        <div v-if="selected">
                            <p><strong>@{{ selected.title }}</strong></p>
                            <p>@{{ selected.data_amount }} GB - @{{ selected.validity_period }} Gün</p>
                            <p class="text-danger fw-bold">$@{{ selected.amount }}</p>
                        </div>
                        <div v-else>
                            <p>Henüz bir paket seçilmedi.</p>
                        </div>
                        <hr>
                        <h5 class="mb-3">👤 Kullanıcı Bilgileri</h5>
                        <input v-model="form.ad" class="form-control mb-2" placeholder="Ad">
                        <input v-model="form.soyad" class="form-control mb-2" placeholder="Soyad">
                        <input v-model="form.email" type="email" class="form-control mb-2" placeholder="E-Posta">
                        <input v-model="form.tc_kimlik_no" class="form-control mb-2" placeholder="T.C. Kimlik No">
                        <input v-model="form.gsm_no" class="form-control mb-2" placeholder="Telefon Numarası">
                        <input v-model="form.dogum_tarihi" type="date" class="form-control mb-3">
                        <button class="btn btn-primary w-100" @click="createEsim" :disabled="!selected">
                            Satın Al
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Kart Bilgileri -->
        <div v-if="step === 2" class="col-md-6 offset-md-3">
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
                            <input v-model="payment.kartSonKullanmaTarihi" class="form-control" placeholder="2028-02-01">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CVV</label>
                            <input v-model="payment.kartCvv" class="form-control" placeholder="123">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kart Sahibi</label>
                        <input v-model="payment.kartSahibi" class="form-control" :placeholder="form.ad + ' ' + form.soyad" readonly>
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
                        <button class="btn btn-success" @click="confirmEsim">
                            Ödemeyi Tamamla ($@{{ selected.amount }})
                        </button>
                        <button class="btn btn-outline-secondary" @click="step = 1">
                            Geri Dön
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3: QR Kodu -->
        <div v-if="step === 3 && qrCodeUrl" class="text-center">
            <div class="card border-success">
                <div class="card-body">
                    <h5 class="text-success fw-semibold">🎉 Satın Alma Başarılı</h5>
                    <p class="text-muted">QR Kodunuz:</p>
                    <img :src="qrCodeUrl" class="img-fluid mt-3 mb-4" style="max-width: 200px;">
                    <div class="alert alert-info">
                        <strong>Paket Bilgisi:</strong> @{{ selected.title }} - @{{ selected.data_amount }} GB / @{{ selected.validity_period }} Gün
                    </div>
                    <button class="btn btn-outline-primary" @click="resetForm">
                        Yeni eSIM Satın Al
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .skeleton-loader .placeholder {
        background-color: #e9ecef;
        border-radius: 4px;
        height: 15px;
        margin-bottom: 8px;
    }
    .card {
        transition: all 0.3s ease;
    }
    .card:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }
</style>
@endsection

@section('scripts')
<script>
const { createApp } = Vue;

createApp({
    data() {
        return {
            loading: true,
            countries: [
                { ulkeKodu: 'ALL', ulkeAd: 'Tüm Avrupa' },
                { ulkeKodu: 'TR', ulkeAd: 'Türkiye' },
                { ulkeKodu: 'US', ulkeAd: 'Amerika Birleşik Devletleri' },
                { ulkeKodu: 'DE', ulkeAd: 'Almanya' },
                { ulkeKodu: 'FR', ulkeAd: 'Fransa' }
            ],
            packages: [],
            selectedCountry: 'ALL',
            selectedDays: '',
            selectedQuota: '',
            selected: null,
            qrCodeUrl: null,
            createdEsimId: null,
            step: 1,
            form: {
                ad: '',
                soyad: '',
                email: '',
                gsm_no: '',
                tc_kimlik_no: '',
                dogum_tarihi: ''
            },
            payment: {
                kartNo: '',
                kartCvv: '',
                kartSonKullanmaTarihi: '',
                kartSahibi: '',
                taksitSayisi: 1
            }
        }
    },
    computed: {
        filteredPackages() {
            return this.packages.filter(pack => {
                const matchDays = this.selectedDays ? pack.validity_period == this.selectedDays : true;
                const matchQuota = this.selectedQuota ? pack.data_amount == this.selectedQuota : true;
                return matchDays && matchQuota;
            });
        },
        uniqueDays() {
            const days = this.packages.map(p => p.validity_period);
            return [...new Set(days)].sort((a, b) => a - b);
        },
        uniqueQuotas() {
            const quotas = this.packages.map(p => p.data_amount);
            return [...new Set(quotas)].sort((a, b) => a - b);
        }
    },
    mounted() {
        // Önce statik verilerle hızlı yükleme
        this.packages = this.getPopularPackages(this.selectedCountry);
        
        // Sonra API'den güncel verileri al
        this.fetchInitialData();
    },
    methods: {
        fetchInitialData() {
            // Ülkeleri getir
            axios.get('/api/countries')
                .then(res => {
                    this.countries = [
                        { ulkeKodu: 'ALL', ulkeAd: 'Tüm Avrupa' },
                        ...res.data.data
                    ];
                })
                .catch(() => {
                    console.log("Ülke listesi yüklenirken hata oluştu, statik liste kullanılıyor");
                });
            
            // Paketleri getir
            this.fetchPackages();
        },
        fetchPackages() {
            this.loading = true;
            axios.get('/api/coverages/' + this.selectedCountry)
                .then(res => {
                    this.packages = res.data.coverages;
                    // Eğer API'den gelen paket yoksa, popüler paketleri göster
                    if (this.packages.length === 0) {
                        this.packages = this.getPopularPackages(this.selectedCountry);
                    }
                })
                .catch(error => {
                    console.error("Paketler yüklenirken hata:", error);
                    this.packages = this.getPopularPackages(this.selectedCountry);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        getPopularPackages(countryCode) {
            const popularPackages = {
                'ALL': [
                    {id: 1, title: 'Avrupa 7GB Paketi', data_amount: 7, validity_period: 30, amount: 29.99, api_id: 'eu_7gb'},
                    {id: 2, title: 'Avrupa 15GB Paketi', data_amount: 15, validity_period: 30, amount: 49.99, api_id: 'eu_15gb'},
                    {id: 3, title: 'Avrupa 30GB Paketi', data_amount: 30, validity_period: 30, amount: 69.99, api_id: 'eu_30gb'}
                ],
                'TR': [
                    {id: 4, title: 'Türkiye 10GB Paketi', data_amount: 10, validity_period: 30, amount: 19.99, api_id: 'tr_10gb'},
                    {id: 5, title: 'Türkiye 20GB Paketi', data_amount: 20, validity_period: 30, amount: 29.99, api_id: 'tr_20gb'}
                ]
            };
            return popularPackages[countryCode] || popularPackages['ALL'];
        },
        selectPackage(pack) {
            this.selected = pack;
            // Seçilen pakete scroll et
            document.querySelector('.card-body').scrollIntoView({ behavior: 'smooth' });
        },
        validateForm() {
            if (!this.selected) return "Lütfen bir paket seçin";
            if (!this.form.ad || !this.form.soyad) return "Ad ve soyad gereklidir";
            if (!this.form.email.includes('@') || !this.form.email.includes('.')) return "Geçerli bir email adresi girin";
            if (!this.form.gsm_no) return "Telefon numarası gereklidir";
            return null;
        },
        async createEsim() {
            const error = this.validateForm();
            if (error) return alert(error);
            
            try {
                const response = await axios.post('/api/esim/create', {
                    api_id: [this.selected.api_id],
                    ...this.form
                });
                
                if (response.data?.sold_esim?.id) {
                    this.createdEsimId = response.data.sold_esim.id;
                    this.payment.kartSahibi = `${this.form.ad} ${this.form.soyad}`;
                    this.step = 2;
                } else {
                    throw new Error("eSIM oluşturulamadı");
                }
            } catch (error) {
                console.error("eSIM oluşturma hatası:", error);
                alert(`Bir hata oluştu: ${error.response?.data?.message || error.message}`);
            }
        },
        async confirmEsim() {
            if (!this.validatePayment()) return;
            
            try {
                const response = await axios.post('/api/esim/confirm', {
                    id: this.createdEsimId,
                    ...this.payment
                });
                
                const qr = response.data?.sold_esim?.parameters?.data?.[0]?.esimDetail?.[0]?.qr_code;
                if (qr) {
                    this.qrCodeUrl = qr;
                    this.step = 3;
                } else {
                    throw new Error("QR kod alınamadı");
                }
            } catch (error) {
                console.error("Ödeme işlemi hatası:", error);
                alert(`Ödeme işlemi sırasında hata: ${error.response?.data?.message || error.message}`);
            }
        },
        validatePayment() {
            if (!this.payment.kartNo || this.payment.kartNo.length < 16) return alert("Geçerli bir kart numarası girin");
            if (!this.payment.kartCvv || this.payment.kartCvv.length < 3) return alert("Geçerli bir CVV numarası girin");
            if (!this.payment.kartSonKullanmaTarihi) return alert("Son kullanma tarihi gereklidir");
            return true;
        },
        resetForm() {
            this.step = 1;
            this.selected = null;
            this.qrCodeUrl = null;
            this.form = {
                ad: '',
                soyad: '',
                email: '',
                gsm_no: '',
                tc_kimlik_no: '',
                dogum_tarihi: ''
            };
            this.payment = {
                kartNo: '',
                kartCvv: '',
                kartSonKullanmaTarihi: '',
                kartSahibi: '',
                taksitSayisi: 1
            };
        }
    }
}).mount('#EsimApp');
</script>
@endsection