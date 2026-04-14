<?php
/**
 * El Celler Nou - Admin Login
 */
require_once __DIR__ . '/includes/auth.php';

// Si ya está autenticado, redirigir según rol
if (isAuthenticated()) {
    if (getUserRole() === 'gestoria') {
        header('Location: ' . getAdminUrl('modules/gestoria/'));
    } else {
        header('Location: ' . getAdminUrl('dashboard.php'));
    }
    exit;
}

$error = '';
$success = '';

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!verifyCsrfToken($csrfToken)) {
        $error = 'Error de seguretat. Torna a intentar-ho.';
    } elseif (empty($usuario) || empty($password)) {
        $error = 'Tots els camps són obligatoris.';
    } else {
        $result = attemptLogin($usuario, $password);
        if ($result['success']) {
            if (getUserRole() === 'gestoria') {
                header('Location: ' . getAdminUrl('modules/gestoria/'));
            } else {
                header('Location: ' . getAdminUrl('dashboard.php'));
            }
            exit;
        } else {
            $error = $result['error'];
        }
    }
}

// Mensajes de error por query string
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'session_expired':
            $error = 'La sessió ha expirat. Torna a iniciar sessió.';
            break;
        case 'unauthorized':
            $error = 'Accés no autoritzat.';
            break;
    }
}

if (isset($_GET['logout'])) {
    $success = 'Has tancat la sessió correctament.';
}

$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Login - El Celler Nou Administració</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #722F37;
            --primary-dark: #5a252c;
            --secondary: #C9A962;
            --dark: #1a1a2e;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, var(--dark) 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
        }

        .login-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 2.5rem 2rem;
            text-align: center;
        }

        .login-logo {
            font-size: 1.75rem;
            font-weight: 700;
            color: white;
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.875rem;
        }

        .login-body {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }

        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            font-size: 1rem;
            border: 2px solid var(--gray-200);
            border-radius: 8px;
            transition: all 0.2s;
            outline: none;
        }

        .form-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(114, 47, 55, 0.1);
        }

        .form-input::placeholder {
            color: var(--gray-400);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 0.875rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            color: white;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(114, 47, 55, 0.4);
        }

        .btn:active {
            transform: translateY(0);
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert svg {
            flex-shrink: 0;
            width: 20px;
            height: 20px;
        }

        .login-footer {
            text-align: center;
            padding: 1.5rem 2rem;
            background: var(--gray-50);
            border-top: 1px solid var(--gray-100);
        }

        .login-footer a {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.875rem;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .login-header {
                padding: 2rem 1.5rem;
            }
            .login-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">El Celler Nou</div>
                <div class="login-subtitle">Panell d'Administració</div>
            </div>

            <div class="login-body">
                <?php if ($error): ?>
                <div class="alert alert-error">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span><?php echo e($error); ?></span>
                </div>
                <?php endif; ?>

                <?php if ($success): ?>
                <div class="alert alert-success">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span><?php echo e($success); ?></span>
                </div>
                <?php endif; ?>

                <form method="POST" action="" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                    <div class="form-group">
                        <label class="form-label" for="usuario">Usuari</label>
                        <input type="text" id="usuario" name="usuario" class="form-input"
                               placeholder="El teu usuari" required autofocus>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Contrasenya</label>
                        <input type="password" id="password" name="password" class="form-input"
                               placeholder="La teva contrasenya" required>
                    </div>

                    <button type="submit" class="btn">
                        Iniciar Sessió
                    </button>
                </form>
            </div>

            <div class="login-footer">
                <a href="<?php echo dirname(BASE_PATH); ?>/">Tornar a la web</a>
            </div>
        </div>
    </div>
</body>
</html>
