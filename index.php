<?php
/** Only company IP may load portal HTML. */
require __DIR__ . '/access.php';
amnote_guard();
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>

<html lang="vi">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1" name="viewport"/>
<meta content="light" name="color-scheme"/>
<title>AMnote · Server Management</title>
<style>
    :root{--ink:#202839;--muted:#727e8e;--line:#e6e9ee;--bg:#f5f7fa;--panel:#fff;--nav:#141c2c;--red:#d6404b;--green:#16835d;--amber:#bb7920;--blue:#3976c5;--shadow:0 12px 34px rgba(24,36,56,.035)}
    *{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;background:var(--bg);color:var(--ink);font:14px/1.5 -apple-system,BlinkMacSystemFont,"Segoe UI",Arial,sans-serif}button,input,a{font:inherit}button{cursor:pointer}a{text-decoration:none;color:inherit}svg{display:block}.layout{display:flex;min-height:100vh}
    .sidebar{position:sticky;top:0;align-self:flex-start;display:flex;flex-direction:column;gap:24px;width:250px;min-width:250px;height:100vh;background:var(--nav);color:#a8b4c7;padding:30px 18px}
    .brand{display:flex;gap:11px;align-items:center;padding:2px 12px 25px;border-bottom:1px solid #2b3443}.brandmark{display:grid;place-items:center;width:41px;height:41px;border-radius:12px;background:linear-gradient(145deg,#ef6570,#c62639);color:white;font-weight:800;font-size:17px;letter-spacing:-1px}.brand strong{display:block;color:#fff;font-size:20px;letter-spacing:-.8px;line-height:1.2}.brand span{display:block;font-size:10px;text-transform:uppercase;letter-spacing:1.75px;margin-top:5px;color:#8897ac}
    .nav-caption{font-size:10px;letter-spacing:1.65px;color:#6f8099;font-weight:800;text-transform:uppercase;padding:4px 14px 10px}.navlink{display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:10px;color:#b7c3d5;margin:2px 0;transition:.15s}.navlink:hover{background:#252f42;color:white}.navlink.active{background:#323c4e;color:white;box-shadow:inset 3px 0 var(--red)}.navlink .symbol{font-size:19px;width:21px;text-align:center;opacity:.88}.sidebar-bottom{margin-top:auto;border:1px solid #323c4b;background:#1e2838;border-radius:14px;padding:15px}.sidebar-bottom small{display:block;color:#899cb3;line-height:1.6}.sidebar-bottom strong{color:#e4eaf2;font-weight:650}.sidebar-bottom .dot{display:inline-block;width:7px;height:7px;background:#7f8ea3;border-radius:50%;margin-right:7px}
    .main{width:calc(100% - 250px);max-width:1800px;min-width:0;margin:0 auto;padding:32px clamp(20px,3.1vw,52px) 50px}.top{display:flex;justify-content:space-between;align-items:center;gap:14px;margin-bottom:29px}.eyebrow{display:flex;gap:8px;align-items:center;color:#738398;font-size:12px;font-weight:700;letter-spacing:1.4px;text-transform:uppercase}.eyebrow .small-bar{height:3px;width:15px;background:var(--red);border-radius:2px}.top h1{font-size:clamp(24px,2.35vw,32px);letter-spacing:-1px;margin:5px 0 5px;font-weight:770}.sub{color:var(--muted);font-size:13px}.top-actions{display:flex;gap:10px;align-items:center;flex-wrap:wrap}.smallbtn{height:38px;border:1px solid #dce1e9;background:#fff;border-radius:10px;padding:0 13px;display:inline-flex;gap:8px;align-items:center;justify-content:center;color:#39465b;font-weight:650;white-space:nowrap}.smallbtn:hover{border-color:#a7b4c8;background:#f8fafc}.smallbtn.primary{background:#1d293b;border:1px solid #1d293b;color:white}.smallbtn.primary:hover{background:#303e54}
    .notice{display:flex;align-items:flex-start;gap:11px;background:#fff;border:1px solid #e4e9f0;border-left:3px solid var(--red);padding:15px 17px;border-radius:12px;margin-bottom:22px;box-shadow:var(--shadow)}.notice i{font-style:normal;font-size:20px;line-height:1}.notice strong{display:block;font-size:13px}.notice p{margin:3px 0 0;color:#718096;font-size:12px}.notice .notice-state{margin-left:auto;white-space:nowrap;border-radius:99px;padding:5px 10px;color:#946319;background:#fff5e5;font-size:11px;font-weight:700}.notice .notice-state.online{color:#0c7552;background:#e9f7f0}
    .stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-bottom:25px}.stat{background:var(--panel);padding:21px;border-radius:16px;border:1px solid var(--line);box-shadow:var(--shadow);min-height:160px}.stat-head{display:flex;align-items:center;justify-content:space-between;gap:8px}.stat-name{font-size:12px;color:#778398;font-weight:650}.stat-icon{display:grid;place-items:center;width:36px;height:36px;border-radius:11px;background:#eef3fa;color:#506a93;font-size:17px}.stat-icon.red{color:#c6454c;background:#fcf0f0}.stat-value{font-size:30px;letter-spacing:-1.2px;font-weight:780;margin-top:16px;line-height:1.2;font-variant-numeric:tabular-nums}.stat-foot{font-size:11px;color:#8895a8;margin-top:11px;white-space:normal}.bar{height:6px;width:100%;border-radius:20px;background:#edf0f5;overflow:hidden;margin-top:13px}.bar>span{display:block;width:0;height:100%;border-radius:20px;background:#506a94;transition:width .4s}.bar.red>span{background:linear-gradient(90deg,#ec8085,#cb3f4a)}
    .bodygrid{display:grid;grid-template-columns:minmax(0,1.52fr) minmax(295px,1fr);gap:18px;margin-bottom:25px}.panel{border-radius:16px;background:var(--panel);border:1px solid var(--line);box-shadow:var(--shadow);min-width:0;overflow:hidden}.panel-pad{padding:22px 23px}.section-title{font-size:16px;letter-spacing:-.3px;font-weight:760;margin:0}.section-sub{color:#8692a1;font-size:12px;margin-top:5px}.panel-head{display:flex;align-items:start;justify-content:space-between;gap:14px;margin-bottom:20px}.panel-chip{display:inline-flex;align-items:center;background:#f0f4f9;border-radius:99px;color:#5b6980;padding:6px 10px;font-size:11px;font-weight:700;white-space:nowrap}
    #cpuChart{display:block;width:100%;height:158px;overflow:visible}#cpuChart .gridline{stroke:#ebeff4;stroke-width:1}#cpuChart .polyline{fill:none;stroke:var(--red);stroke-width:2.7;stroke-linecap:round;stroke-linejoin:round}.chart-foot{display:flex;justify-content:space-between;gap:10px;color:#8b97a7;font-size:11px;margin-top:5px}.desc{color:#8996a7;font-size:12px}.specs{display:grid;grid-template-columns:1fr 1fr;gap:17px 14px;margin-top:19px}.specs .k{display:block;color:#8a95a6;font-size:11px}.specs .v{display:block;font-size:13px;font-weight:700;margin-top:5px;word-break:break-word}.mini-dot{display:inline-block;height:8px;width:8px;border-radius:50%;background:#b8c3d1;margin-right:6px}.mini-dot.green{background:#22aa78}
    .services-head{display:flex;align-items:center;justify-content:space-between;gap:15px;margin-bottom:17px}.services-controls{display:flex;gap:8px;align-items:center;flex-wrap:wrap}#search{width:225px;height:38px;border-radius:10px;border:1px solid #dce2ea;padding:0 13px;color:#2d3546;background:#fff;outline:none}#search:focus{border-color:#718cae;box-shadow:0 0 0 3px #d9e8fc}.filter{border:1px solid #e1e6ec;border-radius:9px;background:#fff;padding:9px 12px;font-weight:650;font-size:12px;color:#788497}.filter.active{color:#b52f3e;background:#fff3f3;border-color:#f3d5d8}.servicegrid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:13px}.service{background:#fff;border:1px solid #e7eaf0;border-radius:15px;padding:17px 16px;min-height:176px;display:flex;flex-direction:column;gap:13px;transition:border-color .15s, transform .15s}.service:hover{border-color:#c1cfdf;transform:translateY(-2px)}.service-title{display:flex;justify-content:space-between;gap:10px;align-items:start}.service-icon{display:grid;place-items:center;width:36px;height:36px;border-radius:11px;background:#eef3f9;color:#4d668a;font-size:17px}.service-title h3{font-size:13px;margin:9px 0 2px;font-weight:760}.service-desc{font-size:11px;color:#929dad;line-height:1.45}.service-port{font:600 11px/1.3 ui-monospace,Consolas,monospace;color:#68778b;border-radius:7px;background:#f2f5f8;padding:4px 8px;white-space:nowrap}.service-bottom{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:auto}.health{display:inline-flex;align-items:center;gap:6px;color:#8390a2;font-size:11px;font-weight:700}.health:before{content:"";width:7px;height:7px;border-radius:50%;background:#b8c2cc}.health.up{color:#13845d}.health.up:before{background:#19b07a}.health.down{color:#a46c22}.health.down:before{background:#dba453}.textlink{background:none;border:0;color:#b33643;font-size:12px;font-weight:760;padding:5px 0}.textlink:hover{text-decoration:underline}.textlink:disabled{color:#a6b0bf;cursor:not-allowed;text-decoration:none}.footer{display:flex;justify-content:space-between;color:#9aa6b4;font-size:11px;margin-top:26px;gap:10px;flex-wrap:wrap}.footer b{font-weight:650;color:#73839a}
    .modalbg{position:fixed;inset:0;background:rgba(8,17,31,.55);display:none;align-items:center;justify-content:center;padding:16px;z-index:30}.modalbg.visible{display:flex}.modal{background:white;border-radius:18px;padding:25px;box-shadow:0 20px 80px #0004;width:100%;max-width:470px}.modal h2{margin:0 0 8px;font-size:20px}.modal p{font-size:13px;color:#778395;margin-bottom:20px}.formlabel{font-size:12px;font-weight:720;color:#4d5867;display:block;margin-bottom:7px}.hostinput{width:100%;padding:12px 13px;border-radius:10px;border:1px solid #d5dce7;outline:none}.modal-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:20px}.small-print{font-size:11px;color:#8691a0;margin-top:11px}a:focus-visible,button:focus-visible,input:focus-visible{outline:3px solid #b5cde9;outline-offset:2px}
    @media(max-width:1280px){.servicegrid{grid-template-columns:repeat(2,minmax(0,1fr))}.sidebar{width:225px;min-width:225px}.main{width:calc(100% - 225px)}}
    @media(max-width:930px){.sidebar{width:75px;min-width:75px;align-items:center;padding:24px 10px}.brand{padding:0 0 20px}.brand>div:last-child,.nav-caption,.navlink span:not(.symbol),.sidebar-bottom{display:none}.navlink{justify-content:center;padding:12px}.navlink .symbol{margin:0}.main{width:calc(100% - 75px);padding:24px 18px}.stats{grid-template-columns:repeat(2,minmax(0,1fr))}.bodygrid{grid-template-columns:1fr}}
    @media(max-width:590px){.sidebar{display:none}.main{width:100%}.top{align-items:flex-start;flex-direction:column}.stats{gap:9px}.stat{padding:16px;min-height:144px}.stat-value{font-size:26px}.servicegrid{grid-template-columns:1fr}.services-head{align-items:flex-start;flex-direction:column}.services-controls,#search{width:100%}.specs{grid-template-columns:1fr 1fr}}
  
    /* v2: navigation is a real client-side view switch, not same-page scrolling */
    .page-view[hidden]{display:none !important}
    .page-view{min-height:330px;animation:fadeView .16s ease-out}
    @keyframes fadeView{from{opacity:.6;transform:translateY(3px)}to{opacity:1;transform:none}}
    .portal-section-title{margin:6px 0 20px}
    .shortcutgrid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:15px;margin-top:2px}
    .shortcut{background:#fff;border:1px solid var(--line);border-radius:15px;padding:22px;display:flex;flex-direction:column;gap:9px;min-height:192px;transition:transform .15s,border-color .15s}
    .shortcut:hover{transform:translateY(-2px);border-color:#aebbd0}
    .shortcut-icon{display:grid;place-items:center;background:#eef3fa;border-radius:10px;width:38px;height:38px;color:#546b92;font-size:20px}
    .shortcut strong{font-size:15px;color:#263347}
    .shortcut span:not(.shortcut-icon){color:#8995a4;font-size:12px}
    .shortcut b{color:#c23443;font-size:12px;margin-top:auto}
    .monitor-note{margin-top:17px;padding:18px 22px;border:1px solid var(--line);border-radius:15px;background:white}
    .monitor-note p{color:#7d899b;font-size:12px;line-height:1.65;margin:6px 0 8px}
    #page-monitor>.panel,#page-system>.panel{max-width:100%;}
    #page-monitor #cpuChart{height:220px}
    #page-monitor #cpuChartText{white-space:nowrap}
    #page-system .specs{grid-template-columns:repeat(3,minmax(0,1fr))}
    @media(max-width:1100px){.shortcutgrid{grid-template-columns:repeat(2,minmax(0,1fr))}#page-system .specs{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:590px){
      .layout{flex-direction:column}
      .sidebar{display:flex;position:sticky;top:0;z-index:15;align-self:stretch;width:100%;min-width:0;height:auto;background:var(--nav);padding:8px 10px;gap:0;overflow-x:auto;justify-content:center}
      .sidebar .brand,.sidebar .nav-caption,.sidebar .sidebar-bottom{display:none}
      .sidebar nav{display:flex;gap:4px;min-width:max-content}
      .sidebar .navlink{padding:9px 10px;gap:6px;margin:0;font-size:12px;white-space:nowrap}
      .sidebar .navlink span:not(.symbol){display:inline}
      .sidebar .navlink .symbol{font-size:15px;width:15px}
      .shortcutgrid{grid-template-columns:1fr}
      .shortcut{min-height:140px}
      #page-system .specs{grid-template-columns:1fr 1fr}
    }
</style>
</head>
<body>
<div class="layout">
<aside aria-label="Điều hướng" class="sidebar">
<div class="brand"><div class="brandmark">AM</div><div><strong>AMnote</strong><span>Server Management</span></div></div>
<nav>
<div class="nav-caption">Workspace</div>
<a aria-current="page" class="navlink active" data-nav-page="overview" href="#overview"><span class="symbol">◫</span><span>Tổng quan</span></a>
<a class="navlink" data-nav-page="monitor" href="#monitor"><span class="symbol">◈</span><span>Giám sát hiệu năng</span></a>
<a class="navlink" data-nav-page="services" href="#services"><span class="symbol">▦</span><span>Dịch vụ hệ thống</span></a>
<a class="navlink" data-nav-page="system" href="#system"><span class="symbol">▤</span><span>Thông tin server</span></a>
</nav>
<div class="sidebar-bottom"><span class="dot"></span><strong>Chỉ đọc / Read-only</strong><small>Trang giám sát không restart service, không sửa cấu hình và không gửi lệnh đến API nghiệp vụ.</small></div>
</aside>
<main class="main">
<section class="top">
<div><div class="eyebrow"><span class="small-bar"></span>Infrastructure / AMnote</div><h1 id="pageTitle">Tổng quan server</h1><div class="sub"><span id="pageSummary">Tài nguyên và tình trạng tổng thể của máy chủ AMnote</span> · <span id="headerHost">118.69.170.82</span></div></div>
<div class="top-actions"><button class="smallbtn" id="configBtn" type="button">⚙ Đổi IP / Host</button><button class="smallbtn primary" id="refreshBtn" type="button">↻ Làm mới</button></div>
</section>




<section class="page-view" data-page="overview" id="page-overview"><section aria-live="polite" class="notice"><i>ⓘ</i><div><strong id="dataTitle">Chưa kết nối dữ liệu giám sát</strong><p id="dataDesc">Các liên kết quản trị vẫn sử dụng được. Số liệu chỉ hiển thị khi API giám sát read-only được triển khai trên server.</p></div><span class="notice-state" id="dataState">CHƯA KẾT NỐI</span></section><section aria-label="Số liệu máy chủ" class="stats">
<div class="stat"><div class="stat-head"><div class="stat-name">CPU sử dụng</div><div class="stat-icon red">◴</div></div><div class="stat-value" id="cpuValue">—</div><div class="bar red"><span id="cpuBar"></span></div><div class="stat-foot">Tải CPU hệ thống</div></div>
<div class="stat"><div class="stat-head"><div class="stat-name">Bộ nhớ RAM</div><div class="stat-icon">▤</div></div><div class="stat-value" id="ramValue">—</div><div class="bar"><span id="ramBar"></span></div><div class="stat-foot" id="ramSub">Chưa có dữ liệu</div></div>
<div class="stat"><div class="stat-head"><div class="stat-name">Ổ đĩa gốc /</div><div class="stat-icon">▣</div></div><div class="stat-value" id="diskValue">—</div><div class="bar"><span id="diskBar"></span></div><div class="stat-foot" id="diskSub">Chưa có dữ liệu</div></div>
<div class="stat"><div class="stat-head"><div class="stat-name">Thời gian hoạt động</div><div class="stat-icon">◷</div></div><div class="stat-value" id="uptimeValue">—</div><div class="stat-foot" id="uptimeSub">Chưa có dữ liệu</div></div>
</section>
<div class="portal-section-title"><h2 class="section-title">Đi đến chức năng</h2><div class="section-sub">Chọn một mục để mở đúng màn hình, không cuộn toàn bộ nội dung trên một trang</div></div>
<div class="shortcutgrid">
<a class="shortcut" href="#monitor"><span class="shortcut-icon">◈</span><strong>Giám sát hiệu năng</strong><span>Biểu đồ CPU và trạng thái tài nguyên theo thời gian thực</span><b>Xem giám sát →</b></a>
<a class="shortcut" href="#services"><span class="shortcut-icon">▦</span><strong>Dịch vụ hệ thống</strong><span>Truy cập Cockpit, Netdata, Supervisor và các API</span><b>Xem dịch vụ →</b></a>
<a class="shortcut" href="#system"><span class="shortcut-icon">▤</span><strong>Thông tin server</strong><span>Cấu hình cơ bản, IP, hostname và trạng thái kết nối</span><b>Xem thông tin →</b></a>
</div>
</section><section class="page-view" data-page="monitor" hidden="" id="page-monitor"><div class="portal-section-title"><h2 class="section-title">Giám sát thời gian thực</h2><div class="section-sub">Biểu đồ chỉ sử dụng dữ liệu từ bộ đọc Linux tại máy chủ. Chưa triển khai sẽ hiển thị chưa kết nối.</div></div><div class="panel panel-pad"><div class="panel-head"><div><h2 class="section-title">Biểu đồ CPU</h2><div class="section-sub">Đọc mỗi 8 giây · dữ liệu từ Linux /proc</div></div><span class="panel-chip" id="cpuBadge">CHƯA CÓ DỮ LIỆU</span></div>
<svg aria-label="Biểu đồ lịch sử CPU" id="cpuChart" preserveaspectratio="none" viewbox="0 0 600 158"><line class="gridline" x1="0" x2="600" y1="30" y2="30"></line><line class="gridline" x1="0" x2="600" y1="75" y2="75"></line><line class="gridline" x1="0" x2="600" y1="120" y2="120"></line><polyline class="polyline" id="cpuLine" points=""></polyline></svg>
<div class="chart-foot"><span>60 mẫu gần nhất</span><span id="cpuChartText">Đang chờ dữ liệu...</span></div>
</div><div class="monitor-note"><strong>Giám sát chuyên sâu với Netdata</strong><p>Netdata là ứng dụng riêng. Khi đã cài và giới hạn truy cập từ IP công ty, bạn có thể mở dashboard để xem CPU, RAM, Disk I/O, Network và cảnh báo.</p><button class="textlink" data-open-tool="netdata" type="button">Mở Netdata ↗</button></div></section><section class="page-view" data-page="services" hidden="" id="page-services">
<div class="services-head"><div><h2 class="section-title">Dịch vụ &amp; Công cụ</h2><div class="section-sub">Mở nhanh các giao diện quản trị hoặc tra cứu trạng thái cổng dịch vụ</div></div><div class="services-controls"><input aria-label="Tìm dịch vụ" id="search" placeholder="Tìm dịch vụ..." type="search"/><button class="filter active" data-filter="all" type="button">Tất cả</button><button class="filter" data-filter="up" type="button">Đang mở</button><button class="filter" data-filter="down" type="button">Chưa mở</button></div></div>
<div class="servicegrid" id="serviceGrid"></div>
</section><section class="page-view" data-page="system" hidden="" id="page-system"><div class="portal-section-title"><h2 class="section-title">Thông tin máy chủ</h2><div class="section-sub">Chi tiết host và hệ điều hành; cấu hình truy cập liên kết không thay đổi IP thật của server.</div></div><div class="panel panel-pad"><div class="panel-head"><div><h2 class="section-title">Thông tin máy chủ</h2><div class="section-sub">Địa chỉ IP và trạng thái kết nối</div></div><span class="panel-chip">PRODUCTION CANDIDATE</span></div>
<div class="specs"><div><span class="k">Public IP / Host</span><span class="v" id="specHost">118.69.170.82</span></div><div><span class="k">Hệ điều hành</span><span class="v">Ubuntu 24.04</span></div><div><span class="k">Máy chủ</span><span class="v" id="specHostname">Chưa có dữ liệu</span></div><div><span class="k">Apache (TCP :80)</span><span class="v" id="apacheState"><span class="mini-dot"></span>Chưa kiểm tra</span></div><div><span class="k">Load Average (1 / 5 / 15m)</span><span class="v" id="loadValue">— / — / —</span></div><div><span class="k">Giám sát</span><span class="v" id="lastCheck">Chưa kết nối</span></div></div>
<p class="small-print" style="margin-top:18px">Trạng thái dịch vụ dựa trên kết nối TCP nội bộ, không xác nhận API xử lý nghiệp vụ thành công.</p>
</div><div class="monitor-note"><strong>Quản trị server</strong><p>Sử dụng Cockpit để xem nhật ký và quản trị Linux. Dashboard AMnote này chỉ giám sát và mở liên kết, không thực hiện lệnh hệ thống hoặc giao dịch thuế.</p><button class="textlink" data-open-tool="cockpit" type="button">Mở Cockpit ↗</button></div></section><footer class="footer"><span>© 2026 <b>AMnote</b> · Infrastructure dashboard · Không lưu mật khẩu hoặc token</span><span>Chỉ dành cho IP công ty <b>115.78.232.22</b> (phiên bản PHP)</span></footer>
</main>
</div>
<div aria-hidden="true" aria-labelledby="hostTitle" aria-modal="true" class="modalbg" id="hostModal" role="dialog">
<div class="modal"><h2 id="hostTitle">Địa chỉ server</h2><p>Thay đổi IP hoặc tên miền khi chuyển máy chủ. Địa chỉ này chỉ dùng để tạo liên kết, không thay đổi cấu hình server.</p><label class="formlabel" for="hostField">IP hoặc hostname</label><input autocomplete="off" class="hostinput" id="hostField" value="118.69.170.82"/><div class="small-print">Ví dụ: 118.69.170.82 hoặc ten-mien-noi-bo.example.com. Không nhập http:// hay port.</div><div class="modal-actions"><button class="smallbtn" id="cancelHost" type="button">Hủy</button><button class="smallbtn primary" id="saveHost" type="button">Lưu địa chỉ</button></div></div>
</div>
<script>
(function(){
  'use strict';
  var DEFAULT_HOST = '118.69.170.82';
  var chosenHost = DEFAULT_HOST;
  try{var saved=localStorage.getItem('amnote_portal_host');if(saved)chosenHost=saved;}catch(e){}
  var services=[
    {id:'cockpit',name:'Cockpit',desc:'Quản trị Linux, systemd, log và Terminal',port:9090,proto:'https',kind:'management',symbol:'▤',action:'Mở Cockpit'},
    {id:'netdata',name:'Netdata',desc:'Biểu đồ CPU, RAM, mạng và cảnh báo',port:19999,proto:'http',kind:'management',symbol:'◫',action:'Mở Netdata'},
    {id:'supervisor',name:'Supervisor',desc:'Quản lý queue workers và tiến trình',port:9002,proto:'http',kind:'management',symbol:'♧',action:'Mở Supervisor'},
    {id:'apache',name:'Apache',desc:'Web server phục vụ các website và API',port:80,proto:'http',kind:'system',symbol:'◇',action:'Mở HTTP'},
    {id:'api-tcgp',name:'API TCGP',desc:'Dịch vụ thông điệp cơ quan thuế',port:8989,proto:'http',kind:'api',symbol:'↗',action:'Mở API'},
    {id:'api-queue',name:'API Queue',desc:'Dịch vụ hàng đợi ứng dụng',port:8888,proto:'http',kind:'api',symbol:'≋',action:'Mở API'},
    {id:'api-mail',name:'API Mail',desc:'Dịch vụ thư điện tử',port:8080,proto:'http',kind:'api',symbol:'✉',action:'Mở API'},
    {id:'api-excel',name:'Excel Export API',desc:'Xuất Excel qua ASP.NET Core',port:8383,proto:'http',kind:'api',symbol:'▦',action:'Mở API'},
    {id:'api-firmbanking',name:'Firm Banking API',desc:'Ứng dụng ASP.NET Core trên cổng 5555',port:5555,proto:'http',kind:'api',symbol:'⇄',action:'Mở API'},
    {id:'api-firmbanking5000',name:'Firm Banking 5000',desc:'Ứng dụng ASP.NET Core trên cổng 5000',port:5000,proto:'http',kind:'api',symbol:'⇄',action:'Mở API'},
    {id:'php73',name:'PHP-FPM 7.3',desc:'Dịch vụ PHP legacy',port:9000,proto:'tcp',kind:'system',symbol:'{ }',action:null},
    {id:'php83',name:'PHP-FPM 8.3',desc:'Dịch vụ PHP 8.3',port:9001,proto:'tcp',kind:'system',symbol:'{ }',action:null}
  ];
  var state=null, filter='all', searchText='', cpuPrev=null, cpuHistory=[];

  var pageMeta = {
    overview: ['Tổng quan server','Tài nguyên và tình trạng tổng thể của máy chủ AMnote'],
    monitor: ['Giám sát hiệu năng','Biểu đồ CPU và thống kê theo thời gian thực'],
    services: ['Dịch vụ hệ thống','Các ứng dụng và điểm truy cập quản trị'],
    system: ['Thông tin server','Chi tiết cấu hình máy chủ và địa chỉ truy cập']
  };
  function navigate(){
    var page = location.hash.slice(1).toLowerCase();
    if(!Object.prototype.hasOwnProperty.call(pageMeta,page))page='overview';
    Array.prototype.forEach.call(document.querySelectorAll('.page-view'),function(el){el.hidden=el.getAttribute('data-page')!==page;});
    Array.prototype.forEach.call(document.querySelectorAll('[data-nav-page]'),function(el){
      var active=el.getAttribute('data-nav-page')===page;
      el.classList.toggle('active',active);
      if(active)el.setAttribute('aria-current','page');else el.removeAttribute('aria-current');
    });
    $('pageTitle').textContent=pageMeta[page][0];
    $('pageSummary').textContent=pageMeta[page][1];
    document.title='AMnote · '+pageMeta[page][0];
    window.scrollTo(0,0);
  }
  window.addEventListener('hashchange',navigate);

  var $=function(id){return document.getElementById(id)};
  var fmt=function(n){return (Math.round(n*10)/10).toLocaleString('vi-VN',{maximumFractionDigits:1})};
  var sizeFmt=function(n){if(n==null)return '—';return fmt(n/1073741824)+' GB'};
  var percent=function(used,total){return total>0?Math.max(0,Math.min(100,100*used/total)):null};
  function sanitizeHost(x){x=String(x||'').trim();return /^(?:[a-zA-Z0-9-]+\.)*[a-zA-Z0-9-]+$/.test(x) && x.length<=253 ? x : null;}
  function urlFor(s){return s.proto+'://'+chosenHost+':'+s.port+'/';}
  function escaped(str){return String(str).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]});}
  function updateHost(){ $('headerHost').textContent=chosenHost;$('specHost').textContent=chosenHost;$('hostField').value=chosenHost;renderServices(); }
  function renderServices(){
    var list=services.filter(function(s){
      var okText=(s.name+' '+s.desc+' '+s.port).toLowerCase().indexOf(searchText)>=0;
      var live=state && state.ports && typeof state.ports[s.id]==='boolean' ? state.ports[s.id] : null;
      return okText && (filter==='all'||(filter==='up'&&live===true)||(filter==='down'&&live===false));
    });
    var html=list.map(function(s){
      var live=state && state.ports && typeof state.ports[s.id]==='boolean' ? state.ports[s.id] : null;
      var health=live===true?'Đang lắng nghe TCP':live===false?'Không lắng nghe':'Chưa kiểm tra';
      var css=live===true?'up':live===false?'down':'';
      var action=s.action?'<button type="button" class="textlink" data-open="'+escaped(s.id)+'">'+escaped(s.action)+' ↗</button>':'<span class="desc">Chỉ theo dõi</span>';
      return '<article class="service"><div class="service-title"><div><div class="service-icon">'+escaped(s.symbol)+'</div><h3>'+escaped(s.name)+'</h3><div class="service-desc">'+escaped(s.desc)+'</div></div><span class="service-port">:'+s.port+'</span></div><div class="service-bottom"><span class="health '+css+'">'+health+'</span>'+action+'</div></article>';
    }).join('');
    $('serviceGrid').innerHTML=html||'<div class="panel panel-pad" style="grid-column:1/-1"><div class="desc">Không tìm thấy dịch vụ phù hợp.</div></div>';
  }
  function uptime(t){if(t==null)return ['—','Chưa có dữ liệu'];var d=Math.floor(t/86400),h=Math.floor((t%86400)/3600),m=Math.floor((t%3600)/60);return [d+' ngày',h+' giờ '+m+' phút'];}
  function updateBars(id,v){$(id).style.width=(v==null?0:v)+'%';}
  function chart(){var x0=0,dx=cpuHistory.length>1?600/(cpuHistory.length-1):0;var pts=cpuHistory.map(function(v,i){return (x0+dx*i).toFixed(1)+','+(145-v*1.3).toFixed(1)});$('cpuLine').setAttribute('points',pts.join(' '));$('cpuChartText').textContent=cpuHistory.length?cpuHistory.length+' mẫu đo':'Đang chờ dữ liệu...';}
  function updateInfo(data){
    state=data;
    $('dataTitle').textContent='Đã kết nối bộ giám sát read-only';
    $('dataDesc').textContent='Dữ liệu Linux /proc và trạng thái cổng TCP localhost. Không đọc database hoặc gọi API nghiệp vụ.';
    $('dataState').textContent='ĐANG GIÁM SÁT';$('dataState').classList.add('online');
    if(data.cpu&&cpuPrev){var total=data.cpu.total-cpuPrev.total,idle=data.cpu.idle-cpuPrev.idle;if(total>0){var value=Math.round(1000*Math.max(0,Math.min(1,(total-idle)/total)))/10;$('cpuValue').textContent=fmt(value)+'%';updateBars('cpuBar',value);$('cpuBadge').textContent=fmt(value)+'% CPU';cpuHistory.push(value);if(cpuHistory.length>60)cpuHistory.shift();chart();}}
    if(data.cpu)cpuPrev=data.cpu;
    if(data.memory){var r=percent(data.memory.used,data.memory.total);$('ramValue').textContent=fmt(r)+'%';$('ramSub').textContent=sizeFmt(data.memory.used)+' / '+sizeFmt(data.memory.total);updateBars('ramBar',r);}
    if(data.disk){var d=percent(data.disk.used,data.disk.total);$('diskValue').textContent=fmt(d)+'%';$('diskSub').textContent=sizeFmt(data.disk.used)+' / '+sizeFmt(data.disk.total);updateBars('diskBar',d);}
    var ut=uptime(data.uptimeSeconds);$('uptimeValue').textContent=ut[0];$('uptimeSub').textContent=ut[1];
    $('specHostname').textContent=data.hostname||'—';
    $('loadValue').textContent=Array.isArray(data.load)?data.load.map(fmt).join(' / '):'— / — / —';
    $('lastCheck').textContent=new Date(data.checkedAt||Date.now()).toLocaleTimeString('vi-VN');
    $('apacheState').innerHTML='<span class="mini-dot '+(data.ports&&data.ports.apache?'green':'')+'"></span>'+(data.ports&&data.ports.apache?'Có kết nối TCP':'Không lắng nghe');
    renderServices();
  }
  function setOffline(message){$('dataTitle').textContent='Chưa kết nối dữ liệu giám sát';$('dataDesc').textContent=message||'Các liên kết quản trị vẫn sử dụng được. Dữ liệu chỉ hiển thị sau khi triển khai bộ đọc read-only.';$('dataState').textContent='CHƯA KẾT NỐI';$('dataState').classList.remove('online');}
  async function fetchData(){
    if(location.protocol==='file:'){setOffline('Đang xem bản preview offline. Để xem số liệu thật, triển khai index.php và status.php trên server.');return;}
    try{var resp=await fetch('status.php',{cache:'no-store',credentials:'same-origin',headers:{Accept:'application/json'}});if(!resp.ok)throw new Error('HTTP '+resp.status);var d=await resp.json();if(!d||d.checkType!=='local-tcp-port-only')throw new Error('Invalid response');updateInfo(d);}catch(e){setOffline('Không nhận được số liệu ('+e.message+'). Kiểm tra status.php, PHP-FPM và giới hạn IP trước khi sử dụng.');}
  }
  $('search').addEventListener('input',function(e){searchText=e.target.value.trim().toLowerCase();renderServices();});
  Array.prototype.forEach.call(document.querySelectorAll('[data-filter]'),function(b){b.addEventListener('click',function(){filter=b.getAttribute('data-filter');Array.prototype.forEach.call(document.querySelectorAll('[data-filter]'),function(x){x.classList.toggle('active',x===b)});renderServices();});});
  $('serviceGrid').addEventListener('click',function(e){var b=e.target.closest('[data-open]');if(!b)return;var s=services.find(function(x){return x.id===b.getAttribute('data-open')});if(s){window.open(urlFor(s),'_blank','noopener,noreferrer');}});
  function showModal(){ $('hostModal').classList.add('visible');$('hostModal').setAttribute('aria-hidden','false');$('hostField').focus();}
  function closeModal(){ $('hostModal').classList.remove('visible');$('hostModal').setAttribute('aria-hidden','true');$('configBtn').focus();}
  $('configBtn').addEventListener('click',showModal);$('cancelHost').addEventListener('click',closeModal);
  $('hostModal').addEventListener('click',function(e){if(e.target===$('hostModal'))closeModal();});
  document.addEventListener('keydown',function(e){if(e.key==='Escape'&&$('hostModal').classList.contains('visible'))closeModal();});
  $('saveHost').addEventListener('click',function(){var v=sanitizeHost($('hostField').value);if(!v){alert('IP/hostname không hợp lệ. Chỉ nhập tên máy chủ hoặc IPv4, không nhập giao thức và port.');return;}chosenHost=v;try{localStorage.setItem('amnote_portal_host',v);}catch(e){}updateHost();closeModal();});
  Array.prototype.forEach.call(document.querySelectorAll('[data-open-tool]'),function(b){b.addEventListener('click',function(){var s=services.find(function(x){return x.id===b.getAttribute('data-open-tool')});if(s)window.open(urlFor(s),'_blank','noopener,noreferrer');});});
  $('refreshBtn').addEventListener('click',fetchData);
  updateHost();renderServices();navigate();fetchData();setInterval(fetchData,8000);
})();
</script>
</body>
</html>
