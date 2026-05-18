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
│   ├── auth.php          # Helper de autenticação para a API
│   ├── imoveis.php       # Endpoint GET/POST de imóveis
│   ├── clientes.php      # Endpoint GET de clientes
│   └── contratos.php     # Endpoint GET de contratos
├── classes/
│   ├── Database.php      # Conexão PDO
│   ├── Imovel.php        # CRUD de imóveis
│   ├── Cliente.php       # CRUD de clientes
│   ├── Contrato.php      # CRUD de contratos
│   ├── Usuario.php       # Login e cadastro
│   └── JWT.php           # Geração e verificação de tokens
├── config/
│   ├── database.php      # Instancia a conexão
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
├── uploads/              # Fotos dos imóveis
│   └── .htaccess         # Bloqueia execução de scripts
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
| foto | VARCHAR(255) | Nome do arquivo de foto |
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

## Classes PHP

### `Database`
Localização: `config/database.php`

Responsável por abrir a conexão com o banco. Lê a `DATABASE_URL` do ambiente, extrai host, nome do banco, usuário e senha usando `parse_url()`, e cria uma conexão PDO com SSL obrigatório. Em caso de erro, registra no log do servidor e encerra — sem expor detalhes ao usuário.

```php
// Como é usado em todas as páginas:
$db = new Database();
$conn = $db->conectar(); // retorna o objeto PDO
```

```php
// Trecho do código (config/database.php):
$url    = parse_url(getenv('DATABASE_URL'));
$dsn    = "pgsql:host={$url['host']};dbname=" . ltrim($url['path'], '/') . ";sslmode=require";
$this->conn = new PDO($dsn, $url['user'], $url['pass']);
$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
```

---

### `Imovel`
Localização: `classes/Imovel.php`

Recebe a conexão PDO no construtor (injeção de dependência) e expõe métodos para cada operação no banco.

| Método | Descrição |
|---|---|
| `listar()` | Retorna todos os imóveis ordenados pelo mais recente |
| `listarComCliente()` | JOIN com contratos e clientes — traz o nome do cliente vinculado |
| `buscarPorId()` | Busca um imóvel pelo ID com LIMIT 1 |
| `criar()` | Valida whitelist de tipo/finalidade, sanitiza campos e faz INSERT |
| `atualizar()` | Valida whitelist de tipo/finalidade/status e faz UPDATE |
| `atualizarStatus()` | UPDATE apenas do campo status — usado ao criar/excluir contratos |
| `deletar()` | DELETE pelo ID |

```php
// Injeção de dependência no construtor:
public function __construct($db) {
    $this->conn = $db; // recebe a conexão pronta, não cria uma nova
}
```

```php
// Exemplo de validação por whitelist antes de salvar:
$tiposValidos = ['casa', 'apartamento', 'chacara', 'terreno', 'sitio', 'empresarial'];
if (!in_array($this->tipo, $tiposValidos)) {
    return false; // rejeita qualquer valor fora da lista
}
```

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

### `Cliente`
Localização: `classes/Cliente.php`

Mesma estrutura da classe `Imovel`. Recebe a conexão no construtor e executa as operações via PDO com `bindParam()`.

| Método | Descrição |
|---|---|
| `listar()` | Retorna todos os clientes em ordem alfabética |
| `buscarPorId()` | Busca um cliente pelo ID |
| `criar()` | Sanitiza os campos e faz INSERT |
| `atualizar()` | Sanitiza os campos e faz UPDATE |
| `deletar()` | DELETE pelo ID |

```php
// Sanitização aplicada antes de salvar:
$this->nome     = htmlspecialchars(strip_tags($this->nome));
$this->cpf      = htmlspecialchars(strip_tags($this->cpf));
$this->email    = htmlspecialchars(strip_tags($this->email));
$this->telefone = htmlspecialchars(strip_tags($this->telefone));
```

---

### `Contrato`
Localização: `classes/Contrato.php`

Gerencia os contratos que vinculam imóvel a cliente. O método `listar()` usa JOIN para trazer os nomes relacionados em uma única query.

| Método | Descrição |
|---|---|
| `listar()` | Retorna contratos com nome do imóvel e cliente via JOIN |
| `buscarPorId()` | Retorna um contrato com JOIN pelo ID |
| `criar()` | Insere um novo contrato |
| `atualizar()` | Atualiza um contrato existente |
| `deletar()` | Remove um contrato |

```php
// Query com JOIN para trazer dados relacionados:
SELECT c.*, i.titulo as imovel_titulo, cl.nome as cliente_nome
FROM contratos c
JOIN imoveis i  ON c.imovel_id = i.id
JOIN clientes cl ON c.cliente_id = cl.id
ORDER BY c.id DESC
```

> Ao criar um contrato em `contratos/novo.php`, o código chama `$imovel->atualizarStatus()` automaticamente para mudar o imóvel para `alugado` ou `vendido`. Ao excluir, volta para `disponivel`.

```php
// Em contratos/novo.php, após criar o contrato:
$imovel->id     = $_POST['imovel_id'];
$imovel->status = $_POST['tipo'] === 'aluguel' ? 'alugado' : 'vendido';
$imovel->atualizarStatus();
```

---

### `Usuario`
Localização: `classes/Usuario.php`

Gerencia autenticação. A senha nunca é salva em texto puro — o PHP gera um hash bcrypt automaticamente com `password_hash()`, e a verificação é feita com `password_verify()` que compara de forma segura.

| Método | Descrição |
|---|---|
| `login()` | Busca o usuário pelo nome e verifica a senha com `password_verify()` |
| `cadastrar()` | Gera hash bcrypt da senha e faz INSERT |
| `usuarioExiste()` | SELECT com LIMIT 1 para checar duplicidade antes de cadastrar |

```php
// Cadastro — senha nunca é salva em texto puro:
$hash = password_hash($this->senha, PASSWORD_DEFAULT); // gera hash bcrypt
$stmt->bindParam(':senha', $hash); // salva o hash no banco

// Login — compara senha digitada com o hash do banco:
if ($row && password_verify($this->senha, $row['senha'])) {
    return $row; // autenticado
}
```

---

### `JWT`
Localização: `classes/JWT.php`

Implementação manual do padrão JWT sem dependências externas. O token é composto por três partes em Base64URL separadas por ponto: `header.payload.assinatura`.

| Método | Descrição |
|---|---|
| `gerar($payload, $secret)` | Cria o token assinado com HMAC-SHA256 |
| `verificar($token, $secret)` | Valida assinatura e expiração, retorna o payload |

```php
// Geração do token — três partes unidas por ponto:
$header  = base64url( json_encode(['alg' => 'HS256', 'typ' => 'JWT']) );
$body    = base64url( json_encode($payload) ); // ex: {id:1, nome:"João", exp:...}
$sig     = base64url( hash_hmac('sha256', "$header.$body", $secret, true) );
return "$header.$body.$sig";

// Verificação — recalcula a assinatura e compara:
$sigEsperada = base64url( hash_hmac('sha256', "$header.$body", $secret, true) );
if (!hash_equals($sigEsperada, $sig)) return false; // assinatura inválida

// Verifica expiração:
if ($dados['exp'] < time()) return false; // token vencido
```

O token expira em 8 horas. Após isso, o usuário é redirecionado para o login automaticamente.

---

## Autenticação

O sistema usa JWT armazenado em cookie `httpOnly`. Não utiliza `session_start()`, garantindo compatibilidade com ambientes serverless como a Vercel, onde cada requisição pode ser processada por uma instância diferente do servidor.

### Fluxo completo de login

```
1. Usuário preenche usuário + senha e envia o formulário (POST)
2. login.php instancia Usuario e chama $usuario->login()
3. A classe busca o usuário no banco pelo nome de login
4. password_verify() compara a senha digitada com o hash bcrypt salvo
5. Se válido, JWT::gerar() cria um token com {id, nome, exp: agora + 8h}
6. setcookie() salva o token no cookie 'token' com httpOnly e SameSite=Strict
7. Redireciona para index.php
8. Em cada página, includes/auth.php lê o cookie e chama JWT::verificar()
9. Se inválido ou expirado, redireciona para login.php
```

### Como a proteção de páginas funciona

```php
// includes/auth.php — incluído no topo de cada página protegida:
$token         = $_COOKIE['token'] ?? '';
$usuario_logado = JWT::verificar($token, getenv('JWT_SECRET'));

if (!$usuario_logado) {
    header('Location: /login.php');
    exit; // para a execução imediatamente
}
// Se chegou aqui, o usuário está autenticado.
// $usuario_logado contém {id, nome} disponível para a página.
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

Todos os endpoints exigem autenticação via header `Authorization: Bearer <token>`.

O token é o mesmo JWT gerado no login (visível no cookie `token` do navegador).

### Endpoints

#### `GET /api/imoveis.php`
Retorna lista de imóveis em JSON.

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

---

#### `GET /api/clientes.php`
Retorna lista de clientes em JSON.

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

### Erros de autenticação

```json
{"erro": "Token não fornecido."}
{"erro": "Token inválido ou expirado."}
```

---

## Segurança

| Proteção | Como é feita |
|---|---|
| SQL Injection | PDO com `prepare()` + `bindParam()` em todas as queries |
| XSS | `strip_tags()` na entrada, `htmlspecialchars()` na saída |
| PHP Injection via upload | `getimagesize()` valida o conteúdo real do arquivo |
| Manipulação de ID | Todos os IDs recebem cast `(int)` antes de qualquer uso |
| Valores inválidos | `tipo`, `finalidade` e `status` validados contra whitelist |
| Execução de script em uploads | `.htaccess` bloqueia PHP na pasta `/uploads` |
| Erros do banco | Erros do PDO vão para `error_log`, nunca exibidos ao usuário |
| Senhas | Hash bcrypt com `password_hash()` + verificação com `password_verify()` |
| Autenticação | JWT HS256, cookie `httpOnly`, `SameSite=Strict`, expira em 8h |

### SQL Injection

Todas as queries usam `prepare()` + `bindParam()`. O PDO separa o SQL dos dados — o banco recebe os dois separadamente e nunca interpreta o dado como código.

```php
// VULNERÁVEL (concatenação direta — NUNCA fazer):
$sql = "SELECT * FROM usuarios WHERE usuario = '$usuario'";
// Se $usuario for: ' OR '1'='1  →  retorna todos os usuários

// CORRETO (PDO com bindParam):
$sql = "SELECT * FROM usuarios WHERE usuario = :usuario";
$stmt = $this->conn->prepare($sql);
$stmt->bindParam(':usuario', $this->usuario); // dado tratado como texto puro
$stmt->execute();
```

### XSS (Cross-Site Scripting)

```php
// Na entrada (ao salvar no banco):
$this->titulo = htmlspecialchars(strip_tags($this->titulo));
// strip_tags remove: <script>alert(1)</script>  →  alert(1)
// htmlspecialchars converte: <b>texto</b>  →  &lt;b&gt;texto&lt;/b&gt;

// Na saída (ao exibir no HTML):
echo htmlspecialchars($row['titulo']);
// Garante que mesmo dados já no banco não executem como HTML
```

### PHP Injection via Upload

```php
// Verifica os bytes reais do arquivo, não apenas a extensão:
$imagemInfo = @getimagesize($_FILES['foto']['tmp_name']);

// Um arquivo PHP renomeado para .jpg não tem assinatura de imagem
// getimagesize() retorna false → upload rejeitado
if ($imagemInfo && in_array($imagemInfo['mime'], ['image/jpeg', 'image/png', 'image/webp'])) {
    // só aqui o arquivo é salvo
}
```

### Manipulação de ID

```php
// Sem cast — vulnerável a strings maliciosas na URL:
// ?id=1 OR 1=1  poderia causar comportamento inesperado
$imovel->id = $_GET['id'];

// Com cast para inteiro — qualquer coisa vira número:
// ?id=1 OR 1=1  →  (int) = 1
$imovel->id = (int)($_GET['id'] ?? 0);
```

---

## Como Rodar Localmente

### Requisitos

- PHP 8.x com extensão `pdo_pgsql`
- Conta no [Neon](https://neon.tech) (banco PostgreSQL gratuito)

### Instalação

```bash
# 1. Clone o repositório
git clone https://github.com/seu-usuario/imobtech.git
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

No painel da Vercel → **Settings → Environment Variables**, adicione `DATABASE_URL` e `JWT_SECRET` com os mesmos valores do `.env` local.
