<?php
session_start();
require_once '../includes/db.php';

// Verifica se a variável de sessão user_id está definida. Se não estiver,
// significa que o usuário não está logado, então redireciona para a página de login.
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Se a execução chegar até aqui, significa que o usuário está logado.
// Podemos então exibir o conteúdo da página inicial.

// Obter informações do usuário da sessão
$user_id = $_SESSION['user_id'];
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';

// Obter informações adicionais do usuário do banco de dados
$user_query = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_stmt->close();

// Obter pedidos recentes
$recent_orders_query = "SELECT o.*, u.name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 3";
$recent_orders_result = $conn->query($recent_orders_query);
$recent_orders = [];
if ($recent_orders_result && $recent_orders_result->num_rows > 0) {
    while ($row = $recent_orders_result->fetch_assoc()) {
        $recent_orders[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Inicial - Economia Compartilhada</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .card-hover:hover {
            transform: translateY(-5px);
            transition: transform 0.3s ease;
        }
        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            padding: 0.75rem 1.25rem;
            border: 1px solid #ffeeba;
            border-radius: 0.25rem;
            margin-top: 1rem;
        }
        .btn-warning {
            background-color: #ffc107;
            color: #212529;
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 0.2rem;
            margin-left: 0.5rem;
            cursor: pointer;
            border: none;
        }
        .btn-warning:hover {
            background-color: #e0a800;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Navigation Bar -->
    <nav class="bg-blue-600 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <a href="home.php" class="flex-shrink-0 flex items-center text-white font-bold text-xl">
                        Economia Compartilhada
                    </a>
                </div>
                <div class="flex items-center">
                    <div class="hidden md:ml-6 md:flex md:space-x-8">
                        <a href="explore_orders.php" class="text-white hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium">
                            Explorar Pedidos
                        </a>
                        <a href="create_order.php" class="text-white hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium">
                            Criar Pedido
                        </a>
                        <a href="chat.php" class="text-white hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium">
                            Chat
                        </a>
                        <a href="logout.php" class="text-white hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="bi bi-box-arrow-right mr-1"></i>Sair
                        </a>
                    </div>
                    <div class="ml-3 relative">
                        <div class="relative">
                            <a href="profile.php" class="flex text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-white">
                                <img class="h-8 w-8 rounded-full" src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_name ?: $user['name']); ?>&background=random" alt="Avatar">
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 flex-grow">
        <!-- Welcome Section -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-900">
                Bem-vindo, <?php echo htmlspecialchars($user_name ?: $user['name']); ?>!
            </h2>
            <p class="mt-3 text-gray-600">
                Explore nossa plataforma de economia compartilhada e descubra novas oportunidades.
            </p>
            <?php if (!$user['latitude'] || !$user['longitude']): ?>
                <div class="alert-warning mt-4">
                    <i class="bi bi-exclamation-triangle-fill"></i> Sua localização não está configurada. 
                    <button id="update-location-alert-btn" class="btn-warning">Atualizar Localização</button>
                </div>
            <?php endif; ?>
        </div>

        <!-- Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
            <!-- Explore Card -->
            <div class="bg-white overflow-hidden shadow rounded-lg card-hover transition-all">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 rounded-full p-3">
                            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Explorar Pedidos
                                </dt>
                                <dd>
                                    <div class="text-lg font-medium text-gray-900">
                                        Explorar
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4">
                    <div class="text-sm">
                        <a href="explore_orders.php" class="font-medium text-blue-600 hover:text-blue-900 flex items-center">
                            Ver todos
                            <svg class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Create Card -->
            <div class="bg-white overflow-hidden shadow rounded-lg card-hover transition-all">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-full p-3">
                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Criar Pedido
                                </dt>
                                <dd>
                                    <div class="text-lg font-medium text-gray-900">
                                        Criar
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4">
                    <div class="text-sm">
                        <a href="create_order.php" class="font-medium text-green-600 hover:text-green-900 flex items-center">
                            Criar
                            <svg class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Profile Card -->
            <div class="bg-white overflow-hidden shadow rounded-lg card-hover transition-all">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-indigo-100 rounded-full p-3">
                            <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Meu Perfil
                                </dt>
                                <dd>
                                    <div class="text-lg font-medium text-gray-900">
                                        Perfil
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4">
                    <div class="text-sm">
                        <a href="profile.php" class="font-medium text-indigo-600 hover:text-indigo-900 flex items-center">
                            Ver
                            <svg class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <h5 class="mb-4 text-xl font-bold text-gray-900">Pedidos Recentes</h5>
            <div class="space-y-3">
                <?php if (empty($recent_orders)): ?>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <p class="text-gray-500">Nenhum pedido recente encontrado.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recent_orders as $order): ?>
                        <a href="view_order.php?id=<?php echo $order['id']; ?>" class="block p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex justify-between items-start">
                                <h6 class="font-semibold text-gray-900"><?php echo htmlspecialchars($order['title']); ?></h6>
                                <small class="text-gray-500 ml-2">
                                    <?php 
                                    $created_at = new DateTime($order['created_at']);
                                    $now = new DateTime();
                                    $interval = $created_at->diff($now);
                                    
                                    if ($interval->d > 0) {
                                        echo $interval->d . ' dia(s) atrás';
                                    } elseif ($interval->h > 0) {
                                        echo $interval->h . ' hora(s) atrás';
                                    } else {
                                        echo $interval->i . ' minuto(s) atrás';
                                    }
                                    ?>
                                </small>
                            </div>
                            <p class="mt-2 text-gray-600"><?php echo htmlspecialchars(substr($order['description'], 0, 100)) . '...'; ?></p>
                            <div class="mt-2 flex items-center justify-between">
                                <small class="text-gray-500">Por: <?php echo htmlspecialchars($order['name']); ?></small>
                                <span class="inline-block bg-blue-500 text-white rounded-full px-2.5 py-0.5 text-xs font-semibold"><?php echo htmlspecialchars($order['category']); ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h5 class="text-lg font-bold mb-4">Economia Compartilhada</h5>
                    <p class="text-gray-300">Uma plataforma para conectar pessoas e promover o consumo consciente.</p>
                </div>
                <div>
                    <h5 class="text-lg font-bold mb-4">Links</h5>
                    <ul class="space-y-2">
                        <li><a href="about.php" class="text-gray-300 hover:text-white transition-colors">Sobre nós</a></li>
                        <li><a href="terms.php" class="text-gray-300 hover:text-white transition-colors">Termos de uso</a></li>
                        <li><a href="privacy.php" class="text-gray-300 hover:text-white transition-colors">Política de privacidade</a></li>
                    </ul>
                </div>
                <div>
                    <h5 class="text-lg font-bold mb-4">Contato</h5>
                    <ul class="space-y-2">
                        <li class="flex items-center"><i class="bi bi-envelope mr-2"></i> contato@economiacompartilhada.com</li>
                        <li class="flex items-center"><i class="bi bi-telephone mr-2"></i> (11) 1234-5678</li>
                    </ul>
                </div>
            </div>
            <hr class="my-6 border-gray-700">
            <div class="text-center text-gray-400">
                <p>&copy; 2023 Economia Compartilhada. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Hidden fields for location -->
    <input type="hidden" id="user-latitude" name="latitude" value="<?php echo $user['latitude']; ?>">
    <input type="hidden" id="user-longitude" name="longitude" value="<?php echo $user['longitude']; ?>">

    <script>
        // Script para atualizar a localização do usuário
        document.getElementById('update-location-alert-btn')?.addEventListener('click', updateLocation);

        function updateLocation(e) {
            if (e) e.preventDefault();
            
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
        }
    </script>
</body>
</html>