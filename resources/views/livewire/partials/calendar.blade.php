@php
    $month = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $calendarMonth)
        ? \Carbon\Carbon::createFromFormat('Y-m-d', $calendarMonth . '-01')
        : now()->startOfMonth();
    $calendarStart = $month->copy()->startOfWeek();
    $calendarEnd = $month->copy()->endOfMonth()->endOfWeek();
@endphp
<div class="calendar-panel">
    <div class="calendar-toolbar">
        <h2>{{ $month->translatedFormat('F Y') }}</h2>
        <label>Pilih bulan
            <input type="month" wire:model.live="calendarMonth" aria-label="Bulan kalender">
        </label>
    </div>
    <div class="calendar-scroll">
        <div class="calendar-grid">
            @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $day)
                <div class="calendar-weekday" wire:key="day-{{ $day }}">{{ $day }}
                </div>
            @endforeach
            @for ($day = $calendarStart->copy(); $day->lte($calendarEnd); $day->addDay())
                <div @class([
                    'calendar-day',
                    'muted' => $day->month !== $month->month,
                    'today' => $day->isToday(),
                ]) wire:key="date-{{ $day->format('Y-m-d') }}">
                    <span>{{ $day->day }}</span>
                    @foreach ($allRecords->where('date', $day->format('Y-m-d')) as $event)
                        <a class="calendar-event" wire:key="event-{{ $event['id'] }}" href="{{ route('schedules.show', $event['id']) }}">
                            <strong>{{ $event['asset'] }}</strong>
                            <small>{{ $event['technician'] }}</small>
                        </a>
                    @endforeach
                </div>
            @endfor
        </div>
    </div>
    <p class="calendar-note">Klik jadwal untuk melihat detail. Pencarian dan filter juga berlaku pada kalender.</p>
</div>
