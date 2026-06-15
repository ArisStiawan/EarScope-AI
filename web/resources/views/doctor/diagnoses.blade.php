<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Diagnoses') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 p-4 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <!-- AVAILABLE CONSULTATIONS -->
            <div id="consultationsSection" class="bg-white shadow rounded-lg p-6">
                <h3 class="font-bold text-lg mb-4">Available Consultations</h3>

                @if($consultations->isEmpty())
                    <p class="text-gray-500">No scheduled consultations available for diagnosis.</p>
                @else
                    <div class="mb-4">
                        <div class="flex items-center gap-3">
                            <input id="searchConsultations" type="text" placeholder="Search patient, complaint, or date..."
                                class="block w-full md:w-96 px-3 py-2 border rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm" />
                            <button type="button" onclick="clearSearch()"
                                class="ml-2 inline-flex items-center px-3 py-2 bg-gray-100 text-sm rounded-md hover:bg-gray-200">Clear</button>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr class="text-left text-xs text-gray-500 uppercase tracking-wider">
                                    <th class="px-4 py-2">ID</th>
                                    <th class="px-4 py-2">Patient</th>
                                    <th class="px-4 py-2">Complaint</th>
                                    <th class="px-4 py-2">Scheduled</th>
                                    <th class="px-4 py-2">Status</th>
                                    <th class="px-4 py-2">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($consultations as $consultation)
                                    <tr>
                                        <td class="px-4 py-2 text-sm font-mono font-bold text-indigo-700 bg-indigo-50 rounded">
                                            #{{ $consultation->id }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ $consultation->patient->name ?? 'N/A' }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ Str::limit($consultation->complaint, 50) }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ \Carbon\Carbon::parse($consultation->scheduled_date)->format('d M Y') }} {{ $consultation->scheduled_time }}</td>
                                        <td class="px-4 py-2 text-sm text-green-800">{{ ucfirst($consultation->status) }}</td>
                                        <td class="px-4 py-2 text-sm">
                                            <button type="button" onclick="openDiagnosisForm('{{ $consultation->id }}', '{{ addslashes($consultation->patient->name ?? 'N/A') }}')" class="text-indigo-600 hover:text-indigo-900 underline font-medium">
                                                Add Diagnosis
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- DIAGNOSIS FORM SECTION -->
            <div id="diagnosisFormSection" class="bg-white shadow rounded-lg p-6" style="display: none;">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="font-bold text-lg">Submit Diagnosis</h3>
                    <button type="button" onclick="closeDiagnosisForm()" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- CONSULTATION DETAILS -->
                <div id="consultationDetails" class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Patient Name</p>
                            <p class="mt-1 text-sm font-medium text-gray-900" id="detailPatientName">-</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Age</p>
                            <p class="mt-1 text-sm font-medium text-gray-900" id="detailPatientAge">-</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Gender</p>
                            <p class="mt-1 text-sm font-medium text-gray-900" id="detailPatientGender">-</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Email</p>
                            <p class="mt-1 text-sm font-medium text-gray-900" id="detailPatientEmail">-</p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Complaint</p>
                            <p class="mt-1 text-sm text-gray-900" id="detailComplaint">-</p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Scheduled</p>
                            <p class="mt-1 text-sm text-gray-900" id="detailScheduled">-</p>
                        </div>
                    </div>
                </div>

                <!-- EARSCOPE FLASK IFRAME SECTION -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-sm font-medium text-gray-700">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            Perangkat Earscope
                        </label>
                        <div id="flaskStatusBadge" class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full bg-gray-100 text-gray-500">
                            <span class="w-2 h-2 rounded-full bg-gray-400 inline-block"></span>
                            Belum terhubung
                        </div>
                    </div>

                    <!-- Flask Start Button -->
                    <div id="flaskStartContainer" class="p-6 border-2 border-dashed border-gray-300 rounded-lg text-center bg-gray-50">
                        <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        <p class="text-sm text-gray-500 mb-3">Klik tombol di bawah untuk menghubungkan perangkat Earscope</p>
                        <button type="button" id="startFlaskBtn" onclick="startFlaskApp()"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Mulai Earscope
                        </button>
                        <p id="flaskStartStatus" class="text-xs text-gray-400 mt-2"></p>
                    </div>

                    <!-- Flask Iframe (hidden until Flask is ready) -->
                    <div id="flaskIframeContainer" class="rounded-lg overflow-hidden border border-gray-200 shadow-sm" style="display: none;">
                        <iframe id="flaskIframe" class="w-full" style="height: 480px; border: none;" allow="camera; microphone" allowfullscreen></iframe>
                    </div>
                </div>

                <!-- PHOTO GALLERY FROM EARSCOPE -->
                <div class="mb-6" id="photoGallerySection" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Foto Pemeriksaan
                        <span id="photoCount" class="ml-1 text-xs text-gray-400">(0 foto)</span>
                    </label>
                    <div id="photoGallery" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                        <!-- Photos will be dynamically inserted here -->
                    </div>
                </div>

                <!-- PROCESSED VIDEO FROM EARSCOPE -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Video Pemeriksaan Telinga
                        <span id="pollingBadge" class="ml-2 inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-800">
                            <span class="animate-pulse w-2 h-2 rounded-full bg-yellow-500 inline-block"></span>
                            Menunggu data earscope...
                        </span>
                    </label>
                    <div id="earVideoContainer" class="mt-2 p-4 border-2 border-dashed border-gray-300 rounded-lg text-center bg-gray-50 min-h-[120px] flex items-center justify-center">
                        <p class="text-sm text-gray-400">Video hasil pemeriksaan akan muncul otomatis setelah perangkat earscope mengirim data.</p>
                    </div>
                </div>

                <!-- AI Screening Result -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Hasil Deteksi AI (Earscope)</label>
                    <div id="aiResultContainer" class="mt-2 p-4 border border-gray-300 rounded-lg bg-gray-50 min-h-[52px] flex items-center">
                        <p class="text-sm text-gray-400 italic" id="aiResultPlaceholder">Menunggu hasil deteksi dari earscope...</p>
                        <p class="text-sm font-semibold text-indigo-700 hidden" id="aiResultText"></p>
                    </div>
                </div>

                <!-- DIAGNOSIS FORM -->
                <form id="diagnosisForm" method="POST" enctype="multipart/form-data" class="mt-6">
                    @csrf
                    <input type="hidden" id="diagnosisConsultationId" name="consultation_request_id">

                    <div class="grid grid-cols-1 gap-6">
                        <!-- Diagnosis Result -->
                        <div>
                            <label for="diagnosis_result" class="block text-sm font-medium text-gray-700">Diagnosis Result</label>
                            <textarea id="diagnosis_result" name="diagnosis_result" rows="4" required class="mt-1 block w-full px-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border"></textarea>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full px-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border"></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            Submit Diagnosis
                        </button>
                        <button type="button" onclick="closeDiagnosisForm()" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-gray-800 hover:bg-gray-300">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <!-- Photo Lightbox Modal -->
    <div id="photoLightbox" class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center" style="display: none;" onclick="closeLightbox(event)">
        <button onclick="closeLightbox()" class="absolute top-4 right-4 text-white text-3xl hover:text-gray-300 z-50">&times;</button>
        <img id="lightboxImage" class="max-w-[90vw] max-h-[85vh] rounded-lg shadow-2xl" />
    </div>

    <script>
        // ============================================================
        // STATE
        // ============================================================
        let pollingInterval = null;
        let photoPollingInterval = null;
        let earscopeLoaded  = false;
        let currentConsultationId = '';
        let currentPatientName = '';
        let knownPhotoIds = new Set();
        const flaskBaseUrl = '{{ env("FLASK_URL", "http://127.0.0.1:5000") }}';

        // ============================================================
        // FLASK APP CONTROL
        // ============================================================
        async function startFlaskApp() {
            const btn = document.getElementById('startFlaskBtn');
            const statusText = document.getElementById('flaskStartStatus');

            btn.disabled = true;
            btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Menghubungkan...';
            statusText.textContent = 'Memulai aplikasi earscope, mohon tunggu...';

            try {
                const response = await fetch('{{ route("doctor.flask.start") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    },
                });

                const data = await response.json();

                if (data.status === 'started' || data.status === 'already_running') {
                    showFlaskIframe();
                    updateFlaskStatus(true);
                    statusText.textContent = data.message;
                } else if (data.status === 'timeout') {
                    showFlaskIframe();
                    updateFlaskStatus(true);
                    statusText.textContent = data.message;
                } else {
                    statusText.textContent = '❌ ' + data.message;
                    btn.disabled = false;
                    btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Coba Lagi';
                }
            } catch (err) {
                statusText.textContent = '❌ Gagal menghubungi server: ' + err.message;
                btn.disabled = false;
                btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Coba Lagi';
            }
        }

        function showFlaskIframe() {
            document.getElementById('flaskStartContainer').style.display = 'none';
            const iframeContainer = document.getElementById('flaskIframeContainer');
            const iframe = document.getElementById('flaskIframe');

            const iframeUrl = flaskBaseUrl
                + '/?consultation_id=' + encodeURIComponent(currentConsultationId)
                + '&patient_name=' + encodeURIComponent(currentPatientName);

            iframe.src = iframeUrl;
            iframeContainer.style.display = 'block';
        }

        function updateFlaskStatus(running) {
            const badge = document.getElementById('flaskStatusBadge');
            if (running) {
                badge.className = 'inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full bg-green-100 text-green-700';
                badge.innerHTML = '<span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span> Terhubung';
            } else {
                badge.className = 'inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full bg-gray-100 text-gray-500';
                badge.innerHTML = '<span class="w-2 h-2 rounded-full bg-gray-400 inline-block"></span> Belum terhubung';
            }
        }

        // ============================================================
        // POLLING: Cek hasil earscope setiap 5 detik
        // ============================================================
        function startEarscopePolling(consultationId) {
            earscopeLoaded = false;
            // Cek langsung, lalu tiap 5 detik
            fetchEarscopeResult(consultationId);
            pollingInterval = setInterval(function () {
                fetchEarscopeResult(consultationId);
            }, 5000);
        }

        function stopEarscopePolling() {
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
            }
            if (photoPollingInterval) {
                clearInterval(photoPollingInterval);
                photoPollingInterval = null;
            }
        }

        function fetchEarscopeResult(consultationId) {
            $.ajax({
                url: '/api/earscope/latest-result?consultation_id=' + consultationId,
                type: 'GET',
                success: function (data) {
                    if (data.success) {
                        // Update video jika ada
                        if (data.ai_result && !earscopeLoaded) {
                            earscopeLoaded = true;
                            renderEarscopeResult(data);
                        }

                        // Update foto gallery
                        if (data.photos && data.photos.length > 0) {
                            renderPhotoGallery(data.photos);
                        }
                    }
                },
                error: function () {
                    // 404 = belum ada data, lanjut polling
                }
            });
        }

        function renderEarscopeResult(data) {
            // --- Badge status ---
            $('#pollingBadge')
                .removeClass('bg-yellow-100 text-yellow-800')
                .addClass('bg-green-100 text-green-800')
                .html('<span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span> Data diterima dari earscope');

            // --- Video processed ---
            if (data.processed_video_url) {
                $('#earVideoContainer').html(
                    '<video controls class="w-full rounded-lg shadow" style="max-height:320px;">'
                    + '<source src="' + data.processed_video_url + '" type="video/mp4">'
                    + 'Browser Anda tidak mendukung video HTML5.'
                    + '</video>'
                );
            } else {
                $('#earVideoContainer').html(
                    '<p class="text-sm text-gray-400">Video earscope tidak tersedia.</p>'
                );
            }

            // --- AI Result label ---
            $('#aiResultPlaceholder').addClass('hidden');
            $('#aiResultText').removeClass('hidden').text(data.ai_result);

            // --- Pre-fill textarea diagnosis_result ---
            const textarea = document.getElementById('diagnosis_result');
            if (textarea && !textarea.value.trim()) {
                textarea.value = data.ai_result;
            }
        }

        function renderPhotoGallery(photos) {
            const gallery = document.getElementById('photoGallery');
            const section = document.getElementById('photoGallerySection');
            const countEl = document.getElementById('photoCount');

            if (photos.length === 0) return;

            section.style.display = 'block';
            countEl.textContent = '(' + photos.length + ' foto)';

            // Only add new photos
            photos.forEach(function(photo) {
                if (knownPhotoIds.has(photo.id)) return;
                knownPhotoIds.add(photo.id);

                const typeLabel = photo.ai_screening_result?.type === 'bbox' ? 'AI Deteksi' : 'Raw';
                const typeBg = photo.ai_screening_result?.type === 'bbox' ? 'bg-cyan-100 text-cyan-700' : 'bg-gray-100 text-gray-600';

                const card = document.createElement('div');
                card.className = 'group relative rounded-lg overflow-hidden border border-gray-200 shadow-sm hover:shadow-md transition-all cursor-pointer animate-fade-in';
                card.onclick = function() { openLightbox(photo.image_url); };

                card.innerHTML = `
                    <img src="${photo.image_url}" alt="Foto pemeriksaan" class="w-full h-40 object-cover" loading="lazy" />
                    <div class="absolute top-2 left-2">
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full ${typeBg}">${typeLabel}</span>
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-2">
                        <p class="text-[10px] text-white/80">${new Date(photo.created_at).toLocaleTimeString('id-ID')}</p>
                    </div>
                `;

                gallery.appendChild(card);
            });
        }

        // ============================================================
        // LIGHTBOX
        // ============================================================
        function openLightbox(imageUrl) {
            document.getElementById('lightboxImage').src = imageUrl;
            document.getElementById('photoLightbox').style.display = 'flex';
        }

        function closeLightbox(event) {
            if (!event || event.target === document.getElementById('photoLightbox') || event.target.tagName === 'BUTTON') {
                document.getElementById('photoLightbox').style.display = 'none';
            }
        }

        // ============================================================
        // BUKA / TUTUP FORM
        // ============================================================
        function openDiagnosisForm(consultationId, patientName) {
            currentConsultationId = consultationId;
            currentPatientName = patientName || '';

            document.getElementById('consultationsSection').style.display = 'none';
            document.getElementById('diagnosisFormSection').style.display = 'block';
            document.getElementById('diagnosisConsultationId').value = consultationId;

            // Reset Flask iframe
            document.getElementById('flaskStartContainer').style.display = 'block';
            document.getElementById('flaskIframeContainer').style.display = 'none';
            document.getElementById('flaskIframe').src = '';
            document.getElementById('startFlaskBtn').disabled = false;
            document.getElementById('startFlaskBtn').innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Mulai Earscope';
            document.getElementById('flaskStartStatus').textContent = '';
            updateFlaskStatus(false);

            // Reset photo gallery
            knownPhotoIds.clear();
            document.getElementById('photoGallery').innerHTML = '';
            document.getElementById('photoGallerySection').style.display = 'none';

            // Reset tampilan earscope
            $('#pollingBadge')
                .removeClass('bg-green-100 text-green-800')
                .addClass('bg-yellow-100 text-yellow-800')
                .html('<span class="animate-pulse w-2 h-2 rounded-full bg-yellow-500 inline-block"></span> Menunggu data earscope...');
            $('#earVideoContainer').html('<p class="text-sm text-gray-400">Video hasil pemeriksaan akan muncul otomatis setelah perangkat earscope mengirim data.</p>');
            $('#aiResultPlaceholder').removeClass('hidden');
            $('#aiResultText').addClass('hidden').text('');

            // Fetch detail konsultasi
            $.ajax({
                url: '/doctor/consultation/' + consultationId + '/details',
                type: 'GET',
                success: function (data) {
                    $('#detailPatientName').text(data.patient?.name || '-');
                    $('#detailPatientAge').text(data.patient?.age || '-');
                    $('#detailPatientGender').text(data.patient?.gender || '-');
                    $('#detailPatientEmail').text(data.patient?.email || '-');
                    $('#detailComplaint').text(data.complaint || '-');
                    $('#detailScheduled').text(data.scheduled_date
                        ? new Date(data.scheduled_date).toLocaleDateString('id-ID', {year: 'numeric', month: 'long', day: 'numeric'})
                          + ' ' + (data.scheduled_time || '')
                        : '-');

                    // Update patient name if we got it from API
                    if (data.patient?.name) {
                        currentPatientName = data.patient.name;
                    }
                },
                error: function () {
                    alert('Gagal memuat detail konsultasi');
                    closeDiagnosisForm();
                }
            });

            // Set action form
            $('#diagnosisForm').attr('action', '{{ route("doctor.diagnoses.store") }}');

            // Mulai polling earscope
            startEarscopePolling(consultationId);

            // Auto-check if Flask is already running
            checkFlaskAndAutoConnect();
        }

        async function checkFlaskAndAutoConnect() {
            try {
                const response = await fetch('{{ route("doctor.flask.status") }}');
                const data = await response.json();
                if (data.running) {
                    showFlaskIframe();
                    updateFlaskStatus(true);
                }
            } catch (e) {
                // Flask not running
            }
        }

        function closeDiagnosisForm() {
            stopEarscopePolling();
            document.getElementById('diagnosisFormSection').style.display = 'none';
            document.getElementById('consultationsSection').style.display = 'block';
            document.getElementById('diagnosisForm').reset();
            document.getElementById('diagnosisConsultationId').value = '';
            document.getElementById('flaskIframe').src = '';
            currentConsultationId = '';
            currentPatientName = '';
        }

        // CSRF token untuk semua AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Client-side search
        function filterConsultations() {
            const q = ($('#searchConsultations').val() || '').toLowerCase().trim();
            if (!q) {
                $('#consultationsSection table tbody tr').show();
                return;
            }
            $('#consultationsSection table tbody tr').each(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(q) !== -1);
            });
        }

        $(document).on('input', '#searchConsultations', filterConsultations);

        function clearSearch() {
            $('#searchConsultations').val('');
            filterConsultations();
            $('#searchConsultations').focus();
        }
    </script>

    <style>
        @keyframes fade-in {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .animate-fade-in {
            animation: fade-in 0.3s ease-out;
        }
    </style>
</x-app-layout>
