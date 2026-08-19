<?php

namespace App\Services;

use App\DTOs\ActivityLogFilterData;
use App\Models\ActivityLog;
use App\Models\DocumentType;
use App\Models\StudentDocument;
use App\Models\User;
use App\Repositories\Contracts\ActivityLogRepository;
use App\Repositories\Contracts\UserRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ActivityLogService
{
    private const ALLOWED_PROPERTIES = [
        'role',
        'is_active',
        'changed_fields',
        'previous_role',
        'new_role',
        'code',
        'document_code',
        'previous_user_id',
        'assigned_user_id',
        'previous_status',
        'new_status',
    ];

    public function __construct(
        private readonly ActivityLogRepository $activityLogs,
        private readonly UserRepository $users,
    ) {}

    /** @return array<string, mixed> */
    public function indexData(ActivityLogFilterData $filters): array
    {
        $activityLogs = $this->activityLogs->paginate($filters);
        $activityLogs->getCollection()->transform(function (ActivityLog $activityLog): ActivityLog {
            $activityLog->setAttribute(
                'display_description',
                $this->descriptionFor($activityLog->event),
            );
            $activityLog->setAttribute(
                'display_subject_label',
                $this->subjectLabel($activityLog->subject_type),
            );

            return $activityLog;
        });

        return [
            'activityLogs' => $activityLogs,
            'actorUsers' => $this->users->allForDocumentFilter(),
            'subjectTypes' => $this->subjectTypes(),
        ];
    }

    /** @return array<string, mixed> */
    public function detail(int $id): array
    {
        $activityLog = $this->activityLogs->findForView($id)
            ?? throw (new ModelNotFoundException)->setModel(ActivityLog::class, [$id]);

        return [
            'activityLog' => $activityLog,
            'metadata' => $this->safeProperties($activityLog->properties ?? []),
            'description' => $this->descriptionFor($activityLog->event),
            'subjectLabel' => $this->subjectLabel($activityLog->subject_type),
        ];
    }

    public function recordLogin(User $user): void
    {
        $this->activityLogs->create([
            'log_name' => 'authentication',
            'description' => 'Đăng nhập hệ thống',
            'subject_type' => User::class,
            'subject_id' => $user->getKey(),
            'event' => 'login',
            'causer_type' => User::class,
            'causer_id' => $user->getKey(),
            'properties' => [],
        ]);
    }

    public function recordLogout(User $user): void
    {
        $this->activityLogs->create([
            'log_name' => 'authentication',
            'description' => 'Đăng xuất hệ thống',
            'subject_type' => User::class,
            'subject_id' => $user->getKey(),
            'event' => 'logout',
            'causer_type' => User::class,
            'causer_id' => $user->getKey(),
            'properties' => [],
        ]);
    }

    public function recordUserCreated(User $actor, User $subject): void
    {
        $this->record(
            actor: $actor,
            subject: $subject,
            event: 'user.created',
            description: 'Tạo tài khoản người dùng',
            properties: [
                'role' => $subject->role->value,
                'is_active' => $subject->is_active,
            ],
        );
    }

    /**
     * @param  array<int, string>  $changedFields
     */
    public function recordUserUpdated(
        User $actor,
        User $subject,
        array $changedFields,
        ?string $previousRole = null,
    ): void {
        $roleChanged = in_array('role', $changedFields, true);
        $properties = ['changed_fields' => $changedFields];

        if ($roleChanged) {
            $properties['previous_role'] = $previousRole;
            $properties['new_role'] = $subject->role->value;
        }

        $this->record(
            actor: $actor,
            subject: $subject,
            event: $roleChanged ? 'user.role_changed' : 'user.updated',
            description: $roleChanged ? 'Thay đổi vai trò người dùng' : 'Cập nhật tài khoản người dùng',
            properties: $properties,
        );
    }

    public function recordUserStatusChanged(User $actor, User $subject): void
    {
        $this->record(
            actor: $actor,
            subject: $subject,
            event: $subject->is_active ? 'user.unlocked' : 'user.locked',
            description: $subject->is_active ? 'Mở khóa tài khoản người dùng' : 'Khóa tài khoản người dùng',
            properties: ['is_active' => $subject->is_active],
        );
    }

    public function recordUserPasswordReset(User $actor, User $subject): void
    {
        $this->record(
            actor: $actor,
            subject: $subject,
            event: 'user.password_reset',
            description: 'Đặt lại mật khẩu người dùng',
        );
    }

    public function recordDocumentTypeCreated(User $actor, DocumentType $subject): void
    {
        $this->record(
            actor: $actor,
            subject: $subject,
            event: 'document_type.created',
            description: 'Tạo loại hồ sơ',
            properties: ['code' => $subject->code],
        );
    }

    /**
     * @param  array<int, string>  $changedFields
     */
    public function recordDocumentTypeUpdated(
        User $actor,
        DocumentType $subject,
        array $changedFields,
    ): void {
        $this->record(
            actor: $actor,
            subject: $subject,
            event: 'document_type.updated',
            description: 'Cập nhật loại hồ sơ',
            properties: [
                'code' => $subject->code,
                'changed_fields' => $changedFields,
            ],
        );
    }

    public function recordDocumentTypeStatusChanged(User $actor, DocumentType $subject): void
    {
        $this->record(
            actor: $actor,
            subject: $subject,
            event: $subject->is_active ? 'document_type.activated' : 'document_type.deactivated',
            description: $subject->is_active ? 'Bật loại hồ sơ' : 'Tắt loại hồ sơ',
            properties: [
                'code' => $subject->code,
                'is_active' => $subject->is_active,
            ],
        );
    }

    public function recordStudentDocumentCreated(User $actor, StudentDocument $subject): void
    {
        $this->record(
            actor: $actor,
            subject: $subject,
            event: 'student_document.created',
            description: 'Tạo hồ sơ sinh viên',
            properties: ['document_code' => $subject->document_code],
        );
    }

    public function recordPublicStudentDocumentCreated(StudentDocument $subject): void
    {
        $this->activityLogs->create([
            'log_name' => 'business',
            'description' => 'Tạo hồ sơ sinh viên',
            'subject_type' => StudentDocument::class,
            'subject_id' => $subject->getKey(),
            'event' => 'student_document.created',
            'causer_type' => null,
            'causer_id' => null,
            'properties' => $this->safeProperties([
                'document_code' => $subject->document_code,
            ]),
        ]);
    }

    /** @param array<int, string> $changedFields */
    public function recordStudentDocumentUpdated(
        User $actor,
        StudentDocument $subject,
        array $changedFields,
    ): void {
        $this->record(
            actor: $actor,
            subject: $subject,
            event: 'student_document.updated',
            description: 'Cập nhật hồ sơ sinh viên',
            properties: [
                'document_code' => $subject->document_code,
                'changed_fields' => $changedFields,
            ],
        );
    }

    public function recordStudentDocumentStatusChanged(
        User $actor,
        StudentDocument $subject,
        string $previousStatus,
    ): void {
        $this->record(
            actor: $actor,
            subject: $subject,
            event: 'student_document.status_changed',
            description: 'Chuyển trạng thái hồ sơ sinh viên',
            properties: [
                'document_code' => $subject->document_code,
                'previous_status' => $previousStatus,
                'new_status' => $subject->status->value,
            ],
        );
    }

    public function descriptionFor(?string $event): string
    {
        return match ($event) {
            'login' => 'Đăng nhập hệ thống',
            'logout' => 'Đăng xuất hệ thống',
            'user.created' => 'Tạo tài khoản người dùng',
            'user.updated' => 'Cập nhật tài khoản người dùng',
            'user.role_changed' => 'Thay đổi vai trò người dùng',
            'user.locked' => 'Khóa tài khoản người dùng',
            'user.unlocked' => 'Mở khóa tài khoản người dùng',
            'user.password_reset' => 'Đặt lại mật khẩu người dùng',
            'document_type.created' => 'Tạo loại hồ sơ',
            'document_type.updated' => 'Cập nhật loại hồ sơ',
            'document_type.activated' => 'Bật loại hồ sơ',
            'document_type.deactivated' => 'Tắt loại hồ sơ',
            'student_document.created' => 'Tạo hồ sơ sinh viên',
            'student_document.updated' => 'Cập nhật hồ sơ sinh viên',
            'student_document.assigned' => 'Phân công người phụ trách hồ sơ',
            'student_document.accepted' => 'Tiếp nhận hồ sơ sinh viên',
            'student_document.status_changed' => 'Chuyển trạng thái hồ sơ sinh viên',
            default => 'Hoạt động hệ thống',
        };
    }

    public function subjectLabel(?string $subjectType): string
    {
        return match ($subjectType) {
            User::class => 'Tài khoản người dùng',
            DocumentType::class => 'Loại hồ sơ',
            StudentDocument::class => 'Hồ sơ sinh viên',
            default => 'Đối tượng hệ thống',
        };
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    private function record(
        User $actor,
        User|DocumentType|StudentDocument $subject,
        string $event,
        string $description,
        array $properties = [],
    ): void {
        $this->activityLogs->create([
            'log_name' => 'business',
            'description' => $description,
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'event' => $event,
            'causer_type' => User::class,
            'causer_id' => $actor->getKey(),
            'properties' => $this->safeProperties($properties),
        ]);
    }

    /** @param array<string, mixed> $properties
     * @return array<string, mixed>
     */
    private function safeProperties(array $properties): array
    {
        return array_intersect_key($properties, array_flip(self::ALLOWED_PROPERTIES));
    }

    /** @return array<string, string> */
    private function subjectTypes(): array
    {
        return [
            User::class => 'Tài khoản người dùng',
            DocumentType::class => 'Loại hồ sơ',
            StudentDocument::class => 'Hồ sơ sinh viên',
        ];
    }
}
