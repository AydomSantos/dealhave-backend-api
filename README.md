# API DealHave

Uma API RESTful desenvolvida em PHP utilizando o framework Slim.

## Requisitos

- PHP 7.4 ou superior
- Composer
- MySQL

## Instalação

1. Clone o repositório
2. Execute `composer install` para instalar as dependências
3. Copie o arquivo `.env.example` para `.env` e configure as variáveis de ambiente
4. Configure seu servidor web para apontar para o diretório `public/`

## Endpoints Disponíveis

### Usuários

- `GET /api/users` - Lista todos os usuários
- `GET /api/users/{id}` - Retorna detalhes de um usuário específico

## Desenvolvimento

Para iniciar o servidor de desenvolvimento, execute:

```bash
php -S localhost:8000 -t public
```

## Estrutura do Projeto

```
├── public/
│   └── index.php
├── src/
│   ├── Controllers/
│   ├── Config/
│   └── routes.php
├── .env
├── .env.example
└── composer.json
```