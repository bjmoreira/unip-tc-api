<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Proficiencia extends Model
{
    protected $table = 'proficiencias';
    protected $fillable = ['user_id','topico_id','pontuacao','ultima_pratica','acertos','erros'];
    protected $casts = ['pontuacao' => 'float', 'ultima_pratica' => 'datetime'];
    public function topico() { return $this->belongsTo(Topico::class); }
}
