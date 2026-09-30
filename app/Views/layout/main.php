<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->renderSection('title');?></title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.3/css/bulma.min.css">
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="/css/modal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.17/dist/sweetalert2.all.min.js"></script>
    <link rel="icon" href="/imag/logoobse.png">
    <link rel="stylesheet" href="/css/style.css">

    <style> 

        :root {
    /* ── Layout & Dimensiones ── */
    --sidebar-w:           210px;
    --sidebar-collapsed-w: 84px;
    --sidebar-tab:         42px;
    --topbar-h:            70px;
    --radius:              22px;

    /* ── Colores Principales / Marca ── */
    --navy:                #0e2a4d;
    --navy-2:              #132f57;
    --navy-3:              #081c34;
    --navy-dark:           #111e30;
    --navy-hover:          rgba(255, 255, 255, 0.07);

    /* ── Azules ── */
    --blue:                #2b7de9;
    --blue-soft:           rgba(43, 125, 233, 0.10);

    /* ── Teal / Verdes ── */
    --teal:                #173051;
    --teal-light:          #214270;
    --teal-bg:             rgba(42, 183, 166, 0.12);
    --teal-soft:           rgba(42, 183, 166, 0.11);

    /* ── Acentos (Naranja / Púrpura) ── */
    --orange:              #f28c46;
    --orange-soft:         rgba(242, 140, 70, 0.12);
    --purple:              #7f69db;
    --purple-soft:         rgba(127, 105, 219, 0.11);

    /* ── Neutrales, Fondos y Bordes ── */
    --bg:                  #eef3f8;
    --gray-bg:             #e8eaed;
    --panel:               #ffffff;
    --white:               #ffffff;
    --border:              rgba(16, 42, 75, 0.10);

    /* ── Tipografía & Texto ── */
    --text:                #1d2b3c;
    --text-main:           #1a2b45;
    --text-muted:          #5a6a7e;
    --muted:               #66778a;

    /* ── Estados & Sombras ── */
    --shadow:              0 18px 40px rgba(10, 28, 48, 0.08);
    --danger:              #e74c3c;
    --warning:             #f0a500;
}
        *, *::before, *::after { 
            box-sizing: border-box; 
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--gray-bg);
            min-height: 100vh;
        }

        /* ── ESTRUCTURA LAYOUT GRID ── */
        /* El sidebar es fixed (no se mueve al hacer scroll); el contenido y el footer
           dejan a la izquierda el espacio que ocupa. */
        .layout {
            display: block;
            padding-left: calc(var(--sidebar-w) + var(--sidebar-tab));
            margin-top: var(--topbar-h);
            min-height: calc(100vh - var(--topbar-h));
            transition: padding-left 0.22s ease;
        }
        .layout ~ .footer {
            margin-left: calc(var(--sidebar-w) + var(--sidebar-tab));
            transition: margin-left 0.22s ease;
        }

        /* Ajuste al colapsar sidebar */
        body.sidebar-collapsed .layout { padding-left: var(--sidebar-collapsed-w); }
        body.sidebar-collapsed .layout ~ .footer { margin-left: var(--sidebar-collapsed-w); }

        /* ── MAIN CONTENT (Contenido Principal Adaptable) ── */
        .main-content {
            flex: 1; 
            padding: 20px; 
            min-width: 0;
            width: 100%;
        }

        /* ── ESTILOS SECUNDARIOS Y NAVBAR ── */
        .pie-pagina {
            position: sticky;
            width: 100%;
            height: 4rem;
            top: calc(100vh - 4rem);
        }

        /* Mapas (Leaflet): sin el recuadro negro de foco al hacer clic en un departamento o marcador */
        .leaflet-container path.leaflet-interactive:focus,
        .leaflet-container .leaflet-interactive:focus,
        .leaflet-container .leaflet-marker-icon:focus {
            outline: none;
        }

        .is-vhcenter {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .navbar-custom {
            background-color: white;
            color: black;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .navbar-admin .navbar-link { color: inherit; }
        .navbar-custom .navbar-brand .navbar-item { color: black; }
        .navbar-custom .navbar-burger span { background-color: black; }
        .navbar-custom .navbar-menu .navbar-item { color: black; }
        .navbar-custom .navbar-item.has-dropdown .navbar-link::after { border-color: black transparent transparent; }
        .navbar-custom .navbar-dropdown { background-color: white; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
        .navbar-custom .navbar-dropdown a.navbar-item { color: black; }
        .navbar-custom .navbar-dropdown a.navbar-item:hover { background-color: #f5f5f5; }

        .bl-btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 8px 16px; border-radius: 8px; border: none;
        font-size: 13px; font-weight: 600; cursor: pointer;
        text-decoration: none; transition: filter 0.15s, opacity 0.15s;
        white-space: nowrap;
    }
        .bl-btn:hover { filter: brightness(1.1); text-decoration: none; }
        .bl-btn.teal    { background: var(--teal);    color: #fff; }
        .bl-btn.navy    { background: var(--navy);    color: #fff; }
        .bl-btn.green   { background: #27ae60;        color: #fff; }
        .bl-btn.ghost   { background: #f0f2f5; color: var(--text-muted); border: 1px solid var(--border); }
        .bl-btn.ghost:hover { background: #e4e7ed; color: var(--text-main); }
        .bl-btn.sm { padding: 5px 11px; font-size: 12px; }
        .bl-btn.danger-ghost { background: rgba(231,76,60,0.09); color: #c0392b; border: 1px solid rgba(231,76,60,0.25); }
        .bl-btn.danger-ghost:hover { background: rgba(231,76,60,0.18); }


        /* ── RESPONSIVE / ADAPTACIÓN MÓVIL ── */
        @media (max-width: 768px) {
            .layout,
            body.sidebar-collapsed .layout { padding-left: 0; }
            .layout ~ .footer,
            body.sidebar-collapsed .layout ~ .footer { margin-left: 0; }
            .main-content { 
                margin-left: 0 !important; 
                padding: 14px; 
            }
        }
    </style>   
</head>

<body>
    <!-- Topbar/Header del sistema -->
    <?= $this->include('layout/header');?>

    <!-- Envoltorio Grid idéntico a base_view -->
    <div class="layout">
        
        <!-- Menú Lateral -->
        <?= $this->include('layout/sidebar');?>

        <!-- Área Principal de Contenido -->
        <main class="main-content">
            <?php if(session('msg')):?>
                <script>
                    Swal.fire({
                        icon: '<?=session('msg.type');?>',
                        title:'',
                        text: '<?= session('msg.body')?>',
                        showConfirmButton: false,
                        timer: 2000
                    })
                </script>
            <?php endif;?>        

            <?= $this->renderSection('content');?>
            <?= $this->renderSection('main');?>
        </main>

    </div>  

    <?= $this->include('layout/footer');?>

    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.js"></script>
    <script src="/js/modal.js"></script>
    <?= $this->renderSection('js');?>
</body>
</html>