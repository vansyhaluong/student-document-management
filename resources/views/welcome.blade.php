@extends('layouts.public')

@section('title', 'Tra cứu và nộp hồ sơ sinh viên')

@section('content')
<div class="public-home">
    <header class="public-navbar">
        <div class="public-navbar-inner">
            <a href="{{ route('home') }}" class="public-brand" aria-label="Trang chủ Khoa Công nghệ Thông tin">
                <span class="public-logo-frame">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Logo Khoa Công nghệ Thông tin" class="public-logo" width="88" height="88">
                </span>
                <span class="public-brand-text">
                    <span class="public-brand-faculty">Khoa Công nghệ Thông tin</span>
                    <span class="public-brand-system">Cao đẳng Công nghệ Thủ Đức</span>
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
            <h1 id="public-home-title" class="sr-only">Tra cứu và nộp hồ sơ sinh viên</h1>

            <a href="#services" class="public-hero-banner">
                <img src="{{ asset('images/banner-tdc.png') }}" alt="Cổng thông tin dành cho sinh viên — Tra cứu và nộp hồ sơ sinh viên. Hỗ trợ theo dõi trạng thái và gửi hồ sơ trực tuyến đến Khoa Công nghệ Thông tin." width="1672" height="941">
            </a>

            <!-- <div class="public-hero-inner">
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
            </div> -->
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
                <header class="public-process-heading">
                    <p class="public-process-eyebrow">Quy trình thực hiện</p>
                    <h2 id="process-title" class="public-process-title">Theo dõi hồ sơ chỉ với 3 bước</h2>
                </header>

                <ol class="public-process-steps">
                    <li class="public-process-step">
                        <div class="public-process-rail">
                            <span class="public-process-number">1</span>
                        </div>
                        <div class="public-process-copy">
                            <span class="public-process-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </span>
                            <h3>Nhập MSSV</h3>
                            <p>Nhập mã số sinh viên do nhà trường cấp.</p>
                        </div>
                    </li>
                    <li class="public-process-step">
                        <div class="public-process-rail">
                            <span class="public-process-number">2</span>
                        </div>
                        <div class="public-process-copy">
                            <span class="public-process-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0ZM8.25 7.5h7.5M8.25 11.25h4.5" />
                                </svg>
                            </span>
                            <h3>Tra cứu hoặc nộp hồ sơ</h3>
                            <p>Xem hồ sơ hiện có hoặc chọn loại hồ sơ cần gửi.</p>
                        </div>
                    </li>
                    <li class="public-process-step">
                        <div class="public-process-rail">
                            <span class="public-process-number">3</span>
                        </div>
                        <div class="public-process-copy">
                            <span class="public-process-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </span>
                            <h3>Theo dõi trạng thái</h3>
                            <p>Kiểm tra tiến độ xử lý và kết quả hồ sơ.</p>
                        </div>
                    </li>
                </ol>
            </div>
        </section>
    </main>

    <footer class="public-footer">
        <div class="public-footer-inner">
            <div class="public-footer-layout">
                <div class="public-footer-main">
                    <div class="public-footer-brand">
                        <span class="public-footer-logo">
                            <img src="{{ asset('images/logo.jpg') }}" alt="Logo Khoa Công nghệ Thông tin, Trường Cao đẳng Công nghệ Thủ Đức" width="88" height="88">
                        </span>
                        <div>
                            <p class="public-footer-school">Trường Cao đẳng Công nghệ Thủ Đức</p>
                            <p class="public-footer-faculty">Khoa Công nghệ Thông tin</p>
                            <p class="public-footer-address">53 Võ Văn Ngân, Phường Thủ Đức, Thành phố Hồ Chí Minh</p>
                        </div>
                    </div>

                    <div class="public-footer-columns">
                        <div>
                            <h2 class="public-footer-heading">Thông tin chung</h2>
                            <ul class="public-footer-list">
                                <li><a href="https://fit.tdc.edu.vn/gioi-thieu" target="_blank" rel="noopener noreferrer">Giới thiệu</a></li>
                                <li><a href="https://fit.tdc.edu.vn/lich-truc-khoa" target="_blank" rel="noopener noreferrer">Lịch trực Khoa</a></li>
                                <li><a href="https://fit.tdc.edu.vn/thoi-khoa-bieu" target="_blank" rel="noopener noreferrer">Thời khóa biểu</a></li>
                                <li><a href="https://tdc.edu.vn/" target="_blank" rel="noopener noreferrer">Trường Cao đẳng Công nghệ Thủ Đức</a></li>
                            </ul>
                        </div>
                        <div>
                            <h2 class="public-footer-heading">Về chúng tôi</h2>
                            <ul class="public-footer-list">
                                <li><a href="https://fit.tdc.edu.vn/lien-he" target="_blank" rel="noopener noreferrer">Liên hệ</a></li>
                                <li><a href="https://fit.tdc.edu.vn/co-so-vat-chat" target="_blank" rel="noopener noreferrer">Cơ sở vật chất</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="public-footer-aside">
                    <h2 class="public-footer-heading">Liên hệ</h2>
                    <ul class="public-footer-contact">
                        <li>
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                            </svg>
                            <a href="tel:+842822158642">(028) 2215 8642</a>
                        </li>
                        <li>
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.71 3 17.25m0 0 3.75 3.75M3 17.25h13.5A4.5 4.5 0 0 0 21 12.75v-1.5A4.5 4.5 0 0 0 16.5 6.75H9.75" />
                            </svg>
                            <a href="tel:+842838962474">(028) 3896 2474</a>
                        </li>
                        <li>
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                            </svg>
                            <a href="mailto:fit@tdc.edu.vn">fit@tdc.edu.vn</a>
                        </li>
                    </ul>

                    <div class="public-footer-social">
                        <a href="https://www.facebook.com/tdc.fit/" target="_blank" rel="noopener noreferrer" aria-label="Facebook Khoa Công nghệ Thông tin">
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="currentColor"><path d="M14.5 8.25H16.5V5.5h-2c-2.3 0-3.75 1.4-3.75 3.85v1.9H8.5v2.7h2.25V20h2.85v-6.05H16.1l.45-2.7h-2.95V9.55c0-.8.35-1.3 1.4-1.3Z" /></svg>
                        </a>
                        <a href="https://www.tiktok.com/@fittdc" target="_blank" rel="noopener noreferrer" aria-label="TikTok Khoa Công nghệ Thông tin">
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="currentColor"><path d="M14.2 4c.3 1.7 1.4 3.1 3 3.8V10c-1.3 0-2.5-.4-3.5-1.1v5.3c0 2.9-2.4 5.3-5.4 5.3A5.35 5.35 0 0 1 4.8 16c.7 1 1.9 1.7 3.2 1.7 2.1 0 3.8-1.7 3.8-3.8V4h2.4Z" /></svg>
                        </a>
                        <a href="https://www.youtube.com/fit-tdc" target="_blank" rel="noopener noreferrer" aria-label="YouTube Khoa Công nghệ Thông tin">
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="currentColor"><path d="M21.6 7.2a2.8 2.8 0 0 0-2-2C17.9 4.8 12 4.8 12 4.8s-5.9 0-7.6.4a2.8 2.8 0 0 0-2 2A29 29 0 0 0 2 12a29 29 0 0 0 .4 4.8 2.8 2.8 0 0 0 2 2c1.7.4 7.6.4 7.6.4s5.9 0 7.6-.4a2.8 2.8 0 0 0 2-2A29 29 0 0 0 22 12a29 29 0 0 0-.4-4.8ZM10.2 15.2V8.8L15.6 12l-5.4 3.2Z" /></svg>
                        </a>
                        <a href="https://vn.linkedin.com/school/fit-tdc/" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn Khoa Công nghệ Thông tin">
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="currentColor"><path d="M6.5 9.3H4V20h2.5V9.3ZM5.2 4A1.5 1.5 0 1 0 5.2 7a1.5 1.5 0 0 0 0-3ZM20 20h-2.5v-5.2c0-1.7-.7-2.3-1.7-2.3s-1.9.8-1.9 2.4V20H11.4V9.3h2.4v1.5c.5-.9 1.6-1.8 3.3-1.8 2.3 0 2.9 1.5 2.9 4.2V20Z" /></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="public-footer-bottom">
            <div class="public-footer-inner">
                <p>© 2026 <a href="https://fit.tdc.edu.vn/" target="_blank" rel="noopener noreferrer">Khoa Công nghệ Thông tin | Cao đẳng Công nghệ Thủ Đức | FIT - TDC</a></p>
                <p>All Rights Reserved.</p>
            </div>
        </div>
    </footer>
</div>
@endsection