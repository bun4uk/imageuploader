<?php
/**
 * Created by PhpStorm.
 * User: vladimir
 * Date: 18.12.2017
 * Time: 22:43
 */

require_once './vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;


$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('images_crop', false, false, false, false);

$callback = function ($msg) {

    try {


        $filename = 'uploads/original/' . $msg->body;
        list($width, $height) = getimagesize($filename);
        $newwidth = 100;
        $newheight = 100;
        $thumb = imagecreatetruecolor($newwidth, $newheight);
        $source = imageCreateFromFile($filename);
        imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
        imagepng($thumb, 'uploads/cropped/' . $msg->body);

        echo "[x] $msg->body was cropped\n";


        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);


    } catch (\Exception $e) {
        echo "[x] $msg->body was fail\n";
    }
};

$channel->basic_consume('images_crop', '', false, false, false, false, $callback);

while (count($channel->callbacks)) {
    $channel->wait();
}

function imageCreateFromFile($filename)
{
    if (!file_exists($filename)) {
        throw new InvalidArgumentException('File "' . $filename . '" not found.');
    }
    switch (strtolower(pathinfo($filename, PATHINFO_EXTENSION))) {
        case 'jpeg':
        case 'jpg':
            return imagecreatefromjpeg($filename);
            break;

        case 'png':
            return imagecreatefrompng($filename);
            break;

        case 'gif':
            return imagecreatefromgif($filename);
            break;

        default:
            throw new InvalidArgumentException('File "' . $filename . '" is not valid jpg, png or gif image.');
            break;
    }
}