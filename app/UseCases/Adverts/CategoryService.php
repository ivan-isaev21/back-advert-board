<?php

namespace App\UseCases\Adverts;

use App\Models\Adverts\Category;
use \Kalnoy\Nestedset\Collection;

class CategoryService
{
    protected function getInstance()
    {
        return Category::defaultOrder();
    }

    /**
     * Method getCategoriesTree
     *
     * @return \Kalnoy\Nestedset\Collection
     */
    public function getCategoriesTree(): Collection
    {
        return $this->getInstance()->withDepth()->get()->toTree();
    }

    /**
     * Method getCategoryTree
     *
     * @param Category $category [explicite description]
     *
     * @return App\Models\Adverts\Category
     */
    public function getCategoryTree(Category $category): Category
    {
        return Category::withDepth()->with(['descendants'])->descendantsAndSelf($category->id)->toTree()->first();
    }
}
