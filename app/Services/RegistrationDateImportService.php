<?php

namespace App\Services;

use App\Models\StudentEnrollments;
use App\Models\StudentRegistration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class RegistrationDateImportService
{
    public function process($uploadedFile): array
    {
        $path = $uploadedFile->getRealPath();
        if (empty($path)) {
            throw new \RuntimeException('Unable to read uploaded file.');
        }

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        if (empty($rows)) {
            return $this->makeReport($uploadedFile->getClientOriginalName() ?? null, [], 0, 0, []);
        }

        $headerRowIndex = $this->findHeaderRowIndex($rows);
        if ($headerRowIndex === null) {
            throw new \RuntimeException('Could not find a header row containing Student Name, Reg No / Roll No and New Reg Date.');
        }

        $headerMap = $this->buildHeaderMap($rows[$headerRowIndex]);
        $nameColumn = $this->resolveHeader($headerMap, ['student name', 'name', 'student', 'studentname']);
        $regNoColumn = $this->resolveHeader($headerMap, ['reg no', 'regno', 'reg number', 'registration no', 'registration number', 'registration no.', 'reg']);
        $rollNoColumn = $this->resolveHeader($headerMap, ['roll no', 'rollno', 'roll number', 'enroll id', 'enrollment id', 'enrollid']);
        $regBranchColumn = $this->resolveHeader($headerMap, ['reg branch', 'registration branch', 'regbranch']);
        $dateColumn = $this->resolveHeader($headerMap, ['new reg date', 'updated reg date', 'updated registration date', 'reg date', 'regdate', 'reg_date', 'registration date', 'date']);

        if (!$nameColumn) {
            throw new \RuntimeException('Student Name column was not found.');
        }

        if (!$regNoColumn || !$rollNoColumn) {
            throw new \RuntimeException('Reg No and Roll No columns were not found.');
        }

        if (!$dateColumn) {
            throw new \RuntimeException('New Reg Date column was not found.');
        }

        $parsedRows = [];
        $regNos = [];
        $rollNos = [];

        foreach ($rows as $rowIndex => $row) {
            if ((int) $rowIndex <= (int) $headerRowIndex) {
                continue;
            }

            if ($this->rowIsEmpty($row)) {
                continue;
            }

            $studentName = $this->normalizeString($row[$nameColumn] ?? null);
            $regNo = $this->normalizeIdentifier($row[$regNoColumn] ?? null);
            $rollNo = $this->normalizeIdentifier($rollNoColumn ? ($row[$rollNoColumn] ?? null) : null);
            $regDate = $this->normalizeDateValue($row[$dateColumn] ?? null);
            $regBranchName = $this->normalizeString($regBranchColumn ? ($row[$regBranchColumn] ?? null) : null);
            if ($regBranchName !== '' && in_array(strtoupper($regBranchName), ['#N/A', 'N/A', 'NA', '-'], true)) {
                $regBranchName = '';
            }

            $entry = [
                'row_no' => $rowIndex,
                'student_name' => $studentName,
                'reg_no' => $regNo,
                'roll_no' => $rollNo,
                'reg_date' => $regDate,
                'reg_branch_name' => $regBranchName,
                'reg_branch_id' => '',
                'registration_id' => '',
                'enrollment_id' => '',
                'previous_reg_date' => '',
                'remarks' => '',
                'status' => 'Pending',
                'reason' => '',
            ];

            if ($studentName === '') {
                $entry['status'] = 'Skipped';
                $entry['reason'] = 'Student Name value is missing';
                $parsedRows[] = $entry;
                continue;
            }

            if ($regNo === '' || $rollNo === '') {
                $entry['status'] = 'Skipped';
                $entry['reason'] = 'Reg No and Roll No values are both required';
                $parsedRows[] = $entry;
                continue;
            }

            if ($regDate === null) {
                $entry['status'] = 'Skipped';
                $entry['reason'] = 'New Reg Date value is missing or invalid';
                $parsedRows[] = $entry;
                continue;
            }

            $parsedRows[] = $entry;

            if ($regNo !== '') {
                $regNos[$regNo] = $regNo;
            }

            if ($rollNo !== '') {
                $rollNos[$rollNo] = $rollNo;
            }
        }

        $registrationsQuery = StudentRegistration::query()->select('id', 'reg_no', 'roll_no', 'regdate', 'stdname');
        $registrationsQuery->where(function ($query) use ($regNos, $rollNos) {
            $hasCondition = false;

            if (!empty($regNos)) {
                $query->whereIn('reg_no', array_values($regNos));
                $hasCondition = true;
            }

            if (!empty($rollNos)) {
                if ($hasCondition) {
                    $query->orWhereIn('roll_no', array_values($rollNos));
                } else {
                    $query->whereIn('roll_no', array_values($rollNos));
                }
            }
        });

        $registrations = $registrationsQuery->get();

        $registrationByReg = [];
        $registrationByRoll = [];
        $registrationIds = [];

        foreach ($registrations as $registration) {
            $regKey = $this->normalizeIdentifier($registration->reg_no);
            $rollKey = $this->normalizeIdentifier($registration->roll_no);

            if ($regKey !== '') {
                $registrationByReg[$regKey][] = $registration;
            }

            if ($rollKey !== '') {
                $registrationByRoll[$rollKey][] = $registration;
            }

            $registrationIds[$registration->id] = $registration->id;
        }

        $activeEnrollments = StudentEnrollments::query()
            ->select('id', 'regId')
            ->whereIn('regId', array_values($registrationIds))
            ->where('active_status', 1)
            ->get()
            ->keyBy('regId');

        $branchLookup = [];
        $branchQuery = User::query()
            ->select('id', 'name')
            ->where('type', 'branch')
            ->where('is_active', 1);

        if (Auth::check()) {
            $branchQuery->where('created_by', Auth::user()->creatorId());
        }

        foreach ($branchQuery->get() as $branch) {
            $branchKey = $this->normalizeString($branch->name ?? '');
            if ($branchKey !== '') {
                $branchLookup[$branchKey] = $branch;
            }
        }

        $updated = 0;
        $skipped = 0;
        $reasonCounts = [];
        $reportRows = [];

        DB::beginTransaction();

        try {
            foreach ($parsedRows as $row) {
                if (($row['status'] ?? '') === 'Skipped') {
                    $skipped++;
                    $reasonCounts[$row['reason']] = ($reasonCounts[$row['reason']] ?? 0) + 1;
                    $reportRows[] = $row;
                    continue;
                }

                $registration = $this->resolveRegistration($row['reg_no'], $row['roll_no'], $row['student_name'], $registrationByReg, $registrationByRoll);
                if (!$registration) {
                    $row['status'] = 'Skipped';
                    $row['reason'] = 'No matching registration found';
                    $skipped++;
                    $reasonCounts[$row['reason']] = ($reasonCounts[$row['reason']] ?? 0) + 1;
                    $reportRows[] = $row;
                    continue;
                }

                $row['registration_id'] = $registration->id;
                $row['roll_no'] = $this->normalizeIdentifier($registration->roll_no) ?: $row['roll_no'];
                $row['previous_reg_date'] = $this->formatDateForReport($registration->regdate ?? null);
                $row['student_name'] = $registration->stdname ?? $row['student_name'];

                $branch = $this->resolveBranch($row['reg_branch_name'] ?? '', $branchLookup);
                if ($branch) {
                    $row['reg_branch_id'] = $branch->id;
                }

                $enrollment = $activeEnrollments->get($registration->id);
                if (!$enrollment) {
                    $row['status'] = 'Skipped';
                    $row['reason'] = 'Active enrollment not found';
                    $skipped++;
                    $reasonCounts[$row['reason']] = ($reasonCounts[$row['reason']] ?? 0) + 1;
                    $reportRows[] = $row;
                    continue;
                }

                $row['enrollment_id'] = $enrollment->id;

                $currentRegDate = $this->normalizeDateValue($registration->regdate ?? null);
                $dateNeedsUpdate = $currentRegDate !== $row['reg_date'];
                $branchNeedsUpdate = $branch && (int) ($registration->reg_branch_id ?? 0) !== (int) $branch->id;

                if (!$dateNeedsUpdate && !$branchNeedsUpdate) {
                    $row['status'] = 'Skipped';
                    $row['reason'] = 'Reg Date is already up to date';
                    $skipped++;
                    $reasonCounts[$row['reason']] = ($reasonCounts[$row['reason']] ?? 0) + 1;
                    $reportRows[] = $row;
                    continue;
                }

                $updateData = [];
                if ($dateNeedsUpdate) {
                    $updateData['regdate'] = $row['reg_date'];
                }

                if ($branchNeedsUpdate) {
                    $updateData['reg_branch_id'] = $branch->id;
                }

                if ($row['reg_branch_name'] !== '' && !$branch) {
                    $remark = 'Updated date but branch not found';
                    $row['remarks'] = $remark;
                    $updateData['remarks'] = $this->appendRemark((string) ($registration->remarks ?? ''), $remark);
                }

                StudentRegistration::where('id', $registration->id)->update($updateData);

                $row['status'] = 'Updated';
                $row['reason'] = '';
                $updated++;
                $reportRows[] = $row;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $this->makeReport($uploadedFile->getClientOriginalName() ?? null, $reportRows, $updated, $skipped, $reasonCounts);
    }


    protected function makeReport(?string $fileName, array $rows, int $updated, int $skipped, array $reasonCounts): array
    {
        return [
            'file_name' => $fileName,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'total_rows' => $updated + $skipped,
            'updated_count' => $updated,
            'skipped_count' => $skipped,
            'reason_counts' => $reasonCounts,
            'rows' => $rows,
        ];
    }

    protected function resolveRegistration(string $regNo, string $rollNo, string $studentName, array $byReg, array $byRoll)
    {
        if ($regNo === '' || $rollNo === '') {
            return null;
        }

        $regCandidates = $byReg[$regNo] ?? [];
        $rollCandidates = $byRoll[$rollNo] ?? [];
        $candidateMap = [];

        foreach ($regCandidates as $registration) {
            $candidateMap[$registration->id] = $registration;
        }

        $matches = [];
        foreach ($rollCandidates as $registration) {
            if (isset($candidateMap[$registration->id])) {
                $matches[] = $candidateMap[$registration->id];
            }
        }

        if (empty($matches)) {
            return null;
        }

        if (count($matches) === 1) {
            return $matches[0];
        }

        $nameMatches = array_values(array_filter($matches, function ($registration) use ($studentName) {
            return $this->normalizeString($registration->stdname ?? '') === $studentName;
        }));

        if (count($nameMatches) === 1) {
            return $nameMatches[0];
        }

        return null;
    }

    protected function resolveBranch(string $branchName, array $branchLookup)
    {
        $branchName = $this->normalizeString($branchName);

        if ($branchName === '') {
            return null;
        }

        return $branchLookup[$branchName] ?? null;
    }

    protected function appendRemark(string $existingRemark, string $remark): string
    {
        $existingRemark = trim($existingRemark);
        $remark = trim($remark);

        if ($remark === '') {
            return $existingRemark;
        }

        if ($existingRemark === '') {
            return $remark;
        }

        if (str_contains($existingRemark, $remark)) {
            return $existingRemark;
        }

        return $existingRemark . ' | ' . $remark;
    }


    protected function findHeaderRowIndex(array $rows): ?int
    {
        foreach ($rows as $index => $row) {
            $joined = strtolower(implode('|', array_map(function ($value) {
                return preg_replace('/[^a-z0-9]+/', '', strtolower(trim((string) $value)));
            }, $row)));

            if (str_contains($joined, 'studentname') || str_contains($joined, 'regno') || str_contains($joined, 'rollno') || str_contains($joined, 'newregdate') || str_contains($joined, 'regdate')) {
                return (int) $index;
            }
        }

        return null;
    }


    protected function buildHeaderMap(array $row): array
    {
        $map = [];
        foreach ($row as $column => $value) {
            $map[$this->normalizeHeaderKey($value)] = $column;
        }

        return $map;
    }

    protected function resolveHeader(array $headerMap, array $aliases): ?string
    {
        foreach ($aliases as $alias) {
            $key = $this->normalizeHeaderKey($alias);
            if (isset($headerMap[$key])) {
                return $headerMap[$key];
            }
        }

        foreach ($headerMap as $key => $column) {
            foreach ($aliases as $alias) {
                $aliasKey = $this->normalizeHeaderKey($alias);
                if ($aliasKey !== '' && str_contains($key, $aliasKey)) {
                    return $column;
                }
            }
        }

        return null;
    }

    protected function rowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    protected function normalizeHeaderKey($value): string
    {
        $value = strtolower(trim((string) $value));
        return preg_replace('/[^a-z0-9]+/', '', $value) ?? '';
    }

    protected function normalizeString($value): string
    {
        return trim(preg_replace('/\s+/', ' ', (string) $value));
    }

    protected function normalizeIdentifier($value): string
    {
        if ($value === null) {
            return '';
        }

        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $upper = strtoupper($value);
        if (in_array($upper, ['#N/A', 'N/A', 'NA', '-'], true)) {
            return '';
        }

        if (preg_match('/^\d+\.0+$/', $value)) {
            $value = preg_replace('/\.0+$/', '', $value);
        }

        return $value;
    }


    protected function normalizeDateValue($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        try {
            return Carbon::parse(trim((string) $value))->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function formatDateForReport($value): string
    {
        $normalized = $this->normalizeDateValue($value);
        return $normalized ? Carbon::parse($normalized)->format('d-M-Y') : '';
    }
}
