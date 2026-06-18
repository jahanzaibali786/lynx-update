<?php

namespace App\Imports;

use App\Models\StudentRegistration;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentImport implements ToModel, WithHeadingRow
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
            if (stripos($key, 'student') !== false || stripos($key, 'roll') !== false) {
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
            $this->errors[] = __("Skipped: Invalid format \"{$combined}\". Use \"Name - Roll No\".");
            $this->errorCount++;
            return null;
        }

        $studentName = trim($parts[0]);
        $rollNo = trim($parts[1]);

        if (empty($studentName) || empty($rollNo)) {
            $this->errors[] = __("Skipped: Could not parse name and roll no from \"{$combined}\".");
            $this->errorCount++;
            return null;
        }

        $student = StudentRegistration::where('stdname', $studentName)
            ->where('roll_no', $rollNo)
            ->where('student_status', 'Enrolled')
            ->whereHas('enrollment', function ($q) {
                $q->where('active_status', 1);
            })
            ->first();

        if (!$student) {
            $this->errors[] = __("Skipped: No enrolled student found with Name \"{$studentName}\" and Roll No \"{$rollNo}\".");
            $this->skipCount++;
            return null;
        }

        $data = [];

        $fieldMap = [
            'reg_date' => 'regdate',
            'reg_no' => 'reg_no',
            'father_name' => 'fathername',
            'mother_name' => 'mothername',
            'dob' => 'dob',
            'gender' => 'gender',
            'religion' => 'religion',
            'nationality' => 'nationality',
            'father_cnic' => 'fathercnic',
            'father_phone' => 'fatherphone',
            'father_cell' => 'fathercell',
            'mother_cnic' => 'mothercnic',
            'email' => 'email',
            'city' => 'city',
            'district' => 'district',
            'address' => 'address',
            'permanent_address' => 'permanent_address',
            'previous_school' => 'prevschool',
            'previous_class' => 'prevclass',
            'birth_place' => 'birth_place',
            'remarks' => 'remarks',
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

        $student->update($data);
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
        if (!in_array($field, ['reg_date', 'dob'])) {
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
