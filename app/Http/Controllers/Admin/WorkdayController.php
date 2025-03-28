<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Workday;
use Illuminate\Http\Request;

class WorkdayController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $workdays = Workday::all();
        return view('admin.workdays.index', compact('workdays'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.workdays.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'day' => 'required|string|unique:workdays,day',
            'is_active' => 'boolean',
        ]);

        Workday::create([
            'day' => $request->day,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.workdays.index')
            ->with('success', 'Hari kerja berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Workday $workday)
    {
        return view('admin.workdays.show', compact('workday'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Workday $workday)
    {
        return view('admin.workdays.edit', compact('workday'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Workday $workday)
    {
        $request->validate([
            'day' => 'required|string|unique:workdays,day,' . $workday->id,
            'is_active' => 'boolean',
        ]);

        $workday->update([
            'day' => $request->day,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.workdays.index')
            ->with('success', 'Hari kerja berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Workday $workday)
    {
        $workday->delete();

        return redirect()->route('admin.workdays.index')
            ->with('success', 'Hari kerja berhasil dihapus.');
    }
}
