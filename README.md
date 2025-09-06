# OrionQueue — Fila de Jobs em PHP puro (sem Composer)

OrionQueue é uma fila de processamento de jobs 100% em PHP e SQLite, feita para ser didática e útil. 
Você consegue enfileirar tarefas (HTTP request, comandos locais, funções PHP autorizadas), rodar um worker CLI com retentativas e backoff exponencial, e gerenciar tudo em um painel simples com autenticação e CSRF.

## Por que usar
- Executar tarefas assíncronas sem bloquear sua aplicação web.
- Agendar execuções para o futuro (available_at).
- Repetir automaticamente em caso de falha com controle de tentativas.
- Separar por filas (ex.: default, email, integracoes).
- Zero dependências externas (sem Composer, sem Redis, sem RabbitMQ).

## Recursos
- Painel administrativo (login, CSRF, CRUD básico de jobs, filtros e ações).
- API interna simples para enfileiramento via POST.
- Tipos de job prontos:
  - `http`: chamada HTTP via cURL.
  - `php`: executa função autorizada da lista segura.
  - `command`: comando local (opcional e desabilitado por padrão).
- Worker CLI com:
  - Loop contínuo e fechamento limpo.
  - Backoff exponencial com jitter.
  - Tentativas e limite por job.
  - Logs em arquivo.
- Banco SQLite com migrações automáticas.
- Código organizado, comentado e fácil de estender.

## Como rodar
1. PHP >= 8.0 com SQLite3 habilitado.
2. Iniciar painel:
   ```bash
   php -S 127.0.0.1:8081 -t public
   ```
   Acesse `http://127.0.0.1:8081/`

   Login padrão:
   - Usuário: `admin@local`
   - Senha: `admin123`
   Altere em `config/config.php`.

3. Iniciar o worker (em outro terminal):
   ```bash
   php scripts/worker.php
   ```

## Enfileirando jobs
### Pelo painel
Use o botão "Novo Job" e informe tipo, payload e fila.

### Via HTTP (simples)
```bash
curl -X POST "http://127.0.0.1:8081/enqueue"   -d "type=http"   -d "queue=default"   --data-urlencode 'payload={"url":"https://httpbin.org/get"}'
```

## Estrutura
```
app/            # Código-fonte (DB, Auth, Repo, Worker, Views)
app/Jobs/       # Funções PHP permitidas para job tipo "php"
config/         # Configurações
data/           # Banco SQLite (auto-criado)
logs/           # Logs de execução
public/         # Front-Controller e assets
scripts/        # Worker e utilitários
```

## Segurança
- Login com password_hash/password_verify.
- Sessão com regeneração de ID.
- CSRF token em formulários.
- Lista de funções PHP autorizadas para jobs do tipo "php".
- Execução de comandos locais desabilitada por padrão.

## Limitações
- Um worker por processo (use múltiplos processos para paralelizar).
- Comandos locais dependem do sistema e estão desabilitados por segurança.

## Autor
MicDog (Michael Douglas)

## Licença
MIT — veja `LICENSE`.
