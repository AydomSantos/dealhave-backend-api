<?php

namespace App\Models;

use App\Database\Connection;
use PDO;


class User {
    private $vDb;

    // Construtor: inicializa a conexão com o banco de dados
    public function __construct() {
        $this->vDb = Connection::getDb();
    }

    // Busca todos os usuários no banco de dados
    public function getAll() {
        try {
            // Query para selecionar todos os usuários (campos específicos)
            $vQuery = "SELECT i_id_users, s_users_name, s_users_email, s_role_users, t_created_at_users FROM users";
            $vStmt = $this->vDb->prepare($vQuery);
            $vStmt->execute();
            return $vStmt->fetchAll(PDO::FETCH_ASSOC); // Retorna resultados como array associativo
        } catch (\PDOException $e) {
            throw $e; // Repassa a exceção para tratamento externo
        }
    }

    // Busca um usuário específico pelo ID
    public function getById($id) {
        try {
            // Query com JOIN para obter dados do usuário
            $vQuery = "SELECT i_id_users, s_users_name, s_users_email, s_role_users, t_created_at_users 
                       FROM users 
                       WHERE i_id_users = :id";
            $vStmt = $this->vDb->prepare($vQuery);
            $vStmt->bindParam(':id', $id, PDO::PARAM_INT); // Previne SQL Injection
            $vStmt->execute();
            return $vStmt->fetch(PDO::FETCH_ASSOC); // Retorna um único registro
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    // Cria um novo usuário no banco de dados
    public function create($data) {
        try {
            // Query de inserção com parâmetros nomeados
            $vQuery = "INSERT INTO users (s_users_name, s_users_email, s_users_password, s_role_users) 
                       VALUES (:name, :email, :password, :role)";
            $vStmt = $this->vDb->prepare($vQuery);
            
            // Vincula os parâmetros com os valores do formulário
            $vStmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $vStmt->bindParam(':email', $data['email'], PDO::PARAM_STR);
            $vStmt->bindParam(':password', $data['password'], PDO::PARAM_STR);
            $vStmt->bindParam(':role', $data['role'], PDO::PARAM_STR);
            
            $vStmt->execute();
            return $this->vDb->lastInsertId(); // Retorna o ID do novo usuário
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    // Atualiza os dados de um usuário existente
    public function update($id, $data) {
        try {
            // Query de atualização com cláusula WHERE para segurança
            $vQuery = "UPDATE users 
                       SET s_users_name = :name, 
                           s_users_email = :email, 
                           s_role_users = :role 
                       WHERE i_id_users = :id";
            $vStmt = $this->vDb->prepare($vQuery);
            
            // Vincula os novos valores e o ID do usuário
            $vStmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $vStmt->bindParam(':email', $data['email'], PDO::PARAM_STR);
            $vStmt->bindParam(':role', $data['role'], PDO::PARAM_STR);
            $vStmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $vStmt->execute(); // Retorna true/false conforme sucesso
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    // Exclui um usuário e todos seus relacionamentos de forma segura
    public function delete($id){
        try {
            // Inicia transação para garantir atomicidade das operações
            $this->vDb->beginTransaction();
            
            // 1. Exclui itens de pedido relacionados ao usuário
            $vQueryOrderItems = "DELETE order_items FROM order_items
                                INNER JOIN orders ON orders.i_id_orders = order_items.i_order_id_order_items
                                WHERE orders.i_user_id_orders = :id";
            $vStmtOrderItems = $this->vDb->prepare($vQueryOrderItems);
            $vStmtOrderItems->bindParam(':id', $id, PDO::PARAM_INT);
            $vStmtOrderItems->execute();
            
            // 2. Exclui os pedidos do usuário
            $vQueryOrders = "DELETE FROM orders WHERE i_user_id_orders = :id";
            $vStmtOrders = $this->vDb->prepare($vQueryOrders);
            $vStmtOrders->bindParam(':id', $id, PDO::PARAM_INT);
            $vStmtOrders->execute();
    
            // 3. Remove os favoritos do usuário
            $vQueryFavorites = "DELETE FROM favorites WHERE i_user_id_favorites = :id";
            $vStmtFavorites = $this->vDb->prepare($vQueryFavorites);
            $vStmtFavorites->bindParam(':id', $id, PDO::PARAM_INT);
            $vStmtFavorites->execute();
    
            // 4. Esvazia o carrinho de compras
            $vQueryCartItems = "DELETE cart_items FROM cart_items 
                               INNER JOIN cart ON cart.i_id_cart = cart_items.i_cart_id_cart_items 
                               WHERE cart.i_user_id_cart = :id";
            $vStmtCartItems = $this->vDb->prepare($vQueryCartItems);
            $vStmtCartItems->bindParam(':id', $id, PDO::PARAM_INT);
            $vStmtCartItems->execute();
            
            // 5. Exclui o carrinho do usuário
            $vQueryCart = "DELETE FROM cart WHERE i_user_id_cart = :id";
            $vStmtCart = $this->vDb->prepare($vQueryCart);
            $vStmtCart->bindParam(':id', $id, PDO::PARAM_INT);
            $vStmtCart->execute();
            
            // 6. Finalmente exclui o usuário
            $vQuery = "DELETE FROM users WHERE i_id_users = :id";
            $vStmt = $this->vDb->prepare($vQuery);
            $vStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $vStmt->execute();
            
            // Confirma todas as operações
            $this->vDb->commit();
            return true;
        } catch (\PDOException $e) {
            // Em caso de erro, desfaz todas as alterações
            $this->vDb->rollBack();
            throw $e;
        }
    }
}