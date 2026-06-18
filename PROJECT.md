# PROJECT.md

## Scopo

WP Show Site by IP e un plugin WordPress per nascondere il sito ai visitatori non autorizzati e mostrare al loro posto una pagina temporanea HTML configurabile. L'accesso al sito reale viene concesso tramite whitelist IP, stringa di autorizzazione in URL o whitelist URL per singole richieste.

## Struttura

- `wp-show-site-by-ip.php`: entrypoint del plugin, header WordPress.org, costanti e bootstrap.
- `inc/wp-show-site-by-ip.class.php`: classe principale con logica runtime, pagina admin, opzioni, whitelist e bypass.
- `inc/help-screens.php`: contenuto degli help tab della pagina impostazioni.
- `inc/help-pointer.php`: pointer WordPress verso l'help contestuale.
- `tpls/`: template PHP della UI admin.
- `parts/`: template e contenuti predefiniti della pagina temporanea pubblica.
- `js/main.js`: tab della pagina impostazioni, editor Ace, warning modifiche non salvate, tooltip e Ajax per vecchio HTML.
- `css/main.css`: stile della pagina impostazioni.
- `lib/`: librerie frontend vendorizzate, attualmente Ace, Prism e tlite.
- `languages/`: file di traduzione e template POT.
- `.github/workflows/deploy.yml`: deploy su WordPress.org al push di un tag.

## Bootstrap

L'entrypoint definisce le costanti `wssbi\VER`, `wssbi\DIR`, `wssbi\INC`, `wssbi\FILE` e `wssbi\URL`, poi registra `wssbi\init` su `plugins_loaded`. La funzione include la classe principale e istanzia `WP_Show_Site_by_IP`.

## Opzioni

Le impostazioni principali sono salvate in `wssbi_settings`:

- `enabled`: abilita o disabilita il filtro.
- `ips`: regole IP autorizzate.
- `url_whitelist_strings`: stringhe che bypassano la pagina temporanea per la richiesta corrente.
- `body`: contenuto HTML del body della pagina temporanea.
- `head`: contenuto inserito nell'head della pagina temporanea.
- `title`: title della pagina temporanea.
- `http`: status HTTP inviato con la pagina temporanea.
- `wordOk`: query string che autorizza l'IP corrente.
- `wordKo`: query string che rimuove l'IP corrente.

L'opzione `wssbi_html_old` conserva il vecchio HTML migrato da versioni precedenti e puo essere eliminata via Ajax dalla UI admin.

## Flusso runtime

Il controllo pubblico avviene in `WP_Show_Site_by_IP::check()`, registrato su `plugins_loaded` con priorita 15.

1. Legge l'IP client da `REMOTE_ADDR`, filtrabile con `wssbi_client_ip`.
2. Legge la request URI corrente.
3. Se la query contiene la stringa OK configurata, aggiunge l'IP alla whitelist.
4. Se la query contiene la stringa KO configurata, rimuove l'IP esatto dalla whitelist.
5. Se il filtro e attivo e la richiesta non e autorizzata, include `parts/temp-page-tpl.php` e termina.

Prima di mostrare la pagina temporanea, il plugin esclude richieste amministrative e tecniche tramite `should_bypass_filter()`, compresi admin, Ajax, cron, REST API, sitemap, asset statici e alcuni file noti come `robots.txt`.

## Whitelist IP

La whitelist IP accetta una regola per riga. Le righe vuote e quelle che iniziano con `#` vengono ignorate. Sono supportati IPv4, IPv6, loopback e wildcard a segmento intero, per esempio `123.123.123.*` o `2001:db8:*:*:*:*:*:*`.

La normalizzazione e il matching sono gestiti dalla classe principale con metodi dedicati per IPv4, IPv6, wildcard, deduplicazione e confronto.

## Whitelist URL

La whitelist URL contiene una stringa per riga. Se la request URI contiene una delle stringhe configurate, il filtro viene bypassato solo per quella richiesta e l'IP del visitatore non viene salvato.

## Admin UI

La pagina impostazioni e registrata sotto Tools > Show Site by IP. La capability predefinita e `manage_options`, modificabile con il filtro `wssbi_manage_options`.

La UI e divisa in tab:

- impostazioni generali;
- pagina temporanea;
- IP autorizzati e whitelist URL.

Il body della pagina temporanea usa `wp_editor()`. Head e lista IP usano Ace editor. La pagina include anche tooltip, help tab e un indicatore nella admin bar sullo stato del filtro.

## Hook pubblici

- `wssbi_manage_options`: modifica la capability richiesta per la pagina admin.
- `wssbi_client_ip`: modifica o sostituisce l'IP client rilevato.
- `wssbi_show_temp_page`: modifica la decisione finale di mostrare la pagina temporanea.
- `wssbi_ip_rules`: filtra le regole IP dopo sanitizzazione.
- `wssbi_ip_rule_matches`: filtra il risultato del matching tra IP e regola.

## Endpoint e shortcode

Non sono presenti shortcode, REST route o endpoint pubblici dedicati. L'unico endpoint Ajax registrato e `wp_ajax_wssbi_forget_old_html`, usato nella UI admin per eliminare `wssbi_html_old`.

## Rilascio

Il workflow GitHub Actions pubblica su WordPress.org quando viene pushato un tag. Non c'e un build step attivo: i file inclusi nel repository sono quelli distribuiti, salvo esclusioni in `.distignore`.
