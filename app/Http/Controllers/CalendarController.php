<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Models\Workday;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    /**
     * Menampilkan kalender acara
     */
    public function index()
    {
        $holidays = Holiday::all();
        $workdays = Workday::all();
        
        $events = [];
        
        // Tambahkan hari libur ke events
        foreach ($holidays as $holiday) {
            $events[] = [
                'title' => $holiday->name,
                'start' => $holiday->date,
                'color' => '#f56954', // merah
                'allDay' => true
            ];
        }
        
        // Tambahkan hari kerja ke events
        $dayMap = [
            'Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 
            'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 0
        ];
        
        foreach ($workdays as $workday) {
            if (isset($dayMap[$workday->day_name])) {
                $events[] = [
                    'title' => 'Hari Kerja',
                    'daysOfWeek' => [$dayMap[$workday->day_name]],
                    'startTime' => $workday->start_time,
                    'endTime' => $workday->end_time,
                    'color' => '#00a65a', // hijau
                    'startRecur' => date('Y-m-d', strtotime('-1 month')),
                    'endRecur' => date('Y-m-d', strtotime('+6 months'))
                ];
            }
        }
        
        return view('calendar.index', compact('events'));
    }
}
