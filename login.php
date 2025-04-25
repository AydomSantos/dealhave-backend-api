<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/security.php'; // Added for security functions

// Verificar se o usuário já está logado
if (isset($_SESSION['user_id'])) {
    header("Location: pages/home.php");
    exit();
}

$error = '';

// Processar o formulário de login quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) { // CSRF token verification
        $error = 'Token de segurança inválido.';
        exit;
    }

    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = sanitize_input($_POST['password']);

    if (!check_login_attempts($email)) { // Login attempt limiting
        $error = 'Muitas tentativas. Tente novamente em 30 minutos.';
        exit;
    }

    // Verificar as credenciais no banco de dados
    $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verificar a senha
        if (password_verify($password, $user['password'])) {
            // Login bem-sucedido, configurar a sessão
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];

            // Redirecionar para a página inicial
            header("Location: pages/home.php");
            exit();
        } else {
            $error = 'Senha incorreta.';
            increment_login_attempts($email); // Increment failed attempts
        }
    } else {
        $error = 'E-mail não encontrado.';
        increment_login_attempts($email); // Increment failed attempts
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Plataforma de Economia Compartilhada</title>
    <script src="https://cdn.tailwindcss.com"></script>  <!-- Added Tailwind CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
            <img src="assets/img/logo.png" alt="Logo" class="mx-auto w-20 mb-4">
            <h2 class="text-2xl font-bold text-center mb-4">Economia Compartilhada</h2>
            <p class="text-center text-gray-600 mb-6">Acesse sua conta</p>
            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Erro:</strong> <span class="block sm:inline"><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">  <!-- Added CSRF token input -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="email">E-mail</label>
                    <input type="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="email" name="email" placeholder="seu@email.com" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="password">Senha</label>
                    <input type="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="password" name="password" placeholder="Sua senha" required>
                </div>
                <div class="mb-4">
                    <input type="checkbox" class="mr-2" id="remember">
                    <label for="remember" class="text-gray-700">Lembrar de mim</label>
                </div>
                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        <i class="bi bi-box-arrow-in-right"></i> Entrar
                    </button>
                </div>
            </form>

            <div class="text-center mt-4">
                <a href="forgot_password.php" class="text-blue-500 hover:underline">Esqueceu sua senha?</a>
            </div>
            <div class="text-center mt-4">
                <p class="text-gray-600">Não tem uma conta? <a href="register.php" class="text-blue-500 hover:underline">Registre-se</a></p>
            </div>
        </div>
        <div class="text-center mt-4 text-gray-600">
            <p>&copy; 2023 Plataforma de Economia Compartilhada</p>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>