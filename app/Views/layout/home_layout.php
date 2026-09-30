<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?></title>
    <link rel="icon" href="/imag/logoobse.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('css/home.css') ?>">
    <?= $this->renderSection('styles') ?>
</head>
<body>
<div class="app-shell">
    <?= $this->include('home/partials/sidebar') ?>
    <div class="sidebar-backdrop" id="sidebar-backdrop"></div>

    <main class="content">
        <?= $this->include('home/partials/topbar') ?>

        <?= $this->renderSection('content') ?>
    </main>
</div>

<script src="<?= base_url('js/home.js') ?>"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>