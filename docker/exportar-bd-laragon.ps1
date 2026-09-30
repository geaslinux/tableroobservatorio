# Exporta las bases rep y base2 del MySQL de Laragon a docker/mysql/init/
# para que el contenedor de MySQL las importe al crearse por primera vez.
#
# Uso (con MySQL de Laragon iniciado):
#   powershell -ExecutionPolicy Bypass -File docker\exportar-bd-laragon.ps1
#   powershell -ExecutionPolicy Bypass -File docker\exportar-bd-laragon.ps1 -Usuario root -Clave ""

param(
    [string]$Usuario = "root",
    [string]$Clave = "",
    [string]$Servidor = "127.0.0.1",
    [int]$Puerto = 3306,
    [string[]]$Bases = @("rep", "base2")
)

$ErrorActionPreference = "Stop"

$mysqldump = Get-ChildItem "C:\laragon\bin\mysql\*\bin\mysqldump.exe" -ErrorAction SilentlyContinue |
    Select-Object -First 1 -ExpandProperty FullName
if (-not $mysqldump) {
    Write-Error "No se encontró mysqldump.exe en C:\laragon\bin\mysql. Verificá la instalación de Laragon."
}

$destino = Join-Path $PSScriptRoot "mysql\init"
New-Item -ItemType Directory -Force $destino | Out-Null

$orden = 10
foreach ($base in $Bases) {
    $archivo = Join-Path $destino ("{0:D2}-{1}.sql" -f $orden, $base)
    Write-Host "Exportando '$base' -> $archivo"

    $argumentos = @(
        "--host=$Servidor", "--port=$Puerto", "--user=$Usuario",
        "--databases", $base,
        "--single-transaction", "--routines", "--triggers", "--events",
        "--default-character-set=utf8mb4",
        "--set-gtid-purged=OFF",
        "--result-file=$archivo"
    )
    if ($Clave -ne "") { $argumentos = @("--password=$Clave") + $argumentos }

    & $mysqldump @argumentos
    if ($LASTEXITCODE -ne 0) {
        Write-Error "Falló la exportación de '$base'."
    }
    $orden += 10
}

Write-Host ""
Write-Host "Listo. Si el volumen de MySQL ya existía, recrealo para que importe los volcados:"
Write-Host "  docker compose down -v"
Write-Host "  docker compose up -d --build"
