<?php

namespace App\Imports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmployeeImport implements ToModel, WithHeadingRow
{
    use Importable;

    protected $successCount = 0;
    protected $errorCount = 0;
    protected $skipCount = 0;
    protected $errors = [];

    public function model(array $row)
    {
        $combined = '';
        foreach ($row as $key => $val) {
            if (stripos($key, 'employee') !== false) {
                $combined = trim($val);
                break;
            }
        }

        if (empty($combined)) {
            $this->skipCount++;
            return null;
        }

        $parts = explode(' - ', $combined, 2);
        if (count($parts) < 2) {
            $this->errors[] = __("Skipped: Invalid format \"{$combined}\". Use \"ID - Name\".");
            $this->errorCount++;
            return null;
        }

        $formattedId = trim($parts[0]);
        $employeeName = trim($parts[1]);

        $numericId = preg_replace('/[^0-9]/', '', $formattedId);
        $rawEmployeeId = (string)((int)$numericId);

        if (empty($rawEmployeeId) || empty($employeeName)) {
            $this->errors[] = __("Skipped: Could not parse employee ID and name from \"{$combined}\".");
            $this->errorCount++;
            return null;
        }

        $employee = Employee::where('employee_id', $rawEmployeeId)
            ->where('name', $employeeName)
            ->where('is_active', 1)
            ->where('created_by', \Auth::user()->creatorId())
            ->first();

        if (!$employee) {
            $this->errors[] = __("Skipped: No active employee found with ID \"{$formattedId}\" and Name \"{$employeeName}\".");
            $this->skipCount++;
            return null;
        }

        $data = [];

        $fieldMap = [
            'salute' => 'salute',
            'father_name' => 'f_name',
            'cnic' => 'cnic',
            'date_of_birth' => 'dob',
            'gender' => 'gender',
            'religion' => 'religion',
            'blood_group' => 'blood_group',
            'phone' => 'phone',
            'email' => 'email',
            'area' => 'area',
            'address' => 'address',
            'present_address' => 'present_address',
            'category' => 'category',
            'date_of_joining' => 'company_doj',
            'probation_period' => 'probation_period',
            'account_holder_name' => 'account_holder_name',
            'account_number' => 'account_number',
            'bank_name' => 'bank_name',
            'bank_identifier_code' => 'bank_identifier_code',
            'branch_location' => 'branch_location',
            'tax_payer_id' => 'tax_payer_id',
        ];

        foreach ($fieldMap as $slugBase => $dbField) {
            $value = $this->findValue($row, $slugBase);
            if ($value !== null && $value !== '') {
                $data[$dbField] = $this->convertDateIfNeeded($slugBase, $value);
            }
        }

        if (empty($data)) {
            return null;
        }

        $employee->update($data);
        $this->successCount++;
        return null;
    }

    protected function findValue(array $row, $base)
    {
        if (isset($row[$base]) && $row[$base] !== null && $row[$base] !== '') {
            return $row[$base];
        }
        $suffixed = $base . '_y_m_d';
        if (isset($row[$suffixed]) && $row[$suffixed] !== null && $row[$suffixed] !== '') {
            return $row[$suffixed];
        }
        $underscored = $base . '_y_m_d_';
        if (isset($row[$underscored]) && $row[$underscored] !== null && $row[$underscored] !== '') {
            return $row[$underscored];
        }
        foreach ($row as $key => $val) {
            if ($val !== null && $val !== '' && stripos($key, $base) === 0) {
                return $val;
            }
        }
        return null;
    }

    protected function convertDateIfNeeded($field, $value)
    {
        if (!in_array($field, ['date_of_birth', 'date_of_joining'])) {
            return $value;
        }
        if (is_numeric($value) && $value > 40000) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
        }
        return $value;
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }

    public function getErrorCount()
    {
        return $this->errorCount;
    }

    public function getSkipCount()
    {
        return $this->skipCount;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
