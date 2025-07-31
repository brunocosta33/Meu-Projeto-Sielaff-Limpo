<?php
namespace App\Forms;

use Illuminate\Database\Eloquent\Model;
class Inquire extends Model
{
public $table = 'inquires';
public $fillable = ['name','email','country', 'description','product_id'];
}