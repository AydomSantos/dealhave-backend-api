<?php
session_start();
require_once '../includes/db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Obter informações do usuário
$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_stmt->close();

// Obter pedidos do usuário
$orders_query = "SELECT * FROM requests WHERE user_id = ? ORDER BY created_at DESC";
$orders_stmt = $conn->prepare($orders_query);
$orders_stmt->bind_param("i", $user_id);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();
$orders = [];
while ($order = $orders_result->fetch_assoc()) {
    $orders[] = $order;
}
$orders_stmt->close();

// Verificar se a tabela responses existe
$responses = [];
$table_check_query = "SHOW TABLES LIKE 'responses'";
$table_exists = $conn->query($table_check_query)->num_rows > 0;

if ($table_exists) {
    // Obter respostas aos pedidos do usuário
    $responses_query = "SELECT r.*, o.title as order_title, u.name as responder_name 
                        FROM responses r 
                        JOIN requests o ON r.order_id = o.id 
                        JOIN users u ON r.responder_id = u.id 
                        WHERE o.user_id = ? 
                        ORDER BY r.created_at DESC";
    $responses_stmt = $conn->prepare($responses_query);
    $responses_stmt->bind_param("i", $user_id);
    $responses_stmt->execute();
    $responses_result = $responses_stmt->get_result();
    
    while ($response = $responses_result->fetch_assoc()) {
        $responses[] = $response;
    }
    $responses_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Economia Compartilhada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .profile-header {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: #6c757d;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            margin-right: 20px;
        }
        .nav-pills .nav-link.active {
            background-color: #0d6efd;
        }
        .order-card {
            transition: transform 0.3s;
            margin-bottom: 15px;
        }
        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container py-4">
        <!-- Profile Header -->
        <div class="profile-header shadow-sm">
            <div class="d-flex align-items-center">
                <div class="profile-avatar">
                    <i class="bi bi-person"></i>
                </div>
                <div>
                    <h1 class="mb-1"><?php echo htmlspecialchars($user['name']); ?></h1>
                    <p class="text-muted mb-2">
                        <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($user['email']); ?>
                    </p>
                    <p class="text-muted mb-2">
                        <i class="bi bi-geo-alt"></i> 
                        <?php 
                        if ($user['latitude'] && $user['longitude']) {
                            echo 'Localização configurada';
                        } else {
                            echo 'Localização não configurada';
                        }
                        ?>
                    </p>
                    <a href="../edit_profile.php" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-pencil"></i> Editar Perfil
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Profile Content -->
        <div class="row">
            <div class="col-md-3">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Menu</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="nav flex-column nav-pills">
                            <a class="nav-link active" id="v-pills-orders-tab" data-bs-toggle="pill" href="#v-pills-orders">
                                <i class="bi bi-list-check"></i> Meus Pedidos
                            </a>
                            <a class="nav-link" id="v-pills-responses-tab" data-bs-toggle="pill" href="#v-pills-responses">
                                <i class="bi bi-chat-dots"></i> Respostas Recebidas
                            </a>
                            <a class="nav-link" id="v-pills-settings-tab" data-bs-toggle="pill" href="#v-pills-settings">
                                <i class="bi bi-gear"></i> Configurações
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Estatísticas</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Pedidos criados:</span>
                            <span class="badge bg-primary"><?php echo count($orders); ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Respostas recebidas:</span>
                            <span class="badge bg-success"><?php echo count($responses); ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Membro desde:</span>
                            <span class="badge bg-secondary">
                                <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Reputação:</span>
                            <div>
                                <?php if ($user['rating'] > 0): ?>
                                    <span class="badge bg-primary">
                                        <?php echo number_format($user['rating'], 1); ?> ⭐
                                    </span>
                                    <?php if ($user['rating'] >= 4): ?>
                                        <span class="badge bg-success">Colaborador Top</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Sem avaliações</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="tab-content">
                    <!-- Meus Pedidos -->
                    <div class="tab-pane fade show active" id="v-pills-orders">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3>Meus Pedidos</h3>
                            <a href="create_order.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Novo Pedido
                            </a>
                        </div>
                        
                        <?php if (empty($orders)): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Você ainda não criou nenhum pedido.
                            </div>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <div class="card order-card shadow-sm">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0"><?php echo htmlspecialchars($order['title']); ?></h5>
                                        <span class="badge bg-primary"><?php echo ucfirst(htmlspecialchars($order['category'])); ?></span>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text"><?php echo nl2br(htmlspecialchars(substr($order['description'], 0, 150))); ?>...</p>
                                        <div class="d-flex align-items-center text-muted">
                                            <i class="bi bi-calendar me-2"></i>
                                            <small>Criado em: <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></small>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <div class="d-flex justify-content-between">
                                            <a href="../view_order.php?id=<?php echo $order['id']; ?>" class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-eye"></i> Ver Detalhes
                                            </a>
                                            <div>
                                                <a href="../edit_order.php?id=<?php echo $order['id']; ?>" class="btn btn-outline-secondary btn-sm">
                                                    <i class="bi bi-pencil"></i> Editar
                                                </a>
                                                <button type="button" class="btn btn-outline-danger btn-sm" 
                                                        onclick="confirmDelete(<?php echo $order['id']; ?>)">
                                                    <i class="bi bi-trash"></i> Excluir
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Respostas Recebidas -->
                    <div class="tab-pane fade" id="v-pills-responses">
                        <h3 class="mb-3">Respostas Recebidas</h3>
                        
                        <?php if (empty($responses)): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Você ainda não recebeu nenhuma resposta aos seus pedidos.
                            </div>
                        <?php else: ?>
                            <?php foreach ($responses as $response): ?>
                                <div class="card mb-3 shadow-sm">
                                    <div class="card-header bg-light">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">Resposta ao pedido: <?php echo htmlspecialchars($response['order_title']); ?></h5>
                                            <small class="text-muted">
                                                <?php echo date('d/m/Y H:i', strtotime($response['created_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-person-circle me-2 text-primary"></i>
                                            <span><?php echo htmlspecialchars($response['responder_name']); ?></span>
                                        </div>
                                        <p class="card-text"><?php echo nl2br(htmlspecialchars($response['message'])); ?></p>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <a href="../view_order.php?id=<?php echo $response['order_id']; ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye"></i> Ver Pedido
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Configurações -->
                    <div class="tab-pane fade" id="v-pills-settings">
                        <h3 class="mb-3">Configurações</h3>
                        
                        <div class="card shadow-sm">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Localização</h5>
                            </div>
                            <div class="card-body">
                                <p>Sua localização atual:</p>
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
                                
                                <button id="update-location-btn" class="btn btn-primary">
                                    <i class="bi bi-geo-alt"></i> Atualizar Localização
                                </button>
                            </div>
                        </div>
                        
                        <div class="card shadow-sm mt-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Conta</h5>
                            </div>
                            <div class="card-body">
                                <a href="../edit_profile.php" class="btn btn-outline-primary mb-2">
                                    <i class="bi bi-pencil"></i> Editar Perfil
                                </a>
                                <a href="../change_password.php" class="btn btn-outline-secondary mb-2">
                                    <i class="bi bi-key"></i> Alterar Senha
                                </a>
                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                                    <i class="bi bi-trash"></i> Excluir Conta
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de confirmação de exclusão de pedido -->
    <div class="modal fade" id="deleteOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir este pedido? Esta ação não pode ser desfeita.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Excluir</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de confirmação de exclusão de conta -->
    <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Exclusão de Conta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> Atenção!
                    </div>
                    <p>Tem certeza que deseja excluir sua conta? Esta ação irá:</p>
                    <ul>
                        <li>Remover todos os seus dados pessoais</li>
                        <li>Excluir todos os seus pedidos</li>
                        <li>Remover todas as suas respostas</li>
                    </ul>
                    <p><strong>Esta ação não pode ser desfeita!</strong></p>
                    
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Digite sua senha para confirmar:</label>
                        <input type="password" class="form-control" id="confirmPassword" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="confirmDeleteAccountBtn" class="btn btn-danger">Excluir Minha Conta</button>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Função para confirmar exclusão de pedido
        function confirmDelete(orderId) {
            const modal = new bootstrap.Modal(document.getElementById('deleteOrderModal'));
            document.getElementById('confirmDeleteBtn').href = '../delete_order.php?id=' + orderId;
            modal.show();
        }
        
        // Script para atualizar a localização do usuário
        document.getElementById('update-location-btn')?.addEventListener('click', function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;
                    
                    // Enviar para o servidor via AJAX
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', '../update_location.php', true);
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
        
        // Confirmar exclusão de conta
        document.getElementById('confirmDeleteAccountBtn')?.addEventListener('click', function() {
            const password = document.getElementById('confirmPassword').value;
            if (!password) {
                alert('Por favor, digite sua senha para confirmar a exclusão da conta.');
                return;
            }
            
            // Enviar para o servidor via AJAX
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '../delete_account.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (this.status === 200) {
                    if (this.responseText === 'success') {
                        alert('Sua conta foi excluída com sucesso.');
                        window.location.href = '../index.php';
                    } else {
                        alert('Senha incorreta. Por favor, tente novamente.');
                    }
                } else {
                    alert('Erro ao excluir conta. Por favor, tente novamente mais tarde.');
                }
            };
            xhr.send(`password=${encodeURIComponent(password)}`);
        });
    </script>
</body>
</html>