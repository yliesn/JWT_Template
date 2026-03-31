<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

send_cors();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Méthode non autorisée'], 405);
}

$body   = json_decode(file_get_contents('php://input'), true);
$nom    = trim($body['nom']    ?? '');
$prenom = trim($body['prenom'] ?? '');
$login  = trim($body['login']  ?? '');
$mdp    = $body['mot_de_passe'] ?? '';
$role   = 'user'; // rôle par défaut, à changer en admin manuellement si besoin

if (!$nom || !$prenom || !$login || !$mdp) {
    json_response(['error' => 'Tous les champs sont obligatoires'], 400);
}

if (strlen($mdp) < 8) {
    json_response(['error' => 'Le mot de passe doit faire au moins 8 caractères'], 400);
}

$pdo = getDB();

// Vérifier si le login existe déjà
$stmt = $pdo->prepare('SELECT id FROM UTILISATEUR WHERE login = ? LIMIT 1');
$stmt->execute([$login]);
if ($stmt->fetch()) {
    json_response(['error' => 'Ce login est déjà utilisé'], 409);
}

$hash = password_hash($mdp, PASSWORD_BCRYPT);

$stmt = $pdo->prepare('INSERT INTO UTILISATEUR (nom, prenom, login, mot_de_passe, role, actif) VALUES (?, ?, ?, ?, ?, true)');
$stmt->execute([$nom, $prenom, $login, $hash, $role]);

json_response(['message' => 'Compte créé avec succès'], 201);