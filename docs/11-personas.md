# 11 — Personas

As personas do HireFlow representam os tipos de usuário do sistema. São perfis fictícios mas realistas, criados para guiar decisões de produto e ilustrar casos de uso de forma humana e concreta. **Não fazem sentido NENHUM sem os casos de uso**.

---

## Ana Souza — Recrutadora

![Persona: Ana Souza](https://placehold.co/80x80?text=AS)

> _"Preciso saber exatamente onde cada candidato está no processo. Quando uso planilha, sempre tem alguém que esquece de atualizar."_

|                      |                                  |
| -------------------- | -------------------------------- |
| **Cargo**            | Recrutadora Sênior               |
| **Empresa**          | TechNova Soluções                |
| **Idade**            | 31 anos                          |
| **Experiência**      | 6 anos em RH e recrutamento tech |
| **Role no HireFlow** | `recruiter`                      |

### Rotina

Ana gerencia de 5 a 10 vagas abertas simultaneamente. Ela é responsável por publicar vagas, triagem de currículos, entrevistas de RH e coordenar o processo com os gestores das áreas. Passa boa parte do dia no painel interno do HireFlow.

### Objetivos

- Ter visibilidade clara de todos os candidatos em todas as vagas ao mesmo tempo
- Mover candidatos pelo pipeline rapidamente sem perder contexto
- Registrar observações sobre candidatos para consultar depois
- Garantir que nenhum candidato fique sem resposta por tempo excessivo

### Frustrações

- Planilhas desatualizadas onde ninguém sabe o status real de cada candidato
- Comunicação sobre candidatos espalhada em e-mails, Slack e anotações pessoais
- Não conseguir saber quanto tempo um candidato ficou parado em uma etapa
- Hiring Managers que não dão feedback sobre entrevistas técnicas

### Como usa o HireFlow

Ana abre o painel interno toda manhã, verifica candidaturas novas, move candidatos de etapa, deixa comentários internos para o Hiring Manager e fecha vagas quando a posição é preenchida.

---

## Carlos Mendes — Hiring Manager

> _"Não preciso ver tudo, só o que é da minha área. E quero poder deixar meu feedback sem depender de ninguém."_

|                      |                                           |
| -------------------- | ----------------------------------------- |
| **Cargo**            | Gerente de Engenharia                     |
| **Empresa**          | TechNova Soluções                         |
| **Idade**            | 38 anos                                   |
| **Experiência**      | 12 anos em desenvolvimento, 3 como gestor |
| **Role no HireFlow** | `hiring-manager`                          |

### Rotina

Carlos lidera um time de 8 engenheiros e está constantemente envolvido em contratações para expandir a equipe. Não é usuário frequente do HireFlow — acessa quando Ana o notifica que há candidatos aguardando avaliação técnica.

### Objetivos

- Ver rapidamente os candidatos que estão na etapa técnica das suas vagas
- Deixar feedback objetivo sobre cada candidato para a Ana
- Não ter acesso a informações de outras áreas que não são de sua responsabilidade

### Frustrações

- Receber currículos por e-mail sem contexto de qual vaga é
- Não ter um lugar centralizado para registrar sua avaliação técnica
- Sistemas complexos demais que exigem treinamento para tarefas simples

### Como usa o HireFlow

Carlos acessa o painel quando recebe notificação de que há candidatos na etapa de Entrevista Técnica. Visualiza o currículo, faz a entrevista, e depois volta ao HireFlow para deixar um comentário interno e pedir para a Ana mover o candidato.

---

## Mariana Costa — Candidata

> _"Odeio mandar currículo e não saber mais nada. Fico sem saber se devo esperar ou já desistir."_

|                      |                                                        |
| -------------------- | ------------------------------------------------------ |
| **Perfil**           | Desenvolvedora Frontend Pleno em transição de carreira |
| **Idade**            | 26 anos                                                |
| **Experiência**      | 3 anos como dev, buscando posição em produto           |
| **Role no HireFlow** | `candidate`                                            |

### Rotina

Mariana está ativamente procurando emprego. Manda entre 3 e 5 candidaturas por semana em diferentes plataformas. Usa o LinkedIn com frequência e prefere processos seletivos transparentes onde consegue acompanhar seu status.

### Objetivos

- Encontrar vagas que correspondam ao seu perfil
- Se candidatar de forma simples, sem preencher o mesmo formulário 15 vezes
- Saber em qual etapa sua candidatura está em tempo real
- Receber feedback quando for reprovada

### Frustrações

- Mandar currículo e nunca receber resposta (ghosting)
- Não saber se está sendo avaliada ou se seu currículo nem foi aberto
- Criar conta em dezenas de plataformas diferentes com formulários longos
- Processos que demoram meses sem nenhuma comunicação

### Como usa o HireFlow

Mariana acessa o portal público, filtra vagas por tipo e localização, lê a descrição e se candidata fazendo upload do currículo. Depois, acompanha o status de cada candidatura pelo painel do candidato e recebe notificações a cada movimentação no pipeline.

---

## Rafael Lima — Administrador

> _"Meu trabalho é garantir que o sistema funcione e que cada pessoa tenha acesso exatamente ao que precisa — nem mais, nem menos."_

|                      |                                                               |
| -------------------- | ------------------------------------------------------------- |
| **Cargo**            | Tech Lead / Responsável pelo sistema                          |
| **Empresa**          | TechNova Soluções                                             |
| **Idade**            | 34 anos                                                       |
| **Experiência**      | 10 anos em TI, cuida da infraestrutura e ferramentas internas |
| **Role no HireFlow** | `admin`                                                       |

### Rotina

Rafael não participa diretamente do processo de recrutamento. Ele configura o sistema, cria contas para novos recrutadores e gestores, e resolve problemas quando algo não funciona como esperado. Acessa o HireFlow com pouca frequência, mas precisa de acesso total quando o faz.

### Objetivos

- Criar e gerenciar usuários internos sem depender de deploys
- Atribuir roles corretamente para que cada pessoa veja só o que deve
- Ter visibilidade completa de tudo que acontece no sistema quando necessário
- Configurar a empresa e manter os dados cadastrais atualizados

### Frustrações

- Sistemas onde criar um novo usuário exige mexer em arquivos de configuração
- Falta de log de auditoria — não saber quem fez o quê quando algo dá errado
- Permissões mal definidas que expõem dados sensíveis para quem não deveria ver

### Como usa o HireFlow

Rafael acessa o painel de administração para criar contas de novos recrutadores e hiring managers, atribuir roles, gerenciar a empresa cadastrada e consultar logs quando necessário.

---

## Resumo das personas

| Persona       | Role             | Frequência de uso | Portal                               |
| ------------- | ---------------- | ----------------- | ------------------------------------ |
| Ana Souza     | `recruiter`      | Diária            | Painel interno                       |
| Carlos Mendes | `hiring-manager` | Semanal           | Painel interno (restrito)            |
| Mariana Costa | `candidate`      | Pontual           | Portal público + painel do candidato |
| Rafael Lima   | `admin`          | Ocasional         | Painel de administração              |

Para ver como essas personas interagem entre si em fluxos reais, veja [Casos de Uso](./11-use-cases.md).
