<?php
$host="";
$dbname="XXX";
$username="XXX";
$password="XXX";
try{
    $pdo= new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4",$username,$password);
} catch(PDOException $e){
    die("Erreur:" . $e->getMessage());
}
?>