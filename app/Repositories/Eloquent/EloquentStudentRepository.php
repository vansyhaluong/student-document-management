<?php

namespace App\Repositories\Eloquent;

use App\Models\Student;
use App\Repositories\Contracts\StudentRepository;
use Illuminate\Support\Collection;

class EloquentStudentRepository implements StudentRepository
{
    public function findByCode(string $studentCode): ?Student
    {
        $studentCode = trim($studentCode);

        if ($studentCode === '') {
            return null;
        }

        $student = Student::query()->find($studentCode);

        if ($student !== null) {
            return $student;
        }

        return Student::query()
            ->whereRaw('LOWER(student_code) = ?', [mb_strtolower($studentCode)])
            ->first();
    }

    public function search(string $keyword, int $limit = 20): Collection
    {
        $keyword = trim($keyword);

        return Student::query()
            ->where(function ($query) use ($keyword): void {
                $query->where('student_code', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhere('first_name', 'like', "%{$keyword}%")
                    ->orWhereRaw("CONCAT(last_name, ' ', first_name) LIKE ?", ["%{$keyword}%"]);
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->limit(min(max($limit, 1), 100))
            ->get();
    }
}
