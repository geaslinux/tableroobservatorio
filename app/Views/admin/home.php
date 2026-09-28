<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio · Ministerio de Salud</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --navy: #0e2a4d;
            --navy-2: #132f57;
            --navy-3: #081c34;
            --blue: #2b7de9;
            --blue-soft: rgba(43, 125, 233, 0.10);
            --teal: #2ab7a6;
            --teal-soft: rgba(42, 183, 166, 0.11);
            --orange: #f28c46;
            --orange-soft: rgba(242, 140, 70, 0.12);
            --purple: #7f69db;
            --purple-soft: rgba(127, 105, 219, 0.11);
            --bg: #eef3f8;
            --panel: #ffffff;
            --text: #1d2b3c;
            --muted: #66778a;
            --border: rgba(16, 42, 75, 0.10);
            --shadow: 0 18px 40px rgba(10, 28, 48, 0.08);
            --sidebar-w: 285px;
            --sidebar-collapsed-w: 100px;
            --topbar-h: 60px;
            --radius: 22px;
        }

        * { -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }
        html {
            overflow-x: hidden !important;
            width: 100%;
        }

        /* ── ESTILOS DEL BOTÓN TOGGLE ── */
        .sidebar-toggle-wrap {
            position: absolute;
            top: 50%;
            left: calc(100% - 17px);
            transform: translate(-50%, -50%);
            width: 42px;
            height: 64px;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 105;
        }

        body.sidebar-collapsed .content {
            margin-left: var(--sidebar-collapsed-w);
        }

        body.sidebar-collapsed .sidebar-toggle-wrap {
            left: calc(100% - 17px);
        }
        body.sidebar-collapsed .sidebar {
            width: var(--sidebar-collapsed-w);
            padding: 18px 8px 16px;
            overflow: hidden !important;
        }
        body.sidebar-collapsed .sidebar:hover {
            width: var(--sidebar-w) !important;
            padding: 18px 16px 16px;
            box-shadow: 15px 0 35px rgba(5, 18, 33, 0.45);
            overflow: hidden !important;
        }

        body.sidebar-collapsed .sidebar:not(:hover) .brand-title,
        body.sidebar-collapsed .sidebar:not(:hover) .brand-subtitle,
        body.sidebar-collapsed .sidebar:not(:hover) .nav-item span,
        body.sidebar-collapsed .sidebar:not(:hover) .nav-chevron,
        body.sidebar-collapsed .sidebar:not(:hover) .nav-sub-group,
        body.sidebar-collapsed .sidebar:not(:hover) .sidebar-footer .user-info {
            display: none !important;
        }

        body.sidebar-collapsed .sidebar:not(:hover) .nav-section {
            font-size: 8px !important;
            letter-spacing: 0.5px;
            padding: 0;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin: 15px 0 10px;
            display: block !important;
            opacity: 0.6;
        }
        body.sidebar-collapsed .sidebar:not(:hover) .brand {
            justify-content: center;
            padding: 5px 0 15px;
        }
        body.sidebar-collapsed .sidebar:not(:hover) .brand img {
            margin: -10px 0 -20px 0; /* Mantiene los márgenes verticales originales */
            width: 220%;            /* Duplica el ancho para que la mitad ocupe el contenedor */
            max-width: none;        /* Anula el límite para permitir el recorte */
            height: auto;
            object-fit: contain;
            margin-left: -120%;     /* Empuja la imagen a la izquierda para mostrar solo el lado derecho */
            display: block;
        }
        body.sidebar-collapsed .sidebar:not(:hover) .nav-item {
            justify-content: center;
            padding: 13px 0;
        }
        body.sidebar-collapsed .sidebar:not(:hover) .sidebar-footer {
            padding: 15px 0 5px;
            text-align: center;
            align-items: center;
            justify-content: center;
        }
        body.sidebar-collapsed .sidebar:not(:hover) .sidebar-footer::before {
            content: "\f2f2"; /* Código del icono de actualización de FontAwesome */
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.35);
            display: inline-block;
        }
        body.sidebar-collapsed .sidebar:hover .sidebar-footer::before {
            display: none !important;
        }

        #sidebar-toggle {
            width:50%;
            height:80%;
            background: #294062;
            border:1px solid #fff;
            border-radius:12px;
            color:#fff;
            display:flex;
            align-items:center;
            justify-content:center;
            cursor:pointer;
            box-shadow:none;
            transition:all .25s ease;
        }

        #sidebar-toggle i {
            font-size: 15px;
            transition: .25s;
        }

        body.sidebar-collapsed #sidebar-toggle i {
            transform: rotate(180deg);
        }

        #sidebar-toggle:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: scale(1.05);
        }
        
        /* Aseguramos la transición suave en la sidebar y el shell */
        .sidebar {
            transition: width 0.22s ease;
        }

        body {
            min-height: 100vh;
            max-width: 100vw;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(42, 183, 166, 0.13), transparent 32%),
                radial-gradient(circle at top right, rgba(43, 125, 233, 0.12), transparent 30%),
                linear-gradient(180deg, #f8fbff 0%, #eef3f8 100%);
                overflow-x: hidden !important;
        }

        a { color: inherit; text-decoration: none; }

        .app-shell {
            display: block;
            width: 100%;
            max-width: 100vw;
            overflow-x: hidden !important;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            height: 100vh;
            padding: 18px 16px 16px;
            background: linear-gradient(180deg, var(--navy-2) 0%, var(--navy-3) 100%);
            color: #fff;
            display: flex;
            flex-direction: column;
            box-shadow: 12px 0 30px rgba(5, 18, 33, 0.22);
            overflow: hidden !important;
            white-space: nowrap;
            width: var(--sidebar-w);
            transition: width 0.28s cubic-bezier(0.4, 0, 0.2, 1), padding 0.28s ease;
            z-index: 100;
        }

        .brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 10px 8px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            margin-bottom: 5px;
        }

        .brand img {
            max-width: auto; 
            height: auto;
            object-fit: contain;
            filter: brightness(1.2);
            margin-top: -20px;
            margin-bottom: -10px;
            display: block; 
            width: 100%; 
            transition: all 0.28s ease;
        }

        .brand-title {
            font-size: 12.5px;
            line-height: 1.05;
            font-weight: 700;
            letter-spacing: 0.2px;
        }

        .brand-subtitle {
            margin-top: 4px;
            font-size: 20px;
            line-height: 1.05;
            font-weight: 700;
            letter-spacing: 0.2px;
            color: rgba(16, 133, 183);
        }

        .nav {
            flex: 1;
            overflow: auto;
            padding-right: 4px;
        }

        .nav-section {
            margin: 18px 0 8px;
            font-size: 10px;
            letter-spacing: 1.6px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.34);
            font-weight: 700;
            padding: 0 10px;
            transition: all 0.2s ease;
        }

        .nav-item,
        .nav-sub {
            display: flex;
            align-items: center;
            gap: 12px;
            border-radius: 14px;
            padding: 13px 14px;
            font-size: 14px;
            transition: transform .18s ease, background .18s ease, color .18s ease;
        }

        .nav-item {
            color: rgba(255, 255, 255, 0.78);
            margin-bottom: 6px;
            position: relative;
        }

        .nav-item:hover,
        .nav-sub:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            transform: translateX(2px);
        }

        .nav-item.active {
            background:  rgba(8, 126, 166);
            color: #dffef9;
            box-shadow: inset 0 0 0 1px rgba(8, 126, 166);
        }

        .nav-item.danger { color: rgba(255, 160, 160, 0.82); }
        .nav-item.danger:hover { background: rgba(255, 110, 110, 0.12); }

        .nav-item i { font-size: 18px; width: 22px; text-align: center; flex-shrink: 0; opacity: 0.8; }
        .nav-sub i {
            width: 18px;
            text-align: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .nav-chevron {
            margin-left: auto;
            opacity: .45;
            font-size: 12px !important;
            transition: transform .2s ease;
        }

        .nav-item.open .nav-chevron { transform: rotate(180deg); }

        .nav-sub-group {
            max-height: 0;
            overflow: hidden;
            transition: max-height .28s ease;
            margin-bottom: 6px;
        }

        .nav-sub-group.open { max-height: 360px; }

        .nav-sub {
            margin: 2px 0 0 12px;
            padding: 10px 14px 10px 48px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.78);
            border-left: 2px solid rgba(255, 255, 255, 0.06);
            width: fit-content;
            max-width: 100%;
        }

        .nav-sub.active {
            background: rgba(42, 183, 166, 0.12);
            color: #e4fffb;
            border-left-color: rgba(42, 183, 166, 0.6);
        }

        .sidebar-update {
            margin-top: auto;
            padding: 15px 10px 5px;
            border-top: 1px solid rgba(255,255,255,0.08);
            font-size: 11px;
            color: rgba(255,255,255,0.4);
            display: flex;
            flex-direction: column;
            gap: 4px;
            overflow: hidden !important;
            white-space: nowrap !important;
            text-overflow: ellipsis;
        }
        .sidebar-footer div {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
            width: 100%;
        }

        .avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, #53d8c9, #1fa996);
            color: #07263d;
            font-weight: 800;
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }

        .user-name { font-size: 13.5px; font-weight: 700; }
        .user-role {
            margin-top: 3px;
            font-size: 10px;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.45);
        }

        .content {
            min-width: 0;
            max-width: none;
            margin: 0 0 0 var(--sidebar-w);
            width: auto;
            padding: 18px 18px 22px;
        }

        .topbar {
            min-height: var(--topbar-h);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 0 8px 14px;
        }

        .topbar-title {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .topbar-title .eyebrow {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.1px;
            text-transform: uppercase;
            color: var(--muted);
        }

        .topbar-title h1 {
            font-size: clamp(22px, 2.2vw, 32px);
            line-height: 1.05;
            color: var(--text);
            font-weight: 800;
        }

        .topbar-title h1 strong { color: var(--navy); }

        .topbar-tools {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .period-box {
            min-width: 240px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.76);
            border: 1px solid rgba(255, 255, 255, 0.88);
            box-shadow: 0 10px 25px rgba(13, 35, 58, 0.05);
            padding: 10px 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            backdrop-filter: blur(10px);
        }

        .period-box i { color: var(--muted); }
        .period-label { font-size: 10px; text-transform: uppercase; letter-spacing: 1.2px; color: var(--muted); font-weight: 700; }
        .period-value { margin-top: 3px; font-size: 13px; font-weight: 700; color: var(--text); }
        .chevron-down { margin-left: auto; color: var(--muted); }

        .icon-btn {
            width: 48px;
            height: 48px;
            border: 0;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.84);
            display: grid;
            place-items: center;
            color: var(--navy);
            box-shadow: 0 10px 24px rgba(13, 35, 58, 0.06);
            position: relative;
        }

        .avatar-btn {
            width: 48px; 
            height: 48px;
            border: none;
            border-radius: 50%; 
            display: grid;
            place-items: center; 
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            position: relative;
            padding: 0; 
            outline: none; 
        }

        /* Efecto al pasar el ratón para indicar interactividad */
        .avatar-btn:hover {
            filter: brightness(90%);
        }

        /* --- Estilos para las Iniciales dentro del Avatar --- */
        .avatar-initials {
            color: #fff; 
            font-size: 20px; 
            font-weight: bold; 
            font-family: sans-serif; 
            line-height: 1; 
        }

        .badge {
            position: absolute;
            top: 2px;
            right: 2px;
            min-width: 18px;
            height: 18px;
            padding: 0 4px;
            border-radius: 999px;
            background: #ea3b3b;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            display: grid;
            place-items: center;
            border: 2px solid #fff;
            z-index: 1;
        }

        .page-grid {
            display: grid;
            gap: 18px;
            width: 100%;
        }

        .hero {
            display: grid;
            grid-template-columns: minmax(0, 1.18fr) minmax(360px, .82fr);
            gap: 16px;
            align-items: stretch;
            width: 100%;
        }

        .panel {
            background: rgba(255, 255, 255, 0.84);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            backdrop-filter: blur(12px);
        }

        .hero-main {
            padding: 22px 22px 20px;
            position: relative;
            overflow: hidden;
            min-height: 216px;
        }

        .hero-main::before,
        .hero-main::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
        }

        .hero-main::before {
            width: 220px;
            height: 220px;
            right: -90px;
            top: -70px;
            background: radial-gradient(circle, rgba(42, 183, 166, 0.18), transparent 68%);
        }

        .hero-main::after {
            width: 280px;
            height: 280px;
            right: -140px;
            bottom: -120px;
            background: radial-gradient(circle, rgba(43, 125, 233, 0.14), transparent 70%);
        }

        .hero-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            position: relative;
            z-index: 1;
        }

        .hero-copy {
            max-width: 540px;
        }

        .hero-copy .small {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.4px;
            text-transform: uppercase;
            color: var(--teal);
            margin-bottom: 8px;
        }

        .hero-copy h2 {
            font-size: clamp(24px, 2.6vw, 38px);
            line-height: 1.06;
            color: var(--navy);
            font-weight: 900;
            text-transform: uppercase;
        }

        .hero-copy p {
            margin-top: 12px;
            color: var(--muted);
            max-width: 470px;
            line-height: 1.65;
            font-size: 14px;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 18px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 13px 18px;
            border-radius: 14px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.2px;
            transition: transform .18s ease, box-shadow .18s ease, opacity .18s ease;
        }

        .btn:hover { transform: translateY(-1px); }

        .btn-primary {
            color: #fff;
            background: linear-gradient(135deg, var(--blue), #2265c6);
            box-shadow: 0 12px 26px rgba(43, 125, 233, 0.24);
        }

        .btn-secondary {
            color: var(--navy);
            background: rgba(255, 255, 255, 0.85);
            border: 1px solid var(--border);
        }

        .btn-disabled {
            color: rgba(29, 43, 60, 0.45);
            background: rgba(255, 255, 255, 0.55);
            border: 1px solid rgba(16, 42, 75, 0.08);
            cursor: default;
            pointer-events: none;
        }

        .hero-visual {
            width: min(300px, 38vw);
            align-self: center;
            position: relative;
            z-index: 1;
            display: grid;
            place-items: center;
        }

        .visual-circle {
            width: 190px;
            height: 190px;
            border-radius: 50%;
            background: radial-gradient(circle at 30% 30%, rgba(42, 183, 166, 0.18), rgba(42, 183, 166, 0.06));
            border: 10px solid rgba(42, 183, 166, 0.18);
            display: grid;
            place-items: center;
            box-shadow: inset 0 0 0 8px rgba(255, 255, 255, 0.42);
        }

        .visual-circle i {
            font-size: 72px;
            color: rgba(42, 183, 166, 0.95);
            filter: drop-shadow(0 12px 20px rgba(42, 183, 166, 0.16));
        }

        .hero-stats {
            margin-top: 18px;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            position: relative;
            z-index: 1;
            width: 100%;
        }

        .stat-card {
            padding: 16px 16px 15px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(16, 42, 75, 0.08);
            display: flex;
            align-items: center;
            gap: 14px;
            overflow: hidden;
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            flex-shrink: 0;
            font-size: 22px;
            color: #fff;
        }

        .stat-card.blue .stat-icon { background: linear-gradient(135deg, #94c8ff, #3d8eff); }
        .stat-card.teal .stat-icon { background: linear-gradient(135deg, #6fd7ca, #2ab7a6); }
        .stat-card.orange .stat-icon { background: linear-gradient(135deg, #f7b06f, #f28c46); }

        .stat-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: var(--muted);
            font-weight: 700;
        }

        .stat-value {
            font-size: clamp(24px, 2.6vw, 33px);
            font-weight: 900;
            line-height: 1.05;
            margin-top: 2px;
            color: var(--navy);
        }

        .stat-sub {
            margin-top: 4px;
            font-size: 12px;
            color: var(--muted);
        }

        .trend {
            margin-top: 8px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 700;
        }

        .trend.up { color: #1f9c73; }
        .trend.neutral { color: #c96a2a; }

        .mini-chart {
            width: 130px;
            height: 42px;
            margin-left: auto;
            opacity: .95;
        }

        .section-title {
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--muted);
            margin: 8px 0 2px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            width: 100%;
        }

        .dashboard-card {
            padding: 20px;
            min-height: 214px;
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(120px, .9fr);
            gap: 14px;
            position: relative;
            overflow: hidden;
        }

        .dashboard-card::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: var(--accent, var(--blue));
        }

        .dashboard-card.blue { --accent: var(--blue); }
        .dashboard-card.teal { --accent: var(--teal); }
        .dashboard-card.purple { --accent: var(--purple); }
        .dashboard-card.orange { --accent: var(--orange); }

        .dash-head {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .dash-badge {
            width: 58px;
            height: 58px;
            border-radius: 18px;
            display: grid;
            place-items: center;
            font-size: 24px;
            color: #fff;
            background: linear-gradient(135deg, var(--accent), rgba(255,255,255,0.2));
            box-shadow: 0 14px 24px rgba(16, 42, 75, 0.10);
        }

        .dash-title {
            font-size: 15px;
            font-weight: 900;
            line-height: 1.12;
            color: var(--navy);
            text-transform: uppercase;
        }

        .dash-subtitle {
            margin-top: 6px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.55;
            max-width: 280px;
        }

        .dash-actions {
            margin-top: 16px;
        }

        .dash-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            color: #fff;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .6px;
            background: linear-gradient(135deg, var(--accent), color-mix(in srgb, var(--accent) 72%, #000 28%));
            box-shadow: 0 12px 24px rgba(16, 42, 75, 0.12);
        }

        .dash-art {
            position: relative;
            min-height: 150px;
            align-self: center;
            display: grid;
            place-items: center;
            overflow: hidden; /* Evita que la imagen agrandada rompa la tarjeta */
            width: 100%;
        }

        .art-cloud,
        .art-cloud::before,
        .art-cloud::after {
            position: absolute;
            border-radius: 50%;
            content: "";
            background: rgba(16, 42, 75, 0.05);
        }

        .art-cloud { width: 92px; height: 34px; top: 6px; right: 12px; }
        .art-cloud::before { width: 44px; height: 44px; left: 10px; top: -20px; }
        .art-cloud::after { width: 58px; height: 58px; left: 38px; top: -28px; }

        .building,
        .brain,
        .network,
        .scales {
            width: 160px;
            height: 120px;
            position: relative;
            opacity: .95;
        }

        .building .block,
        .building .door,
        .building .cross,
        .building .window,
        .brain .head,
        .brain .heart,
        .brain .leaf,
        .network .node,
        .network .line,
        .scales .bar,
        .scales .pole,
        .scales .arm,
        .scales .pan {
            position: absolute;
        }

        .building .block {
            inset: 20px 36px 8px 36px;
            border: 2px solid color-mix(in srgb, var(--accent) 40%, white 60%);
            background: linear-gradient(180deg, rgba(255,255,255,0.6), rgba(255,255,255,0.18));
            border-radius: 12px;
        }

        .building .door {
            width: 36px; height: 42px; left: 62px; bottom: 8px;
            border-radius: 10px 10px 0 0;
            background: rgba(255,255,255,0.55);
            border: 2px solid color-mix(in srgb, var(--accent) 30%, white 70%);
        }

        .building .cross {
            width: 28px; height: 28px; left: 66px; top: 26px;
            border-radius: 8px;
            background: color-mix(in srgb, var(--accent) 90%, white 10%);
        }

        .building .cross::before,
        .building .cross::after {
            content: "";
            position: absolute;
            background: #fff;
            border-radius: 2px;
        }

        .building .cross::before { width: 14px; height: 4px; left: 7px; top: 12px; }
        .building .cross::after { width: 4px; height: 14px; left: 12px; top: 7px; }

        .building .window { width: 12px; height: 18px; border-radius: 4px; background: rgba(255,255,255,0.8); }
        .building .w1 { left: 46px; top: 54px; }
        .building .w2 { right: 46px; top: 54px; }
        .building .w3 { left: 46px; top: 82px; }
        .building .w4 { right: 46px; top: 82px; }

        .brain .head {
            width: 104px; height: 96px; left: 20px; top: 10px;
            border: 3px solid color-mix(in srgb, var(--accent) 45%, white 55%);
            border-radius: 48% 48% 42% 42%;
            background: linear-gradient(180deg, rgba(255,255,255,0.7), rgba(255,255,255,0.2));
        }

        .brain .heart {
            width: 34px; height: 34px; left: 55px; top: 36px;
            transform: rotate(-45deg);
            border-radius: 8px 16px 8px 8px;
            background: rgba(255,255,255,0.92);
            border: 2px solid color-mix(in srgb, var(--accent) 40%, white 60%);
        }
        .brain .heart::before,
        .brain .heart::after {
            content: "";
            position: absolute;
            width: 34px; height: 34px;
            background: inherit;
            border-radius: 50%;
        }
        .brain .heart::before { top: -17px; left: 0; }
        .brain .heart::after { top: 0; left: 17px; }

        .brain .leaf {
            width: 22px; height: 12px; border: 2px solid color-mix(in srgb, var(--accent) 35%, white 65%);
            border-radius: 24px 24px 0 24px; transform: rotate(-20deg);
            background: rgba(255,255,255,0.45);
        }
        .brain .l1 { left: 14px; bottom: 10px; }
        .brain .l2 { right: 14px; bottom: 18px; transform: rotate(28deg); }

        .network .node {
            width: 18px; height: 18px; border-radius: 50%;
            background: color-mix(in srgb, var(--accent) 84%, white 16%);
            box-shadow: 0 0 0 8px rgba(255, 255, 255, 0.48);
        }
        .network .n1 { left: 38px; top: 20px; }
        .network .n2 { left: 104px; top: 32px; }
        .network .n3 { left: 68px; bottom: 22px; }
        .network .n4 { left: 26px; bottom: 26px; }
        .network .n5 { right: 26px; bottom: 18px; }
        .network .line {
            background: color-mix(in srgb, var(--accent) 45%, white 55%);
            border-radius: 999px;
            transform-origin: left center;
            opacity: .8;
        }
        .network .l1 { width: 62px; height: 3px; left: 48px; top: 29px; transform: rotate(17deg); }
        .network .l2 { width: 42px; height: 3px; left: 72px; top: 42px; transform: rotate(122deg); }
        .network .l3 { width: 62px; height: 3px; left: 44px; bottom: 34px; transform: rotate(-10deg); }
        .network .l4 { width: 58px; height: 3px; left: 86px; bottom: 30px; transform: rotate(-28deg); }

        .scales .pole {
            width: 8px; height: 82px; left: 76px; bottom: 10px;
            border-radius: 999px;
            background: color-mix(in srgb, var(--accent) 80%, white 20%);
        }
        .scales .bar {
            width: 120px; height: 8px; left: 20px; top: 26px;
            border-radius: 999px;
            background: color-mix(in srgb, var(--accent) 70%, white 30%);
        }
        .scales .arm {
            width: 82px; height: 4px; left: 39px; top: 34px;
            border-radius: 999px;
            background: rgba(255,255,255,0.92);
            transform: rotate(0deg);
        }
        .scales .pan {
            width: 34px; height: 14px; top: 48px;
            border: 2px solid color-mix(in srgb, var(--accent) 42%, white 58%);
            border-top: 0;
            border-radius: 0 0 24px 24px;
            background: rgba(255,255,255,0.52);
        }
        .scales .p1 { left: 18px; }
        .scales .p2 { right: 18px; }

        .system-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 4px 2px 0;
            color: var(--muted);
            font-size: 12px;
        }

        .system-footer .source {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.78);
            border: 1px solid rgba(16, 42, 75, 0.08);
        }

        .government-mark {
            display: inline-flex;
            flex-direction: column;
            align-items: flex-end;
            line-height: 1;
            color: var(--navy);
            font-weight: 900;
        }

        .government-mark small {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .2px;
            color: var(--muted);
            text-transform: none;
        }

        .government-mark .jujuy {
            font-size: clamp(26px, 3vw, 40px);
            letter-spacing: -1.5px;
        }

        .government-mark .tagline {
            margin-top: 2px;
            font-size: 12px;
            color: var(--teal);
            font-weight: 800;
        }

        .scroll-shadow {
            position: sticky;
            bottom: 0;
            height: 18px;
            background: linear-gradient(180deg, transparent, rgba(238, 243, 248, 0.95));
            pointer-events: none;
        }

        .profile-dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            top: calc(100% + 8px);
            background: rgba(255, 255, 255, 0.98);
            border: 1px solid var(--border);
            border-radius: 16px;
            min-width: 220px;
            box-shadow: var(--shadow);
            z-index: 100;
            padding: 8px;
            backdrop-filter: blur(10px);
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-user-info {
            padding: 8px 14px;
            display: flex;
            flex-direction: column;
        }
        .dropdown-user-info strong { font-size: 14px; color: var(--navy); }
        .dropdown-user-info span { font-size: 11px; color: var(--muted); }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            color: var(--text);
            font-size: 13.5px;
            font-weight: 600;
            border-radius: 10px;
            transition: background 0.15s ease, color 0.15s ease;
        }

        .dropdown-item:hover {
            background: var(--blue-soft);
            color: var(--blue);
        }

.dropdown-item.danger { color: #ea3b3b; }
.dropdown-item.danger:hover { background: rgba(234, 59, 59, 0.08); }

.dropdown-item i { width: 18px; text-align: center; font-size: 14px; }

        .dash-img-art {
            width: 100%;
            max-width: 100%;
            height: 200px;
            max-height: 200px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
            transform: scale(1.2);
            transform-origin: center;
        }
        .dash-img-art-coord {
            width: 100%;
            max-width: 100%;
            height: 180px;
            max-height: 180px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
            transform: scale(1.1);
            transform-origin: center;
        }

        @media (max-width: 1200px) {
            .hero,
            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .hero-visual {
                width: 100%;
                justify-self: start;
                margin-top: 8px;
            }

            .dashboard-card {
                min-height: 200px;
            }
        }

        @media (max-width: 920px) {
            .content {
                padding-top: 14px;
            }

            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .topbar-tools {
                width: 100%;
                justify-content: flex-start;
            }

            .period-box {
                min-width: 0;
                width: 100%;
            }

            .hero-stats {
                grid-template-columns: 1fr;
            }

            .dashboard-card {
                grid-template-columns: 1fr;
            }

            .dashboard-card .dash-art {
                min-height: 110px;
                justify-self: center;
            }
        }

        @media (max-width: 560px) {
            .content { padding: 14px; }
            .hero-main,
            .dashboard-card { padding: 18px 16px; }
            .topbar { padding: 0 0 12px; }
            .hero-copy h2 { font-size: 26px; }
            .dashboard-card { min-height: auto; }
            .system-footer { flex-direction: column; align-items: flex-start; }
            .government-mark { align-items: flex-start; }
        }
    </style>
</head>
<body>
<?php
// --- PASO 1: INSERTAR LA FUNCIÓN PHP AQUÍ ---
    /**
     * Genera un color hexadecimal consistente basado en una cadena de texto.
     *
     * @param string $string La cadena de texto (ej. nombre de usuario).
     * @return string El código de color hexadecimal (ej. #A1C2E3).
     */
    function generateColorFromString($string) {
        // Generamos un hash de la cadena para obtener un valor numérico consistente
        $hash = md5($string);
        
        // Tomamos los primeros 6 caracteres del hash para crear el color
        // y nos aseguramos de que sea un color "seguro" (no demasiado claro u oscuro)
        // ajustando los valores si es necesario.
        $color = '#';
        for ($i = 0; $i < 3; $i++) {
            // Extraemos dos caracteres hexadecimales para cada componente (R, G, B)
            $component = substr($hash, $i * 2, 2);
            
            // Convertimos a decimal y ajustamos el rango para evitar colores extremos
            $decimal = hexdec($component);
            $adjustedDecimal = str_pad(dechex(max(50, min(200, $decimal))), 2, '0', STR_PAD_LEFT);
            
            $color .= $adjustedDecimal;
        }
        
        return $color;
    }
    // --------------------------------------------------

    // Supongamos que estas variables ya vienen definidas de tu controlador
    $userName = $userName ?? 'Usuario Demo'; // Variable de ejemplo si no está definida
    $userRole = $userRole ?? 'Administrador'; // Variable de ejemplo

    // --- PASO 2: CALCULAR EL COLOR DEL USUARIO ---
    $userAvatarColor = generateColorFromString($userName);
    // ----------------------------------------------
    $basesTotal = (int) ($bases_total ?? 0);
    $basesActivas = (int) ($bases_activas ?? 0);
    $movilesTotal = (int) ($moviles_total ?? 0);
    $movilesOperativos = (int) ($moviles_op ?? 0);
    $totalAsistencias = (int) ($total_asistencias ?? 0);
    $internacionTotal = (int) ($internacion_total ?? 0);

    $coberturaSistema = 0;
    if ($basesTotal > 0 || $movilesTotal > 0) {
        $coberturaSistema = round((($basesTotal > 0 ? $basesActivas / $basesTotal : 0) + ($movilesTotal > 0 ? $movilesOperativos / $movilesTotal : 0)) / 2 * 100, 1);
    }

    $alertasActivas = max(0, $basesTotal - $basesActivas) + max(0, $movilesTotal - $movilesOperativos);
    $asistenciasVariacion = $basesTotal > 0 ? round(($totalAsistencias / max(1, $basesTotal)) * 100, 1) : 0;
    $userName = user()->username ?? 'Admin';
    $userRole = 'Usuario';
    $authModel = model('Myth\Auth\Models\GroupModel');
    $groups = $authModel->getGroupsForUser(user_id());
    if (!empty($groups)) {
        $userRole = ucfirst($groups[0]['name']);
    }
    
?>
<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-toggle-wrap">
            <button type="button" class="topbar-icon-btn" id="sidebar-toggle" title="Ocultar menú" aria-label="Ocultar menú">
                <i class="fas fa-angles-left"></i>
            </button>
        </div>
        <a href="<?= base_url(route_to('home')); ?>" class="brand">
        <img src="/imag/jujuy01.png" alt="Logo Jujuy">
        </a>

        <nav class="nav">
            <div class="nav-section">Principal</div>
            <a href="" class="nav-item active">
                <i class="fas fa-house"></i>
                <span>Inicio</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-file-lines"></i>
                <span>Digesto</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-book"></i> 
                <span>Manuales</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-chart-column"></i> 
                <span>Indicadores</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-chart-pie"></i> 
                <span>Estadísticas</span>
            </a>

            <a href="#" class="nav-item">
                <i class="fas fa-database"></i>
                <span>Carga de datos</span>
               <!--  <i class="fas fa-chevron-down nav-chevron"></i> -->
            </a>
           <!--  <div class="nav-sub-group" id="menu-carga">
                <a href="<?= base_url(route_to('base_list')); ?>" class="nav-sub"><i class="fas fa-ambulance"></i> Prehospitalario</a>
                <a href="<?= base_url(route_to('paciente_views')); ?>" class="nav-sub"><i class="fas fa-user-injured"></i> Gestión paciente</a>
                <a href="<?= base_url(route_to('saludmental_views')); ?>" class="nav-sub"><i class="fas fa-brain"></i> Salud mental</a>
                <a href="<?= base_url(route_to('servicio_views')); ?>" class="nav-sub"><i class="fas fa-diagram-project"></i> Servicios transversales</a>
            </div> -->
        </nav>
        <div class="sidebar-update">
            <i class="fas fa-rotate"></i>
            <div>
                <div class="update-label">Última actualización</div>
                <div class="update-value">13 jul 2026, 18:30</div> 
            </div>
        </div>
    </aside>

    <main class="content">
        <header class="topbar">
            <div class="topbar-title">
                <span class="eyebrow">Dirección General del</span>
                <h1>OBSERVATORIO Y ESTADISTICAS DEL M.S.</h1>
            </div>
            <div class="topbar-tools">
                <div class="period-box">
                    <i class="fas fa-calendar-days"></i>
                    <div>
                        <div class="period-label">Período</div>
                        <div class="period-value" id="topbar-date">13 jul 2026 - 13 jul 2026</div>
                    </div>
                    <i class="fas fa-chevron-down chevron-down"></i>
                </div>
                <button type="button" class="icon-btn" aria-label="Notificaciones">
                    <i class="fas fa-bell"></i>
                    <span class="badge"><?= $alertasActivas > 9 ? '9+' : $alertasActivas; ?></span>
                </button>
                <div class="profile-dropdown">
                    <button type="button" class="avatar-btn" id="profileBtn" aria-label="Abrir menú de perfil" style="background-color: <?= esc($userAvatarColor); ?>;">
                        <span class="avatar-initials"><?= esc(strtoupper(substr($userName, 0, 2))); ?></span>
                    </button>
                    
                    <div class="dropdown-menu" id="profileMenu">
                        <div class="dropdown-user-info">
                            <strong><?= esc($userName); ?></strong>
                            <span><?= esc($userRole); ?></span>
                        </div>
                        <hr style="border: 0; border-top: 1px solid var(--border); margin: 6px 0;">
                        
                        <!-- Tus tres opciones requeridas -->
                        <div class="nav-section" style="color: var(--muted); padding: 6px 14px 4px;">Administración</div>
                        <a href="<?= base_url(route_to('list_users')); ?>" class="dropdown-item">
                            <i class="fas fa-user-cog"></i>
                            <span>Usuarios</span>
                        </a>
                        <a href="<?= base_url(route_to('groups_list')); ?>" class="dropdown-item">
                            <i class="fas fa-users-cog"></i>
                            <span>Grupos</span>
                        </a>
                        <a href="<?= base_url(route_to('logout')); ?>" class="dropdown-item danger">
                            <i class="fas fa-right-from-bracket"></i>
                            <span>Cerrar sesión</span>
                        </a>
                    </div>
                </div>
                
            </div>
        </header>

        <div class="page-grid">
            <section class="hero" style="grid-template-columns:1fr;">
                <div class="hero-stats" style="grid-template-columns:repeat(3,minmax(0,1fr));margin-top:0;">
                    <article class="stat-card blue">
                        <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                        <div >
                            <div class="stat-label">Población estimada de la Provincia de Jujuy</div>
                            <div class="stat-value">811.611</div>
                            <div class="trend up"><i class="fas fa-arrow-up"></i> 12,3% vs periodo anterior</div>
                        </div>
                        <svg class="mini-chart" viewBox="0 0 130 42" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M2 31L17 26L31 28L47 20L63 23L79 14L95 18L111 10L128 8" stroke="#8dc2ff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M2 31L17 26L31 28L47 20L63 23L79 14L95 18L111 10L128 8L128 40L2 40Z" fill="url(#chartBlue)" opacity="0.30"/>
                            <defs>
                                <linearGradient id="chartBlue" x1="2" y1="8" x2="128" y2="40" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#2b7de9"/>
                                    <stop offset="1" stop-color="#2ab7a6"/>
                                </linearGradient>
                            </defs>
                        </svg>
                    </article>

                    <article class="stat-card teal">
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                        <div>
                            <div class="stat-label">Efectores Hospitalarios</div>
                            <div class="stat-value">98,6%</div>
                            <div class="trend up"><i class="fas fa-arrow-up"></i> 2,1% vs periodo anterior</div>
                        </div>
                        <div style="width:96px;height:96px;border-radius:50%;border:10px solid rgba(42,183,166,0.18);display:grid;place-items:center;margin-left:auto;background:conic-gradient(from 0deg,#2ab7a6 0 355deg, rgba(42,183,166,0.12) 355deg 360deg);">
                            <div style="width:44px;height:44px;border-radius:50%;background:#fff;display:grid;place-items:center;color:#2ab7a6;font-size:20px;"><i class="fas fa-user"></i></div>
                        </div>
                    </article>

                    <article class="stat-card orange">
                        <div class="stat-icon"><i class="fas fa-triangle-exclamation"></i></div>
                        <div>
                            <div class="stat-label">Establecimientos de APS</div>
                            <div class="stat-value">5</div>
                            <div class="trend neutral"><i class="fas fa-circle-info"></i> Ver detalle de alertas</div>
                        </div>
                        <div style="margin-left:auto;font-size:42px;color:rgba(242,140,70,0.35);"><i class="fas fa-bell"></i></div>
                    </article>
                </div>

                <div class="section-title" style="margin-top:4px;">Tableros de mando</div>
                <div class="dashboard-grid" style="margin-top:10px;">
                    <article class="panel dashboard-card blue">
                        <div>
                            <div class="dash-head">
                                <div class="dash-badge"><i class="fas fa-shield-heart"></i></div>
                                <div>
                                    <div class="dash-title">Secretaría de Salud</div>
                                    <div class="dash-subtitle">Indicadores de atención, cobertura y producción.</div>
                                </div>
                            </div>
                            <div class="dash-actions">
                                <a href="<?= base_url(route_to('inicio_views')); ?>" class="dash-btn">
                                    Acceder al tablero <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="dash-art">
                            <img src="/imag/secretariaSalud.png" alt="Secretaría de Salud" class="dash-img-art">
                        </div>
                    </article>

                    <article class="panel dashboard-card teal">
                        <div>
                            <div class="dash-head">
                                <div class="dash-badge"><i class="fas fa-brain"></i></div>
                                <div>
                                    <div class="dash-title">Secretaría de Salud Mental</div>
                                    <div class="dash-subtitle">Indicadores de salud mental y adicciones.</div>
                                </div>
                            </div>
                            <div class="dash-actions">
                                <span class="dash-btn btn-disabled">
                                    Acceder al tablero <i class="fas fa-arrow-right"></i>
                                </span>
                            </div>
                        </div>
                        <div class="dash-art">
                            <img src="/imag/saludMental.png" alt="Secretaría de Salud Mental" class="dash-img-art">
                        </div>
                    </article>

                    <article class="panel dashboard-card purple">
                        <div>
                            <div class="dash-head">
                                <div class="dash-badge"><i class="fas fa-network-wired"></i></div>
                                <div>
                                    <div class="dash-title">Coord. Gral. de Salud</div>
                                    <div class="dash-subtitle">Indicadores de APS y epidemiología.</div>
                                </div>
                            </div>
                            <div class="dash-actions">
                                <span class="dash-btn btn-disabled">
                                    Acceder al tablero <i class="fas fa-arrow-right"></i>
                                </span>
                            </div>
                        </div>
                        <div class="dash-art">
                            <img src="/imag/coordinacionSalud.png" alt="Coordinacion General de Salud" class="dash-img-art-coord">
                        </div>
                    </article>

                    <article class="panel dashboard-card orange">
                        <div>
                            <div class="dash-head">
                                <div class="dash-badge"><i class="fas fa-scale-balanced"></i></div>
                                <div>
                                    <div class="dash-title">Legal y Técnica</div>
                                    <div class="dash-subtitle">Apoyo legal y técnico en salud.</div>
                                </div>
                            </div>
                            <div class="dash-actions">
                                <span class="dash-btn btn-disabled">
                                    Acceder al tablero <i class="fas fa-arrow-right"></i>
                                </span>
                            </div>
                        </div>
                        <div class="dash-art">
                             <img src="/imag/legalTecnica.png" alt="Secretaría legal y técnica" class="dash-img-art-coord">
                        </div>
                    </article>
                </div>

                <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:16px;flex-wrap:wrap;margin-top:10px;">
                    <div class="source" style="background:transparent;border:none;padding:0;">
                        <i class="fas fa-circle-info"></i>
                        Fuente: Sistema Integrado de Salud de Jujuy
                    </div>
                    <div class="government-mark">
                        <small>Gobierno de</small>
                        <div class="jujuy">JUJUY</div>
                        <div class="tagline">Crece con la gente</div>
                    </div>
                </div>
            </section>
        <div class="scroll-shadow"></div>
    </main>
</div>

<script>
    (function() {
        const meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
        const fecha = new Date();
        const dia = `${fecha.getDate()} ${meses[fecha.getMonth()]} ${fecha.getFullYear()}`;
        const texto = `${dia} - ${dia}`;
        const element = document.getElementById('topbar-date');
        if (element) element.textContent = texto;

        const lastUpdate = document.getElementById('last-update-label');
        if (lastUpdate) {
            const horas = String(fecha.getHours()).padStart(2, '0');
            const minutos = String(fecha.getMinutes()).padStart(2, '0');
            lastUpdate.innerHTML = `<i class="fas fa-clock"></i> Última sincronización: ${dia} - ${horas}:${minutos} hs`;
        }
    })();

    function toggleGroup(event, id) {
        event.preventDefault();
        const group = document.getElementById(id);
        const trigger = event.currentTarget;
        const isOpen = group.classList.contains('open');

        document.querySelectorAll('.nav-sub-group.open').forEach(item => {
            if (item.id !== id) item.classList.remove('open');
        });
        document.querySelectorAll('.nav-item.open').forEach(item => {
            if (item !== trigger) item.classList.remove('open');
        });

        group.classList.toggle('open', !isOpen);
        trigger.classList.toggle('open', !isOpen);
    }

    (function() {
        const path = window.location.pathname;
        document.querySelectorAll('.nav-sub, .nav-item').forEach(link => {
            const href = link.getAttribute('href');
            if (!href || href === '#') return;
            const cleanHref = href.split('/').pop();
            if (cleanHref && path.includes(cleanHref)) {
                link.classList.add('active');
            }
        });
    })();
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const profileBtn = document.getElementById('profileBtn');
        const profileMenu = document.getElementById('profileMenu');

        // Alternar el menú al hacer clic en el botón
        profileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            profileMenu.classList.toggle('show');
        });

        // Cerrar el menú si se hace clic fuera de él
        document.addEventListener('click', function(e) {
            if (!profileMenu.contains(e.target) && e.target !== profileBtn) {
                profileMenu.classList.remove('show');
            }
        });
    });
</script>
<script>
    // Comportamiento del botón de colapsar barra lateral
    (function() {
        const button = document.getElementById('sidebar-toggle');
        if (!button) return;

        // Recuperar el estado guardado previamente
        const savedCollapsed = localStorage.getItem('inicioSidebarCollapsed');
        if (savedCollapsed === '1') {
            document.body.classList.add('sidebar-collapsed');
        }

        const syncButton = () => {
            const collapsed = document.body.classList.contains('sidebar-collapsed');
            button.title = collapsed ? 'Mostrar menú' : 'Ocultar menú';
            button.setAttribute('aria-label', button.title);
            button.innerHTML = `<i class="fas fa-${collapsed ? 'bars' : 'angles-left'}"></i>`;
        };

        syncButton();

        button.addEventListener('click', () => {
            document.body.classList.toggle('sidebar-collapsed');
            localStorage.setItem('inicioSidebarCollapsed', document.body.classList.contains('sidebar-collapsed') ? '1' : '0');
            syncButton();
            
            // Cerrar subgrupos abiertos para evitar visualizaciones extrañas en modo colapsado
            document.querySelectorAll('.nav-sub-group').forEach(group => group.classList.remove('open'));
            document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('open'));
        });
    })();
</script>
</body>
</html>
