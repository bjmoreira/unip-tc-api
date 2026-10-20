<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ProvaQuestao extends Model
{
    protected $table = 'prova_questoes';
    protected $fillable = ['prova_id','questao_id','ordem','alternativa_id','correta'];
    protected $casts = ['correta' => 'boolean'];
    public function prova()   { return $this->belongsTo(Prova::class, 'prova_id'); }
    public function questao() { return $this->belongsTo(Questao::class, 'questao_id'); }
}
