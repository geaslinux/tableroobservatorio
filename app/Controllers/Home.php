<?php

namespace App\Controllers;

class Home extends BaseController
{
    private const MESES = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

    public function index(): string
    {
        helper('actualizacion');

        $userName = user()->username ?? 'Usuario';
        $groups   = user_id() ? model('Myth\Auth\Models\GroupModel')->getGroupsForUser((int) user_id()) : [];
        $userRole = ! empty($groups) ? ucfirst($groups[0]['name']) : 'Usuario';

        $ultimaAct = ultima_actualizacion();

        return view('home/index', [
            'userName'        => $userName,
            'userRole'        => $userRole,
            'userAvatarColor' => $this->colorDesdeTexto($userName),
            'alertasActivas'  => 0,
            'fechaHoy'        => $this->fechaCorta(time()),
            'ultimaActTexto'  => $ultimaAct ? $this->fechaCorta(strtotime($ultimaAct), true) : 'Sin dato',

            // Indicadores de las tarjetas superiores
            'indicadores' => [
                'poblacion' => [
                    'valor'     => '811.611',
                    'tendencia' => '12,3% vs periodo anterior',
                    // Serie de la curva (valores relativos, el último es el actual)
                    'serie'     => [62, 66, 64, 71, 69, 76, 73, 80, 84],
                ],
                'efectores' => [
                    'porcentaje' => 98.6,
                    'tendencia'  => '2,1% vs periodo anterior',
                ],
                'aps' => [
                    'valor'   => 5,
                    'detalle' => 'Ver detalle de establecimientos',
                ],
            ],
        ]);
    }

    /** "30 sep 2026" o, con hora, "30 sep 2026, 11:46 hs". */
    private function fechaCorta(int $ts, bool $conHora = false): string
    {
        $texto = date('j', $ts) . ' ' . self::MESES[(int) date('n', $ts) - 1] . ' ' . date('Y', $ts);

        return $conHora ? $texto . ', ' . date('H:i', $ts) . ' hs' : $texto;
    }

    /** Color de avatar estable a partir del nombre (ni muy claro ni muy oscuro). */
    private function colorDesdeTexto(string $texto): string
    {
        $hash  = md5($texto);
        $color = '#';

        for ($i = 0; $i < 3; $i++) {
            $componente = max(50, min(200, hexdec(substr($hash, $i * 2, 2))));
            $color     .= str_pad(dechex($componente), 2, '0', STR_PAD_LEFT);
        }

        return $color;
    }
}