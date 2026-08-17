@extends('layouts.public')

@section('title', 'Tra cứu và nộp hồ sơ sinh viên')

@section('content')
<div class="public-home">
    <header class="public-navbar">
        <div class="public-navbar-inner">
            <a href="{{ route('home') }}" class="public-brand" aria-label="Trang chủ Hệ thống quản lý hồ sơ sinh viên">
                <span class="public-logo-frame">
                    <img src="{{ asset('images/tdc-logo.png') }}" alt="Logo Khoa Công nghệ Thông tin" class="public-logo">
                </span>
                <span class="public-brand-text">
                    <span class="public-brand-faculty">Khoa Công nghệ Thông tin</span>
                    <span class="public-brand-system">Hệ thống quản lý hồ sơ sinh viên</span>
                </span>
            </a>

            <nav class="public-nav" aria-label="Điều hướng trang chủ">
                <a href="{{ route('home') }}" class="public-nav-link public-nav-link-active" aria-current="page">Trang chủ</a>
                <a href="#lookup" class="public-nav-link">Tra cứu hồ sơ</a>
                <a href="#submission" class="public-nav-link">Nộp hồ sơ</a>
            </nav>

            <a href="{{ route('login') }}" class="public-login-link">
                <svg aria-hidden="true" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H3" />
                </svg>
                <span class="hidden sm:inline">Đăng nhập nội bộ</span>
                <span class="sm:hidden">Đăng nhập</span>
            </a>
        </div>
    </header>

    <main class="flex-1">
        <section class="public-hero" aria-labelledby="public-home-title">
            <div class="public-hero-inner">
                <div class="public-hero-copy">
                    <p class="public-eyebrow">Cổng dịch vụ sinh viên</p>
                    <h1 id="public-home-title" class="public-hero-title">Tra cứu và nộp hồ sơ sinh viên</h1>
                    <p class="public-hero-description">Theo dõi trạng thái hồ sơ đã nộp hoặc gửi hồ sơ mới đến Khoa Công nghệ Thông tin. Thao tác trực tuyến, không cần đăng nhập.</p>
                    <div class="public-hero-ctas">
                        <a href="#lookup" class="btn-primary">
                            <svg aria-hidden="true" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0Z" />
                            </svg>
                            Tra cứu ngay
                        </a>
                        <a href="#submission" class="btn-secondary">Nộp hồ sơ</a>
                    </div>
                </div>

                <div class="public-hero-actions" aria-label="Chọn thao tác nhanh">
                    <a href="#lookup" class="public-hero-action group">
                        <span class="public-hero-action-icon" aria-hidden="true">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0Z" />
                            </svg>
                        </span>
                        <span class="min-w-0">
                            <span class="public-hero-action-title">Tra cứu hồ sơ</span>
                            <span class="public-hero-action-text">Xem trạng thái bằng MSSV</span>
                        </span>
                        <svg aria-hidden="true" class="public-hero-action-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>

                    <a href="#submission" class="public-hero-action group">
                        <span class="public-hero-action-icon" aria-hidden="true">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </span>
                        <span class="min-w-0">
                            <span class="public-hero-action-title">Nộp hồ sơ</span>
                            <span class="public-hero-action-text">Gửi hồ sơ mới đến Khoa</span>
                        </span>
                        <svg aria-hidden="true" class="public-hero-action-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>
                </div>
            </div>
        </section>

        <section id="services" class="public-services" aria-labelledby="student-services-title">
            <div class="public-services-heading">
                <p class="page-eyebrow">Dịch vụ hồ sơ</p>
                <h2 id="student-services-title" class="public-section-title">Chọn nội dung cần thực hiện</h2>
                <p class="public-section-lead">Nhập mã số sinh viên để tra cứu trạng thái hoặc nộp hồ sơ mới.</p>
            </div>

            <div class="public-service-grid">
                <article id="lookup" class="public-service-card">
                    <div class="public-card-accent" aria-hidden="true"></div>
                    <div class="flex flex-1 flex-col p-5 sm:p-8">
                        <div class="flex items-start gap-4">
                            <span class="public-card-icon" aria-hidden="true">
                                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0Z" />
                                </svg>
                            </span>
                            <div>
                                <h3 class="public-card-title">Tra cứu hồ sơ</h3>
                                <p class="public-card-description">Tìm hồ sơ đã nộp và theo dõi tình trạng xử lý tại Khoa.</p>
                            </div>
                        </div>

                        @php($lookupErrors = $errors->getBag('lookup'))
                        <form action="{{ route('public.documents.lookup') }}#lookup" method="POST" class="mt-6" novalidate>
                            @csrf
                            <label for="lookup-student-code" class="mb-2 block text-sm font-semibold text-portal-ink">Mã số sinh viên (MSSV)</label>
                            <div class="public-lookup-row">
                                <input id="lookup-student-code" name="student_code" type="text" value="{{ old('student_code', $lookupStudentCode ?? '') }}" class="form-control {{ $lookupErrors->has('student_code') ? 'form-control-error' : '' }}" placeholder="Nhập mã số sinh viên" maxlength="20" autocomplete="off" aria-invalid="{{ $lookupErrors->has('student_code') ? 'true' : 'false' }}" @if($lookupErrors->has('student_code')) aria-describedby="lookup-student-code-error" @endif>
                                <button type="submit" class="btn-primary public-lookup-submit">
                                    <svg aria-hidden="true" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0Z" />
                                    </svg>
                                    Tra cứu
                                </button>
                            </div>
                            @if($lookupErrors->has('student_code'))
                            <p id="lookup-student-code-error" class="mt-1.5 text-sm text-red-600">{{ $lookupErrors->first('student_code') }}</p>
                            @endif
                        </form>

                        @unless ($lookupPerformed ?? false)
                        <div class="public-info-box mt-5">
                            <svg aria-hidden="true" class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                            </svg>
                            <p>Nhập đúng mã số sinh viên để xem danh sách hồ sơ đã nộp và trạng thái xử lý.</p>
                        </div>
                        @endunless

                        @if($lookupPerformed ?? false)
                        @if(! ($studentExists ?? false))
                        <div class="public-alert public-alert-warning mt-5" role="status">
                            <svg aria-hidden="true" class="mt-0.5 size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                            </svg>
                            <p>Không tìm thấy sinh viên với mã số đã nhập. Vui lòng kiểm tra lại MSSV.</p>
                        </div>
                        @elseif(($lookupResults ?? []) === [])
                        <div class="public-alert public-alert-info mt-5" role="status">
                            <svg aria-hidden="true" class="mt-0.5 size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                            </svg>
                            <p>Sinh viên chưa có hồ sơ nào trong hệ thống.</p>
                        </div>
                        @else
                        <div class="public-result-list mt-5" role="status">
                            <div class="public-result-list-head">
                                <p class="text-sm font-semibold text-portal-ink">Danh sách hồ sơ</p>
                                <p class="mt-0.5 text-xs text-portal-muted">MSSV: <span class="font-semibold text-portal-ink">{{ $lookupStudentCode }}</span> · {{ count($lookupResults) }} hồ sơ</p>
                            </div>
                            <div class="divide-y divide-portal-200">
                                @foreach($lookupResults as $result)
                                <dl class="public-result-item">
                                    <div>
                                        <dt>Mã hồ sơ</dt>
                                        <dd class="font-mono font-bold text-portal-600">{{ $result['document_code'] }}</dd>
                                    </div>
                                    <div>
                                        <dt>Loại hồ sơ</dt>
                                        <dd class="font-semibold">{{ $result['document_type'] }}</dd>
                                    </div>
                                    <div>
                                        <dt>Trạng thái hiện tại</dt>
                                        <dd>
                                            <span @class([ 'public-status-badge' , 'bg-amber-50 text-amber-800 ring-amber-600/20'=> $result['status'] === 'Chờ tiếp nhận',
                                                'bg-sky-50 text-sky-800 ring-sky-600/20' => $result['status'] === 'Đã tiếp nhận',
                                                'bg-blue-50 text-blue-800 ring-blue-600/20' => $result['status'] === 'Đang xử lý',
                                                'bg-orange-50 text-orange-800 ring-orange-600/20' => $result['status'] === 'Cần bổ sung',
                                                'bg-emerald-50 text-emerald-800 ring-emerald-600/20' => $result['status'] === 'Hoàn tất',
                                                'bg-red-50 text-red-800 ring-red-600/20' => $result['status'] === 'Không hợp lệ',
                                                'bg-slate-100 text-slate-700 ring-slate-500/20' => ! in_array($result['status'], [
                                                'Chờ tiếp nhận',
                                                'Đã tiếp nhận',
                                                'Đang xử lý',
                                                'Cần bổ sung',
                                                'Hoàn tất',
                                                'Không hợp lệ',
                                                ], true),
                                                ])>{{ $result['status'] }}</span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt>Ngày nộp</dt>
                                        <dd class="font-semibold">{{ $result['submitted_at'] }}</dd>
                                    </div>
                                    <div>
                                        <dt>Ngày hoàn thành</dt>
                                        <dd class="font-semibold">{{ $result['completed_at'] ?? '—' }}</dd>
                                    </div>
                                </dl>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        @endif
                    </div>
                </article>

                <article id="submission" class="public-service-card">
                    <div class="public-card-accent" aria-hidden="true"></div>
                    <div class="flex flex-1 flex-col p-5 sm:p-8">
                        <div class="flex items-start gap-4">
                            <span class="public-card-icon" aria-hidden="true">
                                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                            </span>
                            <div>
                                <h3 class="public-card-title">Nộp hồ sơ</h3>
                                <p class="public-card-description">Bắt đầu gửi hồ sơ mới đến Khoa Công nghệ Thông tin.</p>
                            </div>
                        </div>

                        @if(session('public_document_code'))
                        <div class="public-success-card mt-6" role="status">
                            <div class="flex items-start gap-3">
                                <span class="public-success-icon" aria-hidden="true">
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-base font-semibold text-emerald-900">Nộp hồ sơ thành công</p>
                                    <p class="mt-4 text-xs font-semibold tracking-wide text-emerald-800 uppercase">Mã hồ sơ của bạn</p>
                                    <p data-public-document-code class="public-document-code">{{ session('public_document_code') }}</p>
                                    <p class="mt-4 text-sm leading-6 text-emerald-900">Vui lòng lưu mã hồ sơ để tiện theo dõi khi cần.</p>
                                    <div class="mt-4 flex flex-col gap-2 sm:flex-row">
                                        <button type="button" data-copy-document-code class="btn-secondary bg-white">Sao chép mã</button>
                                        <a href="#lookup" class="btn-primary">Đến phần tra cứu</a>
                                    </div>
                                    <p data-copy-status class="mt-2 hidden text-xs font-medium text-emerald-800" aria-live="polite"></p>
                                </div>
                            </div>
                        </div>
                        @endif

                        @php($submissionErrors = $errors->getBag('submission'))
                        <form action="{{ route('public.documents.store') }}" method="POST" class="mt-6 flex flex-1 flex-col" novalidate>
                            @csrf

                            <div>
                                <label for="student-code" class="mb-2 block text-sm font-semibold text-portal-ink">Mã số sinh viên (MSSV)</label>
                                <input id="student-code" name="student_code" type="text" value="{{ old('student_code') }}" class="form-control {{ $submissionErrors->has('student_code') ? 'form-control-error' : '' }}" placeholder="Nhập mã sinh viên" maxlength="20" autocomplete="off" aria-invalid="{{ $submissionErrors->has('student_code') ? 'true' : 'false' }}" @if($submissionErrors->has('student_code')) aria-describedby="student-code-error" @endif>
                                @if($submissionErrors->has('student_code'))
                                <p id="student-code-error" class="mt-1.5 text-sm text-red-600">{{ $submissionErrors->first('student_code') }}</p>
                                @endif
                            </div>

                            <div class="mt-4">
                                <label for="document-type" class="mb-2 block text-sm font-semibold text-portal-ink">Loại hồ sơ</label>
                                <select id="document-type" name="document_type_id" class="form-control {{ $submissionErrors->has('document_type_id') ? 'form-control-error' : '' }}" aria-invalid="{{ $submissionErrors->has('document_type_id') ? 'true' : 'false' }}" @if($submissionErrors->has('document_type_id')) aria-describedby="document-type-error" @endif>
                                    <option value="">Chọn loại hồ sơ</option>
                                    @foreach($documentTypes as $documentType)
                                    <option value="{{ $documentType['id'] }}" @selected((string) old('document_type_id')===(string) $documentType['id'])>{{ $documentType['name'] }}</option>
                                    @endforeach
                                </select>
                                @if($submissionErrors->has('document_type_id'))
                                <p id="document-type-error" class="mt-1.5 text-sm text-red-600">{{ $submissionErrors->first('document_type_id') }}</p>
                                @endif
                            </div>

                            @if($documentTypes === [])
                            <p class="mt-4 text-sm leading-6 text-portal-muted">Hiện chưa có loại hồ sơ khả dụng.</p>
                            @endif

                            <button type="submit" class="btn-primary mt-6 w-full sm:w-auto sm:self-start" @disabled($documentTypes===[])>
                                <svg aria-hidden="true" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                Nộp hồ sơ
                            </button>

                            <div class="public-info-box mt-5">
                                <svg aria-hidden="true" class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                                </svg>
                                <p>Sau khi nộp thành công, bạn sẽ nhận được mã hồ sơ để theo dõi trạng thái xử lý.</p>
                            </div>
                        </form>
                    </div>
                </article>
            </div>
        </section>

        <section class="public-process" aria-labelledby="process-title">
            <div class="public-process-inner">
                <p class="page-eyebrow">Quy trình thực hiện</p>
                <h2 id="process-title" class="public-section-title">Ba bước để theo dõi hồ sơ</h2>
                <ol class="public-process-grid">
                    <li class="public-process-step">
                        <span class="public-process-number" aria-hidden="true">1</span>
                        <h3>Nhập MSSV</h3>
                        <p>Dùng đúng mã số sinh viên được nhà trường cấp.</p>
                    </li>
                    <li class="public-process-step">
                        <span class="public-process-number" aria-hidden="true">2</span>
                        <h3>Tra cứu hoặc nộp hồ sơ</h3>
                        <p>Xem hồ sơ đã có, hoặc chọn loại hồ sơ còn hiệu lực để gửi mới.</p>
                    </li>
                    <li class="public-process-step">
                        <span class="public-process-number" aria-hidden="true">3</span>
                        <h3>Theo dõi trạng thái xử lý</h3>
                        <p>Dùng MSSV hoặc mã hồ sơ để kiểm tra tiến độ tại Khoa.</p>
                    </li>
                </ol>
            </div>
        </section>
    </main>

    <footer class="public-footer">
        <div class="public-footer-inner">
            <div>
                <p class="font-semibold text-white">Khoa Công nghệ Thông tin</p>
                <p class="public-footer-muted mt-1">Cổng dịch vụ tra cứu và nộp hồ sơ sinh viên.</p>
            </div>
            <p class="public-footer-muted">© {{ date('Y') }} Khoa Công nghệ Thông tin</p>
        </div>
    </footer>
</div>
@endsection
