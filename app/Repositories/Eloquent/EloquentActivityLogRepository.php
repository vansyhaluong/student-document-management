<?php

namespace App\Repositories\Eloquent;

use App\DTOs\ActivityLogFilterData;
use App\Models\ActivityLog;
use App\Models\User;
use App\Repositories\Contracts\ActivityLogRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EloquentActivityLogRepository implements ActivityLogRepository
{
    public function create(array $attributes): ActivityLog
    {
        return ActivityLog::query()->create($attributes);
    }

    public function paginate(ActivityLogFilterData $filters): LengthAwarePaginator
    {
        return $this->applyFilters($this->viewQuery(), $filters)
            ->orderByDesc('activity_log.created_at')
            ->orderByDesc('activity_log.id')
            ->paginate($filters->perPage)
            ->withQueryString();
    }

    public function findForView(int $id): ?ActivityLog
    {
        return $this->viewQuery()->find($id);
    }

    private function viewQuery(): Builder
    {
        return ActivityLog::query()
            ->leftJoin('users as causers', function ($join): void {
                $join->on('causers.id', '=', 'activity_log.causer_id')
                    ->where('activity_log.causer_type', User::class);
            })
            ->select([
                'activity_log.*',
                'causers.full_name as actor_name',
            ]);
    }

    private function applyFilters(Builder $query, ActivityLogFilterData $filters): Builder
    {
        return $query
            ->when(
                $filters->event !== null,
                fn (Builder $query) => $query->where('activity_log.event', $filters->event),
            )
            ->when(
                $filters->actorUserId !== null,
                fn (Builder $query) => $query->where('activity_log.causer_id', $filters->actorUserId),
            )
            ->when(
                $filters->subjectType !== null,
                fn (Builder $query) => $query->where('activity_log.subject_type', $filters->subjectType),
            )
            ->when(
                $filters->subjectId !== null,
                fn (Builder $query) => $query->where('activity_log.subject_id', $filters->subjectId),
            )
            ->when(
                $filters->from !== null,
                fn (Builder $query) => $query->whereDate('activity_log.created_at', '>=', $filters->from),
            )
            ->when(
                $filters->to !== null,
                fn (Builder $query) => $query->whereDate('activity_log.created_at', '<=', $filters->to),
            );
    }
}
