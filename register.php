<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/security.php'; // For security functions

// Verificar se o usuário já está logado
if (isset($_SESSION['user_id'])) {
    header("Location: pages/home.php");
    exit();
}

$error = '';
$success = '';

// Processar o formulário de registro quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) { // CSRF token verification
        $error = 'Token de segurança inválido.';
    } else {
        // Sanitizar e validar os dados de entrada
        $name = sanitize_input($_POST['name']);
        $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
        $password = sanitize_input($_POST['password']);
        $confirm_password = sanitize_input($_POST['confirm_password']);

        // Validações básicas
        if (empty($name) || strlen($name) < 3) {
            $error = 'Nome deve ter pelo menos 3 caracteres.';
        } elseif (!$email) {
            $error = 'E-mail inválido.';
        } elseif (empty($password) || strlen($password) < 6) {
            $error = 'Senha deve ter pelo menos 6 caracteres.';
        } elseif ($password !== $confirm_password) {
            $error = 'As senhas não coincidem.';
        } else {
            // Verificar se o e-mail já está em uso
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $error = 'Este e-mail já está em uso.';
            } else {
                // Hash da senha
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Inserir o novo usuário no banco de dados
                $insert_stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                $insert_stmt->bind_param("sss", $name, $email, $hashed_password);
                
                if ($insert_stmt->execute()) {
                    $success = 'Registro realizado com sucesso! Você já pode fazer login.';
                } else {
                    $error = 'Erro ao registrar: ' . $conn->error;
                }
                
                $insert_stmt->close();
            }
            
            $check_stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Plataforma de Economia Compartilhada</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
            <img src="assets/img/logo.png" alt="Logo" class="mx-auto w-20 mb-4">
            <h2 class="text-2xl font-bold text-center mb-4">Economia Compartilhada</h2>
            <p class="text-center text-gray-600 mb-6">Crie sua conta</p>
            
            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Erro:</strong> <span class="block sm:inline"><?php echo $error; ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Sucesso:</strong> <span class="block sm:inline"><?php echo $success; ?></span>
                    <p class="mt-2">
                        <a href="login.php" class="text-green-700 font-bold underline">Clique aqui para fazer login</a>
                    </p>
                </div>
            <?php else: ?>
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2" for="name">Nome Completo</label>
                        <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="name" name="name" placeholder="Seu nome completo" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2" for="email">E-mail</label>
                        <input type="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="email" name="email" placeholder="seu@email.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2" for="password">Senha</label>
                        <input type="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="password" name="password" placeholder="Mínimo 6 caracteres" required>
                        <p class="text-gray-500 text-xs mt-1">A senha deve ter pelo menos 6 caracteres.</p>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-gray-700 font-bold mb-2" for="confirm_password">Confirmar Senha</label>
                        <input type="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="confirm_password" name="confirm_password" placeholder="Confirme sua senha" required>
                    </div>
                    
                    <div class="mb-6">
                        <input type="checkbox" class="mr-2" id="terms" name="terms" required>
                        <label for="terms" class="text-gray-700">Concordo com os <a href="terms.php" class="text-blue-500 hover:underline">Termos de Uso</a> e <a href="privacy.php" class="text-blue-500 hover:underline">Política de Privacidade</a></label>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                            <i class="bi bi-person-plus"></i> Registrar
                        </button>
                    </div>
                </form>
            <?php endif; ?>
            
            <div class="text-center mt-4">
                <p class="text-gray-600">Já tem uma conta? <a href="login.php" class="text-blue-500 hover:underline">Faça login</a></p>
            </div>
        </div>
        <div class="text-center mt-4 text-gray-600">
            <p>&copy; 2023 Plataforma de Economia Compartilhada</p>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validação do formulário no lado do cliente
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('As senhas não coincidem!');
            }
        });
    </script>
</body>
</html>