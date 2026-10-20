<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Prova extends Model
{
    protected $fillable = ['user_id','numero','tipo','tamanho','status','acertos','concluida_em'];
    protected $casts = ['concluida_em' => 'datetime'];
    public function estudante() { return $this->belongsTo(User::class, 'user_id'); }
    public function itens()     { return $this->hasMany(ProvaQuestao::class, 'prova_id'); }
}
