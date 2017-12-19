<?php
/**
 * Created by PhpStorm.
 * User: vladimir
 * Date: 19.12.2017
 * Time: 00:01
 */

// Used for deleting images

require_once('db.php');

try {
    $stmt = $pdo->prepare("SELECT name FROM images WHERE id=:id");
    $stmt->bindParam(':id', $_GET['id']);
    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $result = $stmt->fetch();
    if (empty($result)) {
        echo "Image with this ID not found <br>";
        echo "<a href='/images.php'>Back to the list </a>";
        die;
    }
    $filename = $result['name'];
    unlink(__DIR__ . '/uploads/original/' . $filename);
    unlink(__DIR__ . '/uploads/cropped/' . $filename);
    $stmt = $pdo->prepare("DELETE FROM images WHERE id=:id");
    $stmt->bindParam(':id', $_GET['id']);
    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);

    echo "The image <i>$filename</i> was deleted <br>";
    echo "<a href='/images.php'>Back to the list </a>";

} catch (\Exception $exception) {
    die($exception->getMessage());
}

