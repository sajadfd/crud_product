<?php

namespace App\Services;

class ProductServices
{
    public  function getCategoryIdsFromPath($path)
    {
        $categoryIds = explode('.', $path);
        $categoryCount = count($categoryIds);
        $parentCategoryId = null;
        $categoryId = $categoryIds[$categoryCount - 1];
        if ($categoryCount > 1) $parentCategoryId = $categoryIds[$categoryCount - 2];
        return ['parent_category_id' => $parentCategoryId, 'category_id' => $categoryId,];
    }
}
