<?php
// export_books_portable.php
$pdo = new PDO('mysql:host=localhost;dbname=bhps', 'username', 'password');

// Get all books
$stmt = $pdo->query("SELECT * FROM book");
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Convert images to base64 for books that still use file paths
foreach ($books as &$book) {
    if (empty($book['image_data']) && !empty($book['ImagePath']) && file_exists($book['ImagePath'])) {
        $book['image_data'] = base64_encode(file_get_contents($book['ImagePath']));
        $imageInfo = getimagesize($book['ImagePath']);
        $book['image_type'] = $imageInfo['mime'];
    }
}

// Generate SQL file content
$sqlContent = "CREATE TABLE IF NOT EXISTS book (\n";
$sqlContent .= "    BookID VARCHAR(10) PRIMARY KEY,\n";
$sqlContent .= "    Name VARCHAR(255) NOT NULL,\n";
$sqlContent .= "    Author VARCHAR(255),\n";
$sqlContent .= "    Category VARCHAR(100),\n";
$sqlContent .= "    Format VARCHAR(50),\n";
$sqlContent .= "    Price DECIMAL(10,2),\n";
$sqlContent .= "    Quantity INT,\n";
$sqlContent .= "    ImagePath VARCHAR(500),\n";
$sqlContent .= "    image_data LONGTEXT,\n";
$sqlContent .= "    image_type VARCHAR(50),\n";
$sqlContent .= "    Description TEXT,\n";
$sqlContent .= "    is_promotion TINYINT DEFAULT 0,\n";
$sqlContent .= "    promotion_price DECIMAL(10,2)\n";
$sqlContent .= ");\n\n";

foreach ($books as $book) {
    $sqlContent .= "INSERT INTO book (BookID, Name, Author, Category, Format, Price, Quantity, ImagePath, image_data, image_type, Description, is_promotion, promotion_price) VALUES (\n";
    $sqlContent .= "    '" . addslashes($book['BookID']) . "',\n";
    $sqlContent .= "    '" . addslashes($book['Name']) . "',\n";
    $sqlContent .= "    '" . addslashes($book['Author']) . "',\n";
    $sqlContent .= "    '" . addslashes($book['Category']) . "',\n";
    $sqlContent .= "    '" . addslashes($book['Format']) . "',\n";
    $sqlContent .= "    " . $book['Price'] . ",\n";
    $sqlContent .= "    " . $book['Quantity'] . ",\n";
    $sqlContent .= "    '" . addslashes($book['ImagePath']) . "',\n";
    $sqlContent .= "    '" . addslashes($book['image_data']) . "',\n";
    $sqlContent .= "    '" . addslashes($book['image_type']) . "',\n";
    $sqlContent .= "    '" . addslashes($book['Description']) . "',\n";
    $sqlContent .= "    " . $book['is_promotion'] . ",\n";
    $sqlContent .= "    " . ($book['promotion_price'] ?: 'NULL') . "\n";
    $sqlContent .= ");\n\n";
}

// Save SQL file
file_put_contents('books_export_with_images.sql', $sqlContent);

echo "Portable SQL file created: books_export_with_images.sql<br>";
echo "File size: " . round(filesize('books_export_with_images.sql') / 1024 / 1024, 2) . " MB";
