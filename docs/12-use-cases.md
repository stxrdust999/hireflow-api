# 12 — Casos de Uso

Os casos de uso descrevem fluxos reais e completos de como as personas interagem com o HireFlow. Cada fluxo narra uma situação do início ao fim, mostrando o que acontece no sistema a cada passo.

---

## UC-01 — Publicar uma nova vaga

**Persona:** Ana Souza (Recruiter)  
**Contexto:** A TechNova precisa contratar um Desenvolvedor Backend Sênior. Ana recebeu o pedido de Carlos e vai abrir a vaga no HireFlow.

### Fluxo

1. Ana acessa o painel interno e navega para **Vagas → Nova Vaga**
2. Preenche o formulário:
    - Título: _Desenvolvedor Backend Sênior_
    - Descrição: requisitos, responsabilidades e benefícios
    - Tipo: _Tempo integral_
    - Localização: _Remoto_
3. Salva como **rascunho** (`status: draft`) para revisar depois
4. No dia seguinte, revisa, ajusta a descrição e clica em **Publicar**
5. O sistema muda o status para `published` e cria automaticamente as 5 etapas padrão do pipeline
6. A vaga aparece imediatamente no portal público

**O que acontece no sistema:**

- `POST /api/v1/job-openings` — cria a vaga com `status: draft`
- `PATCH /api/v1/job-openings/{id}/publish` — publica a vaga
- Criação automática das `job_stages` padrão associadas à vaga

---

## UC-02 — Candidata se inscreve em uma vaga

**Persona:** Mariana Costa (Candidate)  
**Contexto:** Mariana encontrou a vaga de Backend Sênior no portal público do HireFlow. Ela sabe que não é backend, mas a empresa tem uma cultura que ela admira e decidiu tentar.

### Fluxo

1. Mariana acessa `hireflow-web` e navega pelo portal público
2. Filtra por _Remoto_ e encontra a vaga da TechNova
3. Lê a descrição completa e clica em **Candidatar-se**
4. Como não tem conta, o sistema oferece:
    - Criar conta com email e senha
    - Entrar com LinkedIn _(caminho que ela escolhe)_
5. Após autenticação via LinkedIn, o sistema cria sua conta com role `candidate`
6. Retorna para a página da vaga, já autenticada
7. Faz upload do currículo em PDF e confirma a candidatura
8. Recebe notificação: _"Sua candidatura para Desenvolvedor Backend Sênior foi recebida."_
9. No painel do candidato, vê sua candidatura com status **Pendente** na etapa **Triagem**

**O que acontece no sistema:**

- OAuth LinkedIn via `GET /api/v1/auth/linkedin/redirect` → callback → token Sanctum gerado
- `POST /api/v1/job-openings/{id}/applications` — cria candidatura com `status: pending`, `current_stage: Triagem`
- Notificação inserida na tabela `notifications`
- E-mail enfileirado no Redis para envio assíncrono

---

## UC-03 — Recrutadora avança candidato no pipeline

**Persona:** Ana Souza (Recruiter)  
**Contexto:** Ana tem 12 candidaturas novas na vaga de Backend Sênior. Após triagem dos currículos, decide avançar 3 candidatos para a Entrevista RH — incluindo Mariana, mesmo sendo frontend, pois seu perfil é diferenciado.

### Fluxo

1. Ana acessa **Vagas → Desenvolvedor Backend Sênior → Candidaturas**
2. Visualiza os 12 candidatos na etapa de Triagem
3. Abre o perfil de Mariana, visualiza o currículo e deixa um comentário interno:
    > _"Perfil frontend, mas portfólio excelente. Vale conversar sobre possível fit em produto."_
4. Clica em **Avançar para Entrevista RH**
5. O sistema move Mariana para a próxima etapa
6. Mariana recebe notificação: _"Você avançou para a etapa Entrevista RH na vaga Desenvolvedor Backend Sênior."_
7. Ana repete o processo para os outros 2 candidatos selecionados
8. Os 9 restantes ficam em Triagem. Ana marca alguns como reprovados — eles recebem notificação de encerramento

**O que acontece no sistema:**

- `POST /api/v1/applications/{id}/comments` — comentário interno criado
- `PATCH /api/v1/applications/{id}/stage` — `current_stage_id` atualizado, `status` muda para `in_progress`
- Registro inserido em `application_stage_logs` com `moved_by: Ana`, `moved_at: now()`
- Notificação disparada para Mariana

---

## UC-04 — Hiring Manager avalia candidato na etapa técnica

**Persona:** Carlos Mendes (Hiring Manager)  
**Contexto:** Após as entrevistas de RH, Ana avançou Mariana para a Entrevista Técnica. Carlos recebe uma notificação e acessa o HireFlow para avaliar.

### Fluxo

1. Carlos recebe notificação: _"Há 2 candidatos aguardando avaliação técnica na vaga Desenvolvedor Backend Sênior."_
2. Acessa o painel interno — vê apenas as vagas sob sua responsabilidade
3. Abre a candidatura de Mariana, lê o currículo e o comentário da Ana
4. Realiza a entrevista técnica fora do sistema (videochamada)
5. Volta ao HireFlow e deixa comentário interno:
    > _"Boa comunicação, raciocínio lógico sólido. Conhecimento de backend limitado mas demonstrou interesse real em aprender. Recomendo avançar para proposta com salário na faixa júnior de backend."_
6. Sinaliza para Ana (via comentário) que pode avançar
7. Ana, com acesso de recruiter, move Mariana para **Proposta**

**O que acontece no sistema:**

- Carlos acessa `GET /api/v1/job-openings/{id}/applications` — retorna apenas candidatos das suas vagas (filtrado pela Policy)
- `POST /api/v1/applications/{id}/comments` — comentário de Carlos registrado com `author_id: Carlos`
- Ana executa `PATCH /api/v1/applications/{id}/stage` — movendo para Proposta

---

## UC-05 — Candidata retira a própria candidatura

**Persona:** Mariana Costa (Candidate)  
**Contexto:** Enquanto aguardava a etapa de Proposta no HireFlow, Mariana recebeu e aceitou uma oferta de outra empresa. Ela decide retirar sua candidatura educadamente.

### Fluxo

1. Mariana acessa o painel do candidato
2. Localiza a candidatura na TechNova, na etapa **Proposta**
3. Clica em **Retirar candidatura** e confirma
4. O sistema muda o status para `withdrawn`
5. Ana recebe uma notificação: _"O candidato Mariana Costa retirou a candidatura para Desenvolvedor Backend Sênior."_

**O que acontece no sistema:**

- `PATCH /api/v1/applications/{id}/withdraw` — `status` muda para `withdrawn`
- Notificação disparada para Ana

---

## UC-06 — Admin cria conta para novo recrutador

**Persona:** Rafael Lima (Admin)  
**Contexto:** A TechNova contratou uma nova recrutadora, Beatriz, que vai assumir vagas de design. Rafael precisa criar sua conta no HireFlow.

### Fluxo

1. Rafael acessa o **Painel de Administração → Usuários → Novo Usuário**
2. Preenche nome, e-mail e define uma senha temporária
3. Seleciona a role `recruiter`
4. Salva — Beatriz recebe um e-mail com seus dados de acesso e instruções para trocar a senha
5. Beatriz faz login, troca a senha e já consegue criar vagas

**O que acontece no sistema:**

- `POST /api/v1/admin/users` — usuário criado com role `recruiter`
- E-mail de boas-vindas enfileirado no Redis

---

## UC-07 — Recrutadora consulta métricas de uma vaga

**Persona:** Ana Souza (Recruiter)  
**Contexto:** A vaga de Backend Sênior foi encerrada com uma contratação. Ana quer entender quanto tempo durou o processo e onde houve gargalos.

🚧 _Dashboard de métricas pendente de implementação — fluxo definido._

### Fluxo

1. Ana acessa **Vagas → Desenvolvedor Backend Sênior → Métricas**
2. O dashboard exibe:
    - Total de candidaturas recebidas: 12
    - Taxa de conversão por etapa (ex: 12 na triagem → 3 na entrevista RH → 2 na técnica → 1 contratado)
    - Tempo médio em cada etapa
    - Tempo total do processo: 23 dias
3. Ana exporta o relatório em CSV para apresentar ao time de RH

**O que acontece no sistema:**

- `GET /api/v1/job-openings/{id}/metrics` — dados agregados dos `application_stage_logs`
- Resultado cacheado no Redis para evitar queries pesadas repetidas

---

## Resumo dos casos de uso

| #     | Caso de uso                                      | Personas envolvidas                               |
| ----- | ------------------------------------------------ | ------------------------------------------------- |
| UC-01 | Publicar uma nova vaga                           | Ana (Recruiter)                                   |
| UC-02 | Candidata se inscreve em uma vaga                | Mariana (Candidate)                               |
| UC-03 | Recrutadora avança candidato no pipeline         | Ana (Recruiter), Mariana (Candidate)              |
| UC-04 | Hiring Manager avalia candidato na etapa técnica | Carlos (HM), Ana (Recruiter), Mariana (Candidate) |
| UC-05 | Candidata retira a própria candidatura           | Mariana (Candidate), Ana (Recruiter)              |
| UC-06 | Admin cria conta para novo recrutador            | Rafael (Admin)                                    |
| UC-07 | Recrutadora consulta métricas de uma vaga 🚧     | Ana (Recruiter)                                   |
