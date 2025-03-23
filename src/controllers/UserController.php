<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\User;

class UserController{
    private $vUserModel;

    // Construtor: inicializa o modelo de usuário
    public function __construct(){
        $this->vUserModel = new User();
    }

    // Retorna todos os usuários em formato JSON
    public function getAll(Request $pRequest, Response $pResponse, array $pArgs){
        $vUsers = $this->vUserModel->getAll();
        $pResponse->getBody()->write(json_encode($vUsers));
        return $pResponse->withHeader('Content-Type', 'application/json');
    }

    // Busca um usuário específico pelo ID
    public function getOne(Request $pRequest, Response $pResponse, array $pArgs){
       $vUser = $this->vUserModel->getById($pArgs['id']);
       
       // Verifica se o usuário foi encontrado
       if(!$vUser){
          $pResponse->getBody()->write(json_encode(['message' => 'User not found']));
          return $pResponse->withStatus(404)->withHeader('Content-Type', 'application/json');
       }
       
       // Retorna o usuário encontrado
       $pResponse->getBody()->write(json_encode($vUser));
       return $pResponse->withHeader('Content-Type', 'application/json');
    }

    // Cria um novo usuário com os dados da requisição
    public function create(Request $pRequest, Response $pResponse, array $pArgs){
       $vData = $pRequest->getParsedBody();
       
       // Validação dos campos obrigatórios
       if(!isset($vData['s_users_name']) || !isset($vData['s_users_email']) || !isset($vData['s_role_users'])){
          $pResponse->getBody()->write(json_encode(['message' => 'Missing required fields']));
          return $pResponse->withStatus(400)->withHeader('Content-Type', 'application/json');
       }

       // Cria o usuário e retorna o ID
       $vUserId = $this->vUserModel->create($vData);
       $pResponse->getBody()->write(json_encode(['message' => 'User created', 'id' => $vUserId]));
       return $pResponse->withStatus(201)->withHeader('Content-Type', 'application/json');
    }

    // Atualiza os dados de um usuário existente
    public function update(Request $pRequest, Response $pResponse, array $pArgs){
       $vData = $pRequest->getParsedBody();
       $vId = $pArgs['id'];

       // Verifica campos obrigatórios
       if(!isset($vData['s_users_name']) ||!isset($vData['s_users_email']) ||!isset($vData['s_role_users'])){
          $pResponse->getBody()->write(json_encode(['message' => 'Missing required fields']));
          return $pResponse->withStatus(400)->withHeader('Content-Type', 'application/json');  
       } 

       // Tenta atualizar e verifica sucesso
       $vSuccess = $this->vUserModel->update($vId, $vData);
       if(!$vSuccess){
          $pResponse->getBody()->write(json_encode(['message' => 'User not found']));
          return $pResponse->withStatus(404)->withHeader('Content-Type', 'application/json');
       }
       
       // Retorna sucesso
       $pResponse->getBody()->write(json_encode(['message' => 'User updated']));
       return $pResponse->withHeader('Content-Type', 'application/json');
    }
}