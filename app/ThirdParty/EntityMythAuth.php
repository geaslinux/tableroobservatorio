<?php

namespace CodeIgniter;

/**
 * Nombre viejo de la Entity del framework, que CodeIgniter ya no trae.
 * myth/auth 1.2.1 lo sigue usando en Passwords\ValidatorInterface: sin esta clase, validar una
 * contraseña nueva (regla strong_password) da error fatal. Se carga desde Config\Autoload::$classmap.
 */
class Entity extends \CodeIgniter\Entity\Entity
{
}