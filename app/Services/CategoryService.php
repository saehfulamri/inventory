<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    public function __construct(protected CategoryRepositoryInterface $repository) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    public function findById(int $id): ?Category
    {
        return $this->repository->findById($id);
    }

    public function findActive(): Collection
    {
        return $this->repository->findActive();
    }

    public function create(array $data): Category
    {
        return $this->repository->create($data);
    }

    public function update(Category $category, array $data): Category
    {
        return $this->repository->update($category, $data);
    }

    public function deactivate(Category $category): Category
    {
        if (! $category->is_active) {
            return $category;
        }

        return $this->repository->update($category, ['is_active' => false]);
    }
}
