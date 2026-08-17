<?php

namespace Tests\Feature;

use App\Exceptions\BusinessRuleException;
use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;

class ApiExceptionHandlingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/api/testing/validation-error', function () {
            $validator = Validator::make([], ['name' => ['required']]);

            throw new ValidationException($validator);
        });
        Route::get('/api/testing/authorization-error', fn () => throw new AuthorizationException);
        Route::get('/api/testing/authentication-error', fn () => throw new AuthenticationException);
        Route::get('/api/testing/not-found', function () {
            throw (new ModelNotFoundException)->setModel('StudentDocument');
        });
        Route::get('/api/testing/business-error', fn () => throw new BusinessRuleException(
            'Không thể chuyển trạng thái',
            ['status' => ['Chuyển trạng thái không hợp lệ.']],
            400,
        ));
        Route::get('/api/testing/system-error', fn () => throw new RuntimeException(
            'Sensitive implementation detail',
        ));
        Route::get('/api/testing/success', fn () => ApiResponse::success(
            ['id' => 1],
            'Lấy dữ liệu thành công',
        ));
        Route::get('/api/testing/paginated', fn () => ApiResponse::paginated(
            new LengthAwarePaginator([['id' => 1]], 21, 10, 2),
        ));
    }

    public function test_success_and_pagination_responses_follow_the_standard_format(): void
    {
        $this->getJson('/api/testing/success')
            ->assertOk()
            ->assertExactJson([
                'success' => true,
                'message' => 'Lấy dữ liệu thành công',
                'data' => ['id' => 1],
            ]);

        $this->getJson('/api/testing/paginated')
            ->assertOk()
            ->assertExactJson([
                'success' => true,
                'message' => 'Lấy dữ liệu thành công',
                'data' => [['id' => 1]],
                'meta' => [
                    'current_page' => 2,
                    'per_page' => 10,
                    'total' => 21,
                    'last_page' => 3,
                ],
            ]);
    }

    public function test_validation_errors_follow_the_standard_response(): void
    {
        $this->getJson('/api/testing/validation-error')
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
            ])
            ->assertJsonStructure(['errors' => ['name']]);
    }

    public function test_authorization_and_not_found_errors_are_standardized(): void
    {
        $this->getJson('/api/testing/authentication-error')
            ->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Chưa xác thực',
            ]);

        $this->getJson('/api/testing/authorization-error')
            ->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Không có quyền thực hiện thao tác này',
            ]);

        $this->getJson('/api/testing/not-found')
            ->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Không tìm thấy dữ liệu',
            ]);
    }

    public function test_business_errors_include_only_the_declared_details(): void
    {
        $this->getJson('/api/testing/business-error')
            ->assertStatus(400)
            ->assertExactJson([
                'success' => false,
                'message' => 'Không thể chuyển trạng thái',
                'errors' => [
                    'status' => ['Chuyển trạng thái không hợp lệ.'],
                ],
            ]);
    }

    public function test_system_errors_do_not_expose_internal_details(): void
    {
        $response = $this->getJson('/api/testing/system-error')
            ->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'Có lỗi xảy ra',
            ]);

        $this->assertStringNotContainsString('Sensitive implementation detail', $response->getContent());
    }
}
