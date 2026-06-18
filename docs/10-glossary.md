# 10 — Glossário

Este glossário reúne termos técnicos e de domínio utilizados no HireFlow e em sua documentação. Organizado em duas seções: termos de domínio (negócio) e termos técnicos (tecnologia).

---

## Termos de domínio

**ATS (Applicant Tracking System)**
Sistema de rastreamento de candidatos. Software utilizado por empresas para gerenciar o processo de recrutamento — desde a publicação de vagas até a contratação. O HireFlow é um ATS.

**Candidatura (Application)**
O ato de um candidato se inscrever em uma vaga. No HireFlow, uma candidatura é um registro que conecta um candidato a uma vaga e acompanha o progresso desse candidato pelo pipeline.

**Candidato (Candidate)**
Usuário externo que busca oportunidades de emprego. Acessa o portal público, se inscreve em vagas e acompanha o status de suas candidaturas.

**Etapa (Stage)**
Uma fase específica do pipeline de seleção. Exemplos: Triagem, Entrevista RH, Entrevista Técnica. Cada vaga possui suas próprias etapas, ordenadas sequencialmente.

**Hiring Manager**
Gestor da área que abriu a vaga. Responsável por avaliar candidatos nas etapas técnicas do processo. No HireFlow, tem acesso restrito apenas às vagas sob sua responsabilidade.

**Pipeline**
O fluxo sequencial de etapas que um candidato percorre durante o processo seletivo. Visualizado como um funil: muitos candidatos entram na triagem, poucos chegam à proposta.

**Portal público**
A parte do HireFlow acessível sem login. Candidatos podem navegar pelas vagas publicadas e se inscrever. Contrasta com o painel interno, restrito a usuários da empresa.

**Painel interno (Dashboard)**
A interface do HireFlow acessível apenas para usuários internos (Admin, Recruiter, Hiring Manager). É onde as vagas são gerenciadas e os candidatos são avaliados.

**Recrutador (Recruiter)**
Profissional de RH responsável por criar vagas, receber candidaturas e conduzir as primeiras etapas do processo seletivo.

**Role (Papel)**
O papel de um usuário no sistema, que determina o que ele pode ver e fazer. No HireFlow: `admin`, `recruiter`, `hiring-manager`, `candidate`.

**Vaga (Job Opening)**
Uma posição aberta em uma empresa. Possui título, descrição, tipo de contrato, localização e um status que controla sua visibilidade (`draft`, `published`, `closed`).

---

## Termos técnicos

**API (Application Programming Interface)**
Interface de comunicação entre sistemas. No HireFlow, a API é o Laravel — ela expõe endpoints HTTP que o frontend Next.js consome para buscar e enviar dados.

**Cascata (Cascade)**
Comportamento de banco de dados onde a deleção de um registro pai automaticamente deleta os registros filhos relacionados. Exemplo: deletar uma vaga deleta todas as suas candidaturas.

**CORS (Cross-Origin Resource Sharing)**
Mecanismo de segurança dos navegadores que controla quais origens (domínios) podem fazer requisições para uma API. No HireFlow, somente o `hireflow-web` poderá consumir a API em produção.

**Docker**
Plataforma de containerização. No HireFlow, é usado para rodar MySQL e Redis em containers isolados do sistema operacional host, garantindo que o ambiente seja idêntico em qualquer máquina.

**Eloquent**
ORM (Object-Relational Mapper) nativo do Laravel. Permite interagir com o banco de dados usando classes PHP (Models) em vez de escrever SQL diretamente.

**Enum**
Tipo de dado que restringe um campo a um conjunto fixo de valores. Exemplo: o campo `status` de uma vaga só pode ser `draft`, `published` ou `closed` — nenhum outro valor é aceito.

**Factory**
Classe do Laravel que define como gerar dados falsos para um Model. Usada em seeders e testes. Exemplo: `JobOpeningFactory` gera vagas com títulos, descrições e tipos aleatórios.

**Foreign Key (FK)**
Coluna em uma tabela que referencia a chave primária de outra tabela, estabelecendo uma relação entre elas. Exemplo: `applications.job_id` é uma FK que referencia `job_openings.id`.

**Form Request**
Classe do Laravel que encapsula a validação e autorização de uma requisição HTTP. Mantém os controllers limpos ao separar a responsabilidade de validar dados.

**Herd**
Ferramenta da equipe do Laravel para desenvolvedores Windows e Mac. Instala e gerencia PHP, Nginx e outros serviços necessários para rodar projetos Laravel localmente com configuração mínima.

**HTTP Status Code**
Código numérico retornado por uma API para indicar o resultado de uma requisição. Exemplos: `200` (sucesso), `201` (criado), `401` (não autenticado), `403` (sem permissão), `404` (não encontrado).

**JWT (JSON Web Token)**
Formato de token compacto e autocontido usado para transmitir informações de autenticação. Diferente do Sanctum (que usa tokens opacos armazenados no banco), JWTs carregam os dados do usuário codificados no próprio token.

**Kebab-case**
Convenção de nomenclatura onde palavras são separadas por hífens e escritas em minúsculo. Exemplo: `job-openings`, `hiring-manager`. Usada nas URLs da API do HireFlow.

**Middleware**
Camada intermediária que intercepta requisições HTTP antes de chegarem ao controller. No HireFlow, `auth:sanctum` verifica se o token é válido e `CheckRole` verifica se o usuário tem a role necessária.

**Migration**
Arquivo PHP que descreve uma alteração na estrutura do banco de dados (criar tabela, adicionar coluna, etc.). Funciona como controle de versão do banco — pode ser aplicado (`migrate`) ou revertido (`rollback`).

**Model**
Classe PHP que representa uma tabela do banco de dados e define como a aplicação interage com ela: quais campos são preenchíveis, como se relaciona com outros models, quais transformações aplicar nos dados.

**OAuth**
Protocolo de autorização que permite que um usuário autentique em um serviço usando suas credenciais de outro serviço (ex: "Entrar com LinkedIn"). No HireFlow, implementado via Laravel Socialite.

**Orval**
Ferramenta que lê a especificação Swagger/OpenAPI de uma API e gera automaticamente clients HTTP tipados em TypeScript. No HireFlow, os clients do frontend são gerados pelo Orval a partir do Swagger do Laravel.

**PascalCase**
Convenção de nomenclatura onde cada palavra começa com letra maiúscula, sem separadores. Exemplo: `JobOpeningController`, `ApplicationStageLog`. Usada em classes PHP e componentes React.

**Pivot (Tabela pivot)**
Tabela intermediária que implementa uma relação muitos-para-muitos (N:N) entre duas tabelas. No HireFlow, `user_roles` é a tabela pivot entre `users` e `roles`.

**Policy**
Classe do Laravel que centraliza as regras de autorização para um Model específico. Define quem pode criar, ver, editar ou deletar cada recurso, considerando o usuário autenticado e o recurso em questão.

**RBAC (Role-Based Access Control)**
Modelo de controle de acesso onde as permissões são associadas a roles (papéis), e os usuários recebem roles. É o modelo adotado pelo HireFlow.

**Redis**
Banco de dados em memória, extremamente rápido. No HireFlow, é usado como driver de filas (processamento assíncrono de e-mails e notificações) e como cache de listagens pesadas.

**REST (Representational State Transfer)**
Estilo arquitetural para APIs que usa os métodos HTTP (`GET`, `POST`, `PUT`, `PATCH`, `DELETE`) de forma semântica para operar sobre recursos. A API do HireFlow é REST.

**Sanctum**
Pacote oficial do Laravel para autenticação de SPAs e APIs via tokens. Gera tokens de acesso armazenados no banco que são enviados pelo cliente no header `Authorization`.

**Seeder**
Classe do Laravel que popula o banco de dados com dados iniciais ou de teste. No HireFlow, os seeders criam as roles padrão, uma empresa de exemplo e usuários de teste.

**Slug**
Versão simplificada de um texto, adequada para uso em URLs. Contém apenas letras minúsculas, números e hífens. Exemplo: `hiring-manager`, `acme-corp`. Derivado do nome, mas estável — não muda se o nome mudar.

**snake_case**
Convenção de nomenclatura onde palavras são separadas por underscores e escritas em minúsculo. Exemplo: `job_openings`, `created_by`. Usada em nomes de tabelas e colunas do banco de dados.

**Socialite**
Pacote oficial do Laravel que simplifica a autenticação OAuth com provedores externos (LinkedIn, Google, GitHub, etc.). No HireFlow, usado para o login com LinkedIn.

**SoftDelete**
Mecanismo do Laravel que, ao invés de deletar um registro do banco fisicamente, preenche o campo `deleted_at` com a data atual. O registro "some" das queries normais mas permanece no banco, permitindo recuperação.

**Swagger / OpenAPI**
Especificação padrão da indústria para documentar APIs REST. No HireFlow, gerada automaticamente pelo L5-Swagger a partir de anotações nos controllers. Pode ser visualizada em `http://hireflow-api.test/api/documentation`.

**UUID (Universally Unique Identifier)**
Identificador único de 128 bits, representado como string no formato `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`. Diferente de IDs sequenciais (1, 2, 3...), UUIDs são imprevisíveis e seguros para expor em URLs públicas.

**Versionamento de API**
Prática de incluir um número de versão na URL da API (`/api/v1/`). Permite que mudanças breaking sejam introduzidas em uma nova versão (`/api/v2/`) sem afetar clientes que ainda usam a versão anterior.
