<?php
require_once 'connexion.php';

$id      = (int)($_POST['id'] ?? 0);
$message = trim(strip_tags($_POST['message'] ?? ''));

if ($id > 0 && $message !== '') {
    
    $req = $pdo->prepare('UPDATE messages SET message = ?, date_modification = NOW() WHERE id = ?');
    $req->execute([mb_substr($message, 0, 255), $id]);
}

header('Location: index.php' . ($id > 0 ? '#m' . $id : ''));
exit;
