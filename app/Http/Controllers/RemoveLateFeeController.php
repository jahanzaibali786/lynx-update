<?php

namespace App\Http\Controllers;

use App\Http\Requests\RemoveLateFeeRequest;
use App\Services\RemoveLateFeeService;
use Illuminate\Http\Request;

class RemoveLateFeeController extends Controller
{
    protected RemoveLateFeeService $removeLateFeeService;

    public function __construct(RemoveLateFeeService $removeLateFeeService)
    {
        $this->removeLateFeeService = $removeLateFeeService;
    }

    public function index()
    {
        return view('challan.remove-late-fee.index');
    }

    public function search(Request $request)
    {
        $request->validate([
            'challan_no' => 'required|string',
        ]);

        $result = $this->removeLateFeeService->searchChallan($request->challan_no);

        if (!$result) {
            return response()->json([
                'status' => 'error',
                'message' => 'Challan not found.',
            ], 404);
        }

        if (!$result['has_late_fee']) {
            return response()->json([
                'status' => 'error',
                'message' => 'Late Fee head not found in this challan.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ]);
    }

    public function remove(RemoveLateFeeRequest $request)
    {
        try {
            $result = $this->removeLateFeeService->removeLateFee(
                $request->challan_no,
                $request->remove_amount
            );

            return response()->json([
                'status' => 'success',
                'message' => $result['message'],
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }
}
