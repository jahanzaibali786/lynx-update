<?php

namespace App\Http\Controllers;

use App\Models\AdvanceTaxCollection;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdvanceTaxCollectionController extends Controller
{
    private function normalizeMonthDate($value)
    {
        if (is_string($value) && preg_match('/^\d{4}-\d{2}$/', $value)) {
            return $value . '-01';
        }

        return $value;
    }

    private function branchOptions()
    {
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');

            return $branches;
        }

        $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
        $branches->prepend('Select Branch', '');

        return $branches;
    }

    private function employeeOptions()
    {
        $query = Employee::where('is_res_ter', 0);
        if (\Auth::user()->type == 'company') {
            $query->where('created_by', \Auth::user()->creatorId());
        } else {
            $query->where('owned_by', \Auth::user()->ownedId());
        }

        $employees = $query->get()->pluck('name', 'id');
        $employees->prepend('Select Employee', '');

        return $employees;
    }

    private function storeProofPicture(Request $request, $oldPath = null)
    {
        if (!$request->hasFile('proof_picture')) {
            return $oldPath;
        }

        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $file = $request->file('proof_picture');
        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        return $file->storeAs('uploads/advance_tax_collection_proofs', $fileName, 'public');
    }

    private function ajaxRowResponse(AdvanceTaxCollection $collection, string $message)
    {
        $collection->load(['employee', 'approvedBy']);

        return response()->json([
            'success' => true,
            'id' => $collection->id,
            'message' => $message,
            'row_html' => view('employee.advance_tax_collection.partials.row', [
                'collection' => $collection,
                'index' => 1,
            ])->render(),
        ]);
    }

    public function index(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $query = AdvanceTaxCollection::with(['employee', 'approvedBy'])->where('created_by', \Auth::user()->creatorId());
            $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $designations = Designation::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        } else {
            $query = AdvanceTaxCollection::with(['employee', 'approvedBy'])->where('owned_by', \Auth::user()->ownedId());
            $departments = Department::where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $designations = Designation::where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
        }

        $branches = $this->branchOptions();
        $departments->prepend('Select Department', '');
        $designations->prepend('Select Designation', '');

        if ($request->filled('branches')) {
            $query->whereHas('employee', function ($employeeQuery) use ($request) {
                $employeeQuery->where('branch_id', $request->branches);
            });
        }

        if ($request->filled('department_id')) {
            $query->whereHas('employee', function ($employeeQuery) use ($request) {
                $employeeQuery->where('department_id', $request->department_id);
            });
        }

        if ($request->filled('designation_id')) {
            $query->whereHas('employee', function ($employeeQuery) use ($request) {
                $employeeQuery->where('designation_id', $request->designation_id);
            });
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('tax_month')) {
            $month = \Carbon\Carbon::parse($this->normalizeMonthDate($request->tax_month));
            $query->whereYear('tax_month', $month->year)->whereMonth('tax_month', $month->month);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $collections = $query->latest()->paginate(50);
        $employees = $this->employeeOptions();
        $statuses = AdvanceTaxCollection::$statuses;

        return view('employee.advance_tax_collection.index', compact('collections', 'branches', 'departments', 'designations', 'employees', 'statuses'));
    }

    public function create()
    {
        $branches = $this->branchOptions();
        $employees = $this->employeeOptions();
        $paymentMethods = AdvanceTaxCollection::$paymentMethods;

        return view('employee.advance_tax_collection.create', compact('branches', 'employees', 'paymentMethods'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'tax_month' => $this->normalizeMonthDate($request->input('tax_month')),
        ]);

        $request->validate([
            'branches' => 'required|integer',
            'employee_id' => 'required|integer',
            'tax_month' => 'required|date',
            'collection_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|in:cash,online,check',
            'reference' => 'nullable|string|max:191',
            'remarks' => 'nullable|string',
            'proof_picture' => 'nullable|file|max:600',
        ]);

        $existing = AdvanceTaxCollection::where('employee_id', $request->employee_id)
            ->where('tax_month', $request->tax_month)
            ->where('collection_date', $request->collection_date)
            ->where('amount', $request->amount)
            ->where('payment_method', $request->payment_method)
            ->where('reference', $request->reference)
            ->where('owned_by', $request->branches)
            ->where('created_by', \Auth::user()->creatorId())
            ->where('created_at', '>=', now()->subMinute())
            ->latest()
            ->first();

        if ($existing) {
            if ($request->expectsJson()) {
                return $this->ajaxRowResponse($existing, __('Advance tax collection already saved.'));
            }

            return redirect()->route('advance-tax-collection.index')->with('success', __('Advance tax collection already saved.'));
        }

        $proofPicture = $this->storeProofPicture($request);

        $collection = AdvanceTaxCollection::create([
            'employee_id' => $request->employee_id,
            'tax_month' => $request->tax_month,
            'collection_date' => $request->collection_date,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'reference' => $request->reference,
            'remarks' => $request->remarks,
            'proof_picture' => $proofPicture,
            'status' => 0,
            'owned_by' => $request->branches,
            'created_by' => \Auth::user()->creatorId(),
        ]);

        if ($request->expectsJson()) {
            return $this->ajaxRowResponse($collection, __('Advance tax collection successfully created.'));
        }

        return redirect()->route('advance-tax-collection.index')->with('success', __('Advance tax collection successfully created.'));
    }

    public function show($id)
    {
        $collection = AdvanceTaxCollection::with(['employee', 'approvedBy'])->findOrFail($id);

        return view('employee.advance_tax_collection.show', compact('collection'));
    }

    public function proof($id)
    {
        $collection = AdvanceTaxCollection::findOrFail($id);

        if (\Auth::user()->type == 'company') {
            abort_if($collection->created_by != \Auth::user()->creatorId(), 403);
        } else {
            abort_if($collection->owned_by != \Auth::user()->ownedId(), 403);
        }

        abort_if(empty($collection->proof_picture), 404);
        abort_if(!Storage::disk('public')->exists($collection->proof_picture), 404);

        $path = Storage::disk('public')->path($collection->proof_picture);
        $filename = basename($collection->proof_picture);
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'])) {
            return response()->file($path);
        }

        return response()->download($path, $filename);
    }

    public function edit($id)
    {
        $collection = AdvanceTaxCollection::findOrFail($id);

        if ($collection->status == 1 && \Auth::user()->type != 'company') {
            return response()->json(['error' => __('Only admin can edit approved advance tax collection.')], 401);
        }

        $paymentMethods = AdvanceTaxCollection::$paymentMethods;

        return view('employee.advance_tax_collection.edit', compact('collection', 'paymentMethods'));
    }

    public function update(Request $request, $id)
    {
        $collection = AdvanceTaxCollection::findOrFail($id);

        if ($collection->status == 1 && \Auth::user()->type != 'company') {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => __('Only admin can edit approved advance tax collection.')], 403);
            }

            return redirect()->back()->with('error', __('Only admin can edit approved advance tax collection.'));
        }

        $request->merge([
            'tax_month' => $this->normalizeMonthDate($request->input('tax_month')),
        ]);

        $request->validate([
            'tax_month' => 'required|date',
            'collection_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|in:cash,online,check',
            'reference' => 'nullable|string|max:191',
            'remarks' => 'nullable|string',
            'proof_picture' => 'nullable|file|max:600',
        ]);

        $proofPicture = $this->storeProofPicture($request, $collection->proof_picture);

        $collection->update([
            'tax_month' => $request->tax_month,
            'collection_date' => $request->collection_date,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'reference' => $request->reference,
            'remarks' => $request->remarks,
            'proof_picture' => $proofPicture,
        ]);

        if ($request->expectsJson()) {
            return $this->ajaxRowResponse($collection, __('Advance tax collection successfully updated.'));
        }

        return redirect()->route('advance-tax-collection.index')->with('success', __('Advance tax collection successfully updated.'));
    }

    public function destroy($id)
    {
        $collection = AdvanceTaxCollection::findOrFail($id);

        if ($collection->status == 1) {
            return redirect()->back()->with('error', __('Approved advance tax collection cannot be deleted.'));
        }

        if ($collection->proof_picture && Storage::disk('public')->exists($collection->proof_picture)) {
            Storage::disk('public')->delete($collection->proof_picture);
        }

        $collection->delete();

        return redirect()->back()->with('success', __('Advance tax collection successfully deleted.'));
    }

    public function status($id)
    {
        $collection = AdvanceTaxCollection::with('employee')->findOrFail($id);

        if (\Auth::user()->type != 'company' || $collection->created_by != \Auth::user()->creatorId()) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        return view('employee.advance_tax_collection.status', compact('collection'));
    }

    public function statusChange(Request $request, $id)
    {
        $collection = AdvanceTaxCollection::findOrFail($id);

        if (\Auth::user()->type != 'company' || $collection->created_by != \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($collection->status != 0) {
            return redirect()->back()->with('error', __('Advance tax collection status is already updated.'));
        }

        if ((int) $request->status == 1) {
            $request->validate([
                'approval_date' => 'required|date',
            ]);

            $collection->status = 1;
            $collection->approval_date = $request->approval_date;
            $collection->approved_by = \Auth::id();
            $collection->save();
        } elseif ((int) $request->status == 2) {
            $collection->status = 2;
            $collection->approval_date = now()->format('Y-m-d');
            $collection->approved_by = \Auth::id();
            $collection->save();
        } else {
            return redirect()->back()->with('error', __('Invalid status.'));
        }

        return redirect()->back()->with('success', __('Advance tax collection status successfully updated.'));
    }
}
