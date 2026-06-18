# 01 — Introdução

## O que é o HireFlow?

HireFlow é um **sistema de rastreamento de candidatos** (ATS — Applicant Tracking System) desenvolvido para gerenciar todo o ciclo de recrutamento de uma empresa: desde a abertura de uma vaga até a contratação do candidato.

O sistema possui dois ambientes distintos que coexistem:

- **Portal público** — onde candidatos buscam vagas abertas, se inscrevem e acompanham o andamento de suas candidaturas
- **Painel interno** — onde equipes de RH, recrutadores e gestores gerenciam vagas, avaliam candidatos e movem pessoas pelo pipeline de seleção

---

## Qual problema o HireFlow resolve?

Empresas que recebem muitas candidaturas rapidamente perdem o controle do processo seletivo quando dependem de planilhas, e-mails ou ferramentas genéricas. Sem um sistema dedicado, problemas comuns surgem:

- Candidatos ficam sem resposta por semanas porque ninguém sabe em que etapa estão
- Recrutadores não sabem quem já foi entrevistado e quem ainda está na triagem
- Gestores não têm visibilidade sobre o andamento das vagas sob sua responsabilidade
- Não há histórico de por que um candidato foi reprovado ou avançado
- Comunicação interna sobre um candidato acontece por e-mail, se perde e não fica registrada

O HireFlow centraliza tudo isso em um único lugar, com rastreabilidade completa de cada candidatura.

---

## Quem usa o HireFlow?

O sistema é projetado para quatro tipos de usuários, cada um com responsabilidades e acessos distintos:

### Candidato
Pessoa externa à empresa que está em busca de uma oportunidade. Acessa o portal público, visualiza as vagas disponíveis, se inscreve e acompanha o status da sua candidatura.

### Recrutador
Profissional de RH responsável por criar e publicar vagas, receber as candidaturas e conduzir as primeiras etapas do processo seletivo (triagem e entrevista de RH). Tem acesso ao painel interno.

### Hiring Manager
Gestor da área que abriu a vaga (ex: gerente de engenharia, head de produto). Participa das etapas técnicas do processo, avalia candidatos nas vagas sob sua responsabilidade e adiciona comentários internos. Tem acesso restrito ao painel interno — apenas às vagas que lhe dizem respeito.

### Administrador
Responsável pela configuração e manutenção do sistema. Gerencia usuários, atribui roles e tem acesso irrestrito a todas as funcionalidades.

---

## Como o HireFlow funciona, em linhas gerais?

1. Um **recrutador** cria uma vaga com título, descrição, tipo de contrato e localização
2. A vaga é publicada e aparece no **portal público**
3. Um **candidato** se inscreve, fazendo upload do currículo
4. A candidatura entra na primeira etapa do pipeline: **Triagem**
5. O recrutador avalia e move o candidato para a próxima etapa: **Entrevista RH**
6. Após a entrevista, o recrutador pode avançar para **Entrevista Técnica**, onde o **Hiring Manager** assume
7. A cada movimentação, o candidato recebe uma notificação com o novo status
8. Todo o histórico de movimentações fica registrado para auditoria
9. O processo termina com o candidato sendo marcado como **Contratado** ou tendo sua candidatura encerrada

---

## O que o HireFlow não é

- Não é uma plataforma de vagas pública como LinkedIn ou Indeed — é um sistema interno de gestão, com um portal de candidatura próprio
- Não é uma ferramenta de RH completa — não cobre onboarding, folha de pagamento ou avaliação de desempenho
- Não é um sistema multi-tenant — foi projetado para ser utilizado por uma única empresa

---

## Contexto de desenvolvimento

O HireFlow é um projeto de portfólio desenvolvido individualmente com fins de aprendizado e demonstração técnica. O objetivo é cobrir de forma completa e profissional uma stack fullstack real utilizada em ambiente corporativo, tratando o projeto com a seriedade de um produto entregável.

Para entender como o sistema foi construído tecnicamente, veja [Arquitetura](./02-architecture.md).
