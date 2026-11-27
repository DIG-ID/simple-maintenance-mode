# CLAUDE.md — Simple Maintenance Mode

## Language
- Responder sempre em **português de Portugal** no chat.
- Código, comentários, docblocks, `CHANGELOG.md`, mensagens de commit e strings do admin em **inglês (en-EN)**.

---

## O que é

Plugin de manutenção da dig.id, presente em quase todos os sites live. Mostra uma página de manutenção (HTTP 503) a visitantes anónimos e deixa o site normal a quem tem sessão com permissão.

- É **recomendado e instalado pelo dig.id Theme Starter** (repo `DIG-ID/theme-starter-vite`).
- É **gerido pelo dig.id Control Center** (repo `DIG-ID/control-center`): o agente lê o estado, instala e actualiza o plugin, e mais tarde liga e desliga a manutenção à distância.
- Base: WordPress Plugin Boilerplate (classes `Smm_*`, `Smm_Loader`).
- **WordPress 5.8+, PHP 7.4+** — corre em sites antigos. Cabeçalho do plugin, `README.txt`, `composer.json` e `phpcs.xml.dist` sincronizados.
  - **Porquê 5.8:** é a versão que introduziu o cabeçalho `Update URI` e o filtro `update_plugins_{$hostname}`, de que dependem os updates por GitHub Releases. Não baixar.
  - **Porquê 7.4:** o SMM está em sites antigos que ainda correm PHP 7.4.
- Text domain: `simple-maintenance-mode` (era `smm` até à 1.0.0) · prefixos: `smm_` · `SMM_` · `Smm_`.
- API pública em `includes/smm-functions.php`: `smm_is_enabled()`, `smm_enable()`, `smm_disable()` (sem nonce nem capability — quem chama verifica) e a acção `smm_toggled`, disparada pelos hooks da opção. WP-CLI: `wp smm enable|disable|status`.

---

## Contrato público — NÃO mudar

Há sites em produção e o Control Center já dependem disto. Qualquer mudança aqui é **breaking (MAJOR)** e tem de ser combinada antes.

| O quê | Valor |
|---|---|
| Pasta e ficheiro do plugin | `simple-maintenance-mode/simple-maintenance-mode.php` |
| Opção de estado | `smm_enabled` (`1` ligado / `0` desligado) — o agente do Control Center lê esta opção |
| Opção do Customizer | `smm_customizer` |
| Filtros públicos | `smm_enabled`, `smm_user_can_bypass`, `smm_maintenance_template`, `smm_content_*` |
| Override de template no tema | `themes/<tema>/simple-maintenance-mode/` |
| Resposta com manutenção ligada | HTTP **503** + `Retry-After` + `X-Robots-Tag: noindex` + `DONOTCACHEPAGE` |

Os nomes internos (classes `Smm_*`, funções privadas) podem ser melhorados; os acima não.

**Nunca bloquear** com a manutenção ligada: `wp-login.php`, `/wp-admin/`, `admin-ajax.php`, `wp-cron.php`, WP-CLI, a REST API (`/wp-json/…` e `?rest_route=…` — o agente do Control Center precisa dela) e utilizadores autenticados com bypass. `robots.txt` e sitemaps (core e Yoast) recebem 503 em texto simples, nunca a página HTML.

**Acesso** (decidido em 2026-11-27): ver o site com a manutenção ligada (`smm_user_can_bypass`) = administradores e editores; ligar, desligar e pré-visualizar (`smm_user_can_toggle`) = só `manage_options`.

---

## Comandos

```bash
composer lint:php         # PHPCS — tem de dar 0 erros e 0 warnings antes de um PR
composer lint:php-fix     # PHPCBF
npm ci && npm run prod    # assets de produção
```

Testar no `control-client.digid` (Local), que tem o stack completo dos sites live.

---

## Coding Standards

Todo o PHP segue o **WordPress Coding Standards**: `WordPress-Core`, `WordPress-Docs`, `WordPress-Extra`, mais `PHPCompatibilityWP` (7.4+), text domain `smm` e `PrefixAllGlobals` (ver `phpcs.xml.dist`).

- Nomes de ficheiro WPCS: `class-smm-foo.php`. Classes `Smm_Foo`.
- `.editorconfig`: PHP com tabs (2), JS/CSS/Sass/YAML com 2 espaços, LF.
- Sem sintaxe acima de PHP 7.4 (nada de `match`, named arguments, `readonly`, union types, nullsafe `?->`).
- Um `phpcs:ignore` leva sempre o sniff exacto e o motivo (`-- Constant read by caching plugins, name fixed by them.`).
- Exclusões no `phpcs.xml.dist` só com comentário a justificar.

---

## PHPDoc (obrigatório)

Cada ficheiro PHP começa com file docblock (`@package`, `@subpackage`, `@since`), nunca com comentário `//`:

```php
<?php
/**
 * Decides which requests see the maintenance page.
 *
 * @package simple-maintenance-mode
 * @subpackage Public
 * @since 1.0.0
 */
```

| `@subpackage` | Usar em |
|---|---|
| `Core` | ficheiro principal, `Smm`, loader, i18n, activação, desinstalação |
| `Admin` | admin bar, toggle, notices, assets do admin |
| `Public` | decisão de bloqueio, headers, template da página de manutenção |
| `Customizer` | secção e controlos do Customizer |
| `CLI` | comandos WP-CLI |
| `Helpers` | utilitários |

- `@since` = versão em que o elemento entrou (bate com o `CHANGELOG.md`).
- Funções, métodos e propriedades sempre com docblock: descrição, `@param`, `@return`.
- Hooks públicos (`apply_filters`, `do_action`) com docblock próprio a descrever parâmetros.
- Os docblocks explicam o **porquê** das decisões de segurança.

---

## Segurança (obrigatório)

- **Output:** escaping por tipo (`esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()`); nunca `<?= ?>`. Conteúdo do Customizer é dado de utilizador: escapar sempre.
- **Input:** sanitização, **nonces** (`check_admin_referer()`, `wp_verify_nonce()`) e **`current_user_can()`** antes de qualquer acção.
- **SQL:** sempre `$wpdb->prepare()`; preferir as APIs do WordPress (`get_sites()`, `get_option()`).
- **Cache:** a página de manutenção nunca pode ficar em cache (WP Rocket, SG Optimizer, CDN): `DONOTCACHEPAGE`, `nocache_headers()`.
- **Nunca parte o site:** com o plugin desactivado ou com erro, o site tem de continuar a servir normalmente.

---

## i18n

Strings traduzíveis com o text domain `simple-maintenance-mode`, fonte em **inglês**. Traduções **de_CH** (formal, `ss`, nunca `ß`), **de_DE** (mesmo texto; o WPML dos sites usa muitas vezes `de_DE`) e **pt_PT**, em `languages/` (`.po`, `.mo` e `.l10n.php`). O Customizer pode sobrepor os textos da página. Comandos para regenerar no `README.md`.

---

## Versionamento & Changelog

SemVer, `CHANGELOG.md` em Keep a Changelog (inglês).

| Alteração | Incremento |
|---|---|
| Correcção / ajuste | PATCH |
| Funcionalidade nova | MINOR |
| Breaking (qualquer mudança ao contrato público) | MAJOR |

- A versão vive no cabeçalho do plugin, na constante `SMM_VERSION`, no `Stable tag` do `README.txt` e na última entrada do `CHANGELOG.md`; têm de coincidir.
- **Em cada tarefa**, acrescentar as linhas ao `CHANGELOG.md` em `## [Unreleased]` (Added / Changed / Fixed / Removed / Deprecated / Security). Ao fechar uma versão, passam para `## [X.Y.Z] — YYYY-MM-DD` e o commit é `vX.Y.Z: short summary`.
- Release: tag `vX.Y.Z` → a GitHub Action valida, compila e publica `simple-maintenance-mode.zip` e `simple-maintenance-mode.zip.sha256`.

---

## Git

- `main` está sempre estável. Cada tarefa numa branch curta (`chore/…`, `feature/…`, `fix/…`, `build/…`, `docs/…`), de volta à `main` por PR com o lint a passar.
- **Nunca** fazer push, merge ou criar tags sem confirmação explícita.

---

## Convenções gerais

- Nada de dependências npm ou Composer novas sem confirmação explícita.
- Fonte de autoridade: documentação oficial (Plugin Handbook, Developer Resources, Coding Standards). Em APIs recentes, consultar em vez de assumir — e confirmar que existem em WordPress 5.8.
- Boy-scout rule: qualquer ficheiro tocado fica a cumprir estas regras.
