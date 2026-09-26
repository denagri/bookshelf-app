<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Enums\ReadingPlanStatus;
use App\Http\Requests\StoreReadingPlanRequest;
use App\Http\Requests\UpdateReadingPlanRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReadingPlanController extends Controller
{

    public function index(Request $request)
    {
        $currentStatus = $request->input('status');

        $query = Auth::user()->readingPlans()->with('book');

        if (!empty($currentStatus)) {
            $query->where('status', $currentStatus);
        }

        $readingPlans = $query->orderBy('target_date', 'asc')->get();
        return view('reading-plans.index', compact('readingPlans', 'currentStatus'));
    }

    public function create()
    {
        $books = Book::all();
        return view('reading-plans.create', compact('books'));
    }

    public function store(StoreReadingPlanRequest $request)
    {
        $validated = $request->validated();

        Auth::user()->readingPlans()->create([
            'book_id' => $validated['book_id'],
            'target_date' => $validated['target_date'],
            'status' => ReadingPlanStatus::PLANNED,
        ]);

        return redirect()->route('reading-plans.index')->with('success', '読書計画を作成しました。');
    }

    public function edit(ReadingPlan $readingPlan)
    {
        if ($readingPlan->user_id !== Auth::id()) { abort(403); }
        return view('reading-plans.edit', compact('readingPlan'));
    }

    public function update(UpdateReadingPlanRequest $request, ReadingPlan $readingPlan)
    {

        $validated = $request->validated();

        $readingPlan->update($validated);

        return redirect()->route('reading-plans.index')->with('success', '読書計画を更新しました。');
    }

    public function destroy(ReadingPlan $readingPlan)
    {
        if ($readingPlan->user_id !== Auth::id()) { abort(403); }

        $readingPlan->delete();

        return redirect()->route('reading-plans.index')->with('success', '読書計画を削除しました。');
    }

    public function complete(ReadingPlan $readingPlan)
    {
        if ($readingPlan->user_id !== Auth::id()) { abort(403); }

        $readingPlan->update([
            'status' => ReadingPlanStatus::COMPLETED,
            'completed_at' => now(),
        ]);

        return redirect()->route('reading-plans.index')->with('success', '読書計画を完了しました');
    }
}
