<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentNote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentNoteController extends Controller
{
    /**
     * Store a new student note.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $student
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, User $student)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $note = new StudentNote();
        $note->student_id = $student->id;
        $note->author_id = Auth::id();
        $note->title = $validated['title'];
        $note->content = $validated['content'];
        $note->save();

        return back()->with('success', 'تمت إضافة الملاحظة بنجاح');
    }

    /**
     * Update an existing student note.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\StudentNote  $note
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StudentNote $note)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $note->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
        ]);

        return back()->with('success', 'تم تحديث الملاحظة بنجاح');
    }

    /**
     * Delete a student note.
     *
     * @param  \App\Models\StudentNote  $note
     * @return \Illuminate\Http\Response
     */
    public function destroy(StudentNote $note)
    {
        $note->delete();
        return back()->with('success', 'تم حذف الملاحظة بنجاح');
    }
} 