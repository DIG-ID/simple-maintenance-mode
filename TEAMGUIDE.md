# 📘 Guia da Equipa: Simple Maintenance Mode

**Versão:** 1.0.0  
**Última atualização:** Fevereiro 2024  
**Para uso interno da dig.id**

---

## 🎯 O Que É Este Plugin?

O **Simple Maintenance Mode** é um plugin WordPress desenvolvido internamente pela dig.id para colocar sites em modo de manutenção com um clique. É usado quando:

- Estamos a fazer atualizações ao site
- Precisamos de esconder o site temporariamente dos visitantes
- Estamos a trabalhar em funcionalidades novas
- Há problemas técnicos a resolver

---

## 🚀 Como Usar (Guia Rápido)

### Ativar Modo Manutenção

1. Faz login no WordPress do site
2. Procura o botão na **Admin Bar** (barra preta no topo)
3. Clica no botão verde "Maintenance OFF"
4. O botão fica vermelho → Site em manutenção! ✅

### Desativar Modo Manutenção

1. Clica no botão vermelho "Maintenance ON"
2. O botão fica verde → Site está live! ✅

### Ver Preview da Página de Manutenção

**Opção 1:** Clica em "Ver Preview" no dropdown do botão  
**Opção 2:** Adiciona `?smm_preview=1` no fim de qualquer URL

---

## 👥 Quem Vê O Quê?

| Pessoa | O Que Vê |
|--------|----------|
| **Visitantes (não logados)** | Página de manutenção (erro 503) |
| **Administradores** | Site normal; ligam e desligam a manutenção e vêem o preview |
| **Editores** | Site normal e o estado "Maintenance ON" na admin bar, mas **não** ligam nem desligam (desde a 1.1.0; filtro `smm_user_can_toggle` para abrir a excepção) |
| **Página de login, wp-admin, WP Cron, WP-CLI** | Sempre acessíveis (para ninguém ficar bloqueado) |
| **REST API** | Sempre acessível (o agente do Control Center precisa dela) |
| **robots.txt e sitemaps** | 503 em texto simples |

Também se liga e desliga por WP-CLI: `wp smm enable`, `wp smm disable`, `wp smm status`.

---

## 🎨 Personalizar o Template

### Método Recomendado (Não Perdes Alterações)

1. **Cria a pasta no tema ativo:**
   ```
   /wp-content/themes/NOME-DO-TEMA/simple-maintenance-mode/
   ```

2. **Copia o template padrão:**
   ```
   De: /wp-content/plugins/simple-maintenance-mode/public/partials/smm-public-display.php
   Para: /wp-content/themes/NOME-DO-TEMA/simple-maintenance-mode/maintenance.php
   ```

3. **Edita o ficheiro copiado** - As tuas alterações ficam guardadas mesmo quando atualizas o plugin!

### Método Direto (Perdes Alterações)

Edita diretamente:
```
/wp-content/plugins/simple-maintenance-mode/public/partials/smm-public-display.php
```

⚠️ **Atenção:** Ao atualizar o plugin, perdes as alterações!

---

## 🔌 Integração com ACF Pro

### Setup Inicial (Fazer 1 vez por site)

**1. Cria a página de opções** (adiciona ao `functions.php` do tema):

```php
if( function_exists('acf_add_options_page') ) {
    acf_add_options_page(array(
        'page_title' => 'Definições de Manutenção',
        'menu_title' => 'Manutenção',
        'menu_slug'  => 'smm-options',
        'capability' => 'manage_options',
        'redirect'   => false
    ));
}
```

**2. Cria os campos ACF** (na página de opções):

| Nome do Campo | Tipo | Descrição |
|---------------|------|-----------|
| `smm_title` | Text | Título principal |
| `smm_description` | Textarea/WYSIWYG | Mensagem de manutenção |
| `smm_address` | Text | Morada da empresa |
| `smm_email` | Email | Email de contacto |
| `smm_phone` | Text | Telefone de contacto |
| `smm_facebook` | URL | Link Facebook |
| `smm_instagram` | URL | Link Instagram |
| `smm_linkedin` | URL | Link LinkedIn |

**3. Usa os campos no template:**

```php
<h1><?php echo smm_get_content( 'title', 'Em Manutenção' ); ?></h1>
<p><?php echo smm_get_content( 'description', 'Voltamos brevemente' ); ?></p>
<p>Email: <?php echo smm_get_content( 'email', 'info@empresa.com' ); ?></p>
```

**Como funciona:**
- Se o campo ACF existir → usa o valor do ACF
- Se não existir → usa o valor por defeito (segundo parâmetro)

---

## 💬 Usar Shortcodes no Template

Podes usar qualquer shortcode WordPress no template!

### Exemplos Comuns:

**Contact Form 7:**
```php
<?php echo do_shortcode( '[contact-form-7 id="123" title="Formulário"]' ); ?>
```

**WooCommerce:**
```php
<?php echo do_shortcode( '[products limit="4"]' ); ?>
```

**Qualquer Outro Plugin:**
```php
<?php echo do_shortcode( '[nome_do_shortcode]' ); ?>
```

💡 **Dica:** Os estilos e scripts dos shortcodes carregam automaticamente porque o template tem `wp_head()` e `wp_footer()`.

---

## 🛠️ Casos de Uso Práticos

### Cenário 1: Atualização Planeada

```
1. Avisa o cliente com antecedência
2. No dia da atualização: Ativa o modo manutenção
3. Faz as atualizações necessárias
4. Testa tudo
5. Desativa o modo manutenção
```

### Cenário 2: Problema Urgente no Site

```
1. Ativa modo manutenção IMEDIATAMENTE
2. Resolve o problema com calma
3. Testa a correção
4. Desativa quando tudo estiver OK
```

### Cenário 3: Desenvolvimento de Nova Funcionalidade

```
1. Ativa modo manutenção em staging/produção
2. Desenvolve e testa a funcionalidade
3. Quando estiver pronta, desativa
```

---

## 🚨 Resolução de Problemas

### Problema: Botão não aparece na Admin Bar
**Solução:** Verifica se:
- Estás logado
- Tens permissões de administrador
- O plugin está ativado

### Problema: Não consigo aceder ao admin
**Solução:** 
- Vai diretamente a `seusite.com/wp-admin/`
- Ou `seusite.com/wp-login.php`
- Estas páginas estão SEMPRE acessíveis

### Problema: Estilos não aparecem na página de manutenção
**Solução:**
- Verifica se o tema tem `wp_head()` e `wp_footer()`
- Confirma que o CSS está a compilar (`npm run production`)

### Problema: Shortcodes não funcionam
**Solução:**
- Confirma que o template tem `wp_head()` e `wp_footer()`
- Verifica se o plugin do shortcode está ativo

### Problema: Cliente ficou bloqueado
**Solução:**
1. Acede via FTP/SSH
2. Desativa o plugin renomeando a pasta:
   ```
   /wp-content/plugins/simple-maintenance-mode 
   para
   /wp-content/plugins/simple-maintenance-mode-disabled
   ```
3. Ou acede à base de dados e:
   ```sql
   UPDATE wp_options 
   SET option_value = '0' 
   WHERE option_name = 'smm_enabled';
   ```

---

## 💻 Para Developers: Informação Técnica

### Estrutura de Ficheiros

```
simple-maintenance-mode/
├── admin/
│   ├── class-smm-admin.php      # Lógica da admin bar e toggle
│   └── sass/smm-admin.sass      # Estilos do admin
├── public/
│   ├── class-smm-public.php     # Lógica do redirect
│   ├── partials/
│   │   └── smm-public-display.php  # Template de manutenção
│   └── sass/smm-public.sass     # Estilos públicos
├── includes/
│   ├── class-smm.php            # Orquestrador principal
│   ├── class-smm-utils.php      # Funções helper (smm_get_content)
│   └── class-smm-common.php     # Funcionalidades comuns
└── assets/
    └── sass/smm-common.sass     # Estilos partilhados (admin bar colors)
```

### Compilation de Assets

```bash
# Instalar dependências
npm install

# Development (com source maps)
npm run dev

# Production (minificado)
npm run production
```

### Hooks Úteis para Developers

**Forçar modo manutenção via código:**
```php
add_filter( 'smm_enabled', '__return_true' );
```

**Permitir editors fazerem bypass:**
```php
add_filter( 'smm_user_can_bypass', function( $can ) {
    return current_user_can( 'editor' ) || $can;
});
```

**Permitir endpoint REST API específico:**
```php
add_filter( 'smm_allow_rest_request', function( $allow, $uri ) {
    if ( strpos( $uri, '/wp-json/custom/v1/' ) !== false ) {
        return true;
    }
    return $allow;
}, 10, 2 );
```

**Alterar conteúdo sem ACF:**
```php
add_filter( 'smm_content_title', function() {
    return 'Título personalizado via código';
});
```

---

## 📋 Checklist: Deploy em Novo Site

Quando instalares o plugin num novo site cliente:

```
[ ] Plugin instalado e ativado
[ ] Testado toggle (ativar/desativar)
[ ] Preview funciona
[ ] Template personalizado copiado para o tema (se necessário)
[ ] ACF Pro instalado (se cliente tiver)
[ ] Página de opções ACF criada (se usar ACF)
[ ] Campos ACF configurados
[ ] Conteúdo de manutenção preenchido
[ ] Testado em diferentes browsers
[ ] Cliente informado sobre como usar
[ ] Documentado em Notion/Confluence
```

---

## 🔒 Segurança & Boas Práticas

### O Que o Plugin Faz Automaticamente ✅

- ✅ Envia HTTP 503 (Temporarily Unavailable)
- ✅ Define headers `Retry-After: 600` (10 minutos)
- ✅ Define `X-Robots-Tag: noindex, nofollow`
- ✅ Previne caching da página de manutenção
- ✅ Permite sempre acesso ao login e cron
- ✅ Usa nonces para segurança
- ✅ Verifica capabilities antes de permitir toggle

### O Que Deves Fazer 📝

- ✅ Avisa o cliente antes de ativar
- ✅ Testa sempre depois de desativar
- ✅ Não deixes ativo por mais de 24h sem avisar
- ✅ Usa preview antes de ativar pela primeira vez
- ✅ Documenta quando e porquê ativaste

---

## 📞 Suporte Interno

### Quem Contactar

**Problemas técnicos:** Canal #dev no Slack  
**Dúvidas de uso:** Canal #wordpress no Slack  
**Bugs ou melhorias:** Cria issue no repositório interno

### Informação Útil para Reportar Bugs

Quando reportares um problema, inclui:
- URL do site
- Versão do WordPress
- Tema usado
- Outros plugins ativos
- Screenshots (se aplicável)
- O que tentaste fazer
- O que aconteceu vs o que esperavas

---

## 🎓 Dicas & Truques

### Dica 1: Agendar Manutenção Automática
```php
// No functions.php do tema ou num plugin auxiliar
function agendar_manutencao() {
    // Ativa às 02:00
    if ( ! wp_next_scheduled( 'ativar_manutencao' ) ) {
        wp_schedule_event( strtotime('tomorrow 2:00am'), 'daily', 'ativar_manutencao' );
    }
}
add_action( 'wp', 'agendar_manutencao' );

add_action( 'ativar_manutencao', function() {
    update_option( 'smm_enabled', 1 );
});

// Desativa às 05:00
add_action( 'desativar_manutencao', function() {
    update_option( 'smm_enabled', 0 );
});
```

### Dica 2: Log de Ativações
```php
add_action( 'updated_option', function( $option, $old, $new ) {
    if ( $option === 'smm_enabled' ) {
        $user = wp_get_current_user();
        $status = $new ? 'ATIVADO' : 'DESATIVADO';
        error_log( "Modo manutenção {$status} por {$user->user_login}" );
    }
}, 10, 3 );
```

### Dica 3: Notificar Equipa via Slack
```php
add_action( 'updated_option', function( $option, $old, $new ) {
    if ( $option === 'smm_enabled' && $old !== $new ) {
        $user = wp_get_current_user();
        $status = $new ? 'ATIVADO' : 'DESATIVADO';
        
        wp_remote_post( 'SLACK_WEBHOOK_URL', array(
            'body' => json_encode([
                'text' => "🚨 Modo manutenção {$status} em " . get_bloginfo('name') . " por {$user->display_name}"
            ])
        ));
    }
}, 10, 3 );
```

---

## 📚 Recursos Adicionais

### Links Úteis
- [WordPress Plugin Boilerplate](https://github.com/DevinVinson/WordPress-Plugin-Boilerplate)
- [ACF Pro Documentation](https://www.advancedcustomfields.com/resources/)
- [WordPress HTTP Status Codes](https://developer.wordpress.org/reference/functions/status_header/)

### Tutoriais Internos (quando disponíveis)
- Video: Como usar o plugin (2 min)
- Video: Como personalizar template (5 min)
- Video: Integração com ACF (8 min)

---

## ✅ O Que Fazer Antes de Atualizar o Plugin

```
[ ] Fazer backup do site
[ ] Testar em staging primeiro
[ ] Verificar se não há template override a quebrar
[ ] Ler o changelog
[ ] Avisar a equipa se houver mudanças importantes
[ ] Ter um rollback plan pronto
```

---

## 🚀 Melhorias Futuras (Roadmap)

Funcionalidades que estamos a considerar:

- [ ] Countdown timer (contagem regressiva)
- [ ] Agendamento automático de manutenção
- [ ] Templates múltiplos pré-definidos
- [ ] Whitelist de IPs
- [ ] Log de ativações no admin
- [ ] Notificações por email
- [ ] Modo "Coming Soon" separado
- [ ] Editor visual do template
- [ ] Multi-idioma melhorado

**Tens sugestões?** Partilha no Slack #wordpress!

---

## 📝 Histórico de Versões

### 1.0.0 (Atual)
- ✅ Toggle na Admin Bar
- ✅ Preview mode
- ✅ Template override system
- ✅ Integração ACF Pro
- ✅ Suporte shortcodes
- ✅ Hooks para developers
- ✅ Segurança implementada
- ✅ Laravel Mix + Tailwind setup

---

**Desenvolvido com ❤️ pela equipa dig.id**  
**Para uso interno | Última atualização: Fevereiro 2024**

---

## 📞 Contactos

**Dúvidas Gerais:** #wordpress no Slack  
**Suporte Técnico:** #dev no Slack  
**Bugs:** Repositório interno (criar issue)  
**Features:** Discutir primeiro no Slack

---

**Bom trabalho e mantém os sites seguros! 🔒⚡**