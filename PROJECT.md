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
- `docs/manual-test-checklist.md`: checklist manuale per verifiche runtime, admin e bypass.
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

Prima di mostrare la pagina temporanea, il plugin esclude richieste tecniche tramite `should_bypass_filter()`, compresi Ajax, cron, REST API, sitemap, asset statici e alcuni file noti come `robots.txt`. Le pagine `/wp-admin/` non bypassano il filtro: se l'IP non e autorizzato, anche l'area admin mostra la pagina temporanea.

La stringa OK predefinita `wpok` viene mantenuta per compatibilita, ma e prevedibile. Il plugin mostra un avviso admin non bloccante quando resta invariata, invitando a personalizzarla.

## Whitelist IP

La whitelist IP accetta una regola per riga. Le righe vuote e quelle che iniziano con `#` vengono ignorate. Sono supportati IPv4, IPv6, loopback e wildcard a segmento intero, per esempio `123.123.123.*` o `2001:db8:*:*:*:*:*:*`.

La normalizzazione e il matching sono gestiti dalla classe principale con metodi dedicati per IPv4, IPv6, wildcard, deduplicazione e confronto.

## Whitelist URL

La whitelist URL contiene una stringa per riga. Se la request URI contiene una delle stringhe configurate, il filtro viene bypassato solo per quella richiesta e l'IP del visitatore non viene salvato.

## Sicurezza e compatibilita

Il contenuto `head` e `body` della pagina temporanea e HTML fidato: puo includere markup, CSS e JavaScript e viene pensato per amministratori affidabili. Abbassare la capability tramite `wssbi_manage_options` puo quindi esporre il sito a utenti meno fidati.

I bypass tecnici privilegiano compatibilita con WordPress, WooCommerce, REST API, sitemap, asset statici e challenge `.well-known/acme-challenge/`. I default sono volutamente permissivi per evitare rotture operative quando il filtro IP e attivo, ma non includono le pagine `/wp-admin/`.

I bypass sono estendibili tramite filtri dedicati:

- `wssbi_bypass_exact_paths`
- `wssbi_bypass_path_prefixes`
- `wssbi_bypass_path_patterns`
- `wssbi_bypass_static_extensions`
- `wssbi_should_bypass_filter`

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
- `wssbi_bypass_exact_paths`: filtra i path esatti sempre bypassati.
- `wssbi_bypass_path_prefixes`: filtra i prefissi di path bypassati.
- `wssbi_bypass_path_patterns`: filtra i pattern regex di path bypassati.
- `wssbi_bypass_static_extensions`: filtra le estensioni statiche bypassate.
- `wssbi_should_bypass_filter`: modifica la decisione finale dei bypass tecnici.

## Endpoint e shortcode

Non sono presenti shortcode, REST route o endpoint pubblici dedicati. L'unico endpoint Ajax registrato e `wp_ajax_wssbi_forget_old_html`, usato nella UI admin per eliminare `wssbi_html_old`.

## Rilascio

Il workflow GitHub Actions pubblica su WordPress.org quando viene pushato un tag. Non c'e un build step attivo: i file inclusi nel repository sono quelli distribuiti, salvo esclusioni in `.distignore`.

## Verifiche manuali

La checklist in `docs/manual-test-checklist.md` documenta i controlli manuali consigliati dopo modifiche a salvataggio opzioni, IP rules, whitelist URL, bypass tecnici, editor della pagina temporanea e avvisi admin. Il progetto mantiene una classe principale unica per compatibilita e semplicita distributiva; eventuali split strutturali vanno pianificati separatamente.
