<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignInfo extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'campaign_id',
        'title',
        'content',
        'order',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
