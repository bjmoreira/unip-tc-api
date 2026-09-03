<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Questao extends Model
{
    protected $table = 'questoes';
    protected $fillable = ['topico_id', 'enunciado', 'nivel', 'dificuldade'];
    public function topico() { return $this->belongsTo(Topico::class); }
    public function alternativas() { return $this->hasMany(Alternativa::class); }
}
