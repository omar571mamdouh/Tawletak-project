@pushOnce('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" 
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
      crossorigin=""/>
<style>
    .leaflet-container {
        z-index: 1;
    }
</style>
@endPushOnce

<div 
    x-data="osmPicker()" 
    x-init="initComponent()"
    class="space-y-2"
    wire:ignore
    data-lat="{{ $getState()['lat'] ?? '' }}"
    data-lng="{{ $getState()['lng'] ?? '' }}"
>
    <input
        type="text"
        placeholder=" Search on Place ..."
        class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
        x-model="query"
        @input.debounce.500ms="search"
    />

    <div x-show="results.length > 0" 
         x-cloak
         class="bg-white border rounded-lg shadow-lg max-h-40 overflow-auto z-50">
        <template x-for="item in results" :key="item.place_id">
            <div
                class="px-3 py-2 cursor-pointer hover:bg-gray-100 transition"
                @click="select(item)"
                x-text="item.display_name"
            ></div>
        </template>
    </div>

    <div x-ref="mapContainer" style="height: 400px; width: 100%;" class="rounded-lg border border-gray-300"></div>
    
    <div class="text-sm text-gray-600 mt-2" x-show="currentLat && currentLng">
        <strong>الموقع المحدد:</strong><br>
        <span>خط العرض: <span x-text="currentLat"></span></span> | 
        <span>خط الطول: <span x-text="currentLng"></span></span>
    </div>
</div>

@pushOnce('scripts')
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js" 
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" 
        crossorigin=""></script>

<script>
function osmPicker() {
    return {
        map: null,
        marker: null,
        query: '',
        results: [],
        currentLat: null,
        currentLng: null,
        isMapReady: false,

        initComponent() {
            // جيب القيم من الـ data attributes
            const container = this.$el;
            const savedLat = container.getAttribute('data-lat');
            const savedLng = container.getAttribute('data-lng');
            
            // لو في قيم محفوظة، استخدمها
            if (savedLat && savedLng && savedLat !== '' && savedLng !== '') {
                this.currentLat = parseFloat(savedLat);
                this.currentLng = parseFloat(savedLng);
                console.log('Loaded saved location:', this.currentLat, this.currentLng);
            }
            
            // انتظر لحد ما الـ Leaflet يتحمل
            this.waitForLeaflet();
        },

        waitForLeaflet() {
            if (typeof L !== 'undefined') {
                this.$nextTick(() => {
                    this.initMap();
                });
            } else {
                setTimeout(() => this.waitForLeaflet(), 100);
            }
        },

        initMap() {
            try {
                // استخدم القيم المحفوظة أو الـ default
                const initialLat = this.currentLat || 30.0444;
                const initialLng = this.currentLng || 31.2357;
                
                console.log('Initializing map with:', initialLat, initialLng);

                // إنشاء الخريطة
                this.map = L.map(this.$refs.mapContainer).setView([initialLat, initialLng], 12);

                // إضافة الطبقة
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                    maxZoom: 19
                }).addTo(this.map);

                // إضافة الماركر لو في قيم محفوظة
                if (this.currentLat && this.currentLng) {
                    this.addMarker(this.currentLat, this.currentLng);
                    this.map.setView([this.currentLat, this.currentLng], 15);
                }

                // الاستماع للنقر على الخريطة
                this.map.on('click', (e) => {
                    this.addMarker(e.latlng.lat, e.latlng.lng);
                    this.updateCoordinates(e.latlng.lat, e.latlng.lng);
                });

                this.isMapReady = true;

                // Fix للـ tiles
                setTimeout(() => {
                    this.map.invalidateSize();
                }, 100);

            } catch (error) {
                console.error('خطأ في تحميل الخريطة:', error);
            }
        },

        addMarker(lat, lng) {
            if (!this.map) return;

            if (this.marker) {
                this.marker.setLatLng([lat, lng]);
            } else {
                this.marker = L.marker([lat, lng], { draggable: true }).addTo(this.map);
                
                // الاستماع لسحب الماركر
                this.marker.on('dragend', (e) => {
                    const position = e.target.getLatLng();
                    this.updateCoordinates(position.lat, position.lng);
                });
            }
        },

       updateCoordinates(lat, lng) {
    this.currentLat = parseFloat(lat.toFixed(6));
    this.currentLng = parseFloat(lng.toFixed(6));

    console.log('Updating coordinates:', this.currentLat, this.currentLng);

    // ✅ تحديث القيم في Filament form state
    // ✅ تحديث القيم في Filament form state (Filament uses "data" state path)
this.$wire.$set('data.lat', this.currentLat);
this.$wire.$set('data.lng', this.currentLng);

},


        async search() {
            if (this.query.length < 3) {
                this.results = [];
                return;
            }

            try {
                const response = await fetch(
                    `https://nominatim.openstreetmap.org/search?` + 
                    `format=json&` +
                    `q=${encodeURIComponent(this.query)}&` +
                    `limit=5&` +
                    `accept-language=ar`
                );

                if (response.ok) {
                    this.results = await response.json();
                } else {
                    console.error('فشل البحث');
                    this.results = [];
                }
            } catch (error) {
                console.error('خطأ في البحث:', error);
                this.results = [];
            }
        },

        select(item) {
            const lat = parseFloat(item.lat);
            const lng = parseFloat(item.lon);

            this.addMarker(lat, lng);
            this.updateCoordinates(lat, lng);
            
            // حرك الخريطة للمكان الجديد
            if (this.map) {
                this.map.setView([lat, lng], 15);
            }

            this.results = [];
            this.query = item.display_name;
        }
    }
}
</script>
@endPushOnce

<style>
    [x-cloak] {
        display: none !important;
    }
</style>