<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InquiryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Inquiry::query();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $term = '%' . $request->q . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('message', 'like', $term);
            });
        }

        $inquiries = $query->latest()->paginate(20);
        $counts = [
            'total'  => Inquiry::count(),
            'unread' => Inquiry::unread()->count(),
            'new'    => Inquiry::where('status', Inquiry::STATUS_NEW)->count(),
        ];

        return view('admin.inquiries.index', compact('inquiries', 'counts'));
    }

    public function show(Inquiry $inquiry): View
    {
        if (! $inquiry->read_at) {
            $inquiry->forceFill(['read_at' => now()])->save();
        }

        return view('admin.inquiries.show', compact('inquiry'));
    }

    public function update(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $validated = $request->validate([
            'status'     => ['required', 'in:' . implode(',', array_keys(Inquiry::STATUSES))],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $inquiry->update($validated);

        return redirect()->route('admin.inquiries.show', $inquiry)->with('success', 'Müraciət yeniləndi.');
    }

    public function destroy(Inquiry $inquiry): RedirectResponse
    {
        $inquiry->delete();

        return redirect()->route('admin.inquiries.index')->with('success', 'Müraciət silindi.');
    }
}
