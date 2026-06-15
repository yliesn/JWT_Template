<?php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/jwt.php';
require_once __DIR__ . '/../../config/helpers.php';

send_cors();

// Récupère le token depuis l'header Authorization
$token = get_bearer_token();
if (!$token) {
    json_response(['error' => 'Token manquant'], 401);
}

// Valide le token
$payload = jwt_verify($token);
if (!$payload) {
    json_response(['error' => 'Token invalide ou expiré'], 401);
}

// Récupère l'utilisateur depuis la base de données
$pdo  = getDB();
$stmt = $pdo->prepare('SELECT id, last_name, first_name, login, role, active, last_login_date FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$payload['sub']]);
$user = $stmt->fetch();

if (!$user) {
    json_response(['error' => 'Utilisateur introuvable'], 404);
}

json_response([
    'user' => [
        'id'               => $user['id'],
        'last_name'        => $user['last_name'],
        'first_name'       => $user['first_name'],
        'login'            => $user['login'],
        'role'             => $user['role'],
        'active'           => $user['active'],
        'last_login_date'  => $user['last_login_date'],
    ],
]);
