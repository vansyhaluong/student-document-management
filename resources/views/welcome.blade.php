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
            </a>

            <nav class="public-nav" aria-label="Điều hướng trang chủ">
                <a href="{{ route('home') }}" class="public-nav-link public-nav-link-active">Trang chủ</a>
                <a href="#lookup" class="public-nav-link">Tra cứu hồ sơ</a>
                <a href="#submission" class="public-nav-link">Nộp hồ sơ</a>
            </nav>

            <a href="{{ route('login') }}" class="public-login-link">
                <svg aria-hidden="true" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                    <path d="M10 17l5-5-5-5M15 12H3M14 4h5a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-5" />
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
                    <p class="public-eyebrow">Cổng thông tin dành cho sinh viên</p>
                    <h1 id="public-home-title" class="public-hero-title">Tra cứu và nộp hồ sơ sinh viên</h1>
                    <p class="public-hero-description">Hỗ trợ sinh viên theo dõi trạng thái và gửi hồ sơ trực tuyến đến Khoa Công nghệ Thông tin.</p>
                </div>

                <div class="public-hero-actions" aria-label="Chọn thao tác nhanh">
                    <a href="#lookup" class="public-hero-action">
                        <span class="public-hero-action-icon" aria-hidden="true">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <circle cx="11" cy="11" r="6" />
                                <path d="m16 16 4 4" />
                            </svg>
                        </span>
                        <span class="min-w-0">
                            <span class="public-hero-action-title">Tra cứu hồ sơ</span>
                            <span class="public-hero-action-text">Xem trạng thái bằng MSSV</span>
                        </span>
                    </a>

                    <a href="#submission" class="public-hero-action">
                        <span class="public-hero-action-icon" aria-hidden="true">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M5 3h10l4 4v14H5V3Z" />
                                <path d="M14 3v5h5M12 17V10M9 13l3-3 3 3" />
                            </svg>
                        </span>
                        <span class="min-w-0">
                            <span class="public-hero-action-title">Nộp hồ sơ</span>
                            <span class="public-hero-action-text">Gửi hồ sơ mới đến Khoa</span>
                        </span>
                    </a>
                </div>
            </div>
        </section>

        <section id="services" class="public-services" aria-labelledby="student-services-title">
            <div class="public-services-heading">
                <p class="page-eyebrow">Dịch vụ hồ sơ</p>
                <h2 id="student-services-title" class="public-section-title">Chọn nội dung cần thực hiện</h2>
            </div>

            <div class="public-service-grid">
                <article id="lookup" class="public-service-card">
                    <div class="public-card-accent" aria-hidden="true"></div>
                    <div class="flex flex-1 flex-col p-5 sm:p-7">
                        <div class="flex items-start gap-4">
                            <span class="public-card-icon" aria-hidden="true">
                                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="11" cy="11" r="6" />
                                    <path d="m16 16 4 4M8 11h6M11 8v6" />
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
                            <label for="lookup-student-code" class="mb-2 block text-sm font-semibold text-slate-800">Mã số sinh viên (MSSV)</label>
                            <input id="lookup-student-code" name="student_code" type="text" value="{{ old('student_code', $lookupStudentCode ?? '') }}" class="form-control {{ $lookupErrors->has('student_code') ? 'form-control-error' : '' }}" placeholder="Nhập mã số sinh viên" maxlength="20" autocomplete="off" aria-invalid="{{ $lookupErrors->has('student_code') ? 'true' : 'false' }}" @if($lookupErrors->has('student_code')) aria-describedby="lookup-student-code-error" @endif>
                            @if($lookupErrors->has('student_code'))
                            <p id="lookup-student-code-error" class="mt-1.5 text-sm text-red-600">{{ $lookupErrors->first('student_code') }}</p>
                            @endif
                            <button type="submit" class="btn-primary mt-3 w-full sm:w-auto">
                                <svg aria-hidden="true" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="6" />
                                    <path d="m16 16 4 4" />
                                </svg>
                                Tra cứu
                            </button>
                        </form>

                        @unless ($lookupPerformed ?? false)
                        <div class="public-info-box mt-5">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                            </svg>

                            <p>Nhập đúng mã số sinh viên để xem danh sách hồ sơ đã nộp và trạng thái xử lý.</p>
                        </div>
                        @endunless

                        @if($lookupPerformed ?? false)
                        @if(! ($studentExists ?? false))
                        <div class="mt-5 flex gap-3 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-900" role="status">
                            <svg aria-hidden="true" class="mt-0.5 size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <circle cx="12" cy="12" r="9" />
                                <path d="M12 11v5M12 8h.01" />
                            </svg>
                            <p>Không tìm thấy sinh viên với mã số đã nhập. Vui lòng kiểm tra lại MSSV.</p>
                        </div>
                        @elseif(($lookupResults ?? []) === [])
                        <div class="mt-5 flex gap-3 rounded-lg border border-blue-200 bg-faculty-50 p-4 text-sm leading-6 text-slate-700" role="status">
                            <svg aria-hidden="true" class="mt-0.5 size-5 shrink-0 text-faculty-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <circle cx="12" cy="12" r="9" />
                                <path d="M12 11v5M12 8h.01" />
                            </svg>
                            <p>Sinh viên chưa có hồ sơ nào trong hệ thống.</p>
                        </div>
                        @else
                        <div class="mt-5 overflow-hidden rounded-lg border border-faculty-200 bg-white" role="status">
                            <div class="border-b border-faculty-100 bg-faculty-50 px-4 py-3">
                                <p class="text-sm font-semibold text-ink-950">Danh sách hồ sơ</p>
                                <p class="mt-0.5 text-xs text-slate-600">MSSV: <span class="font-semibold">{{ $lookupStudentCode }}</span> · {{ count($lookupResults) }} hồ sơ</p>
                            </div>
                            <div class="divide-y divide-faculty-100">
                                @foreach($lookupResults as $result)
                                <dl class="grid gap-3 p-4 text-sm sm:grid-cols-2">
                                    <div>
                                        <dt class="text-xs font-medium text-slate-500">Mã hồ sơ</dt>
                                        <dd class="mt-1 font-mono font-bold text-faculty-900">{{ $result['document_code'] }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-medium text-slate-500">Loại hồ sơ</dt>
                                        <dd class="mt-1 font-semibold text-slate-800">{{ $result['document_type'] }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-medium text-slate-500">Trạng thái hiện tại</dt>
                                        <dd class="mt-1">
                                            <span @class([ 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset' , 'bg-amber-50 text-amber-800 ring-amber-600/20'=> $result['status'] === 'Chờ tiếp nhận',
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
                                        <dt class="text-xs font-medium text-slate-500">Ngày nộp</dt>
                                        <dd class="mt-1 font-semibold text-slate-800">{{ $result['submitted_at'] }}</dd>
                                    </div>
                                    @if($result['completed_at'] !== null)
                                    <div>
                                        <dt class="text-xs font-medium text-slate-500">Ngày hoàn thành</dt>
                                        <dd class="mt-1 font-semibold text-slate-800">{{ $result['completed_at'] }}</dd>
                                    </div>
                                    @endif
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
                    <div class="flex flex-1 flex-col p-5 sm:p-7">
                        <div class="flex items-start gap-4">
                            <span class="public-card-icon" aria-hidden="true">
                                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M5 3h10l4 4v14H5V3Z" />
                                    <path d="M14 3v5h5M12 17V10M9 13l3-3 3 3" />
                                </svg>
                            </span>
                            <div>
                                <h3 class="public-card-title">Nộp hồ sơ</h3>
                                <p class="public-card-description">Bắt đầu gửi hồ sơ mới đến Khoa Công nghệ Thông tin.</p>
                            </div>
                        </div>

                        @if(session('public_document_code'))
                        <div class="public-success-card mt-6" role="status">
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
                        @endif

                        @php($submissionErrors = $errors->getBag('submission'))
                        <form action="{{ route('public.documents.store') }}" method="POST" class="mt-6 flex flex-1 flex-col" novalidate>
                            @csrf

                            <div>
                                <label for="student-code" class="mb-2 block text-sm font-semibold text-slate-800">Mã số sinh viên (MSSV)</label>
                                <input id="student-code" name="student_code" type="text" value="{{ old('student_code') }}" class="form-control {{ $submissionErrors->has('student_code') ? 'form-control-error' : '' }}" placeholder="Nhập mã sinh viên" maxlength="20" autocomplete="off" aria-invalid="{{ $submissionErrors->has('student_code') ? 'true' : 'false' }}" @if($submissionErrors->has('student_code')) aria-describedby="student-code-error" @endif>
                                @if($submissionErrors->has('student_code'))
                                <p id="student-code-error" class="mt-1.5 text-sm text-red-600">{{ $submissionErrors->first('student_code') }}</p>
                                @endif
                            </div>

                            <div class="mt-4">
                                <label for="document-type" class="mb-2 block text-sm font-semibold text-slate-800">Loại hồ sơ</label>
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
                            <p class="mt-4 text-sm leading-6 text-slate-600">Hiện chưa có loại hồ sơ khả dụng.</p>
                            @endif

                            <button type="submit" class="btn-primary mt-6 w-full sm:w-auto sm:self-start" @disabled($documentTypes===[])>
                                <svg aria-hidden="true" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 5v14M5 12h14" />
                                </svg>
                                Nộp hồ sơ
                            </button>

                            <div class="public-info-box mt-5">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                                </svg>

                                <p>Sau khi nộp thành công, bạn sẽ nhận được mã hồ sơ để theo dõi trạng thái xử lý.</p>
                            </div>
                        </form>
                    </div>
                </article>
            </div>
        </section>
    </main>

    <footer class="public-footer">
        <div class="public-footer-inner">
            <p class="font-semibold">Khoa Công nghệ Thông tin</p>
            <p class="public-footer-muted">Hệ thống quản lý hồ sơ sinh viên</p>
        </div>
    </footer>
</div>
@endsection