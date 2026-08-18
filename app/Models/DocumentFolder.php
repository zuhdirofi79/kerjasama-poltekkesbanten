<?php

namespace App\Models;

use App\Support\CooperationFolderTaxonomy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DocumentFolder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'year_folder_id',
        'parent_id',
        'name',
        'slug',
        'description',
        'sort_order',
        'is_active',
        'created_by',
        'updated_by',
        'legacy_institution_folder_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (DocumentFolder $folder): void {
            if (empty($folder->uuid)) {
                $folder->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function yearFolder(): BelongsTo
    {
        return $this->belongsTo(YearFolder::class, 'year_folder_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'document_folder_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function legacyInstitutionFolder(): BelongsTo
    {
        return $this->belongsTo(InstitutionFolder::class, 'legacy_institution_folder_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeCanonicalRoots(Builder $query): Builder
    {
        $names = array_values(array_unique([
            ...CooperationFolderTaxonomy::rootNames(),
            ...CooperationFolderTaxonomy::rootAliases(),
        ]));
        $slugs = CooperationFolderTaxonomy::rootSlugs();

        return $query->root()->where(function (Builder $inner) use ($names, $slugs): void {
            $inner->whereIn('name', $names)->orWhereIn('slug', $slugs);
        });
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function isStructuralRoot(): bool
    {
        return CooperationFolderTaxonomy::isCanonicalRoot($this);
    }

    /**
     * @return list<DocumentFolder>
     */
    public function ancestorChain(): array
    {
        $chain = [];
        $current = $this->parent;

        while ($current) {
            array_unshift($chain, $current);
            $current = $current->parent;
        }

        return $chain;
    }

    public function depth(): int
    {
        return count($this->ancestorChain()) + 1;
    }

    public function isDescendantOf(self $other): bool
    {
        $current = $this->parent;

        while ($current) {
            if ($current->id === $other->id) {
                return true;
            }
            $current = $current->parent;
        }

        return false;
    }
}
