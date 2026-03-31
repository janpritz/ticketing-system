<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Training
 * 
 * @property int $id
 * @property string $status
 * @property int $faq_count
 * @property int $doc_count
 * @property Carbon|null $started_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Training extends Model
{
	protected $table = 'trainings';

	protected $casts = [
		'faq_count' => 'int',
		'doc_count' => 'int',
		'started_at' => 'datetime',
		'completed_at' => 'datetime'
	];

	protected $fillable = [
		'status',
		'faq_count',
		'doc_count',
		'started_at',
		'completed_at',
		'trigger'
	];
}
