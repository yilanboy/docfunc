<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// Find a user with some posts
$user = User::has('posts')->first();
if (!$user) {
    echo "No users with posts found.\n";
    die();
}

$startMemory = memory_get_usage();
$startTime = microtime(true);
DB::enableQueryLog();

$categories = Category::with([
    'posts' => function ($query) use ($user) {
        $query->where('user_id', $user->id);
    },
])->get();

$count = 0;
foreach ($categories as $category) {
    $count += $category->posts->count();
}

$endMemory = memory_get_usage();
$endTime = microtime(true);

echo "--- BEFORE OPTIMIZATION ---\n";
echo "Queries: " . count(DB::getQueryLog()) . "\n";
echo "Memory Used: " . number_format($endMemory - $startMemory) . " bytes\n";
echo "Time: " . number_format(($endTime - $startTime) * 1000, 2) . " ms\n";
echo "Total posts count sum: " . $count . "\n";

DB::flushQueryLog();

$startMemory2 = memory_get_usage();
$startTime2 = microtime(true);

$categories2 = Category::withCount([
    'posts' => function ($query) use ($user) {
        $query->where('user_id', $user->id);
    },
])->get();

$count2 = 0;
foreach ($categories2 as $category) {
    $count2 += $category->posts_count;
}

$endMemory2 = memory_get_usage();
$endTime2 = microtime(true);

echo "\n--- AFTER OPTIMIZATION ---\n";
echo "Queries: " . count(DB::getQueryLog()) . "\n";
echo "Memory Used: " . number_format($endMemory2 - $startMemory2) . " bytes\n";
echo "Time: " . number_format(($endTime2 - $startTime2) * 1000, 2) . " ms\n";
echo "Total posts count sum: " . $count2 . "\n";
