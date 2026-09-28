# sistemabase en Docker

## Servicios

| Servicio     | Contenedor        | URL / puerto en el host         |
|--------------|-------------------|---------------------------------|
| `app`        | `sistemabase_app` | http://localhost:8080           |
| `db`         | `sistemabase_db`  | `localhost:3307` (MySQL 8.0)    |
| `phpmyadmin` | `sistemabase_pma` | http://localhost:8081           |

La imagen de `app` es PHP 8.1 + Apache con `intl`, `mysqli`, `pdo_mysql`, `gd`, `zip`, `mbstring`, `xml`, `bcmath` y `opcache`.
El DocumentRoot apunta a `public/` y `mod_rewrite` está activo.

## Archivos

```
Dockerfile
docker-compose.yml
.dockerignore
docker/
├── apache/000-default.conf       # VirtualHost -> public/
├── php/php.ini                   # zona horaria, límites de subida y memoria
├── entrypoint.sh                 # permisos de writable/ y composer install si falta vendor/
├── exportar-bd-laragon.ps1       # exporta rep y base2 desde Laragon
└── mysql/
    ├── conf.d/sistemabase.cnf    # charset, sql_mode, lower_case_table_names
    └── init/00-crear-bases.sql   # crea rep y base2 y da permisos al usuario
```

## Primer arranque

1. **Exportar las bases desde Laragon** (con MySQL de Laragon iniciado):
   ```powershell
   powershell -ExecutionPolicy Bypass -File docker\exportar-bd-laragon.ps1
   ```
   Genera `docker/mysql/init/10-rep.sql` y `20-base2.sql`. Están en `.gitignore` porque tienen datos reales.

2. **Levantar los contenedores**:
   ```bash
   docker compose up -d --build
   ```
   MySQL importa los `.sql` de `docker/mysql/init/` **solo la primera vez** (con el volumen vacío).

3. Abrir http://localhost:8080

Para volver a importar los volcados hay que borrar el volumen: `docker compose down -v` y `docker compose up -d`.

## Configuración

La conexión a la base se toma de variables de entorno del contenedor, que tienen prioridad sobre `.env`
y `app/Config/Database.php`. No hace falta modificar `.env`: el resto de sus valores se sigue leyendo normalmente.

Se pueden cambiar puertos y credenciales sin tocar el `docker-compose.yml`, con variables de entorno
(Docker Compose también las lee del `.env` del proyecto, que es el mismo que usa CodeIgniter):

| Variable           | Por defecto   |
|--------------------|---------------|
| `APP_PORT`         | `8080`        |
| `DB_PORT`          | `3307`        |
| `PMA_PORT`         | `8081`        |
| `DB_USER`          | `sistemabase` |
| `DB_PASSWORD`      | `sistemabase` |
| `DB_ROOT_PASSWORD` | `root`        |
| `CI_ENVIRONMENT`   | `development` |

Ejemplo en PowerShell, si los puertos ya están ocupados:
```powershell
$env:APP_PORT='8090'; $env:DB_PORT='3318'; $env:PMA_PORT='8091'; docker compose up -d
```

## Comandos útiles

```bash
docker compose up -d --build                      # construir y levantar
docker compose down                               # detener (conserva la base)
docker compose down -v                            # detener y BORRAR la base
docker compose logs -f app                        # logs de Apache/PHP
docker compose exec app php spark routes          # spark dentro del contenedor
docker compose exec app php spark migrate         # migraciones
docker compose exec app composer install          # dependencias
docker compose exec db mysql -uroot -proot rep    # consola MySQL
```

## Notas

- El código se monta como volumen (`./:/var/www/html`), así que los cambios se ven sin reconstruir.
  Se usa la carpeta `vendor/` del proyecto; si no existe, el contenedor corre `composer install` al arrancar.
- `.env` y `app/Config/google-credentials.json` no se copian a la imagen (`.dockerignore`),
  pero sí están disponibles en desarrollo gracias al volumen.
- Para producción conviene quitar el volumen del código en `app`, usar `CI_ENVIRONMENT=production`
  y cambiar todas las contraseñas.
