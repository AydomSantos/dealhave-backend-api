<?php
session_start();
require_once '../includes/db.php'; // Fixed path to db.php

// Inicializar variáveis de filtro
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$search_term = isset($_GET['search']) ? $_GET['search'] : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Construir a consulta SQL base
$sql = "SELECT o.*, u.name, u.latitude, u.longitude 
        FROM orders o 
        JOIN users u ON o.user_id = u.id
        WHERE 1=1";

$params = [];
$types = "";

// Adicionar filtro de categoria, se selecionado
if (!empty($category_filter)) {
    $sql .= " AND o.category = ?";
    $params[] = $category_filter;
    $types .= "s";
}

// Adicionar filtro de pesquisa, se fornecido
if (!empty($search_term)) {
    $sql .= " AND (o.title LIKE ? OR o.description LIKE ?)";
    $params[] = "%$search_term%";
    $params[] = "%$search_term%";
    $types .= "ss";
}

// Adicionar ordenação
switch ($sort_by) {
    case 'oldest':
        $sql .= " ORDER BY o.created_at ASC";
        break;
    case 'title_asc':
        $sql .= " ORDER BY o.title ASC";
        break;
    case 'title_desc':
        $sql .= " ORDER BY o.title DESC";
        break;
    default: // newest
        $sql .= " ORDER BY o.created_at DESC";
        break;
}

// Preparar e executar a consulta
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Obter todas as categorias para o filtro
$categories_query = "SELECT DISTINCT category FROM orders ORDER BY category";
$categories_result = $conn->query($categories_query);
$categories = [];

if ($categories_result->num_rows > 0) {
    while ($row = $categories_result->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}

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

// Removida a função calculateDistance() pois já está definida em includes/db.php
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explorar Pedidos - Economia Compartilhada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .order-card {
            transition: transform 0.3s;
            height: 100%;
        }
        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .category-badge {
            font-size: 0.8rem;
            padding: 0.4rem 0.6rem;
            margin-right: 0.3rem;
            margin-bottom: 0.3rem;
            border-radius: 50px;
        }
        .distance-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            background-color: rgba(0, 0, 0, 0.6);
            color: white;
        }
        .filters-card {
            position: sticky;
            top: 20px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container py-4">
        <h1 class="mb-4"><i class="bi bi-search"></i> Explorar Pedidos</h1>
        
        <div class="row">
            <!-- Filtros e Pesquisa -->
            <div class="col-lg-3 mb-4">
                <div class="card filters-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtros</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="">
                            <!-- Pesquisa -->
                            <div class="mb-3">
                                <label for="search" class="form-label">Pesquisar</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="search" name="search" 
                                           placeholder="Buscar pedidos..." value="<?php echo htmlspecialchars($search_term); ?>">
                                    <button class="btn btn-outline-primary" type="submit">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Categoria -->
                            <div class="mb-3">
                                <label for="category" class="form-label">Categoria</label>
                                <select name="category" id="category" class="form-select">
                                    <option value="">Todas as categorias</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat); ?>" 
                                                <?php echo ($category_filter === $cat) ? 'selected' : ''; ?>>
                                            <?php echo ucfirst(htmlspecialchars($cat)); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Ordenação -->
                            <div class="mb-3">
                                <label for="sort" class="form-label">Ordenar por</label>
                                <select name="sort" id="sort" class="form-select">
                                    <option value="newest" <?php echo ($sort_by === 'newest') ? 'selected' : ''; ?>>
                                        Mais recentes primeiro
                                    </option>
                                    <option value="oldest" <?php echo ($sort_by === 'oldest') ? 'selected' : ''; ?>>
                                        Mais antigos primeiro
                                    </option>
                                    <option value="title_asc" <?php echo ($sort_by === 'title_asc') ? 'selected' : ''; ?>>
                                        Título (A-Z)
                                    </option>
                                    <option value="title_desc" <?php echo ($sort_by === 'title_desc') ? 'selected' : ''; ?>>
                                        Título (Z-A)
                                    </option>
                                </select>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
                                <?php if (!empty($category_filter) || !empty($search_term) || $sort_by !== 'newest'): ?>
                                    <a href="explore_orders.php" class="btn btn-outline-secondary">Limpar Filtros</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Categorias populares -->
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-tags"></i> Categorias</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($categories as $cat): ?>
                            <a href="explore_orders.php?category=<?php echo urlencode($cat); ?>" 
                               class="badge bg-<?php echo ($category_filter === $cat) ? 'primary' : 'secondary'; ?> category-badge">
                                <?php echo ucfirst(htmlspecialchars($cat)); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Listagem de pedidos -->
            <div class="col-lg-9">
                <!-- Resultados da pesquisa -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <?php 
                                if (!empty($search_term)) {
                                    echo 'Resultados para: "' . htmlspecialchars($search_term) . '"';
                                } elseif (!empty($category_filter)) {
                                    echo 'Categoria: ' . ucfirst(htmlspecialchars($category_filter));
                                } else {
                                    echo 'Todos os pedidos';
                                }
                                ?>
                            </h5>
                            <span class="badge bg-primary"><?php echo count($orders); ?> pedido(s) encontrado(s)</span>
                        </div>
                    </div>
                </div>
                
                <?php if (empty($orders)): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Nenhum pedido encontrado com os filtros selecionados.
                    </div>
                <?php else: ?>
                    <div class="row row-cols-1 row-cols-md-2 g-4">
                        <?php foreach ($orders as $order): ?>
                            <?php
                            // Calcular a distância se o usuário estiver logado e tiver localização
                            $distance = null;
                            if ($user_lat && $user_lng && $order['latitude'] && $order['longitude']) {
                                $distance = calculateDistance($user_lat, $user_lng, $order['latitude'], $order['longitude']);
                            }
                            ?>
                            <div class="col">
                                <div class="card order-card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0"><?php echo htmlspecialchars($order['title']); ?></h5>
                                        <?php if ($distance !== null): ?>
                                            <span class="distance-badge">
                                                <i class="bi bi-geo-alt"></i> <?php echo number_format($distance, 1); ?> km
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text"><?php echo nl2br(htmlspecialchars(substr($order['description'], 0, 150))); ?>...</p>
                                        <div class="mb-3">
                                            <span class="badge bg-primary"><?php echo ucfirst(htmlspecialchars($order['category'])); ?></span>
                                        </div>
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bi bi-person-circle me-2 text-primary"></i>
                                            <span><?php echo htmlspecialchars($order['name']); ?></span>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-calendar me-2 text-muted"></i>
                                            <small class="text-muted">
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
                                    </div>
                                    <div class="card-footer">
                                        <a href="view_order.php?id=<?php echo $order['id']; ?>" class="btn btn-primary w-100">
                                            <i class="bi bi-eye"></i> Ver Detalhes
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>