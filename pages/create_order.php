<?php
session_start();
require_once '../includes/db.php'; // Fixed path to db.php

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php'); // Fixed redirect path
    exit;
}

$categories = [
    'ferramentas' => 'Ferramentas',
    'eletronicos' => 'Eletrônicos',
    'livros' => 'Livros',
    'roupas' => 'Roupas',
    'moveis' => 'Móveis',
    'outros' => 'Outros'
];

$message = '';

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = $_POST['category'];
    $user_id = $_SESSION['user_id'];
    
    // Validação básica
    if (empty($title) || empty($description) || empty($category)) {
        $message = '<div class="alert alert-danger">Todos os campos são obrigatórios.</div>';
    } else {
        // Inserir o pedido no banco de dados
        $stmt = $conn->prepare("INSERT INTO orders (user_id, title, description, category) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $title, $description, $category);
        
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Pedido criado com sucesso!</div>';
            // Limpar os campos do formulário
            $title = $description = '';
            $category = '';
        } else {
            $message = '<div class="alert alert-danger">Erro ao criar pedido: ' . $stmt->error . '</div>';
        }
        
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Pedido - Plataforma de Economia Compartilhada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0">Criar Novo Pedido</h2>
                    </div>
                    <div class="card-body">
                        <?php echo $message; ?>
                        
                        <form method="POST" action="">
                            <div class="form-group mb-3">
                                <label for="title" class="form-label">Título</label>
                                <input type="text" class="form-control" id="title" name="title" value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="description" class="form-label">Descrição</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="category" class="form-label">Categoria</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Selecione uma categoria</option>
                                    <?php foreach ($categories as $key => $value): ?>
                                        <option value="<?php echo $key; ?>" <?php echo (isset($category) && $category === $key) ? 'selected' : ''; ?>>
                                            <?php echo $value; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Campos ocultos para armazenar a localização -->
                            <input type="hidden" id="user-latitude" name="latitude">
                            <input type="hidden" id="user-longitude" name="longitude">
                            
                            <div class="form-group mb-3">
                                <button type="button" id="update-location-btn" class="btn btn-info">
                                    <i class="bi bi-geo-alt"></i> Atualizar Localização
                                </button>
                            </div>
                            
                            <div class="form-group d-flex justify-content-between">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Criar Pedido
                                </button>
                                <a href="home.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script para atualizar a localização do usuário
        document.getElementById('update-location-btn')?.addEventListener('click', function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;
                    
                    // Atualizar os campos ocultos
                    document.getElementById('user-latitude').value = latitude;
                    document.getElementById('user-longitude').value = longitude;
                    
                    alert('Localização atualizada com sucesso!');
                }, function() {
                    alert('Não foi possível obter sua localização. Verifique as permissões do navegador.');
                });
            } else {
                alert('Geolocalização não é suportada pelo seu navegador.');
            }
        });
    </script>
</body>
</html>