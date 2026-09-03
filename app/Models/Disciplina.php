<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Disciplina extends Model
{
    protected $fillable = ['nome', 'slug'];
    public function topicos() { return $this->hasMany(Topico::class); }
}
