<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tra cứu &amp; Nộp đơn Sinh viên</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,600;1,6..72,500&family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@500&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy-deep: #0E1F35;
            --navy: #17304F;
            --paper: #F6F7F9;
            --card: #FFFFFF;
            --amber: #C97A2E;
            --amber-dark: #A8631F;
            --slate: #5B6B82;
            --ink: #1A1D23;
            --line: #E1E4EA;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: var(--paper);
            color: var(--ink);
            font-family: 'IBM Plex Sans', sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        a { color: inherit; }

        header.top {
            background: var(--navy-deep);
            color: #fff;
            padding: 18px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        header.top .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: 'IBM Plex Mono', monospace;
            font-size: 14px;
            letter-spacing: 0.04em;
        }
        header.top .brand .mark {
            width: 28px; height: 28px;
            border: 1.5px solid #fff;
            border-radius: 4px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 500;
        }
        header.top nav a {
            text-decoration: none;
            font-size: 14px;
            color: #C7D0DC;
            margin-left: 28px;
        }
        header.top nav a:hover { color: #fff; }

        .hero {
            max-width: 1080px;
            margin: 0 auto;
            padding: 72px 32px 56px;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 56px;
            align-items: center;
        }
        @media (max-width: 860px) {
            .hero { grid-template-columns: 1fr; padding-top: 48px; }
        }

        .eyebrow {
            font-family: 'IBM Plex Mono', monospace;
            font-size: 12px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--amber-dark);
            margin: 0 0 14px;
        }
        h1 {
            font-family: 'Newsreader', serif;
            font-weight: 600;
            font-size: 44px;
            line-height: 1.15;
            margin: 0 0 18px;
            color: var(--navy-deep);
        }
        h1 em {
            font-style: italic;
            font-weight: 500;
            color: var(--amber-dark);
        }
        .lede {
            color: var(--slate);
            font-size: 16px;
            line-height: 1.6;
            max-width: 46ch;
            margin: 0 0 32px;
        }

        /* ---- Signature element: thẻ tra cứu MSSV kiểu "thẻ sinh viên" ---- */
        .lookup-card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 28px;
            box-shadow: 0 1px 2px rgba(14,31,53,0.04), 0 12px 32px -16px rgba(14,31,53,0.18);
        }
        .lookup-card .slot {
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 16px;
            margin-bottom: 20px;
            border-bottom: 1px dashed var(--line);
        }
        .lookup-card .slot .dot {
            width: 7px; height: 7px; border-radius: 50%;
            background: var(--amber);
        }
        .lookup-card .slot span {
            font-family: 'IBM Plex Mono', monospace;
            font-size: 11px;
            letter-spacing: 0.06em;
            color: var(--slate);
            text-transform: uppercase;
        }
        .field label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--navy);
            margin-bottom: 8px;
        }
        .field input {
            width: 100%;
            font-family: 'IBM Plex Mono', monospace;
            font-size: 18px;
            letter-spacing: 0.03em;
            padding: 13px 14px;
            border: 1.5px solid var(--line);
            border-radius: 8px;
            outline: none;
            transition: border-color .15s ease, box-shadow .15s ease;
            background: #FCFCFD;
        }
        .field input:focus {
            border-color: var(--navy);
            box-shadow: 0 0 0 3px rgba(23,48,79,0.10);
        }
        .field input::placeholder { color: #A6AEBB; }

        .lookup-card button {
            width: 100%;
            margin-top: 18px;
            padding: 13px 16px;
            background: var(--amber);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background .15s ease;
        }
        .lookup-card button:hover { background: var(--amber-dark); }
        .lookup-card button:focus-visible {
            outline: 2px solid var(--navy);
            outline-offset: 2px;
        }

        .lookup-card .hint {
            margin-top: 14px;
            font-size: 12.5px;
            color: var(--slate);
            line-height: 1.5;
        }

        /* ---- Danh mục loại đơn ---- */
        section.types {
            max-width: 1080px;
            margin: 0 auto;
            padding: 8px 32px 80px;
        }
        section.types h2 {
            font-family: 'Newsreader', serif;
            font-weight: 600;
            font-size: 24px;
            color: var(--navy-deep);
            margin: 0 0 6px;
        }
        section.types p.sub {
            color: var(--slate);
            font-size: 14.5px;
            margin: 0 0 28px;
        }
        .type-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1px;
            background: var(--line);
            border: 1px solid var(--line);
            border-radius: 12px;
            overflow: hidden;
        }
        @media (max-width: 760px) {
            .type-grid { grid-template-columns: 1fr; }
        }
        .type-item {
            background: var(--card);
            padding: 22px 22px 24px;
        }
        .type-item .idx {
            font-family: 'IBM Plex Mono', monospace;
            font-size: 12px;
            color: var(--amber-dark);
            margin-bottom: 10px;
        }
        .type-item h3 {
            font-size: 15px;
            font-weight: 600;
            margin: 0 0 6px;
            color: var(--ink);
        }
        .type-item p {
            font-size: 13px;
            color: var(--slate);
            margin: 0;
            line-height: 1.5;
        }

        footer.site {
            border-top: 1px solid var(--line);
            padding: 24px 32px;
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            color: var(--slate);
            max-width: 1080px;
            margin: 0 auto;
        }

        @media (prefers-reduced-motion: reduce) {
            * { transition: none !important; }
        }
    </style>
</head>
<body>

    <header class="top">
        <div class="brand">
            <span class="mark">DT</span>
            DocTrack — Cổng tra cứu sinh viên
        </div>
        <nav>
            <a href="{{ route('lookup.show') ?? '#' }}">Tra cứu đơn</a>
            <a href="{{ route('document.create') ?? '#' }}">Nộp đơn mới</a>
        </nav>
    </header>

    <div class="hero">
        <div>
            <p class="eyebrow">Hệ thống Tra cứu &amp; Nộp đơn Sinh viên</p>
            <h1>Nhập <em>mã số sinh viên</em>,<br>theo dõi hồ sơ của bạn.</h1>
            <p class="lede">
                Không cần tài khoản, không cần mật khẩu. Nhập MSSV để nộp đơn mới
                hoặc xem trạng thái các đơn đã gửi — từ "Đã gửi" đến "Đã tiếp nhận".
            </p>
        </div>

        <form class="lookup-card" method="POST" action="{{ route('lookup.submit') ?? '#' }}">
            @csrf
            <div class="slot">
                <span class="dot"></span>
                <span>Tra cứu bằng mã số sinh viên</span>
            </div>
            <div class="field">
                <label for="mssv">Mã số sinh viên (MSSV)</label>
                <input
                    type="text"
                    id="mssv"
                    name="mssv"
                    placeholder="VD: 23211TT4672"
                    autocomplete="off"
                    required
                >
            </div>
            <button type="submit">Tra cứu / Nộp đơn</button>
            <p class="hint">
                Hệ thống chỉ hiển thị họ tên và ngày sinh để xác nhận đúng sinh viên —
                không lưu trữ thông tin đăng nhập nào khác.
            </p>
        </form>
    </div>

    <section class="types">
        <h2>Các loại đơn có thể nộp</h2>
        <p class="sub">Chọn đúng loại đơn sẽ giúp bộ phận xử lý tiếp nhận nhanh hơn.</p>

        <div class="type-grid">
            @forelse ($documentTypes ?? [] as $index => $type)
                <div class="type-item">
                    <div class="idx">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</div>
                    <h3>{{ $type->name }}</h3>
                    <p>{{ $type->description }}</p>
                </div>
            @empty
                {{-- fallback tĩnh khi chưa seed document_types --}}
                <div class="type-item">
                    <div class="idx">01</div>
                    <h3>Đơn xin nghỉ học tạm thời</h3>
                    <p>Áp dụng khi sinh viên cần tạm dừng học tập trong một học kỳ.</p>
                </div>
                <div class="type-item">
                    <div class="idx">02</div>
                    <h3>Đơn xin chuyển ngành</h3>
                    <p>Đăng ký nguyện vọng chuyển sang ngành đào tạo khác.</p>
                </div>
                <div class="type-item">
                    <div class="idx">03</div>
                    <h3>Đơn xin cấp lại bảng điểm</h3>
                    <p>Yêu cầu cấp lại bảng điểm khi bị mất hoặc cần bổ sung hồ sơ.</p>
                </div>
            @endforelse
        </div>
    </section>

    <footer class="site">
        <span>© {{ date('Y') }} DocTrack — Phòng Công tác Sinh viên</span>
        <span>Hỗ trợ: support@truong.edu.vn</span>
    </footer>

</body>
</html>
