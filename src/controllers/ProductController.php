<?php

nomespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Product;

class ProductController{
    private $vProductModel;

    // Constructor: initializes the Product model
    public function __construct(){
        $this->vProductModel = new Product();
    }

    // Retrieves all products in JSON format
    public function getAll(Request $pRequest, Response $pResponse, $pArgs){
        $vProducts = $this->vProductModel->getAll();
        $pResponse->getBody()->write(json_encode($vProducts));
        return $pResponse->withHeader('Content-Type', 'application/json');
    }

    public function getOne(Request $pRequest, Response $pResponse, array $pArgs){
        $vProduct = $this->vProductModel->getOne($pArgs['id']);
        if(!$vProduct){
            $pResponse->getBody()->write(json_encode(['message' => 'Product not found']));
            return $pResponse->withStatus(404)->withHeader('Content-Type', 'application/json');
        } else {
            $pResponse->getBody()->write(json_encode($vProduct));
            return $pResponse->withHeader('Content-Type', 'application/json');
        }
    }

    public function create(Request $pRequest, Response $pResponse, array $pArgs){
        $vData = $pRequest->getParsedBody();
        $vId = $pArgs['id'];
        if(!isset($vData['name']) || !isset($vData['description']) || !isset($vData['price'])){
            $pResponse->getBody()->write(json_encode(['message' => 'Missing data']));
            return $pResponse->withStatus(400)->withHeader('Content-Type', 'application/json'); 
        }
        $vSuccess = $this->vProductModel->update($vId, $vData);
        if(!$vSuccess){
            $pResponse->getBody()->write(json_encode(['message' => 'Product not found']));
            return $pResponse->withStatus(404)->withHeader('Content-Type', 'application/json');
        } else {
            $pResponse->getBody()->write(json_encode(['message' => 'Product updated']));
            return $pResponse->withHeader('Content-Type', 'application/json');
        }

        
    }

    public function update(Request $pRequest, Response $pResponse, array $pArgs){
       $vData = $pRequest->getParsedBody();
       $vId = $pArgs['id'];
       
       if(!isset($vData['name']) || !isset($vData['description']) || !isset($vData['price'])){
        $pResponse->getBody()->write(json_encode(['message' => 'Missing data']));
        return $pResponse->withStatus(400)->withHeader('Content-Type', 'application/json'); 
       }

       $vSuccess = $this->vProductModel->update($vId, $vData);
       if(!$vSuccess){
        $pResponse->getBody()->write(json_encode(['message' => 'Product not found']));
        return $pResponse->withStatus(404)->withHeader('Content-Type', 'application/json'); 
       }

       $pResponse->getBody()->write(json_encode(['message' => 'Product updated']));
       return $pResponse->withHeader('Content-Type', 'application/json');
    }

    public function delete(Request $pRequest, Response $pResponse, array $pArgs){
        $vId = $pArgs['id'];
        $vSuccess = $this->vProductModel->delete($vId);
        if(!$vSuccess){
           $pResponse->getBody()->write(json_encode(['message' => 'Product not found']));
           return $pResponse->withStatus(404)->withHeader('Content-Type', 'application/json'); 
        }
        $pResponse->getBody()->write(json_encode(['message' => 'Product deleted']));
        return $pResponse->withHeader('Content-Type', 'application/json');
    }
}