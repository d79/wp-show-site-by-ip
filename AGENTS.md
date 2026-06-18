# AGENTS.md

## Istruzioni operative

- Mantieni compatibilita con PHP 7.0 e con la distribuzione WordPress.org.
- Non introdurre strumenti di build o nuove dipendenze senza una richiesta esplicita.
- Usa il namespace `wssbi` e il prefisso `wssbi_` per hook, handle, opzioni e identificatori pubblici.
- Preferisci le API WordPress esistenti per opzioni, hook, nonce, traduzioni, enqueue e URL.
- Mantieni le modifiche piccole e localizzate: la classe principale contiene sia runtime sia admin UI.
- Prima di cambiare il filtro pubblico, controlla `WP_Show_Site_by_IP::check()` e `should_bypass_filter()`.
- Prima di cambiare whitelist o matching IP, controlla normalizzazione IPv4/IPv6, wildcard e commenti.
- Prima di cambiare la pagina impostazioni, controlla insieme `tpls/settings-form.php`, `js/main.js` e `css/main.css`.
- Tratta `head` e `body` della pagina temporanea come HTML fidato: non restringerli senza richiesta esplicita.
- Non abbassare la capability `wssbi_manage_options` per utenti non fidati: consente di salvare HTML e script pubblici.
- Mantieni i bypass tecnici compatibili con WordPress e WooCommerce; se li modifichi, preferisci i filtri disponibili ai cambi hardcoded.
- Ricorda che la stringa OK predefinita `wpok` e comoda ma prevedibile: non cambiarne il default senza piano di compatibilita.
- Se aggiungi o modifichi stringhe visibili, aggiorna anche i file di traduzione quando richiesto.
- Prima di un rilascio, allinea header plugin, costante `VER`, `readme.txt`, stable tag e changelog.

## Verifiche consigliate

- Apri Tools > Show Site by IP e verifica salvataggio delle impostazioni.
- Verifica abilitazione/disabilitazione del filtro e notice admin.
- Verifica accesso con la stringa OK e rimozione con la stringa KO.
- Verifica whitelist IP con IPv4, IPv6, loopback e wildcard a segmento intero.
- Verifica whitelist URL e bypass tecnici per admin, Ajax, cron, REST API, sitemap e asset statici.
- Verifica che HTML, CSS e script salvati in `head` e `body` restino invariati dopo il salvataggio.
- Verifica eventuali filtri custom sui bypass: path esatti, prefissi, pattern, estensioni e filtro finale.
