<?php

namespace App\Http\Controllers;

use App\Http\Requests\DailyClosingRequest;
use App\Models\DailyClosing;
use App\Services\DailyClosingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class DailyClosingController extends Controller
{
    public function __construct(private DailyClosingService $dailyClosingService)
    {
    }

    public function index(Request $request)
    {
        if (!Auth::user()->can('manage daily cash closing')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $user = Auth::user();
        if ($user->type == 'company') {
            $query = DailyClosing::where('created_by', $user->creatorId());
        } else {
            $query = DailyClosing::where('owned_by', $user->ownedId());
        }

        if (!empty($request->from_date)) {
            $query->where('from_date', '>=', $request->from_date);
        }
        if (!empty($request->to_date)) {
            $query->where('to_date', '<=', $request->to_date);
        }

        $closings = $query->orderBy('from_date', 'desc')->get();

        return view('dailyClosing.index', compact('closings'));
    }

    public function create()
    {
        if (!Auth::user()->can('create daily cash closing')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $hoEmployees = $this->dailyClosingService->employeeOptions();

        return view('dailyClosing.create', compact('hoEmployees'));
    }

    public function getTransfers(Request $request)
    {
        if (!Auth::user()->can('create daily cash closing') && !Auth::user()->can('edit daily cash closing')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        if (!$fromDate || !$toDate) {
            return response()->json(['error' => 'Invalid dates'], 400);
        }

        return response()->json($this->dailyClosingService->transferSummary($fromDate, $toDate, $request->exclude_id));
    }

    public function store(DailyClosingRequest $request)
    {
        if (!Auth::user()->can('create daily cash closing')) {
            return response()->json(['success' => false, 'error' => __('Permission denied.')], 403);
        }

        try {
            $dailyClosing = $this->dailyClosingService->create($request->validated());

            return response()->json([
                'success' => true,
                'message' => __('Daily Closing successfully created.'),
                'id' => $dailyClosing->id,
                'row' => view('dailyClosing.partials.row', [
                    'closing' => $dailyClosing,
                    'index' => 1,
                ])->render(),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], $e->getCode() ?: 422);
        } catch (\Throwable $e) {
            \Log::error('Daily closing create failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'error' => __('Something went wrong: ') . $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        if (!Auth::user()->can('edit daily cash closing')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $dailyClosing = DailyClosing::findOrFail($id);
        $hoEmployees = $this->dailyClosingService->employeeOptions();

        return view('dailyClosing.edit', compact('dailyClosing', 'hoEmployees'));
    }

    public function update(DailyClosingRequest $request, $id)
    {
        if (!Auth::user()->can('edit daily cash closing')) {
            return response()->json(['success' => false, 'error' => __('Permission denied.')], 403);
        }

        $dailyClosing = DailyClosing::findOrFail($id);
        try {
            $dailyClosing = $this->dailyClosingService->update($dailyClosing, $request->validated());

            return response()->json([
                'success' => true,
                'message' => __('Daily Closing successfully updated.'),
                'id' => $dailyClosing->id,
                'row' => view('dailyClosing.partials.row', [
                    'closing' => $dailyClosing,
                    'index' => null,
                ])->render(),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], $e->getCode() ?: 422);
        } catch (\Throwable $e) {
            \Log::error('Daily closing update failed', [
                'error' => $e->getMessage(),
                'daily_closing_id' => $dailyClosing->id,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'error' => __('Something went wrong: ') . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        if (!Auth::user()->can('delete daily cash closing')) {
            return response()->json(['success' => false, 'error' => __('Permission denied.')], 403);
        }

        $dailyClosing = DailyClosing::findOrFail($id);
        $this->dailyClosingService->delete($dailyClosing);

        return response()->json([
            'success' => true,
            'message' => __('Daily Closing successfully deleted.'),
        ]);
    }

    public function approve(Request $request, $id)
    {
        if (!Auth::user()->can('approve daily cash closing')) {
            return response()->json(['success' => false, 'error' => __('Permission denied.')], 403);
        }

        $dailyClosing = DailyClosing::findOrFail($id);
        $desiredStatus = $request->input('status');
        $dailyClosing = $this->dailyClosingService->setApprovalStatus($dailyClosing, $desiredStatus);

        return response()->json([
            'success' => true,
            'message' => __('Approval status updated successfully.'),
            'status' => $dailyClosing->status,
            'id' => $dailyClosing->id,
            'row' => view('dailyClosing.partials.row', [
                'closing' => $dailyClosing,
                'index' => null,
            ])->render(),
        ]);
    }

    public function show($id)
    {
        if (!Auth::user()->can('show daily cash closing')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $dailyClosing = DailyClosing::findOrFail($id);

        $transferRows = $this->dailyClosingService->printRows($dailyClosing);

        return view('dailyClosing.show', compact('dailyClosing', 'transferRows'));
    }
}
