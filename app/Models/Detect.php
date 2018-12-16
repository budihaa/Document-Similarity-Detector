<?php

namespace App\Models;

use App\Models\DetectSimilarity;
use Illuminate\Database\Eloquent\Model;

class Detect extends Model
{
    protected $fillable = [

    ];

    /**
     * Create One to Many Relationship
     *
     * @return void
     */
    public function detectSimilarities()
    {
        return $this->hasMany(DetectSimilarity::class);
    }
}
