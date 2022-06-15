<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;
    protected $fillable = [
         'path','project_id'
    ];
    public function Projects()
    {
        return $this->belongsTo('App\Models\Project');
    }
}
