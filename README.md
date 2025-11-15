# Controle de Estacionamento Inteligente

Aplicação acadêmica em PHP 8.2 que registra entradas e saídas de veículos, calcula tarifas por tipo, gera relatórios (HTML/JSON/PDF) e mantém uma lista em tempo real dos veículos ainda no pátio. O projeto foi organizado em camadas (`Domain`, `Application`, `Infra`, `Presentation`) seguindo princípios SOLID, DRY, KISS e PSR-12.

## Requisitos

- PHP 8.2+ com a extensão `pdo_sqlite` habilitada
- Composer 2+
- Navegador moderno com suporte a ES6 (para o JavaScript do front-end)

## Instalação

```bash
composer install
```

O comando cria o arquivo `vendor/autoload.php` e instala as dependências, incluindo **mPDF** (utilizado para exportar relatórios em PDF).

## Executando o projeto

1. Inicie o servidor embutido do PHP apontando para a pasta `public/`:
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
Presentation/      Interface simples em HTML/CSS + JavaScript vanilla
public/            Ponto de entrada HTTP (roteamento) e autoload do Composer
storage/           Banco SQLite persistido (criado automaticamente)
```

### Camadas principais

- **Domain**: contém a entidade `ParkingSession`, interfaces para repositórios e estratégias de preço (`CarService`, `MotoService`, `TruckService`).
- **Application**: orquestra os casos de uso (`RegEntry`, `RegExit`, `GetReport`, `ListOpenSessions`, `ExportReportPdf`).
- **Infra**: implementa a conexão com SQLite (`Infra\Connection`), o repositório (`ParkingSessionRepository`) e o `WebController`, que expõe rotas simples para a interface.
- **Presentation**: página estática (`index.html`) com Tailwind CSS + SweetAlert2 e um script (`index.js`) que consome as rotas da API.

## Funcionalidades

- Registrar entrada de veículos (carro, moto, caminhão) com validação de duplicidade.
- Registrar saída via botão na tabela de "Veículos no Pátio"; cálculo automático de horas (arredondadas para cima) e tarifa por tipo.
- Relatório consolidado de faturamento por tipo (visualizado em modal ou exportado em PDF).
- Listagem em tempo real dos veículos que ainda não deram saída.

## Fluxo da interface

1. A página carrega `Presentation/index.html`/`index.js`.
2. O JavaScript chama `/parking/api/open-sessions` para popular a tabela inicial.
3. Entradas enviam `POST /parking/api/entry`.
4. Saídas são registradas com `POST /parking/api/exit`, disparados direto pelos botões da tabela.
5. O relatório usa `GET /parking/api/report` (JSON) e `GET /parking/api/report?format=pdf` (download PDF).

## Rotas expostas (servidor embutido)

| Método | Caminho                          | Descrição                        |
| ------ | -------------------------------- | -------------------------------- |
| GET    | `/parking/`                      | Página inicial                   |
| GET    | `/parking/index.js`              | Script front-end                 |
| POST   | `/parking/api/entry`             | Registra entrada                 |
| POST   | `/parking/api/exit`              | Registra saída (por ID ou placa) |
| GET    | `/parking/api/report`            | Relatório JSON por tipo          |
| GET    | `/parking/api/report?format=pdf` | Baixa relatório em PDF           |
| GET    | `/parking/api/open-sessions`     | Lista veículos ainda no pátio    |

> **Nota:** o roteamento em `public/index.php` usa o prefixo `/parking`. Caso sirva o projeto em outro contexto, ajuste o valor de `$basePath` e da constante `BASE_PATH` em `Presentation/index.html`.

## Banco de dados

- SQLite armazenado em `storage/parking.db`.
- Estrutura única: tabela `parking_sessions` com os campos necessários (placa, tipo, horários, valor).

## Exportação em PDF

- Implementada no caso de uso `Application\Flow\ExportReportPdf` utilizando **mPDF**.
- A página de relatório (`Infra\Report` + `Presentation/report.html`) também pode ser utilizada diretamente caso queira visualizar em HTML.

## Convenções e boas práticas

- Código formatado seguindo PSR-12.
- Dependências resolvidas por injeção via construtores, mantendo DIP e SRP.
- Validações concentradas nas entidades/fluxos para evitar estados inválidos.
- Comentários apenas onde ajudam a entender decisões importantes.

## Testes e validação

O projeto não possui testes automatizados ainda. Sugestões de próximos passos:

1. Incluir cenários básicos com PHPUnit (entrada duplicada, cálculo de tarifas, geração de relatório).
2. Adicionar verificação de estilo com `phpcs` ou `php-cs-fixer`.
3. Substituir o armazenamento em disco por variáveis de ambiente se desejar parametrizar o caminho do banco.

## Créditos

Projeto acadêmico desenvolvido por integrantes da disciplina, com foco em aplicar princípios de engenharia de software em PHP moderno.
