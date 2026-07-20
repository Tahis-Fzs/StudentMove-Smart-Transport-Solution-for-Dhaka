<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Faculty;
use App\Models\University;

class AcademicCatalogService
{
    public function resolveUniversity(?string $selected, ?string $custom): ?string
    {
        $name = $this->pick($selected, $custom);
        if ($name === null) {
            return null;
        }

        University::firstOrCreate(
            ['name' => $name],
            ['short_name' => null, 'calendar_type' => 'bi', 'is_custom' => $selected === '__other__']
        );

        return $name;
    }

    public function resolveFaculty(?string $selected, ?string $custom): ?string
    {
        $name = $this->pick($selected, $custom);
        if ($name === null) {
            return null;
        }

        Faculty::firstOrCreate(
            ['name' => $name],
            ['is_custom' => $selected === '__other__']
        );

        return $name;
    }

    public function resolveDepartment(?string $selected, ?string $custom): ?string
    {
        $name = $this->pick($selected, $custom);
        if ($name === null) {
            return null;
        }

        Department::firstOrCreate(
            ['name' => $name],
            ['is_custom' => $selected === '__other__']
        );

        return $name;
    }

    public function calendarForUniversity(?string $universityName): string
    {
        if (!$universityName) {
            return 'bi';
        }

        $uni = University::where('name', $universityName)->first();

        return $uni?->calendar_type ?: 'bi';
    }

    private function pick(?string $selected, ?string $custom): ?string
    {
        $selected = trim((string) $selected);
        $custom = trim((string) $custom);

        if ($selected === '' || $selected === '__other__') {
            return $custom !== '' ? $custom : null;
        }

        return $selected;
    }
}
