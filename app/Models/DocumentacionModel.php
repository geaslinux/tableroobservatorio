<?php
namespace App\Models;

use CodeIgniter\Model;

/**
 * Manual de usuario: una fila = una página que documenta una pantalla (vista) de un módulo para un rol.
 * Lo carga Administración → Manual y lo lee el módulo Documentación (y el otro sistema que comparte la base).
 */
class DocumentacionModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'documentacion';
    protected $primaryKey       = 'documentacion_id';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\Documentacion::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'modulo', 'modulo_nombre', 'vista', 'vista_nombre', 'tipo_vista', 'rol',
        'titulo', 'resumen', 'descripcion', 'validaciones', 'imagen',
        'orden', 'version', 'estado', 'created_by', 'updated_by',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Tipo de pantalla que se documenta
    public const TIPOS_VISTA = [
        'listado'     => 'Listado',
        'formulario'  => 'Formulario de carga',
        'panel'       => 'Panel / tablero',
        'importacion' => 'Importación de Excel',
        'reporte'     => 'Reporte / exportación',
    ];

    // Quién usa la pantalla. "ambos" aparece en el manual de los dos roles.
    public const ROLES = [
        'administrativo' => 'Administrativo',
        'funcionario'    => 'Funcionario',
        'ambos'          => 'Ambos roles',
    ];

    // Roles que se eligen al entrar a Documentación
    public const ROLES_MANUAL = ['administrativo', 'funcionario'];

    // Páginas publicadas que ve un rol (las suyas y las marcadas "ambos"), en orden de lectura
    public function publicadasParaRol(string $rol): self
    {
        return $this->whereIn('rol', [$rol, 'ambos'])
                    ->where('estado', 'activo')
                    ->orderBy('modulo_nombre', 'ASC')
                    ->orderBy('orden', 'ASC')
                    ->orderBy('titulo', 'ASC');
    }
}
