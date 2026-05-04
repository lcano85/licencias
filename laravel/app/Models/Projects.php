<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Projects extends Model {
    use HasFactory;

    protected $table = 'projects';
    protected $fillable = [
        'project_title', 'description', 'created_by', 'clientID', 'assign_by', 'comments', 'status', 'due_date'
    ];

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function activities() {
        return $this->hasMany(Activities::class, 'projectID', 'id');
    }

    public function getAssignedUsersListAttribute(): array{
        $raw = $this->attributes['assign_by'] ?? $this->assign_by ?? null;

        if ($raw === null || $raw === '') {
            return [];
        }

        $ids = [];
        if (is_array($raw)) {
            $ids = $raw;
        } elseif (is_int($raw) || ctype_digit((string) $raw)) {
            // single numeric id (int or numeric string)
            $ids = [(int) $raw];
        } elseif (is_string($raw)) {
            $trimmed = trim($raw);

            // JSON? (["1","2"] or [1,2])
            if ($this->looksLikeJson($trimmed)) {
                $decoded = json_decode($trimmed, true);
                if (is_array($decoded)) {
                    $ids = $decoded;
                } elseif (is_numeric($decoded)) {
                    $ids = [(int)$decoded];
                }
            } else {
                // comma separated string: "1,2" or "1, 2"
                $parts = preg_split('/\s*,\s*/', $trimmed);
                foreach ($parts as $p) {
                    if ($p === '') continue;
                    if (is_numeric($p)) {
                        $ids[] = (int)$p;
                    }
                }
            }
        }

        $ids = array_values(array_unique(array_filter($ids, function ($v) {
            return is_numeric($v);
        })));

        if (empty($ids)) {
            return [];
        }
        return \App\Models\User::whereIn('id', $ids)->pluck('name')->toArray();
    }

    protected function looksLikeJson($string): bool {
        if (!is_string($string)) return false;

        $s = trim($string);
        if ($s === '') return false;

        $first = $s[0];
        $last  = $s[strlen($s) - 1];

        if (($first === '{' && $last === '}') || ($first === '[' && $last === ']')) {
            json_decode($s);
            return json_last_error() === JSON_ERROR_NONE;
        }

        return false;
    }
}
