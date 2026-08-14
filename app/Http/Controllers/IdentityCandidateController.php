<?php

namespace App\Http\Controllers;

use App\Models\IdentityCandidate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IdentityCandidateController extends Controller
{
    public function index(Request $request): Response
    {
        $status = in_array($request->string('status')->toString(), ['pending', 'confirmed', 'rejected'], true)
            ? $request->string('status')->toString() : 'pending';
        $candidates = IdentityCandidate::query()
            ->with(['leftPerson.employments.position', 'rightPerson.employments.position', 'reviewer'])
            ->where('status', $status)->orderBy('id')->paginate(25)->withQueryString();

        return Inertia::render('Admin/IdentityCandidates/Index', ['status' => $status, 'candidates' => $candidates]);
    }

    public function update(Request $request, IdentityCandidate $identityCandidate): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:confirmed,rejected'],
            'review_notes' => ['required', 'string', 'min:5', 'max:2000'],
        ]);
        $identityCandidate->update([
            'status' => $validated['status'], 'review_notes' => $validated['review_notes'],
            'reviewed_by' => $request->user()->id, 'reviewed_at' => now(),
        ]);

        return back()->with('status', 'Decisão registrada sem fundir os registros de origem.');
    }
}
