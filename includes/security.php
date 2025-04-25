
<?php
// Check if a session is already active before starting one
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Função para gerar token CSRF
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Função para validar token CSRF
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Função para limitar tentativas de login
function check_login_attempts($email) {
    if (!isset($_SESSION['login_attempts'][$email])) {
        $_SESSION['login_attempts'][$email] = ['count' => 0, 'time' => time()];
    }
    
    $attempts = &$_SESSION['login_attempts'][$email];
    
    // Reset após 30 minutos
    if (time() - $attempts['time'] > 1800) {
        $attempts['count'] = 0;
        $attempts['time'] = time();
        return true;
    }
    
    if ($attempts['count'] >= 5) {
        return false;
    }
    
    return true;
}

// Função para sanitizar inputs
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
