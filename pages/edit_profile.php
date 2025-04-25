<?php
session_start();
require_once '../includes/db.php'; // Fixed path to db.php

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

// Buscar informações atuais do usuário
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $bio = isset($_POST['bio']) ? trim($_POST['bio']) : '';
    
    // Validação básica
    if (empty($name) || empty($email)) {
        $message = '<div class="alert alert-danger">Nome e e-mail são obrigatórios.</div>';
    } else {
        // Verificar se o e-mail já está em uso por outro usuário
        $check_email = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check_email->bind_param("si", $email, $user_id);
        $check_email->execute();
        $email_result = $check_email->get_result();
        
        if ($email_result->num_rows > 0) {
            $message = '<div class="alert alert-danger">Este e-mail já está em uso por outro usuário.</div>';
        } else {
            // Atualizar informações do usuário
            $update = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, bio = ? WHERE id = ?");
            $update->bind_param("ssssi", $name, $email, $phone, $bio, $user_id);
            
            if ($update->execute()) {
                $message = '<div class="alert alert-success">Perfil atualizado com sucesso!</div>';
                
                // Atualizar os dados da sessão
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                
                // Recarregar informações do usuário
                $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                $stmt->close();
            } else {
                $message = '<div class="alert alert-danger">Erro ao atualizar perfil: ' . $conn->error . '</div>';
            }
            
            $update->close();
        }
        
        $check_email->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - Plataforma de Economia Compartilhada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container py-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0"><i class="bi bi-person-gear"></i> Editar Perfil</h2>
                    </div>
                    <div class="card-body">
                        <?php echo $message; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nome Completo</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Telefone</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                <div class="form-text">Opcional. Formato recomendado: (XX) XXXXX-XXXX</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="bio" class="form-label">Sobre Mim</label>
                                <textarea class="form-control" id="bio" name="bio" rows="4"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                                <div class="form-text">Opcional. Uma breve descrição sobre você.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Localização</label>
                                <?php if ($user['latitude'] && $user['longitude']): ?>
                                    <div class="alert alert-success">
                                        <i class="bi bi-geo-alt-fill"></i> Localização configurada
                                        <p class="mb-0 mt-2">
                                            <small>Latitude: <?php echo $user['latitude']; ?></small><br>
                                            <small>Longitude: <?php echo $user['longitude']; ?></small>
                                        </p>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle"></i> Localização não configurada
                                    </div>
                                <?php endif; ?>
                                
                                <button type="button" id="update-location-btn" class="btn btn-outline-primary">
                                    <i class="bi bi-geo-alt"></i> Atualizar Localização
                                </button>
                                <input type="hidden" id="latitude" name="latitude" value="<?php echo $user['latitude'] ?? ''; ?>">
                                <input type="hidden" id="longitude" name="longitude" value="<?php echo $user['longitude'] ?? ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Membro desde</label>
                                <p class="form-control-static">
                                    <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                                </p>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Salvar Alterações
                                </button>
                                <a href="pages/profile.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Voltar ao Perfil
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-secondary text-white">
                        <h3 class="mb-0"><i class="bi bi-key"></i> Segurança</h3>
                    </div>
                    <div class="card-body">
                        <p>Para alterar sua senha ou gerenciar configurações de segurança:</p>
                        <a href="change_password.php" class="btn btn-outline-secondary">
                            <i class="bi bi-lock"></i> Alterar Senha
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script para atualizar a localização do usuário
        document.getElementById('update-location-btn')?.addEventListener('click', function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;
                    
                    // Atualizar os campos ocultos
                    document.getElementById('latitude').value = latitude;
                    document.getElementById('longitude').value = longitude;
                    
                    // Enviar para o servidor via AJAX
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'update_location.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onload = function() {
                        if (this.status === 200) {
                            alert('Localização atualizada com sucesso!');
                            location.reload();
                        } else {
                            alert('Erro ao atualizar localização.');
                        }
                    };
                    xhr.send(`latitude=${latitude}&longitude=${longitude}`);
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