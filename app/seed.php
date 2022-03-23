<?php

use Carbon\Carbon;
use Cocur\Slugify\Slugify;
use Ramsey\Uuid\Uuid;

require_once 'vendor/autoload.php';
$faker = Faker\Factory::create();
define('AUTHORS_COUNT', rand(2, 8));
define('CATEGORIES_COUNT', rand(2, 8));
define('POSTS_COUNT', rand(30, 50));

try {
    $pdo = new PDO('mysql:host=database;port=3306;dbname=blog', 'mysql', 'mysql', [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    var_dump($e);
    exit;
}

$slugify = new Slugify();

//////////////////////////////
/// AUTHORS
//////////////////////////////

echo '// Creating authors <br>';
$pdo->exec(<<<SQL
    TRUNCATE authors;
    SQL
);

for ($i = 0; $i < AUTHORS_COUNT; $i++) {
    $author_id = Uuid::uuid4();
    $author_name = strtolower($faker->name());
    $author_slug = $slugify->slugify($author_name);
    $author_avatar = $faker->imageUrl(128, 128, 'people', true, $author_name);
    $pdo->exec(<<<SQL
        INSERT INTO authors(id, name, slug, avatar, created_at, deleted_at, updated_at) 
        VALUES('$author_id', '$author_name', '$author_slug', '$author_avatar', now(), NULL, now())
    SQL
    );
}

//////////////////////////////
/// CATEGORIES
//////////////////////////////

echo '// Creating categories <br>';
$pdo->exec(<<<SQL
    SET FOREIGN_KEY_CHECKS = 0;
    TRUNCATE categories;
    SET FOREIGN_KEY_CHECKS = 1;
    SQL
);

for ($i = 0; $i < CATEGORIES_COUNT; $i++) {
    $category_id = Uuid::uuid4();
    $category_name = strtolower(substr($faker->sentence(2), 0, -1));
    $category_slug = $slugify->slugify($category_name);
    $pdo->exec(<<<SQL
        INSERT INTO categories(id, name, slug, created_at, deleted_at, updated_at) 
        VALUES('$category_id', '$category_name', '$category_slug', now(), NULL, now())
    SQL
    );
}

//////////////////////////////
/// POSTS
//////////////////////////////

echo '// Creating posts <br>';
$pdo->exec(<<<SQL
    SET FOREIGN_KEY_CHECKS = 0;
    TRUNCATE posts;
    SET FOREIGN_KEY_CHECKS = 1;
    SQL
);

for ($i = 0; $i < POSTS_COUNT; $i++) {
    $post_id = Uuid::uuid4();
    $creation_date = Carbon::create($faker->dateTimeBetween('-3 years', 'now')->format('Y-m-d H:i:s'));
    $post_created_at = $creation_date;
    $post_published_at = $creation_date->addDays(rand(0, 1) * rand(2, 20));
    $post_updated_at = rand(0, 10) ? $post_created_at : $post_created_at->addWeeks(rand(2, 8));
    $post_deleted_at = rand(0, 10) ? NULL : Carbon::now();
    $post_author_id = $pdo->query('SELECT id FROM authors ORDER BY rand() LIMIT 1', PDO::FETCH_COLUMN, 0)->fetch();
    $post_title = $faker->sentence(10);
    $post_slug = $slugify->slugify($post_title);
    $post_excerpt = $faker->sentence(40);
    $post_thumbnail = $faker->imageUrl(640, 480, 'landscape', true);
    $post_body = '<p>' . implode('</p><p>', $faker->paragraphs(12)) . '</p>';

    $pdo->exec(<<<SQL
        INSERT INTO posts(id, title, slug, excerpt, author_id, body, created_at, updated_at, published_at, thumbnail) 
        VALUES('$post_id', '$post_title', '$post_slug', '$post_excerpt', '$post_author_id', '$post_body', '$post_created_at', '$post_updated_at' ,'$post_published_at' , '$post_thumbnail');
    SQL
    );
}

//////////////////////////////
/// CATEGORY_POST
//////////////////////////////

echo '// Creating relationships between categories and posts <br>';
$pdo->exec(<<<SQL
    SET FOREIGN_KEY_CHECKS = 0;
    TRUNCATE category_post;
    SET FOREIGN_KEY_CHECKS = 1;
    SQL
);

$categories_ids = $pdo->query('SELECT id FROM categories')->fetchAll(PDO::FETCH_ASSOC);

for ($i = 0; $i < POSTS_COUNT; $i++) {
    $post_id = $pdo->query("SELECT id FROM posts LIMIT $i,1", PDO::FETCH_COLUMN, 0)->fetch();
    for ($j = 0; $j < CATEGORIES_COUNT; $j += rand(intdiv(CATEGORIES_COUNT, 2), CATEGORIES_COUNT)) {
        $category_id = $categories_ids[$j]['id'];
        $pdo->exec(<<<SQL
            INSERT INTO category_post(category_id, post_id) 
            VALUES('$category_id', '$post_id');
        SQL
        );
    }
}
echo '<a href="index.php">Back to website!</a>';