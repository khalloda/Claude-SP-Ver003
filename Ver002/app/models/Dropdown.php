<?php

/**
 * File: app/models/Dropdown.php
 * Purpose: Dropdown model for categories and lookup data
 * Depends on: Model base class
 * Notes: Handles hierarchical dropdown data, categories, lookups
 */

namespace App\Models;

use App\Core\Model;

class Dropdown extends Model
{
    protected static string $table = 'sp_dropdowns';
    
    protected static array $fillable = [
        'type',
        'name',
        'value',
        'parent_id',
        'sort_order',
        'status'
    ];

    protected static array $casts = [
        'id' => 'int',
        'parent_id' => 'int',
        'sort_order' => 'int',
        'status' => 'int'
    ];

    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;

    // Common dropdown types
    public const TYPE_CATEGORY = 'category';
    public const TYPE_UNIT = 'unit_of_measure';
    public const TYPE_PAYMENT_TERMS = 'payment_terms';
    public const TYPE_COUNTRY = 'country';
    public const TYPE_CITY = 'city';
    public const TYPE_STATUS = 'status';

    public static function getByType(string $type, bool $activeOnly = true): array
    {
        $query = self::where('type', $type);
        
        if ($activeOnly) {
            $query->where('status', self::STATUS_ACTIVE);
        }
        
        return $query->orderBy('sort_order')
                    ->orderBy('name')
                    ->get();
    }

    public static function getByParent(int $parentId, bool $activeOnly = true): array
    {
        $query = self::where('parent_id', $parentId);
        
        if ($activeOnly) {
            $query->where('status', self::STATUS_ACTIVE);
        }
        
        return $query->orderBy('sort_order')
                    ->orderBy('name')
                    ->get();
    }

    public static function getHierarchical(string $type, bool $activeOnly = true): array
    {
        $items = self::getByType($type, $activeOnly);
        return self::buildHierarchy($items);
    }

    public static function getCategories(bool $activeOnly = true): array
    {
        return self::getByType(self::TYPE_CATEGORY, $activeOnly);
    }

    public static function getUnitsOfMeasure(bool $activeOnly = true): array
    {
        return self::getByType(self::TYPE_UNIT, $activeOnly);
    }

    public static function getPaymentTerms(bool $activeOnly = true): array
    {
        return self::getByType(self::TYPE_PAYMENT_TERMS, $activeOnly);
    }

    public static function getCountries(bool $activeOnly = true): array
    {
        return self::getByType(self::TYPE_COUNTRY, $activeOnly);
    }

    private static function buildHierarchy(array $items, int $parentId = null): array
    {
        $hierarchy = [];
        
        foreach ($items as $item) {
            if ($item->parent_id === $parentId) {
                $children = self::buildHierarchy($items, $item->id);
                if (!empty($children)) {
                    $item->children = $children;
                }
                $hierarchy[] = $item;
            }
        }
        
        return $hierarchy;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function activate(): bool
    {
        $this->status = self::STATUS_ACTIVE;
        return $this->save();
    }

    public function deactivate(): bool
    {
        $this->status = self::STATUS_INACTIVE;
        return $this->save();
    }

    public function hasChildren(): bool
    {
        return !empty($this->children());
    }

    public function getDepth(): int
    {
        $depth = 0;
        $parent = $this->parent();
        
        while ($parent) {
            $depth++;
            $parent = $parent->parent();
        }
        
        return $depth;
    }

    public function getPath(): array
    {
        $path = [];
        $current = $this;
        
        while ($current) {
            array_unshift($path, $current);
            $current = $current->parent();
        }
        
        return $path;
    }

    public function getIndentedName(): string
    {
        $depth = $this->getDepth();
        $indent = str_repeat('&nbsp;&nbsp;', $depth);
        return $indent . htmlspecialchars($this->name);
    }

    public function getStatusLabel(): string
    {
        return $this->isActive() ? 'Active' : 'Inactive';
    }

    public function getStatusClass(): string
    {
        return $this->isActive() ? 'success' : 'secondary';
    }

    public function getDisplayValue(): string
    {
        return $this->value ?: $this->name;
    }

    public function canBeDeleted(): bool
    {
        // Check if this dropdown is being used
        if ($this->hasChildren()) {
            return false;
        }
        
        // Check if it's referenced by products
        if ($this->type === self::TYPE_CATEGORY) {
            $productCount = Product::where('category_id', $this->id)->count();
            if ($productCount > 0) {
                return false;
            }
        }
        
        return true;
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        
        // Add computed fields
        $data['status_label'] = $this->getStatusLabel();
        $data['status_class'] = $this->getStatusClass();
        $data['display_value'] = $this->getDisplayValue();
        $data['indented_name'] = $this->getIndentedName();
        $data['has_children'] = $this->hasChildren();
        $data['depth'] = $this->getDepth();
        $data['is_active'] = $this->isActive();
        $data['can_be_deleted'] = $this->canBeDeleted();
        
        return $data;
    }

    // Relationships
    public function parent(): ?self
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): array
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function products(): array
    {
        if ($this->type === self::TYPE_CATEGORY) {
            return $this->hasMany(Product::class, 'category_id');
        }
        
        return [];
    }

    // Validation
    public function validate(array $data = null): array
    {
        $errors = [];
        $data = $data ?: $this->attributes;

        // Required fields
        if (empty($data['type'])) {
            $errors['type'] = 'Type is required';
        }

        if (empty($data['name'])) {
            $errors['name'] = 'Name is required';
        }

        // Check for circular references
        if (isset($data['parent_id']) && $data['parent_id']) {
            if ($this->id && $this->wouldCreateCircularReference($data['parent_id'])) {
                $errors['parent_id'] = 'Cannot create circular reference';
            }
        }

        // Sort order validation
        if (isset($data['sort_order']) && $data['sort_order'] < 0) {
            $errors['sort_order'] = 'Sort order cannot be negative';
        }

        return $errors;
    }

    private function wouldCreateCircularReference(int $parentId): bool
    {
        if ($parentId === $this->id) {
            return true;
        }
        
        $parent = self::find($parentId);
        
        while ($parent) {
            if ($parent->id === $this->id) {
                return true;
            }
            $parent = $parent->parent();
        }
        
        return false;
    }

    // Helper methods for seeding data
    public static function seed(): void
    {
        $categories = [
            ['type' => self::TYPE_CATEGORY, 'name' => 'Automotive Parts', 'sort_order' => 1],
            ['type' => self::TYPE_CATEGORY, 'name' => 'Electronics', 'sort_order' => 2],
            ['type' => self::TYPE_CATEGORY, 'name' => 'Mechanical Parts', 'sort_order' => 3],
            ['type' => self::TYPE_CATEGORY, 'name' => 'Tools & Equipment', 'sort_order' => 4],
        ];
        
        $units = [
            ['type' => self::TYPE_UNIT, 'name' => 'Piece', 'value' => 'pcs', 'sort_order' => 1],
            ['type' => self::TYPE_UNIT, 'name' => 'Kilogram', 'value' => 'kg', 'sort_order' => 2],
            ['type' => self::TYPE_UNIT, 'name' => 'Liter', 'value' => 'l', 'sort_order' => 3],
            ['type' => self::TYPE_UNIT, 'name' => 'Meter', 'value' => 'm', 'sort_order' => 4],
            ['type' => self::TYPE_UNIT, 'name' => 'Set', 'value' => 'set', 'sort_order' => 5],
        ];
        
        $paymentTerms = [
            ['type' => self::TYPE_PAYMENT_TERMS, 'name' => 'Cash', 'value' => 'cash', 'sort_order' => 1],
            ['type' => self::TYPE_PAYMENT_TERMS, 'name' => 'Net 15', 'value' => 'net_15', 'sort_order' => 2],
            ['type' => self::TYPE_PAYMENT_TERMS, 'name' => 'Net 30', 'value' => 'net_30', 'sort_order' => 3],
            ['type' => self::TYPE_PAYMENT_TERMS, 'name' => 'Net 60', 'value' => 'net_60', 'sort_order' => 4],
        ];
        
        $allItems = array_merge($categories, $units, $paymentTerms);
        
        foreach ($allItems as $item) {
            $item['status'] = self::STATUS_ACTIVE;
            self::create($item);
        }
    }
}