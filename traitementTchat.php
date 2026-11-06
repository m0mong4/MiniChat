<?php
session_start();
require_once'connexion.php';


$_SESSION['pseudo']=strip_tags($_POST['pseudo']); 
	
$req = $pdo->prepare('INSERT INTO messages (pseudo, date_message, message) VALUES(?,NOW(),?)');
$req->execute(array($_SESSION['pseudo'], strip_tags($_POST['message']))); 

header('Location: index.php');
?>