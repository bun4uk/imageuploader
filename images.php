<?php
/**
 * Created by PhpStorm.
 * User: vladimir
 * Date: 18.12.2017
 * Time: 23:24
 */

require_once('db.php');

echo "<a href='/'>Homepage</a><br>";

if (!empty($_POST)) {
    try {
        $imageId = $_POST['id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $stmt = $pdo->prepare('UPDATE images SET title=(:title), description=(:description) WHERE id = (:id)');
        $stmt->bindParam(':id', $imageId);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->execute();
    } catch (\Exception $e) {
        die($e->getMessage());
    }
}

if (!empty($_GET['id'])) {
    echo "<a href='/images.php'>Images List</a><br>";
    try {
        $stmt = $pdo->prepare("SELECT * FROM images WHERE id=:id");
        $stmt->bindParam(':id', $_GET['id']);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetch();

        if (empty($result)) {
            echo "Image with this ID not found";
            die;
        }
        ?>
        <img src="<?= '../uploads/original/' . $result['name'] ?>" alt="<?= $result['name'] ?>" width="500"><br>

        <form action="../images.php/?id=<?= $result['id'] ?>" method="post">
            <br>
            <input type="hidden" name="id" value="<?= $result['id'] ?>">
            <label for="title">Title</label>
            <input type="text" name="title" id="title" required value="<?= $result['title'] ?>">
            <label for="description">Description</label>
            <textarea type="text" name="description" id="description" required><?= $result['description'] ?></textarea>
            <input type="submit" name="">
        </form>
        <a href="../delete.php/?id=<?= $result['id'] ?>">Remove the image</a>

        <?php
    } catch (\Exception $e) {
        die($e->getMessage());
    }
}
else {

?>

<div>
    <?php
    try {
        $sql = "SELECT * FROM images";
        foreach ($pdo->query($sql) as $row) {
            ?>
            <div style="float: left; margin: 25px">
                <a href="/images.php/?id=<?= $row['id'] ?>">
                    <img src="<?= '../uploads/cropped/' . $row['name'] ?>" alt="<?= $row['description'] ?>"
                         style="float: left;">
                    <?php
                    echo '<b>Filename: </b>' . $row['name'] . '<br>';
                    echo '<b>Upload date: </b>' . $row['upload_date'] . '<br>';
                    echo '<b>Title: </b>' . $row['title'] . '<br>';
                    echo '<b>Description: </b>' . $row['description'] . '<br>';
                    ?>
                </a>
            </div>
            <?php
        }
    } catch (\Exception $e) {
        die($e->getMessage());
    }
    }
    ?>
</div>