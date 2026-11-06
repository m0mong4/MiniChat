<?php
require_once 'connexion.php';
session_start();

if (!isset($_SESSION['pseudo'])) {
    $_SESSION['pseudo'] = '';
}


$choixLimites = [5, 10, 20, 50];
if (isset($_GET['limit']) && in_array((int)$_GET['limit'], $choixLimites, true)) {
    $_SESSION['limit'] = (int)$_GET['limit'];
}
$limit = $_SESSION['limit'] ?? 5;


$action  = null;
$cible   = null;
$idCible = 0;

if (isset($_GET['edit'])) {
    $action = 'edit';
    $idCible = (int)$_GET['edit'];
} elseif (isset($_GET['delete'])) {
    $action = 'delete';
    $idCible = (int)$_GET['delete'];
}

if ($action) {
    $r = $pdo->prepare("SELECT * FROM messages WHERE id = ?");
    $r->execute([$idCible]);
    $cible = $r->fetch(PDO::FETCH_ASSOC);
    if (!$cible) {
        $action = null;
    }
}

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function dateFr($d) { return date('d/m/Y à H:i', strtotime($d)); }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MiniChat</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<form id="formulaire" action="traitementTchat.php" method="post">
    <table id="champs">
        <tr class="lignes">
            <td><label for="pseudo">Pseudo (25 caractères max) :</label></td>
            <td><input type="text" name="pseudo" id="pseudo" value="<?= h($_SESSION['pseudo']) ?>" maxlength="25" onClick="this.value=''" required /></td>
        </tr>
        <tr class="lignes">
            <td><label for="message">Message (255 caractères max) :</label></td>
            <td><input type="text" name="message" id="message" maxlength="255" required /></td>
        </tr>
        <tr class="lignes" id="bouton">
            <td colspan="2"><input type="submit" value="Envoyer" /></td>
        </tr>
    </table>
</form>


<aside id="soleil">


    <section class="bloc">
        <h3>Commentaires affichés</h3>
        <form method="get" action="index.php" class="choix-nombre">
            <label for="limit" class="sr-only">Nombre de commentaires</label>
            <select name="limit" id="limit" onchange="this.form.submit()">
                <?php foreach ($choixLimites as $n): ?>
                    <option value="<?= $n ?>" <?= $n === $limit ? 'selected' : '' ?>><?= $n ?> derniers</option>
                <?php endforeach; ?>
            </select>
            <noscript><button type="submit" class="btn">OK</button></noscript>
        </form>
    </section>

    <?php if ($action === 'edit'): ?>
        
    <section class="bloc bloc-actif">
        <h3>🖉  Modifier le commentaire</h3>
        <p class="aide">de <strong><?= h($cible['pseudo']) ?></strong></p>
        <form method="post" action="modifierTchat.php">
            <input type="hidden" name="id" value="<?= (int)$cible['id'] ?>">
            <label for="nouveau-message" class="sr-only">Nouveau message</label>
            <textarea name="message" id="nouveau-message" maxlength="255" rows="4" required><?= h($cible['message']) ?></textarea>
            <div class="boutons">
                <button type="submit" class="btn">Enregistrer</button>
                <a href="index.php" class="btn btn-sec">Annuler</a>
            </div>
        </form>
    </section>
    <?php elseif ($action === 'delete'): ?>
        
    <section class="bloc bloc-actif bloc-danger">
        <h3>🗑 Supprimer ce commentaire ?</h3>
        <p class="apercu">« <?= h($cible['message']) ?> »<br><small>— <?= h($cible['pseudo']) ?></small></p>
        <p class="aide">Cette action est définitive.</p>
        <form method="post" action="supprimerTchat.php">
            <input type="hidden" name="id" value="<?= (int)$cible['id'] ?>">
            <div class="boutons">
                <button type="submit" class="btn btn-danger">Oui, supprimer</button>
                <a href="index.php" class="btn btn-sec">Annuler</a>
            </div>
        </form>
    </section>
    <?php else: ?>
    <p class="astuce">Utilise 🖉  ou 🗑 sur un message pour le modifier ou le supprimer.</p>
    <?php endif; ?>

</aside>

<div id="messages">
    <h2>Les <?= $limit ?> derniers messages</h2>
<?php
$requete = $pdo->prepare("SELECT * FROM messages ORDER BY date_message DESC, id DESC LIMIT :n");
$requete->bindValue(':n', $limit, PDO::PARAM_INT);
$requete->execute();

foreach ($requete->fetchAll(PDO::FETCH_ASSOC) as $m):
    $surligne = ($cible && (int)$m['id'] === (int)$cible['id']) ? ' bulle-active' : '';
?>
    <div class="bulle<?= $surligne ?>" id="m<?= (int)$m['id'] ?>">
        <div class="texte"><strong><?= h($m['pseudo']) ?></strong> – <?= h($m['message']) ?></div>
        <div class="meta">
            <span>Créé le <?= dateFr($m['date_message']) ?></span>
            <?php if (!empty($m['date_modification'])): ?>
                <span class="modif">Modifié le <?= dateFr($m['date_modification']) ?></span>
            <?php endif; ?>
        </div>
        <div class="actions">
            <a href="index.php?edit=<?= (int)$m['id'] ?>#m<?= (int)$m['id'] ?>" title="Modifier" aria-label="Modifier ce commentaire">🖉 </a>
            <a href="index.php?delete=<?= (int)$m['id'] ?>#m<?= (int)$m['id'] ?>" title="Supprimer" aria-label="Supprimer ce commentaire">🗑</a>
        </div>
    </div>
<?php endforeach; ?>
</div>

</body>
</html>
