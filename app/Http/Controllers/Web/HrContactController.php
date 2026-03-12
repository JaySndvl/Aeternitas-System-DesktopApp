<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\HrContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HrContactController extends Controller
{
    /**
     * Quick inbox payload for header dropdown (HR/Admin only)
     */
    public function quickInbox()
    {
        $user = Auth::user();

        if (!in_array(strtolower($user->role), ['hr', 'admin', 'administrator'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $messages = HrContact::with(['user', 'employee'])
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'sender_name' => $message->employee
                        ? trim(($message->employee->first_name ?? '') . ' ' . ($message->employee->last_name ?? ''))
                        : ($message->user->full_name ?? $message->user->email ?? 'Unknown'),
                    'subject' => $message->subject,
                    'status' => $message->status,
                    'status_label' => ucfirst(str_replace('_', ' ', $message->status)),
                    'category' => $message->category,
                    'category_label' => ucfirst($message->category),
                    'time_ago' => $message->created_at->diffForHumans(),
                ];
            });

        $pendingCount = HrContact::where('status', 'pending')->count();

        return response()->json([
            'messages' => $messages,
            'pending_count' => $pendingCount,
        ]);
    }

    /**
     * Show the contact form
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get user's own contacts
        $contacts = HrContact::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('hr.contact', compact('user', 'contacts'));
    }

    /**
     * Store a new contact request
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'category' => 'required|in:leave,payroll,benefits,schedule,general,complaint,request'
        ]);

        $validated['user_id'] = $user->id;

        // Link to employee if user is an employee
        if ($user->employee) {
            $validated['employee_id'] = $user->employee->id;
        }

        HrContact::create($validated);

        return redirect()->route('hr.contact.index')
            ->with('success', 'Your message has been sent to HR. We will respond as soon as possible.');
    }

    /**
     * Show all contact requests for HR staff
     */
    public function admin(Request $request)
    {
        $user = Auth::user();

        // Only HR/Admins can view all contacts
        if (!in_array(strtolower($user->role), ['hr', 'admin', 'administrator'])) {
            abort(403, 'Unauthorized');
        }

        // Build query with filters
        $query = HrContact::with(['user', 'employee', 'responder']);

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->get('category'));
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $contacts = $query->orderBy('created_at', 'desc')->paginate(20);

        $totalContacts = HrContact::count();
        $pendingCount = HrContact::where('status', 'pending')->count();
        $inProgressCount = HrContact::where('status', 'in_progress')->count();
        $resolvedCount = HrContact::where('status', 'resolved')->count();

        return view('hr.contacts-admin', compact(
            'user', 
            'contacts', 
            'totalContacts',
            'pendingCount',
            'inProgressCount',
            'resolvedCount'
        ));
    }

    /**
     * Show contact details
     */
    public function show(HrContact $hrContact)
    {
        $user = Auth::user();

        // Users can only see their own contacts unless they're HR
        if (!in_array(strtolower($user->role), ['hr', 'admin', 'administrator']) && $hrContact->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        return view('hr.contact-show', compact('user', 'hrContact'));
    }

    /**
     * Update contact status and add response (HR only)
     */
    public function respond(Request $request, HrContact $hrContact)
    {
        $user = Auth::user();

        // Only HR/Admins can respond
        if (!in_array(strtolower($user->role), ['hr', 'admin', 'administrator'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'response' => 'required|string|max:5000',
            'status' => 'required|in:pending,in_progress,resolved,closed'
        ]);

        $hrContact->update([
            'response' => $validated['response'],
            'status' => $validated['status'],
            'responded_by' => $user->id,
            'responded_at' => now()
        ]);

        return redirect()->route('hr.contact.show', $hrContact)
            ->with('success', 'Response sent successfully.');
    }

    /**
     * Show all messages from employees for HR staff
     */
    public function messages()
    {
        $user = Auth::user();

        // Only HR/Admins can view messages
        if (!in_array(strtolower($user->role), ['hr', 'admin', 'administrator'])) {
            abort(403, 'Unauthorized');
        }

        // Get all contacts with pagination, ordered by latest first
        $messages = HrContact::with(['user', 'employee', 'responder'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Count messages by status
        $totalMessages = HrContact::count();
        $unreadCount = HrContact::where('status', 'pending')->count();
        $respondedCount = HrContact::whereNotNull('responded_at')->count();

        return view('hr.messages', compact(
            'user',
            'messages',
            'totalMessages',
            'unreadCount',
            'respondedCount'
        ));
    }
}
