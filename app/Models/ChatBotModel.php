<?php
namespace App\Models;

use CodeIgniter\Model;

class ChatBotModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'chat_bot';
    protected $primaryKey       = 'chat_bot_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'App\Entities\ChatBot';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'ejercicio', 'mes', 'fecha', 'efector_id',
        'turnos_otorgados', 'observacion', 'estado'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function conRelaciones()
    {
        return $this->select('
                chat_bot.*,
                efector.nombre            AS efector_nombre,
                efector.nivel_complejidad AS efector_nivel,
                efector.region            AS efector_region
            ')
            ->join('efector', 'efector.efector_id = chat_bot.efector_id', 'left');
    }
}