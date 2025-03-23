<?php

namespace App\Models; 

use App\Database\Connection;
use PDO;

class Product {
    private $vDb;

    public function __construct() {
        $this->vDb = Connection::getDb();
    }

    public function getAll() {
        $vQuery = 'SELECT * FROM products';
        $vSmt = $this->vDb->prepare($vQuery); 
        $vSmt->execute();
        $results = $vSmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Debugging: Log the results
        error_log(print_r($results, true));
        
        return $results;
    }

    public function getById($pId) {
        $vQuery = 'SELECT * FROM products WHERE i_id_products = :id';
        $vSmt = $this->vDb->prepare($vQuery); // Use prepare instead of query
        $vSmt->bindParam(':id', $pId, PDO::PARAM_INT);
        $vSmt->execute();
        return $vSmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($pData){
        $vQuery = "INSERT INTO products (
        s_name_products, 
        s_description_products, 
        d_price_products, 
        d_rating_products, 
        i_category_id_products, 
        i_sales_count_products, 
        i_seller_id_products, 
        i_stock_products, 
        s_delivery_time_products, 
        s_image_url_products, 
        t_created_at_products
        ) 
        VALUES (:name, :description, :price, :rating, :category_id, :sales_count, :seller_id, :stock, :delivery_time, :image_url, :created_at)";
        $vSmt = $this->vDb->prepare($vQuery);
        $vSmt->bindParam(':name', $pData['name'], PDO::PARAM_STR);
        $vSmt->bindParam(':description', $pData['description'], PDO::PARAM_STR);
        $vSmt->bindParam(':price', $pData['price'], PDO::PARAM_STR);
        $vSmt->bindParam(':rating', $pData['rating'], PDO::PARAM_STR);
        $vSmt->bindParam(':category_id', $pData['category_id'], PDO::PARAM_STR);
        $vSmt->bindParam(':sales_count', $pData['sales_count'], PDO::PARAM_STR);
        $vSmt->bindParam(':seller_id', $pData['seller_id'], PDO::PARAM_STR);
        $vSmt->bindParam(':stock', $pData['stock'], PDO::PARAM_STR);
        $vSmt->bindParam(':delivery_time', $pData['delivery_time'], PDO::PARAM_STR);
        $vSmt->bindParam(':image_url', $pData['image_url'], PDO::PARAM_STR);
        $vSmt->bindParam(':created_at', $pData['created_at'], PDO::PARAM_STR);
        $vSmt->execute();
        return $this->vDb->lastInsertId();
    }

    public function update($pId, $pData){
       $vQuery = "UPDATE products SET s_name_products = :name, 
       s_description_products = :description, 
       d_price_products = :price, 
       d_rating_products = :rating, 
       i_category_id_products = :category_id, 
       i_sales_count_products = :sales_count, 
       i_seller_id_products = :seller_id, i_stock_products = :stock, 
       s_delivery_time_products = :delivery_time, s_image_url_products = :image_url WHERE i_id_products = :id" ;

       $vSmt = $this->vDb->prepare($vQuery);
       $vSmt->bindParam(':name', $pData['name'], PDO::PARAM_STR);
       $vSmt->bindParam(':description', $pData['description'], PDO::PARAM_STR);
       $vSmt->bindParam(':price', $pData['price'], PDO::PARAM_STR);
       $vSmt->bindParam(':rating', $pData['rating'], PDO::PARAM_STR);
       $vSmt->bindParam(':category_id', $pData['category_id'], PDO::PARAM_STR);
       $vSmt->bindParam(':sales_count', $pData['sales_count'], PDO::PARAM_STR);
       $vSmt->bindParam(':seller_id', $pData['seller_id'], PDO::PARAM_STR);
       $vSmt->bindParam(':stock', $pData['stock'], PDO::PARAM_STR);
       $vSmt->bindParam(':delivery_time', $pData['delivery_time'], PDO::PARAM_STR);
       $vSmt->bindParam(':image_url', $pData['image_url'], PDO::PARAM_STR);
       $vSmt->bindParam(':id', $pId, PDO::PARAM_INT);
       return $vSmt->execute();
    }

    public function delete($pId){
        $vQuery = "DELETE FROM products WHERE i_id_products = :id";
        $vSmt = $this->vDb->prepare($vQuery);
        $vSmt->bindParam(':id', $pId, PDO::PARAM_INT);
        return $vSmt->execute();
    }
}