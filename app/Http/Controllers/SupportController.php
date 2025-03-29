<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewSupportTicketNotification;
use App\Notifications\SupportTicketStatusNotification;

class SupportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            $tickets = SupportTicket::with('user')
                ->latest()
                ->paginate(15);
        } else {
            $tickets = SupportTicket::where('user_id', $user->id)
                ->latest()
                ->paginate(15);
        }
        
        return view('support.index', compact('tickets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('support.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'required|in:low,medium,high',
        ]);
        
        $ticket = SupportTicket::create([
            'user_id' => Auth::id(),
            'subject' => $request->subject,
            'message' => $request->message,
            'priority' => $request->priority,
            'status' => 'open',
            'ticket_id' => 'TKT-' . time() . rand(1000, 9999),
        ]);
        
        // Notifikasi admin
        $admins = User::where('role', 'Admin')->get();
        // Notification::send($admins, new NewSupportTicketNotification($ticket));
        
        return redirect()->route('support.index')->with('success', 'Tiket dukungan berhasil dibuat');
    }

    /**
     * Display the specified resource.
     */
    public function show(SupportTicket $support)
    {
        $this->authorize('view', $support);
        return view('support.show', compact('support'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SupportTicket $support)
    {
        $this->authorize('update', $support);
        
        $request->validate([
            'status' => 'required|in:open,in_progress,closed',
            'assigned_to' => 'nullable|exists:users,id',
        ]);
        
        $oldStatus = $support->status;
        
        $support->update([
            'status' => $request->status,
            'assigned_to' => $request->assigned_to,
            'closed_at' => $request->status === 'closed' ? now() : $support->closed_at,
        ]);
        
        // Kirim notifikasi jika status berubah
        if ($oldStatus !== $request->status) {
            // Notification::send($support->user, new SupportTicketStatusNotification($support));
        }
        
        return redirect()->route('support.show', $support)->with('success', 'Tiket dukungan berhasil diperbarui');
    }

    /**
     * Close a ticket.
     */
    public function close(SupportTicket $support)
    {
        $this->authorize('update', $support);
        
        $support->close();
        
        // Notification::send($support->user, new SupportTicketStatusNotification($support));
        
        return redirect()->route('support.index')->with('success', 'Tiket dukungan berhasil ditutup');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
