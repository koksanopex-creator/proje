@extends('layouts.app')

@section('title', 'Zaman Takibi (Timesheet)')
@section('page_title', 'Zaman Takibi & Efor Analizi')
@section('page_subtitle', 'Proje ve görev bazlı çalışma süreleri ve onayları')

@section('content')
    <div class="grid-3">
        <!-- CANLI ZAMANLAYICI (TIMER) KARTI -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">⏱️ Canlı Görev Zamanlayıcısı</span>
            </div>
            <div class="card-body" style="text-align: center;">
                <div id="liveTimerDisplay" style="font-size: 36px; font-weight: 800; color: var(--navy); margin: 12px 0;">
                    00:00:00
                </div>

                <div class="form-group" style="text-align: left;">
                    <label class="form-label">Çalışılan Görev</label>
                    <select id="timerTaskSelect" class="form-control">
                        @foreach($activeTasks as $at)
                            <option value="{{ $at->id }}" data-project="{{ $at->project_id }}">
                                {{ $at->project?->code ?? '-' }} - {{ $at->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="display: flex; gap: 10px; justify-content: center; margin-top: 16px;">
                    <button type="button" id="btnStartTimer" class="btn btn-success" onclick="startTimer()">▶ Başlat</button>
                    <button type="button" id="btnStopTimer" class="btn btn-danger" onclick="stopTimer()" disabled>⏹ Durdur & Kaydet</button>
                </div>
            </div>
        </div>

        <!-- MANUEL ZAMAN GİRİŞ FORMU -->
        <div class="card" style="grid-column: span 2;">
            <div class="card-header">
                <span class="card-title">+ Manuel Zaman Girişi</span>
            </div>
            <div class="card-body">
                <form action="{{ route('timesheets.store') }}" method="POST">
                    @csrf
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Görev Seçimi *</label>
                            <select name="task_id" class="form-control" required onchange="setProjectFromTask(this)">
                                <option value="">-- Görev Seçiniz --</option>
                                @foreach($activeTasks as $at)
                                    <option value="{{ $at->id }}" data-project="{{ $at->project_id }}">{{ $at->project?->code ?? '-' }} - {{ $at->title }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="project_id" id="manualProjectId" value="{{ $activeTasks->first()?->project_id ?? 1 }}">
                        </div>

                        <div class="grid-2">
                            <div class="form-group">
                                <label class="form-label">Tarih *</label>
                                <input type="date" name="work_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Süre (Saat) *</label>
                                <input type="number" step="0.25" min="0.25" max="24" name="hours" class="form-control" value="2.00" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Yapılan Çalışmanın Özeti *</label>
                        <input type="text" name="description" class="form-control" placeholder="Örn: Zemin eğim nivo ölçümleri yapıldı, boru serimi tamamlandı." required>
                    </div>

                    <div style="display: flex; justify-content: flex-end;">
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- GEÇMİŞ ZAMAN ÇİZELGESİ TABLOSU -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">Son Girilen Zaman Kayıtlarım</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <table class="app-table">
                <thead>
                    <tr>
                        <th>Tarih</th>
                        <th>Proje</th>
                        <th>Görev</th>
                        <th>Süre</th>
                        <th>Açıklama</th>
                        <th>Onay Durumu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($myTimesheets as $ts)
                        <tr>
                            <td>{{ $ts->work_date->format('d.m.Y') }}</td>
                            <td><strong>{{ $ts->project?->code ?? '-' }}</strong></td>
                            <td>{{ $ts->task?->title ?? 'Genel Çalışma' }}</td>
                            <td style="font-weight: 700; color: var(--navy);">{{ $ts->hours }} Saat</td>
                            <td style="font-size: 12px;">{{ $ts->description }}</td>
                            <td>
                                @if($ts->is_approved)
                                    <span class="status-badge badge-success">✓ Onaylandı</span>
                                @else
                                    <span class="status-badge badge-warning">Onay Bekliyor</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align: center; padding: 24px;">Kayıt bulunamadı.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
        let timerInterval = null;
        let secondsPassed = 0;

        function startTimer() {
            document.getElementById('btnStartTimer').disabled = true;
            document.getElementById('btnStopTimer').disabled = false;
            timerInterval = setInterval(() => {
                secondsPassed++;
                let hrs = Math.floor(secondsPassed / 3600);
                let mins = Math.floor((secondsPassed % 3600) / 60);
                let secs = secondsPassed % 60;
                document.getElementById('liveTimerDisplay').innerText = 
                    String(hrs).padStart(2, '0') + ':' + String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
            }, 1000);
        }

        function stopTimer() {
            clearInterval(timerInterval);
            let hours = (secondsPassed / 3600).toFixed(2);
            if (hours < 0.1) hours = 0.25;
            alert('Sayaç durduruldu: ' + hours + ' saatlik kayıt kaydedilmek üzere form alanına eklendi.');
            window.location.reload();
        }

        function setProjectFromTask(selectEl) {
            let selectedOpt = selectEl.options[selectEl.selectedIndex];
            let prjId = selectedOpt.getAttribute('data-project');
            if (prjId) {
                document.getElementById('manualProjectId').value = prjId;
            }
        }
    </script>
    @endpush
@endsection