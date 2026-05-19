CREATE TABLE imoveis (
    id SERIAL PRIMARY KEY,
    tipo VARCHAR(20) NOT NULL,
    finalidade VARCHAR(20) NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    valor NUMERIC(15,2) NOT NULL,
    area NUMERIC(10,2),
    quartos INT DEFAULT 0,
    banheiros INT DEFAULT 0,
    vagas INT DEFAULT 0,
    cidade VARCHAR(100),
    bairro VARCHAR(100),
    status VARCHAR(20) DEFAULT 'disponivel',
    criado_em TIMESTAMP DEFAULT NOW()
);

CREATE TABLE clientes (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    cpf VARCHAR(14),
    email VARCHAR(255),
    telefone VARCHAR(20),
    criado_em TIMESTAMP DEFAULT NOW()
);

CREATE TABLE usuarios (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    usuario VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT NOW()
);

CREATE TABLE contratos (
    id SERIAL PRIMARY KEY,
    imovel_id INT NOT NULL REFERENCES imoveis(id),
    cliente_id INT NOT NULL REFERENCES clientes(id),
    tipo VARCHAR(20) NOT NULL,
    forma_pagamento VARCHAR(20),
    valor_entrada NUMERIC(15,2) DEFAULT 0,
    valor_parcela NUMERIC(15,2) DEFAULT 0,
    calcao NUMERIC(15,2) DEFAULT 0,
    valor_total NUMERIC(15,2) NOT NULL,
    parcelas INT DEFAULT 1,
    data_inicio DATE,
    data_fim DATE,
    criado_em TIMESTAMP DEFAULT NOW()
);

-- Migração para banco existente:
-- ALTER TABLE contratos ADD COLUMN IF NOT EXISTS forma_pagamento VARCHAR(20);
-- ALTER TABLE contratos ADD COLUMN IF NOT EXISTS valor_entrada NUMERIC(15,2) DEFAULT 0;
-- ALTER TABLE contratos ADD COLUMN IF NOT EXISTS valor_parcela NUMERIC(15,2) DEFAULT 0;
-- ALTER TABLE contratos ADD COLUMN IF NOT EXISTS calcao NUMERIC(15,2) DEFAULT 0;
