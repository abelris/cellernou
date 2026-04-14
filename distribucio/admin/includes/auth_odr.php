<?php
/**
 * El Celler Nou - Sistema de Autenticación Seguro
 * Admin Panel Authentication Functions
 */

// Configuración de sesión segura
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', 7200); // 2 horas
ini_set('session.cookie_lifetime', 7200);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ruta base de archivos
define('ADMIN_PATH', dirname(__DIR__));
define('DISTRIBUCIO_PATH', dirname(ADMIN_PATH));

// Cargar configuración de URLs (debe ir antes de connexio por si necesita las rutas)
require_once __DIR__ . '/config.php';

// Cargar conexión a base de datos
require_once DISTRIBUCIO_PATH . '/admin/connexio/connexio.php';

// Configuración del admin
define('ADMIN_TITLE', 'El Celler Nou - Administració');
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900); // 15 minutos de bloqueo

/**
 * Verificar si el usuario está autenticado
 */
function isAuthenticated(): bool {
    return isset($_SESSION['admin_user_id']) &&
           isset($_SESSION['admin_user_name']) &&
           isset($_SESSION['admin_last_activity']) &&
           (time() - $_SESSION['admin_last_activity']) < 7200;
}

/**
 * Requerir autenticación - redirige si no está autenticado
 */
function requireAuth(): void {
    if (!isAuthenticated()) {
        $_SESSION = [];
        session_destroy();
        header('Location: ' . getAdminUrl('index.php?error=session_expired'));
        exit;
    }
    // Actualizar tiempo de actividad
    $_SESSION['admin_last_activity'] = time();
}

/**
 * Intentar login
 */
function attemptLogin(string $usuario, string $password): array {
    global $bd;

    // Verificar intentos de login
    if (isLockedOut()) {
        return ['success' => false, 'error' => 'Compte bloquejat. Intenta de nou en 15 minuts.'];
    }

    // Sanitizar entrada (igual que el sistema original)
    $usuarioSafe = mysqli_real_escape_string($bd, trim($usuario));
    $passwordSafe = mysqli_real_escape_string($bd, $password);

    // Buscar usuario con contraseña en texto plano (como el sistema original)
    $sql = "SELECT id, nombre, usuario FROM usuarios
            WHERE usuario = '$usuarioSafe' AND password = '$passwordSafe' LIMIT 1";
    $result = mysqli_query($bd, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // Login exitoso
        regenerateSession();
        $_SESSION['admin_user_id'] = $user['id'];
        $_SESSION['admin_user_name'] = $user['nombre'];
        $_SESSION['admin_usuario'] = $user['usuario'];
        $_SESSION['admin_last_activity'] = time();
        $_SESSION['admin_ip'] = $_SERVER['REMOTE_ADDR'];

        clearLoginAttempts();
        logActivity('login', 'Inicio de sesión exitoso');

        return ['success' => true];
    }

    recordLoginAttempt(false);
    return ['success' => false, 'error' => 'Usuari o contrasenya incorrectes.'];
}

/**
 * Actualizar contraseña a hash seguro
 */
function updatePasswordHash(int $userId, string $password): void {
    global $bd;
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $sql = "UPDATE usuarios SET password = ? WHERE id = ?";
    $stmt = mysqli_prepare($bd, $sql);
    mysqli_stmt_bind_param($stmt, 'si', $hash, $userId);
    mysqli_stmt_execute($stmt);
}

/**
 * Regenerar ID de sesión (prevención de session fixation)
 */
function regenerateSession(): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

/**
 * Cerrar sesión
 */
function logout(): void {
    logActivity('logout', 'Cierre de sesión');
    $_SESSION = [];
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    session_destroy();
}

/**
 * Verificar si está bloqueado por intentos fallidos
 */
function isLockedOut(): bool {
    if (!isset($_SESSION['login_attempts'])) {
        return false;
    }

    $attempts = $_SESSION['login_attempts'];
    if ($attempts['count'] >= MAX_LOGIN_ATTEMPTS) {
        if (time() - $attempts['last_attempt'] < LOGIN_TIMEOUT) {
            return true;
        }
        // Timeout pasado, resetear
        clearLoginAttempts();
    }
    return false;
}

/**
 * Registrar intento de login
 */
function recordLoginAttempt(bool $success): void {
    if ($success) {
        clearLoginAttempts();
        return;
    }

    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = ['count' => 0, 'last_attempt' => 0];
    }

    $_SESSION['login_attempts']['count']++;
    $_SESSION['login_attempts']['last_attempt'] = time();
}

/**
 * Limpiar intentos de login
 */
function clearLoginAttempts(): void {
    unset($_SESSION['login_attempts']);
}

/**
 * Registrar actividad
 */
function logActivity(string $action, string $details = ''): void {
    global $bd;

    // Verificar si existe la tabla de logs
    $tableCheck = mysqli_query($bd, "SHOW TABLES LIKE 'admin_logs'");
    if (mysqli_num_rows($tableCheck) == 0) {
        // Crear tabla de logs si no existe
        mysqli_query($bd, "CREATE TABLE IF NOT EXISTS admin_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            action VARCHAR(50),
            details TEXT,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_action (action),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    $userId = $_SESSION['admin_user_id'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    $sql = "INSERT INTO admin_logs (user_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($bd, $sql);
    mysqli_stmt_bind_param($stmt, 'issss', $userId, $action, $details, $ip, $userAgent);
    mysqli_stmt_execute($stmt);
}

/**
 * Generar token CSRF
 */
function generateCsrfToken(): string {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verificar token CSRF
 */
function verifyCsrfToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Nota: getAdminUrl() y getBaseUrl() están definidas en config.php

/**
 * Obtener información del usuario actual
 */
function getCurrentUser(): ?array {
    if (!isAuthenticated()) {
        return null;
    }
    return [
        'id' => $_SESSION['admin_user_id'],
        'nombre' => $_SESSION['admin_user_name'],
        'usuario' => $_SESSION['admin_usuario'] ?? ''
    ];
}

/**
 * Obtener rol del usuario actual
 */
function getUserRole(): string {
    return $_SESSION['admin_user_rol'] ?? 'admin';
}

/**
 * Requerir un rol específico
 */
function requireRole(string $role): void {
    if (getUserRole() !== $role && getUserRole() !== 'admin') {
        header('Location: ' . getAdminUrl('dashboard.php'));
        exit;
    }
}

/**
 * Verificar permisos (para futuro uso)
 */
function hasPermission(string $permission): bool {
    // Por ahora, todos los usuarios autenticados tienen todos los permisos
    // Se puede expandir con un sistema de roles/permisos
    return isAuthenticated();
}

/**
 * Escapar HTML
 */
function e(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}
