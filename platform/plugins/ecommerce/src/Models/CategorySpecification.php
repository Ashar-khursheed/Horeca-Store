<?php

namespace Botble\Ecommerce\Models;

use Illuminate\Database\Eloquent\Model;

class CategorySpecification extends Model
{
	protected $fillable = [
		'category_id',
		'specification_name',
		'created_by',
		'updated_by'
	];
}