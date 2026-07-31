<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccountSubType;
use App\Models\ChartOfAccountType;
use Illuminate\Http\Request;

class ChartOfAccountSubTypeController extends Controller
{
    public function index()
    {
        if (!\Auth::user()->can('manage chart of account')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $subCategories = ChartOfAccountSubType::with('accountType')
            ->where('created_by', \Auth::user()->creatorId())
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return view('chartOfAccountSubCategory.index', compact('subCategories'));
    }

    public function create()
    {
        if (!\Auth::user()->can('create chart of account')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $types = $this->accountTypes();

        return view('chartOfAccountSubCategory.create', compact('types'));
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('create chart of account')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        $accountType = ChartOfAccountType::where('created_by', \Auth::user()->creatorId())
            ->where('id', $request->type)
            ->first();

        if (!$accountType) {
            return redirect()->back()->with('error', __('Invalid account type selected.'));
        }

        ChartOfAccountSubType::create([
            'name' => $request->name,
            'type' => $accountType->id,
            'created_by' => \Auth::user()->creatorId(),
        ]);

        return redirect()->route('chart-of-account-sub-category.index')->with('success', __('Chart of account sub category successfully created.'));
    }

    public function edit(ChartOfAccountSubType $chartOfAccountSubType)
    {
        if (!\Auth::user()->can('edit chart of account')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $chartOfAccountSubType = $this->ownedSubCategory($chartOfAccountSubType->id);
        if (!$chartOfAccountSubType) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $types = $this->accountTypes();

        return view('chartOfAccountSubCategory.edit', compact('chartOfAccountSubType', 'types'));
    }

    public function update(Request $request, ChartOfAccountSubType $chartOfAccountSubType)
    {
        if (!\Auth::user()->can('edit chart of account')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        $chartOfAccountSubType->name = $request->name;
        // $chartOfAccountSubType->type = $accountType->id;
        $chartOfAccountSubType->save();

        return redirect()->route('chart-of-account-sub-category.index')->with('success', __('Chart of account sub category successfully updated.'));
    }

    public function destroy(ChartOfAccountSubType $chartOfAccountSubType)
    {
        return redirect()->route('chart-of-account-sub-category.index')->with('error', __('Delete is not available for chart of account sub categories.'));
    }

    private function accountTypes()
    {
        return ChartOfAccountType::where('created_by', \Auth::user()->creatorId())
            ->orderBy('name')
            ->pluck('name', 'id');
    }

    private function ownedSubCategory($id)
    {
        return ChartOfAccountSubType::where('created_by', \Auth::user()->creatorId())
            ->where('id', $id)
            ->first();
    }
}
