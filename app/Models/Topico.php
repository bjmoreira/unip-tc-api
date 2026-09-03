<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Topico extends Model
{
    protected $fillable = ['disciplina_id', 'nome', 'slug'];
    public function disciplina() { return $this->belongsTo(Disciplina::class); }
    public function questoes() { return $this->hasMany(Questao::class); }
}
