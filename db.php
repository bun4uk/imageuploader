<?php
/**
 * Created by PhpStorm.
 * User: vladimir
 * Date: 19.12.2017
 * Time: 00:07
 */

$pdo = new PDO('mysql:host=192.168.10.10:3306;dbname=images', 'homestead', 'secret');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);