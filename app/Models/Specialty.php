<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Specialty extends Model
{
    protected $fillable = ['name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function doctors()
    {
        return $this->hasMany(User::class);
    }

    public function fields()
    {
        return $this->hasMany(SpecialtyField::class)->orderBy('sort_order');
    }

    // Returns all fields (core defaults merged with saved config + custom fields)
    public function resolvedFields(): Collection
    {
        $saved = $this->fields()->get()->keyBy('field_key');

        // Build core field objects (use saved row if exists, otherwise default)
        $coreFields = collect(SpecialtyField::coreFields())->map(function ($def, $key) use ($saved) {
            if ($saved->has($key)) {
                return $saved[$key];
            }
            return new SpecialtyField([
                'specialty_id' => $this->id,
                'field_key'    => $key,
                'label'        => $def['label'],
                'type'         => $def['type'],
                'options'      => $def['options'],
                'is_active'    => true,
                'is_core'      => true,
                'sort_order'   => $def['sort_order'],
            ]);
        })->sortBy('sort_order')->values();

        // Custom fields (non-core saved fields)
        $customFields = $saved->filter(fn($f) => !$f->is_core)->sortBy('sort_order')->values();

        return $coreFields->concat($customFields);
    }
}
