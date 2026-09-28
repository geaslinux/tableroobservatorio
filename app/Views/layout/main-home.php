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
            --sidebar-w:           285px;
            --sidebar-collapsed-w: 100px;
            --topbar-h:            60px;
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
            --teal:                #2ab7a6;
            --teal-light:          #00d4bc;
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
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* ── ESTRUCTURA LAYOUT GRID ── */
        .layout {
            display: grid;
            grid-template-columns: var(--sidebar-w) 1fr;
            flex: 1;
            min-height: 100vh;
            transition: grid-template-columns 0.28s ease;
        }

        /* Ajuste de grid al colapsar sidebar */
        body.sidebar-collapsed .layout {
            grid-template-columns: var(--sidebar-collapsed-w) 1fr;
        }

        /* ── MAIN CONTENT (Contenido Principal Adaptable) ── */
        .main-content {
            flex: 1;
            padding: 20px;
            min-width: 0;
            width: 100%;
            overflow-y: auto;
        }

        /* ── RESPONSIVE / ADAPTACIÓN MÓVIL ── */
        @media (max-width: 768px) {
            .layout {
                grid-template-columns: 1fr;
            }
            .main-content {
                margin-left: 0 !important;
                padding: 14px;
            }
        }
    </style>
</head>

<body>
    <!-- Topbar/Header del sistema -->
    <?= $this->include('layout/header-home');?>

    <!-- Envoltorio Grid idéntico a base_view -->
    <div class="layout">

        <!-- Menú Lateral -->
        <?= $this->include('layout/sidebar-home');?>

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