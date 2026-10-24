<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Clinical department (Cardiology, Paediatrics...). Deliberately separate from
 * the HR `departments` table — a payroll unit and a place of treatment are not
 * the same thing.
 */
class HospitalDepartment extends Model
{
    use SoftDeletes;

    protected $table = 'hospital_departments';

    protected $fillable = ['name', 'code', 'description', 'location', 'phone', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function doctors()
    {
        return $this->hasMany(Doctor::class, 'department_id', 'id');
    }

    public function wards()
    {
        return $this->hasMany(HospitalWard::class, 'department_id', 'id');
    }
}
