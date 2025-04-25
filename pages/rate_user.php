
<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $from_user_id = $_SESSION['user_id'];
    $to_user_id = $_POST['to_user_id'];
    $score = intval($_POST['score']);
    $comment = trim($_POST['comment']);

    if ($score >= 1 && $score <= 5) {
        $stmt = $conn->prepare("INSERT INTO ratings (from_user_id, to_user_id, score, comment) 
                               VALUES (?, ?, ?, ?) 
                               ON DUPLICATE KEY UPDATE score = ?, comment = ?");
        $stmt->bind_param("iiisss", $from_user_id, $to_user_id, $score, $comment, $score, $comment);
        
        if ($stmt->execute()) {
            // Atualizar rating médio do usuário
            $avg_query = "UPDATE users u 
                         SET rating = (SELECT AVG(score) FROM ratings WHERE to_user_id = u.id) 
                         WHERE id = ?";
            $avg_stmt = $conn->prepare($avg_query);
            $avg_stmt->bind_param("i", $to_user_id);
            $avg_stmt->execute();
            
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Erro ao salvar avaliação']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Pontuação inválida']);
    }
    exit;
}

$user_id = $_GET['user_id'] ?? 0;
$user_query = "SELECT name, rating FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliar Usuário - Economia Compartilhada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .star-rating {
            font-size: 24px;
            cursor: pointer;
        }
        .star-rating .bi-star-fill {
            color: #ffd700;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Avaliar <?php echo htmlspecialchars($user['name']); ?></h4>
                        <?php if ($user['rating'] > 0): ?>
                            <div class="current-rating">
                                Reputação atual: <?php echo number_format($user['rating'], 1); ?> 
                                <?php if ($user['rating'] >= 4): ?>
                                    <span class="badge bg-success">⭐ Colaborador Top</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <form id="ratingForm">
                            <input type="hidden" name="to_user_id" value="<?php echo $user_id; ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Sua avaliação:</label>
                                <div class="star-rating" id="starRating">
                                    <i class="bi bi-star" data-rating="1"></i>
                                    <i class="bi bi-star" data-rating="2"></i>
                                    <i class="bi bi-star" data-rating="3"></i>
                                    <i class="bi bi-star" data-rating="4"></i>
                                    <i class="bi bi-star" data-rating="5"></i>
                                </div>
                                <input type="hidden" name="score" id="ratingInput" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="comment" class="form-label">Comentário:</label>
                                <textarea class="form-control" name="comment" id="comment" rows="3"></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Enviar Avaliação</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.star-rating i').forEach(star => {
            star.addEventListener('mouseover', function() {
                const rating = this.dataset.rating;
                updateStars(rating);
            });
            
            star.addEventListener('click', function() {
                const rating = this.dataset.rating;
                document.getElementById('ratingInput').value = rating;
                updateStars(rating, true);
            });
        });

        document.querySelector('.star-rating').addEventListener('mouseout', function() {
            const currentRating = document.getElementById('ratingInput').value;
            updateStars(currentRating);
        });

        function updateStars(rating, permanent = false) {
            document.querySelectorAll('.star-rating i').forEach(star => {
                const starRating = star.dataset.rating;
                star.classList.remove('bi-star', 'bi-star-fill');
                star.classList.add(starRating <= rating ? 'bi-star-fill' : 'bi-star');
            });
            if (permanent) {
                document.getElementById('ratingInput').value = rating;
            }
        }

        document.getElementById('ratingForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('rate_user.php', {
                method: 'POST',
                body: new URLSearchParams(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Avaliação enviada com sucesso!');
                    window.location.reload();
                } else {
                    alert(data.error || 'Erro ao enviar avaliação');
                }
            });
        });
    </script>
</body>
</html>
