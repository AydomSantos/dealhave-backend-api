
# Plataforma de Economia Compartilhada

Uma aplicação web baseada em PHP para facilitar a economia compartilhada, permitindo aos usuários gerenciar solicitações, oferecer serviços e produtos, com autenticação de usuários e funcionalidades de chat em tempo real.

## Funcionalidades Implementadas

- Autenticação de Usuários (Login/Registro)
- Sistema de Sessões Seguro
- Página Inicial Personalizada
- Exploração de Pedidos
- Criação e Gerenciamento de Pedidos
- Perfil de Usuário com Edição
- Geolocalização para Pedidos Próximos
- Sistema de Categorias para Pedidos
- Interface Responsiva com Bootstrap 5
- Pesquisa e Filtros de Pedidos
- Visualização de Detalhes de Pedidos
- Atualização de Localização do Usuário
- Sistema de Chat em Tempo Real
  - Chat privado entre usuários
  - Histórico de conversas
  - Atualização automática de mensagens
  - Indicador de novas mensagens
  - Interface intuitiva com scroll automático
  - Avatares personalizados com iniciais
  - Indicadores de status de mensagem
  - Busca de usuários para novas conversas

## Implementações Recentes

- Interface de chat redesenhada com princípios modernos de UX
- Avatares coloridos com iniciais para identificação rápida de usuários
- Bolhas de mensagens estilizadas para melhor legibilidade
- Modal de busca de usuários para iniciar novas conversas
- Indicadores de tempo para mensagens
- Atualização automática via AJAX a cada 3 segundos
- Tratamento de estados vazios com mensagens informativas
- Marketplace para Produtos com categorias e filtros
- Seção de Serviços com geolocalização
- Área de Comunidade para interação entre usuários
- Gerenciamento de Perfil com upload de imagem
- Interface totalmente responsiva com Bootstrap 5

## Estrutura do Projeto

```
├── assets/  
│   ├── css/ (Bootstrap e estilos personalizados)
│   ├── js/ (Scripts JavaScript)
│   ├── img/ (Ícones e logos)
├── includes/  
│   ├── header.php (Cabeçalho comum)
│   ├── footer.php (Rodapé comum)
│   ├── db.php (Conexão MySQL)
│   ├── auth.php (Funções de autenticação)
├── classes/  
│   ├── User.php (Classe de gerenciamento de usuários)
│   ├── Request.php (Classe de gerenciamento de solicitações)
├── pages/  
│   ├── login.php (Login de usuário)
│   ├── register.php (Registro de usuário)
│   ├── home.php (Página inicial)
│   ├── dashboard.php (Listagem de solicitações)
│   ├── profile.php (Perfil do usuário)
│   ├── marketplace.php (Marketplace de produtos)
│   ├── services.php (Ofertas de serviços)
│   ├── community.php (Área da comunidade)
│   ├── logout.php (Logout de usuário)
│   ├── chat.php (Sistema de chat em tempo real)
│   ├── send_message.php (API para envio de mensagens)
│   ├── get_messages.php (API para receber mensagens)
│   ├── check_new_messages.php (API para verificar novas mensagens)
└── config.php (Configurações globais)
```

## Configuração

1. Configure suas configurações de banco de dados em `config.php`
2. Crie um banco de dados MySQL chamado `request_system`
3. Importe o esquema do banco de dados (se fornecido)
4. Inicie o servidor PHP: `php -S 0.0.0.0:5000`

## Requisitos

- PHP 8.0+
- MySQL 5.7+
- Navegador web moderno
- XAMPP (recomendado para ambiente de desenvolvimento)

## Autenticação

O sistema utiliza autenticação baseada em sessão com os seguintes recursos:
- Hash seguro de senha
- Gerenciamento de sessão
- Funcionalidade de login/logout
- Validação de formulários

## Estrutura do Banco de Dados

O sistema utiliza MySQL com as seguintes tabelas principais:

```sql
-- Tabela de usuários
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    profile_image VARCHAR(255) DEFAULT 'default.jpg',
    bio TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de solicitações
CREATE TABLE requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(50) NOT NULL,
    status ENUM('aberta', 'em_andamento', 'concluída', 'cancelada') DEFAULT 'aberta',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabela de produtos
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    category VARCHAR(50) NOT NULL,
    condition ENUM('novo', 'usado', 'seminovo') NOT NULL,
    available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabela de serviços
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(50) NOT NULL,
    location VARCHAR(100),
    available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabela de mensagens
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    content TEXT NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);
```

## Contribuindo

1. Faça um fork do repositório
2. Crie sua branch de recurso
3. Faça commit das suas alterações
4. Envie para a branch
5. Crie um novo Pull Request
