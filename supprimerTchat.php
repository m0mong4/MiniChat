<?php
require_once 'connexion.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $req = $pdo->prepare('DELETE FROM messages WHERE id = ?');
        $req->execute([$id]);
    }
}

header('Location: index.php');
exit;
