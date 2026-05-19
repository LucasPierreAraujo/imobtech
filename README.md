# Imobtech — Sistema de Gerenciamento Imobiliário

Sistema web desenvolvido em PHP com POO (Programação Orientada a Objetos) para gerenciamento de imóveis, clientes e contratos. Banco de dados PostgreSQL hospedado no Neon, deploy na Vercel.

---

## Sumário

- [Tecnologias](#tecnologias)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [Banco de Dados](#banco-de-dados)
- [Classes PHP](#classes-php)
- [Autenticação](#autenticação)
- [Páginas do Sistema](#páginas-do-sistema)
- [API REST](#api-rest)
- [Segurança](#segurança)
- [Como Rodar Localmente](#como-rodar-localmente)
- [Deploy na Vercel](#deploy-na-vercel)

---

## Tecnologias

- **PHP 8.3** — backend e renderização de páginas
- **PostgreSQL** — banco de dados (Neon)
- **PDO** — conexão com banco de dados
- **JWT (HS256)** — autenticação sem sessão
- **Bootstrap 5** — interface
- **Vercel** — hospedagem com runtime `vercel-php@0.7.2`

---

## Estrutura do Projeto

```
imobtech/
├── api/
│   ├── auth.php          # Classe Api — autenticação e resposta JSON
│   ├── login.php         # LoginController — POST /api/login.php
│   ├── imoveis.php       # ImoveisController — GET/POST /api/imoveis.php
│   ├── clientes.php      # ClientesController — GET /api/clientes.php
│   └── contratos.php     # ContratosController — GET /api/contratos.php
├── classes/
│   ├── Imovel.php        # Modelo — propriedades do imóvel
│   ├── ImovelDAO.php     # DAO — queries SQL de imóveis
│   ├── Cliente.php       # Modelo — propriedades do cliente
│   ├── ClienteDAO.php    # DAO — queries SQL de clientes
│   ├── Contrato.php      # Modelo — propriedades do contrato
│   ├── ContratoDAO.php   # DAO — queries SQL de contratos
│   ├── Usuario.php       # Modelo — propriedades do usuário
│   ├── UsuarioDAO.php    # DAO — login e cadastro
│   └── JWT.php           # Geração e verificação de tokens
├── config/
│   ├── database.php      # Classe Database — instancia a conexão
│   └── env.php           # Carrega variáveis do .env
├── includes/
│   ├── header.php        # Navbar reutilizável
│   ├── footer.php        # Rodapé reutilizável
│   └── auth.php          # Proteção de páginas via JWT
├── imoveis/
│   ├── index.php         # Listagem
│   ├── novo.php          # Cadastro
│   ├── editar.php        # Edição
│   └── deletar.php       # Exclusão
├── clientes/
│   ├── index.php
│   ├── novo.php
│   ├── editar.php
│   └── deletar.php
├── contratos/
│   ├── index.php
│   ├── novo.php
│   ├── editar.php
│   └── deletar.php
├── index.php             # Página inicial
├── login.php             # Tela de login
├── cadastro.php          # Tela de cadastro
├── logout.php            # Encerra sessão
├── banco.sql             # Script de criação das tabelas
├── vercel.json           # Configuração de deploy
├── .env.example          # Modelo de variáveis de ambiente
└── .gitignore
```

---

## Banco de Dados

### Criação das tabelas

Execute o arquivo `banco.sql` no banco PostgreSQL:

```bash
psql 'SUA_CONNECTION_STRING' -f banco.sql
```

### Tabelas

#### `usuarios`
| Coluna | Tipo | Descrição |
|---|---|---|
| id | SERIAL PK | Identificador |
| nome | VARCHAR(255) | Nome completo |
| usuario | VARCHAR(100) UNIQUE | Login |
| senha | VARCHAR(255) | Hash bcrypt |
| criado_em | TIMESTAMP | Data de cadastro |

#### `imoveis`
| Coluna | Tipo | Descrição |
|---|---|---|
| id | SERIAL PK | Identificador |
| tipo | VARCHAR(20) | casa, apartamento, chacara, terreno, sitio, empresarial |
| finalidade | VARCHAR(20) | alugar, comprar, financiamento |
| titulo | VARCHAR(255) | Título do imóvel |
| descricao | TEXT | Descrição |
| valor | NUMERIC(15,2) | Valor em reais |
| area | NUMERIC(10,2) | Área em m² |
| quartos | INT | Número de quartos |
| banheiros | INT | Número de banheiros |
| vagas | INT | Vagas de garagem |
| cidade | VARCHAR(100) | Cidade |
| bairro | VARCHAR(100) | Bairro |
| status | VARCHAR(20) | disponivel, vendido, alugado |
| foto | TEXT | Foto em base64 (data URI) |
| criado_em | TIMESTAMP | Data de cadastro |

#### `clientes`
| Coluna | Tipo | Descrição |
|---|---|---|
| id | SERIAL PK | Identificador |
| nome | VARCHAR(255) | Nome completo |
| cpf | VARCHAR(14) | CPF |
| email | VARCHAR(255) | E-mail |
| telefone | VARCHAR(20) | Telefone |
| criado_em | TIMESTAMP | Data de cadastro |

#### `contratos`
| Coluna | Tipo | Descrição |
|---|---|---|
| id | SERIAL PK | Identificador |
| imovel_id | INT FK | Referência ao imóvel |
| cliente_id | INT FK | Referência ao cliente |
| tipo | VARCHAR(20) | aluguel, compra, financiamento |
| valor_total | NUMERIC(15,2) | Valor total do contrato |
| parcelas | INT | Número de parcelas |
| data_inicio | DATE | Início do contrato |
| data_fim | DATE | Fim do contrato |
| criado_em | TIMESTAMP | Data de criação |

---

## Padrão DAO (Data Access Object)

O sistema aplica o padrão DAO para separar as responsabilidades em duas camadas distintas:

| Camada | Arquivos | Responsabilidade |
|---|---|---|
| **Modelo** | `Imovel.php`, `Cliente.php`, `Contrato.php`, `Usuario.php` | Apenas os dados — propriedades do objeto |
| **DAO** | `ImovelDAO.php`, `ClienteDAO.php`, `ContratoDAO.php`, `UsuarioDAO.php` | Apenas o banco — queries SQL |

```php
// MODELO — só propriedades, sem nenhuma lógica de banco:
class Imovel {
    public $id;
    public $titulo;
    public $valor;
    public $tipo;
    // ...
}

// DAO — recebe o modelo como parâmetro e faz o acesso ao banco:
class ImovelDAO {
    private $conn;

    public function __construct($db) {
        $this->conn = $db; // recebe a conexão via injeção de dependência
    }

    public function criar(Imovel $imovel) {
        $sql  = "INSERT INTO imoveis (titulo, valor, ...) VALUES (:titulo, :valor, ...)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':titulo', $imovel->titulo);
        return $stmt->execute();
    }
}
```

```php
// Como as páginas usam o padrão DAO:
$db  = new Database();
$dao = new ImovelDAO($db->conectar());

$imovel         = new Imovel();
$imovel->titulo = $_POST['titulo'];
$imovel->valor  = $_POST['valor'];

$dao->criar($imovel);
```

---

## Classes PHP

### `Database`
Localização: `config/database.php`

Responsável por abrir a conexão com o banco. Lê a `DATABASE_URL` do ambiente, extrai host, nome do banco, usuário e senha usando `parse_url()`, e cria uma conexão PDO com SSL obrigatório.

```php
$db   = new Database();
$conn = $db->conectar(); // retorna o objeto PDO
```

---

### `Api`
Localização: `api/auth.php`

Classe utilitária usada por todos os controllers da API. Centraliza a autenticação JWT e o envio de respostas JSON.

| Método | Descrição |
|---|---|
| `autenticar()` | Valida o token JWT do header `Authorization: Bearer` — encerra com 401 se inválido |
| `responder()` | Envia JSON com o código HTTP informado e encerra a execução |

```php
// Verifica o token e retorna o payload se válido:
$usuario = Api::autenticar();

// Envia resposta JSON:
Api::responder(['mensagem' => 'ok'], 200);
Api::responder(['erro' => 'não encontrado'], 404);
```

---

### `Imovel` / `ImovelDAO`
Localização: `classes/Imovel.php` e `classes/ImovelDAO.php`

| Método | Descrição |
|---|---|
| `listar()` | Retorna todos os imóveis ordenados pelo mais recente |
| `listarComCliente()` | JOIN com contratos e clientes — traz o nome do cliente vinculado |
| `buscarPorId()` | Busca um imóvel pelo ID |
| `criar()` | Valida whitelist de tipo/finalidade e faz INSERT |
| `atualizar()` | Valida whitelist de tipo/finalidade/status e faz UPDATE |
| `atualizarStatus()` | UPDATE apenas do campo status — usado ao criar/excluir contratos |
| `deletar()` | DELETE pelo ID |

```php
// listarComCliente usa DISTINCT ON do PostgreSQL para
// retornar apenas um registro por imóvel (o contrato mais recente):
SELECT DISTINCT ON (i.id) i.*, cl.nome as cliente_nome
FROM imoveis i
LEFT JOIN contratos c ON c.imovel_id = i.id
LEFT JOIN clientes cl ON cl.id = c.cliente_id
ORDER BY i.id DESC, c.id DESC
```

---

### `Cliente` / `ClienteDAO`
Localização: `classes/Cliente.php` e `classes/ClienteDAO.php`

| Método | Descrição |
|---|---|
| `listar()` | Retorna todos os clientes em ordem alfabética |
| `buscarPorId()` | Busca um cliente pelo ID |
| `criar()` | Insere um novo cliente |
| `atualizar()` | Atualiza os dados de um cliente |
| `deletar()` | Remove um cliente |

---

### `Contrato` / `ContratoDAO`
Localização: `classes/Contrato.php` e `classes/ContratoDAO.php`

| Método | Descrição |
|---|---|
| `listar()` | Retorna contratos com nome do imóvel e cliente via JOIN |
| `buscarPorId()` | Retorna um contrato com JOIN pelo ID |
| `criar()` | Insere um novo contrato |
| `atualizar()` | Atualiza um contrato existente |
| `deletar()` | Remove um contrato |

> Ao criar um contrato, o status do imóvel é atualizado automaticamente para `alugado` ou `vendido`. Ao excluir, volta para `disponivel`.

---

### `Usuario` / `UsuarioDAO`
Localização: `classes/Usuario.php` e `classes/UsuarioDAO.php`

| Método | Descrição |
|---|---|
| `login()` | Busca o usuário pelo nome e verifica a senha com `password_verify()` |
| `criar()` | Gera hash bcrypt da senha e faz INSERT |
| `usuarioExiste()` | Verifica duplicidade antes de cadastrar |

```php
// Senha nunca salva em texto puro:
$hash = password_hash($usuario->senha, PASSWORD_DEFAULT);

// Verificação no login:
if ($row && password_verify($usuario->senha, $row['senha'])) {
    return $row;
}
```

---

### `JWT`
Localização: `classes/JWT.php`

Implementação manual do padrão JWT sem dependências externas.

| Método | Descrição |
|---|---|
| `gerar($payload, $secret)` | Cria o token assinado com HMAC-SHA256 |
| `verificar($token, $secret)` | Valida assinatura e expiração, retorna o payload |

```php
$header = base64url( json_encode(['alg' => 'HS256', 'typ' => 'JWT']) );
$body   = base64url( json_encode($payload) );
$sig    = base64url( hash_hmac('sha256', "$header.$body", $secret, true) );
return "$header.$body.$sig";
```

O token expira em 8 horas.

---

## Autenticação

O sistema usa JWT armazenado em cookie `httpOnly`. Não utiliza `session_start()`, garantindo compatibilidade com ambientes serverless como a Vercel.

### Fluxo completo de login

```
1. Usuário preenche usuário + senha e envia o formulário (POST)
2. login.php instancia Usuario e chama $dao->login()
3. UsuarioDAO busca o usuário no banco pelo nome de login
4. password_verify() compara a senha digitada com o hash bcrypt salvo
5. Se válido, JWT::gerar() cria um token com {id, nome, exp: agora + 8h}
6. setcookie() salva o token com httpOnly e SameSite=Strict
7. Redireciona para index.php
8. Em cada página, includes/auth.php lê o cookie e chama JWT::verificar()
9. Se inválido ou expirado, redireciona para login.php
```

### Como a proteção de páginas funciona

```php
// includes/auth.php — incluído no topo de cada página protegida:
$token          = $_COOKIE['token'] ?? '';
$usuario_logado = JWT::verificar($token, getenv('JWT_SECRET'));

if (!$usuario_logado) {
    header('Location: /login.php');
    exit;
}
```

---

## Páginas do Sistema

| URL | Descrição |
|---|---|
| `/login.php` | Tela de login |
| `/cadastro.php` | Criação de conta |
| `/logout.php` | Encerra a autenticação |
| `/index.php` | Página inicial com cards de imóveis |
| `/imoveis/index.php` | Listagem de imóveis |
| `/imoveis/novo.php` | Cadastro de imóvel |
| `/imoveis/editar.php?id=X` | Edição de imóvel |
| `/imoveis/deletar.php?id=X` | Exclusão de imóvel |
| `/clientes/index.php` | Listagem de clientes |
| `/clientes/novo.php` | Cadastro de cliente |
| `/clientes/editar.php?id=X` | Edição de cliente |
| `/clientes/deletar.php?id=X` | Exclusão de cliente |
| `/contratos/index.php` | Listagem de contratos |
| `/contratos/novo.php` | Cadastro de contrato |
| `/contratos/editar.php?id=X` | Edição de contrato |
| `/contratos/deletar.php?id=X` | Exclusão de contrato |

---

## API REST

Todos os endpoints (exceto `/api/login.php`) exigem autenticação via header `Authorization: Bearer <token>`.

Cada endpoint é implementado como uma **classe controller** em OOP.

### Endpoints

#### `POST /api/login.php`
Autentica o usuário e retorna o token JWT.

```bash
curl -X POST http://localhost:8000/api/login.php \
  -H "Content-Type: application/json" \
  -d '{"usuario": "admin", "senha": "123456"}'
```

Resposta:
```json
{"token": "eyJ...", "nome": "Admin"}
```

Erros:
```json
{"erro": "Usuário ou senha inválidos."}
{"erro": "Os campos usuario e senha são obrigatórios."}
```

---

#### `GET /api/imoveis.php`
Retorna lista de imóveis com nome do cliente vinculado.

```bash
curl -H "Authorization: Bearer SEU_TOKEN" http://localhost:8000/api/imoveis.php
```

Resposta:
```json
[
  {
    "id": 1,
    "tipo": "casa",
    "finalidade": "alugar",
    "titulo": "Casa no Centro",
    "valor": "1500.00",
    "cidade": "São Paulo",
    "status": "disponivel",
    "cliente_nome": null
  }
]
```

---

#### `POST /api/imoveis.php`
Cadastra um novo imóvel.

```bash
curl -X POST http://localhost:8000/api/imoveis.php \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "tipo": "apartamento",
    "finalidade": "comprar",
    "titulo": "Apto Jardins",
    "valor": 320000,
    "area": 65,
    "quartos": 2,
    "banheiros": 1,
    "vagas": 1,
    "cidade": "São Paulo",
    "bairro": "Jardins"
  }'
```

Resposta:
```json
{"mensagem": "Imóvel cadastrado com sucesso."}
```

Valores válidos para `tipo`: `casa`, `apartamento`, `chacara`, `terreno`, `sitio`, `empresarial`

Valores válidos para `finalidade`: `alugar`, `comprar`, `financiamento`

Campos obrigatórios: `tipo`, `finalidade`, `titulo`, `cidade`

---

#### `GET /api/clientes.php`
Retorna lista de clientes.

```bash
curl -H "Authorization: Bearer SEU_TOKEN" http://localhost:8000/api/clientes.php
```

---

#### `GET /api/contratos.php`
Retorna lista de contratos com nome do imóvel e cliente.

```bash
curl -H "Authorization: Bearer SEU_TOKEN" http://localhost:8000/api/contratos.php
```

---

### Códigos de resposta

| Código | Situação |
|---|---|
| 200 | Sucesso |
| 201 | Recurso criado |
| 400 | JSON inválido ou ausente |
| 401 | Token ausente, inválido ou expirado |
| 405 | Método HTTP não permitido |
| 422 | Dados inválidos (campo obrigatório vazio ou valor fora da whitelist) |
| 500 | Erro interno do servidor |

---

## Segurança

| Proteção | Como é feita |
|---|---|
| SQL Injection | PDO com `prepare()` + `bindParam()` em todas as queries |
| XSS | `htmlspecialchars()` na exibição de todos os dados no HTML |
| PHP Injection via upload | `getimagesize()` valida o conteúdo real do arquivo |
| Manipulação de ID | Todos os IDs recebem cast `(int)` antes de qualquer uso |
| Valores inválidos | `tipo`, `finalidade` e `status` validados contra whitelist |
| Erros do banco | Erros do PDO vão para `error_log`, nunca exibidos ao usuário |
| Senhas | Hash bcrypt com `password_hash()` + verificação com `password_verify()` |
| Autenticação | JWT HS256, cookie `httpOnly`, `SameSite=Strict`, expira em 8h |
| Timing attack | `hash_equals()` na comparação de assinaturas JWT |

### SQL Injection

```php
// VULNERÁVEL (concatenação direta):
$sql = "SELECT * FROM usuarios WHERE usuario = '$usuario'";

// CORRETO (PDO com bindParam):
$sql = "SELECT * FROM usuarios WHERE usuario = :usuario";
$stmt = $this->conn->prepare($sql);
$stmt->bindParam(':usuario', $usuario->usuario);
$stmt->execute();
```

### XSS (Cross-Site Scripting)

Os dados são armazenados no banco como estão e escapados somente na exibição:

```php
// Na exibição (templates HTML):
echo htmlspecialchars($row['titulo']);
echo htmlspecialchars($row['nome']);
```

### PHP Injection via Upload

```php
$imagemInfo = @getimagesize($_FILES['foto']['tmp_name']);
if ($imagemInfo && in_array($imagemInfo['mime'], ['image/jpeg', 'image/png', 'image/webp'])) {
    // arquivo válido
}
```

### Manipulação de ID

```php
$id = (int)($_GET['id'] ?? 0);
// ?id=1 OR 1=1  →  (int) = 1
```

---

## Como Rodar Localmente

### Requisitos

- PHP 8.x com extensão `pdo_pgsql`
- Conta no [Neon](https://neon.tech) (banco PostgreSQL gratuito)

### Instalação

```bash
# 1. Clone o repositório
git clone https://github.com/LucasPierreAraujo/imobtech.git
cd imobtech

# 2. Instale a extensão PHP para PostgreSQL (Ubuntu/Debian)
sudo apt install php-pgsql

# 3. Crie o arquivo .env com suas credenciais
cp .env.example .env

# 4. Crie as tabelas no banco
psql 'SUA_CONNECTION_STRING' -f banco.sql

# 5. Suba o servidor
php -S localhost:8000
```

Acesse `http://localhost:8000` e crie uma conta em `/cadastro.php`.

### Variáveis de ambiente (`.env`)

```env
DATABASE_URL=postgresql://usuario:senha@host/banco?sslmode=require
JWT_SECRET=chave_secreta_longa
```

---

## Deploy na Vercel

```bash
npm i -g vercel
vercel
```

No painel da Vercel → **Settings → Environment Variables**, adicione `DATABASE_URL` e `JWT_SECRET`.

Acesso: imobtech-delta.vercel.app
