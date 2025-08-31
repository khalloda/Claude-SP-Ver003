<?php

/**
 * File: app/core/Model.php
 * Purpose: Base model with database operations, validation, and relationships
 * Depends on: Database, Config
 * Notes: Provides CRUD operations, query builder, relationships, validation
 */

namespace App\Core;

use App\Config\Database;
use PDO;
use PDOException;

abstract class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';
    protected static array $fillable = [];
    protected static array $hidden = [];
    protected static array $casts = [];
    protected static array $relationships = [];
    
    protected array $attributes = [];
    protected array $original = [];
    protected bool $exists = false;

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    public static function getTable(): string
    {
        if (empty(static::$table)) {
            $className = (new \ReflectionClass(static::class))->getShortName();
            static::$table = 'sp_' . strtolower($className) . 's';
        }
        return static::$table;
    }

    public static function find(int $id): ?static
    {
        $sql = "SELECT * FROM " . static::getTable() . " WHERE " . static::$primaryKey . " = ? LIMIT 1";
        
        try {
            $stmt = Database::getInstance()->prepare($sql);
            $stmt->execute([$id]);
            
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($data) {
                $instance = new static($data);
                $instance->exists = true;
                $instance->original = $data;
                return $instance;
            }
        } catch (PDOException $e) {
            error_log("Model find error: " . $e->getMessage());
        }

        return null;
    }

    public static function findOrFail(int $id): static
    {
        $model = static::find($id);
        
        if (!$model) {
            throw new \RuntimeException("Model not found with ID: {$id}");
        }
        
        return $model;
    }

    public static function where(string $column, $operator = null, $value = null): QueryBuilder
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        return (new QueryBuilder(static::class))->where($column, $operator, $value);
    }

    public static function orderBy(string $column, string $direction = 'ASC'): QueryBuilder
    {
        return (new QueryBuilder(static::class))->orderBy($column, $direction);
    }

    public static function limit(int $limit): QueryBuilder
    {
        return (new QueryBuilder(static::class))->limit($limit);
    }

    public static function whereRaw(string $rawSql, array $bindings = []): QueryBuilder
    {
        return (new QueryBuilder(static::class))->whereRaw($rawSql, $bindings);
    }

    public static function all(): array
    {
        $sql = "SELECT * FROM " . static::getTable() . " ORDER BY " . static::$primaryKey;
        
        try {
            $stmt = Database::getInstance()->prepare($sql);
            $stmt->execute();
            
            $results = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $instance = new static($row);
                $instance->exists = true;
                $instance->original = $row;
                $results[] = $instance;
            }
            
            return $results;
        } catch (PDOException $e) {
            error_log("Model all error: " . $e->getMessage());
            return [];
        }
    }

    public static function create(array $attributes): static
    {
        $instance = new static();
        $instance->fill($attributes);
        $instance->save();
        
        return $instance;
    }

    public function save(): bool
    {
        if ($this->exists) {
            return $this->update();
        } else {
            return $this->insert();
        }
    }

    private function insert(): bool
    {
        $attributes = $this->getInsertableAttributes();
        
        if (empty($attributes)) {
            return false;
        }

        $attributes['created_at'] = date('Y-m-d H:i:s');
        $attributes['updated_at'] = date('Y-m-d H:i:s');

        $columns = array_keys($attributes);
        $placeholders = array_fill(0, count($attributes), '?');
        
        $sql = "INSERT INTO " . static::getTable() . " (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        try {
            $stmt = Database::getInstance()->prepare($sql);
            $result = $stmt->execute(array_values($attributes));
            
            if ($result) {
                $this->attributes[static::$primaryKey] = Database::getInstance()->lastInsertId();
                $this->attributes['created_at'] = $attributes['created_at'];
                $this->attributes['updated_at'] = $attributes['updated_at'];
                $this->exists = true;
                $this->original = $this->attributes;
                return true;
            }
        } catch (PDOException $e) {
            error_log("Model insert error: " . $e->getMessage());
        }

        return false;
    }

    private function update(): bool
    {
        $attributes = $this->getDirtyAttributes();
        
        if (empty($attributes)) {
            return true;
        }

        $attributes['updated_at'] = date('Y-m-d H:i:s');

        $setPairs = [];
        foreach (array_keys($attributes) as $column) {
            $setPairs[] = "{$column} = ?";
        }

        $sql = "UPDATE " . static::getTable() . " SET " . implode(', ', $setPairs) . " WHERE " . static::$primaryKey . " = ?";
        
        try {
            $stmt = Database::getInstance()->prepare($sql);
            $values = array_values($attributes);
            $values[] = $this->attributes[static::$primaryKey];
            
            $result = $stmt->execute($values);
            
            if ($result) {
                $this->attributes['updated_at'] = $attributes['updated_at'];
                $this->original = $this->attributes;
                return true;
            }
        } catch (PDOException $e) {
            error_log("Model update error: " . $e->getMessage());
        }

        return false;
    }

    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }

        $sql = "DELETE FROM " . static::getTable() . " WHERE " . static::$primaryKey . " = ?";
        
        try {
            $stmt = Database::getInstance()->prepare($sql);
            $result = $stmt->execute([$this->attributes[static::$primaryKey]]);
            
            if ($result) {
                $this->exists = false;
                return true;
            }
        } catch (PDOException $e) {
            error_log("Model delete error: " . $e->getMessage());
        }

        return false;
    }

    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, static::$fillable) || empty(static::$fillable)) {
                $this->attributes[$key] = $this->castAttribute($key, $value);
            }
        }

        return $this;
    }

    public function toArray(): array
    {
        $attributes = $this->attributes;
        
        foreach (static::$hidden as $hidden) {
            unset($attributes[$hidden]);
        }

        return $attributes;
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }

    public function __get(string $name)
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }

        if (method_exists($this, $name)) {
            return $this->$name();
        }

        return null;
    }

    public function __set(string $name, $value): void
    {
        if (in_array($name, static::$fillable) || empty(static::$fillable)) {
            $this->attributes[$name] = $this->castAttribute($name, $value);
        }
    }

    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    private function castAttribute(string $key, $value)
    {
        if (!isset(static::$casts[$key])) {
            return $value;
        }

        switch (static::$casts[$key]) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'float':
            case 'double':
                return (float) $value;
            case 'string':
                return (string) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'array':
            case 'json':
                return is_string($value) ? json_decode($value, true) : $value;
            case 'date':
            case 'datetime':
                return $value instanceof \DateTime ? $value : new \DateTime($value);
            default:
                return $value;
        }
    }

    private function getInsertableAttributes(): array
    {
        $insertable = [];
        
        foreach ($this->attributes as $key => $value) {
            if ($key !== static::$primaryKey && (in_array($key, static::$fillable) || empty(static::$fillable))) {
                $insertable[$key] = $value;
            }
        }

        return $insertable;
    }

    private function getDirtyAttributes(): array
    {
        $dirty = [];
        
        foreach ($this->attributes as $key => $value) {
            if ($key !== static::$primaryKey && 
                (in_array($key, static::$fillable) || empty(static::$fillable)) &&
                (!isset($this->original[$key]) || $this->original[$key] !== $value)) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    protected function belongsTo(string $related, string $foreignKey = null, string $ownerKey = null): ?Model
    {
        $foreignKey = $foreignKey ?: strtolower(class_basename($related)) . '_id';
        $ownerKey = $ownerKey ?: 'id';
        
        $foreignValue = $this->attributes[$foreignKey] ?? null;
        
        if (!$foreignValue) {
            return null;
        }

        return $related::where($ownerKey, $foreignValue)->first();
    }

    protected function hasMany(string $related, string $foreignKey = null, string $localKey = null): array
    {
        $foreignKey = $foreignKey ?: strtolower(class_basename(static::class)) . '_id';
        $localKey = $localKey ?: static::$primaryKey;
        
        $localValue = $this->attributes[$localKey] ?? null;
        
        if (!$localValue) {
            return [];
        }

        return $related::where($foreignKey, $localValue)->get();
    }
}

class QueryBuilder
{
    private string $modelClass;
    private array $wheres = [];
    private array $orderBy = [];
    private ?int $limit = null;
    private ?int $offset = null;

    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
    }

    public function where(string $column, $operatorOrValue, $value = null): self
    {
        if ($value === null) {
            // Two arguments: column and value (operator defaults to '=')
            $this->wheres[] = ['AND', $column, '=', $operatorOrValue];
        } else {
            // Three arguments: column, operator, and value
            $this->wheres[] = ['AND', $column, $operatorOrValue, $value];
        }
        return $this;
    }

    public function orWhere(string $column, $operatorOrValue, $value = null): self
    {
        if ($value === null) {
            // Two arguments: column and value (operator defaults to '=')
            $this->wheres[] = ['OR', $column, '=', $operatorOrValue];
        } else {
            // Three arguments: column, operator, and value
            $this->wheres[] = ['OR', $column, $operatorOrValue, $value];
        }
        return $this;
    }

    public function whereRaw(string $rawSql, array $bindings = []): self
    {
        $this->wheres[] = ['AND', 'RAW', $rawSql, $bindings];
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy[] = [$column, $direction];
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    public function get(): array
    {
        $sql = $this->buildSelectQuery();
        $bindings = $this->getBindings();

        try {
            $stmt = Database::getInstance()->prepare($sql);
            $stmt->execute($bindings);
            
            $results = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $instance = new $this->modelClass($row);
                $instance->exists = true;
                $instance->original = $row;
                $results[] = $instance;
            }
            
            return $results;
        } catch (PDOException $e) {
            error_log("QueryBuilder get error: " . $e->getMessage());
            return [];
        }
    }

    public function first(): ?Model
    {
        $results = $this->limit(1)->get();
        return $results[0] ?? null;
    }

    private function buildSelectQuery(): string
    {
        $table = $this->modelClass::getTable();
        $sql = "SELECT * FROM {$table}";

        if (!empty($this->wheres)) {
            $whereClauses = [];
            $isFirst = true;
            
            foreach ($this->wheres as $where) {
                $connector = $isFirst ? '' : " {$where[0]} ";
                
                if ($where[1] === 'RAW') {
                    // Raw SQL condition
                    $whereClauses[] = "{$connector}({$where[2]})";
                } else {
                    // Regular condition
                    $column = $where[1];
                    $operator = $where[2];
                    $whereClauses[] = "{$connector}{$column} {$operator} ?";
                }
                $isFirst = false;
            }
            $sql .= " WHERE " . implode('', $whereClauses);
        }

        if (!empty($this->orderBy)) {
            $orderClauses = [];
            foreach ($this->orderBy as $order) {
                $orderClauses[] = "{$order[0]} {$order[1]}";
            }
            $sql .= " ORDER BY " . implode(', ', $orderClauses);
        }

        if ($this->limit) {
            $sql .= " LIMIT {$this->limit}";
        }

        if ($this->offset) {
            $sql .= " OFFSET {$this->offset}";
        }

        return $sql;
    }

    private function getBindings(): array
    {
        $bindings = [];
        foreach ($this->wheres as $where) {
            if ($where[1] === 'RAW') {
                // Raw SQL bindings are arrays
                if (is_array($where[3])) {
                    $bindings = array_merge($bindings, $where[3]);
                }
            } else {
                // Regular bindings
                $bindings[] = $where[3]; // Value is at index 3: [connector, column, operator, value]
            }
        }
        return $bindings;
    }
}