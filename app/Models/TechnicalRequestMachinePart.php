<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TechnicalRequestMachinePart extends Model {
    protected $fillable = ['tr_machine_id','item_id','quantidade','applied_at','user_id'];

    public function intervention() { return $this->belongsTo(TechnicalRequestMachine::class, 'tr_machine_id'); }
    public function item() { return $this->belongsTo(Item::class); }
    public function user() { return $this->belongsTo(User::class); }
}
