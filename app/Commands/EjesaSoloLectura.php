<?php

namespace App\Commands;

use App\Libraries\Ejesa\Lectura;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Comprueba que la conexión 'ejesa' NO puede escribir en las tablas de sistemabasec.
 * Intenta un UPDATE que no coincide con ninguna fila: no cambia nada aunque la base lo acepte.
 */
class EjesaSoloLectura extends BaseCommand
{
    protected $group       = 'EJESA';
    protected $name        = 'ejesa:solo-lectura';
    protected $description = 'Verifica que la conexión de la pestaña EJESA sea de solo lectura.';

    public function run(array $params)
    {
        $lectura = new Lectura();
        CLI::write('Conexión: ' . $lectura->usuarioConexion());

        if ($lectura->puedeEscribir()) {
            CLI::error('INSEGURO: la conexión "ejesa" puede escribir en ejesa_corte.');
            CLI::write('Crear un usuario de solo SELECT y configurarlo en .env (database.ejesa.username / password).');
            CLI::write('Ejemplo (como root en MySQL):');
            CLI::write("  CREATE USER 'ejesa_lector'@'localhost' IDENTIFIED BY '<clave>';");
            CLI::write("  GRANT SELECT ON rep.* TO 'ejesa_lector'@'localhost';");

            return EXIT_ERROR;
        }

        CLI::write('OK: la base rechazó el UPDATE. La conexión es de solo lectura.', 'green');

        return EXIT_SUCCESS;
    }
}
