<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\YearFolder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class DocumentFolderService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly DefaultYearFolderProvisioner $defaultYearFolderProvisioner,
    ) {}

    /**
     * @return array{created: int, skipped: int}
     */
    public function provisionDefaultFolders(YearFolder $yearFolder, ?int $actorId = null): array
    {
        return $this->defaultYearFolderProvisioner->provision($yearFolder, $actorId);
    }

    public function createFolder(
        YearFolder $yearFolder,
        string $name,
        ?DocumentFolder $parent = null,
        ?string $description = null,
        ?int $actorId = null,
    ): DocumentFolder {
        $name = $this->sanitizeName($name);

        if ($parent && $parent->year_folder_id !== $yearFolder->id) {
            throw new InvalidArgumentException('Folder induk harus berada dalam tahun yang sama.');
        }

        $depth = $parent ? $parent->depth() + 1 : 1;
        $maxDepth = (int) config('cooperation.max_folder_depth', 10);
        if ($depth > $maxDepth) {
            throw new InvalidArgumentException("Kedalaman folder maksimal {$maxDepth} level.");
        }

        $this->assertUniqueName($yearFolder->id, $parent?->id, $name);

        $maxSort = DocumentFolder::query()
            ->where('year_folder_id', $yearFolder->id)
            ->where(function ($q) use ($parent) {
                if ($parent) {
                    $q->where('parent_id', $parent->id);
                } else {
                    $q->whereNull('parent_id');
                }
            })
            ->max('sort_order');

        $folder = DocumentFolder::query()->create([
            'year_folder_id' => $yearFolder->id,
            'parent_id' => $parent?->id,
            'name' => $name,
            'slug' => $this->uniqueSlug($yearFolder->id, $parent?->id, Str::slug($name) ?: 'folder'),
            'description' => $description,
            'sort_order' => ((int) $maxSort) + 1,
            'is_active' => true,
            'created_by' => $actorId,
            'updated_by' => $actorId,
        ]);

        $this->auditLogService->log('document_folder.created', $folder, null, [
            'name' => $folder->name,
            'year_folder_id' => $folder->year_folder_id,
            'parent_id' => $folder->parent_id,
        ]);

        return $folder;
    }

    public function renameFolder(DocumentFolder $folder, string $name, ?string $description = null, ?int $actorId = null): DocumentFolder
    {
        $name = $this->sanitizeName($name);
        $this->assertUniqueName($folder->year_folder_id, $folder->parent_id, $name, $folder->id);

        $old = $folder->only(['name', 'description', 'slug']);

        $folder->update([
            'name' => $name,
            'slug' => $this->uniqueSlug($folder->year_folder_id, $folder->parent_id, Str::slug($name) ?: 'folder', $folder->id),
            'description' => $description ?? $folder->description,
            'updated_by' => $actorId,
        ]);

        $this->auditLogService->log(
            'document_folder.renamed',
            $folder,
            $old,
            $folder->only(['name', 'description', 'slug']),
        );

        return $folder->fresh();
    }

    public function moveFolder(DocumentFolder $folder, ?DocumentFolder $destinationParent, ?int $actorId = null): DocumentFolder
    {
        $this->validateMove($folder, $destinationParent);

        $this->assertUniqueName(
            $folder->year_folder_id,
            $destinationParent?->id,
            $folder->name,
            $folder->id,
        );

        $old = [
            'parent_id' => $folder->parent_id,
            'year_folder_id' => $folder->year_folder_id,
        ];

        $folder->update([
            'parent_id' => $destinationParent?->id,
            'updated_by' => $actorId,
        ]);

        $this->auditLogService->log(
            'document_folder.moved',
            $folder,
            $old,
            [
                'parent_id' => $folder->parent_id,
                'year_folder_id' => $folder->year_folder_id,
            ],
        );

        return $folder->fresh();
    }

    public function validateMove(DocumentFolder $folder, ?DocumentFolder $destinationParent): void
    {
        if ($destinationParent) {
            if ($destinationParent->id === $folder->id) {
                throw new InvalidArgumentException('Folder tidak dapat dipindahkan ke dirinya sendiri.');
            }

            if ($destinationParent->year_folder_id !== $folder->year_folder_id) {
                throw new InvalidArgumentException('Folder hanya dapat dipindahkan dalam tahun yang sama.');
            }

            if ($destinationParent->isDescendantOf($folder) || $this->isAncestorLoaded($destinationParent, $folder)) {
                throw new InvalidArgumentException('Folder tidak dapat dipindahkan ke dalam turunannya sendiri.');
            }

            $newDepth = $destinationParent->depth() + 1 + $this->subtreeExtraDepth($folder);
            $maxDepth = (int) config('cooperation.max_folder_depth', 10);
            if ($newDepth > $maxDepth) {
                throw new InvalidArgumentException("Pemindahan melebihi kedalaman maksimal {$maxDepth} level.");
            }
        }
    }

    public function deleteFolder(DocumentFolder $folder, bool $confirmNonEmpty = false, ?int $actorId = null): void
    {
        $childCount = $folder->children()->count();
        $documentCount = $folder->documents()->count();

        if (($childCount > 0 || $documentCount > 0) && ! $confirmNonEmpty) {
            throw new InvalidArgumentException(
                "Folder ini berisi {$childCount} folder dan {$documentCount} dokumen. Konfirmasi ulang untuk menghapus."
            );
        }

        DB::transaction(function () use ($folder, $actorId): void {
            $this->softDeleteRecursive($folder, $actorId);
        });

        $this->auditLogService->log('document_folder.deleted', $folder, [
            'id' => $folder->id,
            'uuid' => $folder->uuid,
            'name' => $folder->name,
        ]);
    }

    public function moveDocument(Document $document, ?DocumentFolder $folder, ?int $actorId = null): Document
    {
        if ($folder && $folder->year_folder_id !== $document->year_folder_id) {
            throw new InvalidArgumentException('Dokumen hanya dapat dipindahkan dalam tahun yang sama.');
        }

        $old = [
            'document_folder_id' => $document->document_folder_id,
            'institution_folder_id' => $document->institution_folder_id,
        ];

        $document->update([
            'document_folder_id' => $folder?->id,
            // Keep legacy column in sync when destination maps to a migrated institution.
            'institution_folder_id' => $folder?->legacy_institution_folder_id,
        ]);

        $this->auditLogService->log('document.moved', $document, $old, [
            'document_folder_id' => $document->document_folder_id,
            'institution_folder_id' => $document->institution_folder_id,
            'actor_id' => $actorId,
        ]);

        return $document->fresh();
    }

    public function reorder(DocumentFolder $folder, string $direction, ?int $actorId = null): void
    {
        $siblings = DocumentFolder::query()
            ->where('year_folder_id', $folder->year_folder_id)
            ->where(function ($q) use ($folder) {
                if ($folder->parent_id) {
                    $q->where('parent_id', $folder->parent_id);
                } else {
                    $q->whereNull('parent_id');
                }
            })
            ->ordered()
            ->get()
            ->values();

        $index = $siblings->search(fn (DocumentFolder $item) => $item->id === $folder->id);
        if ($index === false) {
            return;
        }

        $swapWith = $direction === 'up' ? $index - 1 : $index + 1;
        if ($swapWith < 0 || $swapWith >= $siblings->count()) {
            return;
        }

        $other = $siblings[$swapWith];
        $currentSort = $folder->sort_order;
        $folder->update(['sort_order' => $other->sort_order, 'updated_by' => $actorId]);
        $other->update(['sort_order' => $currentSort, 'updated_by' => $actorId]);
    }

    public function sanitizeName(string $name): string
    {
        $name = trim(preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?? '');

        if ($name === '' || $name === '.' || $name === '..') {
            throw new InvalidArgumentException('Nama folder tidak valid.');
        }

        if (mb_strlen($name) > 255) {
            throw new InvalidArgumentException('Nama folder maksimal 255 karakter.');
        }

        return $name;
    }

    private function softDeleteRecursive(DocumentFolder $folder, ?int $actorId): void
    {
        foreach ($folder->children()->get() as $child) {
            $this->softDeleteRecursive($child, $actorId);
        }

        $folder->update(['updated_by' => $actorId, 'is_active' => false]);
        $folder->delete();
    }

    private function assertUniqueName(int $yearFolderId, ?int $parentId, string $name, ?int $ignoreId = null): void
    {
        $normalized = mb_strtolower($name);

        $exists = DocumentFolder::query()
            ->where('year_folder_id', $yearFolderId)
            ->where(function ($q) use ($parentId) {
                if ($parentId === null) {
                    $q->whereNull('parent_id');
                } else {
                    $q->where('parent_id', $parentId);
                }
            })
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->get()
            ->contains(fn (DocumentFolder $f) => mb_strtolower($f->name) === $normalized);

        if ($exists) {
            throw new InvalidArgumentException('Nama folder sudah digunakan pada tingkat yang sama.');
        }
    }

    private function uniqueSlug(int $yearFolderId, ?int $parentId, string $base, ?int $ignoreId = null): string
    {
        $slug = $base !== '' ? $base : 'folder';
        $candidate = $slug;
        $i = 2;

        while (
            DocumentFolder::withTrashed()
                ->where('year_folder_id', $yearFolderId)
                ->where(function ($q) use ($parentId) {
                    if ($parentId === null) {
                        $q->whereNull('parent_id');
                    } else {
                        $q->where('parent_id', $parentId);
                    }
                })
                ->where('slug', $candidate)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $candidate = $slug.'-'.$i;
            $i++;
        }

        return $candidate;
    }

    private function subtreeExtraDepth(DocumentFolder $folder): int
    {
        $max = 0;
        $walk = function (DocumentFolder $node, int $depth) use (&$walk, &$max): void {
            $max = max($max, $depth);
            foreach ($node->children as $child) {
                $walk($child, $depth + 1);
            }
        };

        $folder->loadMissing('children.children.children.children.children');
        $walk($folder, 1);

        return max(0, $max - 1);
    }

    private function isAncestorLoaded(DocumentFolder $candidate, DocumentFolder $folder): bool
    {
        // Walk up from destination via fresh queries to avoid stale relations.
        $currentId = $candidate->parent_id;
        while ($currentId) {
            if ($currentId === $folder->id) {
                return true;
            }
            $currentId = DocumentFolder::query()->whereKey($currentId)->value('parent_id');
        }

        return false;
    }
}
