<?php
namespace App\Entities;

use App\Models\DocumentacionModel;
use CodeIgniter\Entity\Entity;

class Documentacion extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'documentacion_id' => 'integer',
        'orden'            => 'integer',
    ];

    // ─── Links del listado de carga ──────────────────────────────────────
    public function getEditLink()
    {
        return '<a class="bl-icon-link" title="Editar" href="' . base_url(route_to('documentacion_show', $this->documentacion_id)) . '"><i class="fas fa-edit"></i></a>';
    }

    public function getDeleteLink()
    {
        return '<form style="display:inline;" action="' . base_url(route_to('documentacion_destroy')) . '" method="post">
                    <input type="hidden" name="_method" value="DELETE" />
                    <input type="hidden" name="id" value="' . $this->documentacion_id . '" />'
                    . csrf_field() .
                    '<a class="bl-icon-link" title="Eliminar" href="javascript:void(0)" onclick="if(confirm(\'¿Eliminar esta página del manual?\')){this.closest(\'form\').submit();} return false;"><i class="fas fa-trash"></i></a>
                </form>';
    }

    public function setCampoOculto()
    {
        $this->campoOculto = '<input type="hidden" name="documentacion_id" value="' . $this->documentacion_id . '">
                              <input type="hidden" name="_method" value="PUT" />';
    }

    public function getCampoOculto()
    {
        return $this->campoOculto;
    }

    // ─── Datos para mostrar ──────────────────────────────────────────────
    // Validaciones como array de ['campo', 'regla', 'mensaje'] (en la base es JSON)
    public function getValidacionesLista(): array
    {
        $lista = json_decode((string) ($this->attributes['validaciones'] ?? ''), true);

        return is_array($lista) ? $lista : [];
    }

    // URL pública de la captura, o null si no tiene
    public function getImagenUrl(): ?string
    {
        $imagen = $this->attributes['imagen'] ?? null;

        return $imagen ? base_url($imagen) : null;
    }

    public function getTipoVistaNombre(): string
    {
        return DocumentacionModel::TIPOS_VISTA[$this->attributes['tipo_vista'] ?? ''] ?? (string) ($this->attributes['tipo_vista'] ?? '');
    }

    public function getRolNombre(): string
    {
        return DocumentacionModel::ROLES[$this->attributes['rol'] ?? ''] ?? (string) ($this->attributes['rol'] ?? '');
    }
}
