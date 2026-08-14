<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class NoteController extends Controller
{
    public function index(Request $request): View
    {
        $notes = $request->user()->notes()->latest()->get();
        return view('notes.index', compact('notes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $request->user()->notes()->create($validated);

        return redirect()->route('notes.index')->with('status', 'Note created successfully.');
    }

    public function destroy(Request $request, Note $note): RedirectResponse
    {
        if ($note->user_id !== $request->user()->id) 
        {
            abort(403);
        }

        $note->delete();

        return redirect()->route('notes.index')->with('status', 'Note deleted.');
    }
}
