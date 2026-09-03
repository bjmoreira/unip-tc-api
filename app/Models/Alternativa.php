<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Alternativa extends Model
{
    protected $fillable = ['questao_id', 'letra', 'texto', 'correta'];
    protected $casts = ['correta' => 'boolean'];
    public function questao() { return $this->belongsTo(Questao::class); }
}
