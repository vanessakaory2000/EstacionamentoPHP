# EstacionamentoPHP

Aplicação feita como atividade avaliativa com objetivo de criar um código seguindo normas e boas práticas de programação.<br>
Projeto feito para matéria de DESIGN PATTERNS E CLEAN CODE ministrada pelo professor Valdir e pela universidade UNIMAR

## Requisitos

- PHP
- Composer

## Tip

- Atualizar php.ini, descomentando a linha `extension=gd` para permitir a instalação do pacote de gerar pdf

## Instalação

```bash
composer install
```

O comando instala as dependências, incluindo **mPDF** (utilizado para exportar relatórios em PDF).

## Executando o projeto

1. Inicie o servidor embutido do PHP dentro da pasta `public/`:
```bash
composer start
```
2. Acesse pelo navegador: [http://127.0.0.1:8000/parking/](http://127.0.0.1:8000/parking/)

Na primeira execução o banco SQLite é criado automaticamente em `storage/parking.db`.

## Estrutura das pastas

```
Application/       Casos de uso (fluxos) e DTOs
Domain/            Entidades, interfaces e estratégias de tarifação
Infra/             Conexão SQLite, repositórios concretos e controlador web
Presentation/      Interface simples em HTML/CSS
public/            Ponto de entrada HTTP (roteamento) e autoload do Composer
storage/           Banco SQLite persistido (criado automaticamente)
```

## Funcionalidades

- Registrar entrada de veículos (carro, moto, caminhão) com validação de duplicidade.
- Registrar saída via botão na tabela de "Veículos no Pátio"; cálculo automático de horas (arredondadas para cima) e tarifa por tipo.
- Relatório consolidado de faturamento por tipo (visualizado em modal ou exportado em PDF).
- Listagem em tempo real dos veículos que ainda não deram saída.

## Fluxo da interface

1. A página carrega `Presentation/index.html`.
2. O JavaScript chama `/parking/api/open-sessions` para popular a tabela inicial.
3. Entradas enviam `POST /parking/api/entry`.
4. Saídas são registradas com `POST /parking/api/exit`, disparados direto pelos botões da tabela.
5. O relatório usa `GET /parking/api/report` (JSON) e `GET /parking/api/report?format=pdf` (download PDF).

## Rotas
Post ->  /parking/api/entry             -> Registra entrada <br>
Post ->  /parking/api/exit              -> Registra saída <br>
Get  ->  /parking/api/report            -> Relatório JSON por tipo <br>
Get  ->  /parking/api/report?format=pdf -> Baixa relatório em PDF <br>
Get  ->  /parking/api/open-sessions     -> Lista veículos ainda no pátio

## Banco de dados

- SQLite armazenado em `storage/parking.db`.
- Estrutura única: tabela `parking_sessions` com os campos necessários (placa, tipo, horários, valor).

## Exportação em PDF

- Implementada no caso de uso `Application\Flow\ExportReportPdf` utilizando _mPDF_.
- A página de relatório (`Infra\Report` + `Presentation/report.html`) também pode ser utilizada diretamente caso queira visualizar em HTML.

## Convenções e boas práticas

- Código formatado.
- Dependências por injeção.
- Validações para evitar estados inválidos.
- Comentários para auxiliar entender decisões.

## Integrantes

Estevão Alves dos Santos - 1990000
<br>
Vanessa Kaori Kurauchi   - 2002344
