<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Specialty;
use App\Models\SpecialtyField;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SpecialtyController extends Controller
{
    public function index()
    {
        $specialties = Specialty::withCount('doctors')->latest()->paginate(15);
        return view('admin.specialties.index', compact('specialties'));
    }

    public function create()
    {
        return view('admin.specialties.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255|unique:specialties,name',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Specialty::create($validated);

        return redirect()->route('admin.specialties.index')
            ->with('success', 'İxtisas uğurla yaradıldı.');
    }

    public function edit(Specialty $specialty)
    {
        $fields = $specialty->resolvedFields();
        return view('admin.specialties.edit', compact('specialty', 'fields'));
    }

    public function update(Request $request, Specialty $specialty)
    {
        $validated = $request->validate([
            'name'                    => 'required|string|max:255|unique:specialties,name,' . $specialty->id,
            'is_active'               => 'boolean',
            'core_fields'             => 'nullable|array',
            'core_fields.*.is_active' => 'boolean',
            'core_fields.*.label'     => 'nullable|string|max:255',
        ]);

        $validated['is_active'] = $request->boolean('is_active', false);
        $specialty->update(['name' => $validated['name'], 'is_active' => $validated['is_active']]);

        // Sync core field configs
        $coreDefaults = SpecialtyField::coreFields();
        $sort = 0;
        foreach ($coreDefaults as $key => $def) {
            $sort += 10;
            $isActive = $request->boolean("core_fields.{$key}.is_active", false);
            $label    = $request->input("core_fields.{$key}.label") ?: $def['label'];

            $specialty->fields()->updateOrCreate(
                ['field_key' => $key, 'is_core' => true],
                [
                    'label'      => $label,
                    'type'       => $def['type'],
                    'options'    => $def['options'],
                    'is_active'  => $isActive,
                    'sort_order' => $def['sort_order'],
                ]
            );
        }

        return redirect()->route('admin.specialties.edit', $specialty)
            ->with('success', 'İxtisas yeniləndi.');
    }

    public function addField(Request $request, Specialty $specialty)
    {
        $validated = $request->validate([
            'label'        => 'required|string|max:255',
            'type'         => 'required|in:text,number,date,select,textarea',
            'options_text' => 'nullable|string',
        ]);

        $options = null;
        if ($validated['type'] === 'select' && !empty($validated['options_text'])) {
            $options = collect(preg_split('/\r?\n/', trim($validated['options_text'])))
                ->map(fn($line) => trim($line))
                ->filter()
                ->values()
                ->map(fn($line) => ['value' => Str::slug($line, '_'), 'label' => $line])
                ->all();
        }

        $maxSort = $specialty->fields()->max('sort_order') ?? 50;

        $specialty->fields()->create([
            'field_key'  => 'custom_' . Str::random(8),
            'label'      => $validated['label'],
            'type'       => $validated['type'],
            'options'    => $options,
            'is_active'  => true,
            'is_core'    => false,
            'sort_order' => $maxSort + 10,
        ]);

        return redirect()->route('admin.specialties.edit', $specialty)
            ->with('success', 'Sahə əlavə edildi.');
    }

    public function updateField(Request $request, Specialty $specialty, SpecialtyField $field)
    {
        abort_if($field->specialty_id !== $specialty->id, 404);

        $validated = $request->validate([
            'label'        => 'required|string|max:255',
            'type'         => 'required|in:text,number,date,select,textarea',
            'options_text' => 'nullable|string',
            'is_active'    => 'boolean',
        ]);

        $options = $field->options;
        if ($validated['type'] === 'select' && !empty($validated['options_text'])) {
            $options = collect(preg_split('/\r?\n/', trim($validated['options_text'])))
                ->map(fn($line) => trim($line))
                ->filter()
                ->values()
                ->map(fn($line) => ['value' => Str::slug($line, '_'), 'label' => $line])
                ->all();
        }

        $field->update([
            'label'     => $validated['label'],
            'type'      => $validated['type'],
            'options'   => $options,
            'is_active' => $request->boolean('is_active', false),
        ]);

        return redirect()->route('admin.specialties.edit', $specialty)
            ->with('success', 'Sahə yeniləndi.');
    }

    public function removeField(Specialty $specialty, SpecialtyField $field)
    {
        abort_if($field->specialty_id !== $specialty->id || $field->is_core, 404);
        $field->delete();

        return redirect()->route('admin.specialties.edit', $specialty)
            ->with('success', 'Sahə silindi.');
    }

    public function destroy(Specialty $specialty)
    {
        $specialty->delete();
        return redirect()->route('admin.specialties.index')
            ->with('success', 'İxtisas silindi.');
    }
}
