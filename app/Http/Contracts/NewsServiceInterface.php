<?php
namespace App\Http\Contracts;


interface NewsServiceInterface
{
    public function fetchArticles(string $category = null): array;
    public function getSource(): string;
}
