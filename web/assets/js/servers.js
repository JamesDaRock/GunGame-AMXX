/* Render public server data without injecting remote HTML. */
(() => {
    'use strict';
    const section = document.getElementById('gameservers');
    if (!section) return;
    const labels = JSON.parse(document.getElementById('server-translations').textContent);
    const t = key => labels['servers_' + key] || key;
    const grid = document.getElementById('server-cards');
    const summary = document.getElementById('server-summary');
    const el = (tag, cls, text) => {
        const node = document.createElement(tag);
        if (cls) node.className = cls;
        if (text !== undefined) node.textContent = text;
        return node;
    };
    // Countdown uses a monotonic browser clock and the age of the server cache.
    let countdowns = [];
    function tick() {
        for (const {node, deadline} of countdowns) {
            const seconds = Math.max(0, Math.ceil((deadline - performance.now()) / 1000));
            node.textContent = `${String(Math.floor(seconds / 60)).padStart(2, '0')}:${String(seconds % 60).padStart(2, '0')} · ${t('remaining')}`;
        }
    }
    setInterval(tick, 1000);
    function card(s, age) {
        const article = el('article', 'server-card');
        const maps = el('div', 'server-maps');
        function mapTile(label, name, path, current = false) {
            const tile = el('div', 'server-map-tile' + (current ? ' current' : ''));
            tile.append(el('small', '', t(label)));
            if (name) {
                const image = el('img');
                image.src = typeof path === 'string' && /^assets\/images\/maps\/[a-z0-9_./-]+\.(jpg|png|webp)$/.test(path) && !path.includes('..') ? path : 'assets/images/maps/cs16/noimage.jpg';
                image.alt = ''; image.loading = 'lazy';
                tile.append(image);
            } else {
                tile.append(el('div', 'server-map-placeholder', '—'));
            }
            tile.append(el('strong', '', name || t('unknown')));
            if (current && /^\d{1,3}:[0-5]\d$/.test(s.timeleft || '')) {
                const [minutes, seconds] = s.timeleft.split(':').map(Number);
                const node = el('small', 'server-countdown');
                countdowns.push({node, deadline: performance.now() + Math.max(0, minutes * 60 + seconds - age) * 1000});
                tile.append(node);
            }
            return tile;
        }
        if (s.online) maps.append(mapTile(s.previous_source === 'match' ? 'previous_match' : 'previous', s.previous_map, s.previous_image), mapTile('map', s.map, s.image, true), mapTile('next', s.next_map, s.next_image));
        const body = el('div', 'server-body');
        body.append(el('span', 'server-status ' + (s.online ? 'is-online' : 'is-offline'), t(s.online ? 'online' : 'offline')), el('h3', '', s.name));
        const actions = el('div', 'server-actions');
        actions.append(el('small', '', s.address));
        function action(label, href, reason) {
            actions.append(el('span', 'server-action-separator', '|'));
            const item = el(href ? 'a' : 'span', 'server-action' + (href ? '' : ' is-disabled'), label);
            if (href) {
                item.href = href;
                if (/^https?:\/\//i.test(href)) { item.target = '_blank'; item.rel = 'noopener noreferrer'; }
            } else { item.title = reason; item.setAttribute('aria-disabled', 'true'); }
            actions.append(item);
        }
        action(t('connect'), s.online && /^[0-9.]+:[0-9]{1,5}$/.test(s.address) ? 'steam://connect/' + s.address : '', t('offline'));
        for (const [key, label] of [['hlstatsx', 'HLstatsX'], ['psychostats', 'PsychoStats']]) {
            const info = s.stats_links?.[key];
            const url = typeof info?.url === 'string' && /^https?:\/\//i.test(info.url) ? info.url : '';
            action(label, url, t(info?.detected ? 'stats_no_url' : 'stats_unavailable'));
        }
        body.append(actions);
        if (s.online) {
            body.append(el('small', '', (s.game === 'cscz' ? 'Counter-Strike: Condition Zero' : 'Counter-Strike 1.6') + (s.version ? ' · GunGame ' + s.version : '')));
            const facts = el('div', 'server-facts');
            for (const [label, value] of [['players', `${s.players} / ${s.max}`], ['humans', s.bots == null ? '–' : Math.max(0, s.players - s.bots)], ['bots', s.bots ?? '–']]) {
                const item = el('div'); item.append(el('small', '', t(label)), el('strong', '', value)); facts.append(item);
            }
            const bar = el('progress', 'server-progress'); bar.max = Math.max(1, s.max); bar.value = Math.min(bar.max, s.players); bar.setAttribute('aria-label', t('players'));
            body.append(facts, bar);
            if (s.password) body.append(el('small', '', t('password')));

        }
        article.append(body); if (s.online) article.append(maps); return article;
    }
    async function refresh() {
        try {
            const response = await fetch('api/servers.php', {cache: 'no-store'});
            if (!response.ok) throw new Error('Status unavailable');
            const data = await response.json();
            if (data.pending) { setTimeout(refresh, 3000); return; }
            countdowns = [];
            grid.replaceChildren(...data.servers.map(s => card(s, Math.max(0, Number(data.age) || 0))));
            tick();
            summary.textContent = data.servers.length ? `${data.servers.filter(s => s.online).length} / ${data.servers.length} ${t('count')} · ${t('checked')}: ${new Date(data.updated * 1000).toLocaleTimeString()}` : t('empty');
        } catch (_) { summary.textContent = t('error'); }
        setTimeout(refresh, Number(section.dataset.refresh) * 1000);
    }
    refresh();
})();
