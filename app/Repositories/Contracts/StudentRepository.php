<?php

namespace App\Repositories\Contracts;

use App\Models\Student;
use Illuminate\Support\Collection;

interface StudentRepository
{
    public function findByCode(string $studentCode): ?Student;

    /** @return Collection<int, Student> */
    public function search(string $keyword, int $limit = 20): Collection;
}
