<?php
session_start();
require_once '../includes/db.php'; // Changed from 'includes/db.php' to '../includes/db.php'

// Verificar se o ID do pedido foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$order_id = $_GET['id'];

// Obter os detalhes do pedido
$stmt = $conn->prepare("SELECT o.*, u.name, u.email, u.latitude, u.longitude 
                        FROM orders o 
                        JOIN users u ON o.user_id = u.id 
                        WHERE o.id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php');
    exit;
}

$order = $result->fetch_assoc();
$stmt->close();

// Obter a localização do usuário atual (se estiver logado)
$user_lat = $user_lng = null;
if (isset($_SESSION['user_id'])) {
    $user_query = "SELECT latitude, longitude FROM users WHERE id = ?";
    $user_stmt = $conn->prepare($user_query);
    $user_stmt->bind_param("i", $_SESSION['user_id']);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    
    if ($user_row = $user_result->fetch_assoc()) {
        $user_lat = $user_row['latitude'];
        $user_lng = $user_row['longitude'];
    }
    $user_stmt->close();
}

// Calcular a distância se o usuário estiver logado e tiver localização
$distance = null;
if ($user_lat && $user_lng && $order['latitude'] && $order['longitude']) {
    $distance = calculateDistance($user_lat, $user_lng, $order['latitude'], $order['longitude']);
}

// Verificar se as coordenadas do pedido estão disponíveis para o mapa
$has_map = ($order['latitude'] && $order['longitude']);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($order['title']); ?> - Plataforma de Economia Compartilhada</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <?php if ($has_map): ?>
    <style>
        #map {
            height: 400px;
            width: 100%;
            margin-bottom: 20px;
            border-radius: 5px;
        }
    </style>
    <?php endif; ?>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0"><?php echo htmlspecialchars($order['title']); ?></h2>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h5>Descrição:</h5>
                            <p><?php echo nl2br(htmlspecialchars($order['description'])); ?></p>
                        </div>
                        
                        <?php if ($has_map): ?>
                        <div class="mb-4">
                            <h5>Localização:</h5>
                            <div id="map"></div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>Detalhes:</h5>
                                <ul class="list-group">
                                    <li class="list-group-item"><strong>Categoria:</strong> <?php echo ucfirst(htmlspecialchars($order['category'])); ?></li>
                                    <li class="list-group-item"><strong>Data de Criação:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></li>
                                    <?php if ($distance !== null): ?>
                                        <li class="list-group-item"><strong>Distância:</strong> <?php echo number_format($distance, 1); ?> km</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            
                            <div class="col-md-6">
                                <h5>Solicitante:</h5>
                                <ul class="list-group">
                                    <li class="list-group-item"><strong>Nome:</strong> <?php echo htmlspecialchars($order['name']); ?></li>
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <li class="list-group-item"><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                        
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $order['user_id']): ?>
                            <div class="text-center">
                                <a href="chat.php?user=<?php echo $order['user_id']; ?>" class="btn btn-success">Entrar em Contato</a>
                            </div>
                        <?php elseif (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $order['user_id']): ?>
                            <div class="text-center">
                                <a href="edit_order.php?id=<?php echo $order_id; ?>" class="btn btn-warning">Editar Pedido</a>
                                <a href="delete_order.php?id=<?php echo $order_id; ?>" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir este pedido?')">Excluir Pedido</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <a href="index.php" class="btn btn-secondary">Voltar para a Lista</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <?php if ($has_map): ?>
    <!-- Google Maps API -->
    <script>
        function initMap() {
            // Coordenadas do pedido
            const orderLocation = {
                lat: <?php echo $order['latitude']; ?>,
                lng: <?php echo $order['longitude']; ?>
            };
            
            // Criar o mapa centralizado na localização do pedido
            const map = new google.maps.Map(document.getElementById("map"), {
                zoom: 13,
                center: orderLocation,
            });
            
            // Marcador para a localização do pedido
            const orderMarker = new google.maps.Marker({
                position: orderLocation,
                map: map,
                title: "<?php echo htmlspecialchars($order['title']); ?>",
                icon: {
                    url: "http://maps.google.com/mapfiles/ms/icons/red-dot.png"
                }
            });
            
            // Adicionar janela de informação ao marcador do pedido
            const orderInfoWindow = new google.maps.InfoWindow({
                content: "<strong><?php echo htmlspecialchars($order['title']); ?></strong><br>Solicitado por: <?php echo htmlspecialchars($order['name']); ?>"
            });
            
            orderMarker.addListener("click", () => {
                orderInfoWindow.open(map, orderMarker);
            });
            
            <?php if ($user_lat && $user_lng && $_SESSION['user_id'] != $order['user_id']): ?>
            // Adicionar marcador para a localização do usuário atual
            const userLocation = {
                lat: <?php echo $user_lat; ?>,
                lng: <?php echo $user_lng; ?>
            };
            
            const userMarker = new google.maps.Marker({
                position: userLocation,
                map: map,
                title: "Sua localização",
                icon: {
                    url: "http://maps.google.com/mapfiles/ms/icons/blue-dot.png"
                }
            });
            
            const userInfoWindow = new google.maps.InfoWindow({
                content: "<strong>Sua localização</strong>"
            });
            
            userMarker.addListener("click", () => {
                userInfoWindow.open(map, userMarker);
            });
            
            // Desenhar linha entre os dois pontos
            const path = new google.maps.Polyline({
                path: [orderLocation, userLocation],
                geodesic: true,
                strokeColor: "#FF0000",
                strokeOpacity: 1.0,
                strokeWeight: 2,
            });
            
            path.setMap(map);
            
            // Ajustar o zoom para mostrar ambos os marcadores
            const bounds = new google.maps.LatLngBounds();
            bounds.extend(orderLocation);
            bounds.extend(userLocation);
            map.fitBounds(bounds);
            <?php endif; ?>
        }
    </script>
    <script async defer
        src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap">
    </script>
    <?php endif; ?>
</body>
</html>