<?php

namespace Config;

use Myth\Auth\Config\Auth as AuthBase;

/**
 * Configuración de Myth\Auth del proyecto.
 *
 * Hereda todo de la configuración de la librería (vendor/myth/auth) y solo reemplaza
 * los listados de usuarios, grupos y permisos por las vistas de app/Views/auth/:
 * mismo estilo que sistemabasec y solo de consulta (sin crear, editar ni eliminar).
 * Así no hace falta modificar vendor/.
 */
class Auth extends AuthBase
{
    public function __construct()
    {
        parent::__construct();

        $this->views = array_merge($this->views, [
            'listUsers'         => 'auth/list_users',
            'listPermisosUser'  => 'auth/list_permisos_user',
            'listGroupUser'     => 'auth/list_group_user',
            'listGroups'        => 'auth/list_groups',
            'listPermisosGroup' => 'auth/list_permisos_group',
        ]);
    }
}
