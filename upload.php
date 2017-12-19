<?php
/**
 * Created by PhpStorm.
 * User: vladimir
 * Date: 18.12.2017
 * Time: 21:54
 */

// Used for images upload

require_once './vendor/autoload.php';
require_once('db.php');

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

try {
    $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
    $channel = $connection->channel();
    $channel->queue_declare('images_crop', false, false, false, false);
} catch (\Exception $exception) {
    die('Something went wrong with RabbitMQ');
}

try {
    //creating directories
    if (!file_exists('uploads')) {
        mkdir('uploads', 0777, true);
        mkdir('uploads/original', 0777, true);
        mkdir('uploads/cropped', 0777, true);
    }

    //set max count of uploaded files
    $maxFilesCount = 5;
    $currentFilesCount = count($_FILES['images']['name']);

    if ($currentFilesCount > $maxFilesCount) {
        $currentFilesCount = $maxFilesCount;
        echo('Max files count is 5 at a time! <br>');
    }

    for ($i = 0; $i < $currentFilesCount; $i++) {
        if (is_image($_FILES['images']['name'][$i])) {
            //upload and stored images
            $target_dir = "uploads/original/";
            $saltedFilename = time() . '_' . $_FILES['images']['name'][$i];
            $target_file = $target_dir . $saltedFilename;
            if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $target_file)) {
                $stmt = $pdo->prepare("INSERT INTO images (name) VALUES (:name)");
                $stmt->bindParam(':name', $saltedFilename);
                $stmt->execute();

                $msg = new AMQPMessage($saltedFilename);
                $channel->basic_publish($msg, '', 'images_crop');
                echo (sprintf('File <i>%s</i> was successfully uploaded', $saltedFilename)) . '<br>';
            }
        } else {
            echo 'File ' . $_FILES['images']['name'][$i] . ' is not an image!';
        }
    }
} catch (\Exception $exception) {
    die('Something went wrong');
}

$channel->close();
$connection->close();

// check if the file is an image
function is_image($filename)
{
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if (in_array($ext, ['gif', 'jpg', 'jpeg', 'png', 'jpe'])) {
        return true;
    }

    return false;
}