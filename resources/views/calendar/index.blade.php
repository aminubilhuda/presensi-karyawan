@extends('layouts.app')

@section('title', 'Kalender')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/main.min.css">
<style>
    #calendar {
        height: 650px;
    }
    .fc-event {
        cursor: pointer;
    }
    .fc-day-today {
        background-color: rgba(0, 123, 255, 0.1) !important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Kalender Acara</h3>
                </div>
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Acara -->
<div class="modal fade" id="eventModal" tabindex="-1" role="dialog" aria-labelledby="eventModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventModalLabel">Detail Acara</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="eventDetails">
                    <div class="form-group">
                        <label>Judul</label>
                        <p id="eventTitle" class="font-weight-bold"></p>
                    </div>
                    <div class="form-group">
                        <label>Tanggal</label>
                        <p id="eventDate"></p>
                    </div>
                    <div class="form-group">
                        <label>Waktu</label>
                        <p id="eventTime"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/locales/id.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
            },
            locale: 'id',
            timeZone: 'local',
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                meridiem: false,
                hour12: false
            },
            events: @json($events),
            eventClick: function(info) {
                $('#eventTitle').text(info.event.title);
                
                // Format tanggal
                var startDate = info.event.start;
                var endDate = info.event.end;
                
                if (info.event.allDay) {
                    $('#eventDate').text(startDate.toLocaleDateString('id-ID', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    }));
                    $('#eventTime').text('Sepanjang hari');
                } else {
                    $('#eventDate').text(startDate.toLocaleDateString('id-ID', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    }));
                    
                    var startTime = startDate.toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    
                    var endTime = endDate ? endDate.toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit'
                    }) : '';
                    
                    $('#eventTime').text(startTime + (endTime ? ' - ' + endTime : ''));
                }
                
                $('#eventModal').modal('show');
            }
        });
        calendar.render();
    });
</script>
@endsection 