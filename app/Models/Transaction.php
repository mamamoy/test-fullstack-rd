<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $appends = ['is_approved'];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
    public function approvedBy(){
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function type(){
        return $this->belongsTo(TransactionType::class, 'transaction_type_id');
    }

    public function getIsApprovedAttribute(){
        return $this->attributes['isApproved'] ? 'Approved' : 'Pending';
    }

    public function detail(){
        return $this->hasMany(TransactionDetail::class, 'transaction_id');
    }

}
