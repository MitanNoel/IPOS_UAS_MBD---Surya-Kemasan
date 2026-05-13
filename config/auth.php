<?php

function current_user() {
    return $_SESSION['auth_user'] ?? null;
}

function current_user_role() {
    $user = current_user();

    if (!$user) {
        return null;
    }

    return strtolower((string) ($user['role'] ?? ''));
}

function is_logged_in() {
    return current_user() !== null;
}

function login_user(array $user) {
    $_SESSION['auth_user'] = [
        'id' => $user['id'] ?? null,
        'username' => $user['username'] ?? null,
        'full_name' => $user['full_name'] ?? ($user['nama_lengkap'] ?? $user['username'] ?? 'Pengguna'),
        'role' => strtolower((string) ($user['role'] ?? 'admin')),
    ];
}

function logout_user() {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}

function require_auth(string $redirectTo) {
    if (!is_logged_in()) {
        header("Location: {$redirectTo}");
        exit();
    }

    return current_user();
}

function require_role($allowedRoles, string $redirectTo, string $forbiddenTo = '') {
    $user = require_auth($redirectTo);
    $allowedRoles = is_array($allowedRoles) ? $allowedRoles : [$allowedRoles];
    $allowedRoles = array_map('strtolower', $allowedRoles);
    $currentRole = current_user_role();

    if (!in_array($currentRole, $allowedRoles, true)) {
        if ($forbiddenTo !== '') {
            header("Location: {$forbiddenTo}");
        } else {
            header("Location: {$redirectTo}");
        }

        exit();
    }

    return $user;
}

function role_label($role) {
    $role = strtolower((string) $role);

    return match ($role) {
        'admin' => 'Admin',
        'cashier' => 'Kasir',
        default => ucfirst($role),
    };
}
