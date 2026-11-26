/**
 * Copyright (C) 2026 Pascal 'JamesDaRock' Neumann
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 *
 * This file is part of GunGame AMXX 3++ - pur3gaming Edition.
 *
 * See LICENSE for the full license text.
 */
/* Render public server data without injecting remote HTML. */
(() => {
    'use strict';
    const section = document.getElementById('gameservers');
    if (!section) return;
    const labels = JSON.parse(document.getElementById('server-translations').textContent);
    const t = key => labels['servers_' + key] || key;
    const grid = document.getElementById('server-cards');
    const summary = document.getElementById('server-summary');
    const totals = document.getElementById('server-totals');
    const el = (tag, cls, text) => {
        const node = document.createElement(tag);
        if (cls) node.className = cls;
        if (text !== undefined) node.textContent = text;
        return node;
    };
    // Countdown uses a monotonic browser clock and the age of the server cache.
    let countdowns = [];
    const expandedServers = new Set();
    function tick() {
        for (const {node, deadline} of countdowns) {
            const seconds = Math.max(0, Math.ceil((deadline - performance.now()) / 1000));
            node.textContent = `◷ ${t('remaining')}: ${String(Math.floor(seconds / 60)).padStart(2, '0')}:${String(seconds % 60).padStart(2, '0')}`;
        }
    }
    setInterval(tick, 1000);
    function card(s, age, wide = false) {
        const article = el('article', 'server-card' + (wide ? ' server-card-wide' : ''));
        const maps = el('div', 'server-maps');
        function mapTile(label, name, path, current = false) {
            const tile = el('div', 'server-map-tile' + (current ? ' current' : ''));
            tile.append(el('small', '', t(label)));
            if (name) {
                const image = el('img');
                image.src = typeof path === 'string' && /^assets\/images\/maps\/[a-z0-9_./-]+\.(jpg|png|webp)(\?v=[a-f0-9]{16})?$/.test(path) && !path.includes('..') ? section.dataset.base + path : section.dataset.fallbackImage;
                image.alt = ''; image.loading = 'lazy';
                tile.append(image);
            } else {
                tile.append(el('div', 'server-map-placeholder', '—'));
            }
            tile.append(el('strong', '', name || t('unknown')));
            return tile;
        }
        if (s.online) maps.append(mapTile(s.previous_source === 'match' ? 'previous_match' : 'previous', s.previous_map, s.previous_image), mapTile('map', s.map, s.image, true), mapTile('next', s.next_map, s.next_image));
        const body = el('div', 'server-body');
        const header = el('div', 'server-card-heading');
        header.append(el('h3', '', s.name));
        // Keep status and server actions together, above the address.
        const statusRow = el('div', 'server-status-row');
        statusRow.append(el('span', 'server-status ' + (s.online ? 'is-online' : 'is-offline'), t(s.online ? 'online' : 'offline')));
        const actions = el('div', 'server-actions');
        const details = el('a', 'server-action', t('details'));
        details.href = section.dataset.detailUrl.replace('GGADDRESSPLACEHOLDER', encodeURIComponent(s.address).replace(/%3A/gi, ':'));
        actions.append(details);
        function action(label, href, reason) {
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

        statusRow.append(actions);
        // Align both live metadata lines at the same left edge in a centered block.
        const addressRow = el('div', 'server-address-row');
        const identity = el('div', 'server-identity');
        const live = el('div', 'server-live-info');
        // Copy the public address without changing the connection action.
        const address = el('div', 'server-copy-row');
        address.append(el('small', '', s.address));
        const copy = el('button', 'server-copy', '⧉');
        copy.type = 'button'; copy.title = t('copy_address'); copy.setAttribute('aria-label', t('copy_address'));
        copy.addEventListener('click', async () => {
            try { await navigator.clipboard.writeText(s.address); copy.textContent = '✓'; copy.title = t('copied'); }
            catch (_) { copy.title = t('copy_failed'); copy.textContent = '!'; }
        });
        address.append(copy); identity.append(address);
        if (s.online) {
            identity.append(el('small', '', s.game === 'cscz' ? 'Counter-Strike: Condition Zero' : 'Counter-Strike 1.6'));
            const validTime = /^\d{1,3}:[0-5]\d$/.test(s.timeleft || '');
            const node = el('small', 'server-countdown');
            if (validTime) {
                const [minutes, seconds] = s.timeleft.split(':').map(Number);
                countdowns.push({node, deadline: performance.now() + Math.max(0, minutes * 60 + seconds - age) * 1000});
            }
            live.append(node);
            // Never infer live mode from server names or missing rules.
            const rules = s.gungame_rules || {};
            const mode = rules.gg_enabled !== '0' && ['0', '1'].includes(rules.gg_teamplay) ? t(rules.gg_teamplay === '1' ? 'mode_team' : 'mode_solo') : '';
            const version = el('small', 'server-version', s.version ? 'GunGame ' + s.version : '');
            if (mode) addressRow.append(el('small', 'server-mode-field', mode));
            live.append(version);
        }
        addressRow.prepend(identity, live);
        body.append(addressRow);
        let facts, bar;
        if (s.online) {
            facts = el('div', 'server-facts');
            for (const [label, value] of [['players', `${s.players} / ${s.max}`], ['humans', s.bots == null ? '–' : Math.max(0, s.players - s.bots)], ['bots', s.bots ?? '–']]) {
                const item = el('div'); item.append(el('small', '', t(label)), el('strong', '', value)); facts.append(item);
            }
            bar = el('progress', 'server-progress'); bar.max = Math.max(1, s.max); bar.value = Math.min(bar.max, s.players); bar.setAttribute('aria-label', t('players'));
            if (s.password) body.append(el('small', '', t('password')));

        }
        // Stack population, occupancy and maps across the full card width.
        article.append(header, statusRow, body);
        if (s.online) {
            const mapColumn = el('div', 'server-map-column');
            mapColumn.append(maps);
            article.append(facts, bar, mapColumn);
            const details = el('details', 'server-player-list');
            details.open = expandedServers.has(s.address);
            details.addEventListener('toggle', () => {
                if (details.open) expandedServers.add(s.address);
                else expandedServers.delete(s.address);
            });
            // Decorative local SVG icons; native details/summary retains keyboard support.
            const toggle = el('summary');
            function toggleIcon(cls, paths) {
                const ns = 'http://www.w3.org/2000/svg';
                const svg = document.createElementNS(ns, 'svg');
                svg.setAttribute('class', cls); svg.setAttribute('viewBox', '0 0 24 24');
                svg.setAttribute('aria-hidden', 'true'); svg.setAttribute('focusable', 'false');
                for (const d of paths) {
                    const path = document.createElementNS(ns, 'path'); path.setAttribute('d', d); svg.append(path);
                }
                return svg;
            }
            toggle.append(toggleIcon('server-player-icon', ['M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2', 'M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8', 'M22 21v-2a4 4 0 0 0-3-3.87', 'M16 3.13a4 4 0 0 1 0 7.75']), el('span', '', `${t('connected_players')} (${s.players})`), toggleIcon('server-player-chevron', ['m6 9 6 6 6-6']));
            details.append(toggle);
            if (!Array.isArray(s.connected_players)) {
                details.append(el('p', 'muted', t('players_unavailable')));
            } else if (!s.connected_players.length) {
                details.append(el('p', 'muted', t('players_empty')));
            } else {
                const table = el('table');
                const head = el('thead'); const heading = el('tr');
                for (const key of ['player_name', 'score', 'connected_time']) heading.append(el('th', '', t(key)));
                head.append(heading); table.append(head);
                const tbody = el('tbody');
                for (const player of s.connected_players) {
                    const row = el('tr');
                    const seconds = player.seconds;
                    const duration = seconds == null ? '–' : `${Math.floor(seconds / 3600)}:${String(Math.floor(seconds / 60) % 60).padStart(2, '0')}:${String(seconds % 60).padStart(2, '0')}`;
                    row.append(el('td', '', player.name), el('td', '', player.score ?? '–'), el('td', '', duration));
                    tbody.append(row);
                }
                table.append(tbody); const scroll = el('div', 'table-scroll'); scroll.append(table); details.append(scroll);
            }
            article.append(details);
        }
        if (!s.online) article.classList.add('server-card-offline');
        return article;
    }
    // Totals use public occupancy only; bot/human classification is unchanged.
    function renderTotals(servers) {
        if (!totals) return;
        const online = servers.filter(s => s.online);
        const players = online.reduce((n, s) => n + (Number(s.players) || 0), 0);
        const capacity = online.reduce((n, s) => n + (Number(s.max) || 0), 0);
        const values = [['total', servers.length], ['online', `${online.length} / ${servers.length}`], ['players', capacity ? `${players} / ${capacity}` : '–'], ['occupancy', capacity ? `${Math.round(players / capacity * 100)} %` : '–']];
        totals.replaceChildren(...values.map(([key, value]) => {
            const item = el('article'); item.append(el('small', '', t(key)), el('strong', '', value)); return item;
        }));
        totals.hidden = false;
    }
    async function refresh() {
        try {
            const response = await fetch(section.dataset.base + 'api/servers.php', {cache: 'no-store'});
            if (!response.ok) throw new Error('Status unavailable');
            const data = await response.json();
            if (data.pending) { setTimeout(refresh, 3000); return; }
            // Reuse live hostnames for match cards without issuing another query.
            const serverNames = new Map(data.servers.map(server => [server.address, server.name]));
            for (const heading of document.querySelectorAll('[data-match-server]')) {
                const address = heading.dataset.matchServer;
                heading.textContent = serverNames.get(address) || address;
            }
            renderTotals(data.servers);
            countdowns = [];
            const visible = section.dataset.server ? data.servers.filter(s => s.address === section.dataset.server) : data.servers;
            // A lone card or the last card of an odd-sized grid spans both columns.
            grid.replaceChildren(...visible.map((s, i) => card(s, Math.max(0, Number(data.age) || 0), visible.length % 2 === 1 && i === visible.length - 1)));
            if (section.dataset.server && visible.length) {
                const rules = visible[0].gungame_rules || {};
                const entries = Object.entries(rules);
                if (visible[0].online && entries.length) {
                    const panel = el('section', 'panel server-rule-panel');
                    panel.append(el('h2', '', t('rules')));
                    // Group only the public rules actually reported by the server.
                    const groups = {
                        general: ['gg_enabled','gg_map_iterations','gg_block_objectives','gg_warmup_timer_setting'],
                        stats: ['gg_ignore_bots','gg_stats_mode','gg_stats_split','gg_stats_winbonus'],
                        deathmatch: ['gg_dm','gg_dm_sp_time','gg_dm_sp_mode','gg_dm_spawn_random','gg_dm_spawn_delay'],
                        teamplay: ['gg_teamplay','gg_teamplay_knife_mod','gg_teamplay_nade_mod','gg_teamplay_timeratio','gg_ff_auto','gg_tk_penalty'],
                        weapons: ['gg_max_lvl','gg_triple_on','gg_turbo','gg_knife_pro','gg_knife_elite','gg_suicide_penalty','gg_pickup_others','gg_kills_per_lvl','gg_give_armor','gg_give_helmet','gg_awp_oneshot'],
                        handicap: ['gg_handicap_on','gg_top10_handicap','gg_kills_botmod','gg_bots_skipnade','gg_bots_knifeable']
                    };
                    const binary = new Set(['gg_enabled','gg_stats_split','gg_dm','gg_block_objectives','gg_teamplay_timeratio','gg_ff_auto','gg_turbo','gg_knife_elite','gg_suicide_penalty','gg_pickup_others','gg_top10_handicap','gg_give_helmet','gg_awp_oneshot','gg_bots_skipnade','gg_bots_knifeable']);
                    const seconds = new Set(['gg_dm_sp_time','gg_dm_spawn_delay','gg_warmup_timer_setting']);
                    function ruleValue(key, value) {
                        const n = Number(value);
                        if (binary.has(key) && [0,1].includes(n)) return value + ' · ' + t('rule_' + (n ? 'enabled' : 'disabled'));
                        if (seconds.has(key)) return value + ' ' + t('rule_seconds');
                        const modes = key === 'gg_stats_mode' ? ['disabled','wins','points'] : key === 'gg_teamplay' ? ['solo','team','random'] : [];
                        return modes[n] ? value + ' · ' + t('rule_' + modes[n]) : value;
                    }
                    const table = el('table', 'server-rules-table');
                    const head = el('thead'); const header = el('tr');
                    for (const key of ['variable','value','description']) { const cell = el('th', '', t('rule_' + key)); cell.scope = 'col'; header.append(cell); }
                    head.append(header); table.append(head);
                    for (const [group, keys] of Object.entries(groups)) {
                        const present = keys.filter(key => Object.hasOwn(rules, key));
                        if (!present.length) continue;
                        const body = el('tbody'); const heading = el('tr', 'server-rule-group');
                        const cell = el('th', '', t('rule_' + group)); cell.colSpan = 3; cell.scope = 'rowgroup'; heading.append(cell); body.append(heading);
                        for (const key of present) {
                            const row = el('tr'); const name = el('th', '', key); name.scope = 'row';
                            row.append(name, el('td', 'server-rule-value', ruleValue(key, rules[key])), el('td', '', t('rule_description_' + key))); body.append(row);
                        }
                        table.append(body);
                    }
                    const scroll = el('div', 'table-scroll'); scroll.append(table); panel.append(scroll); grid.append(panel);
                }
            }
            tick();
            summary.textContent = data.servers.length ? `${data.servers.filter(s => s.online).length} / ${data.servers.length} ${t('count')} · ${t('checked')}: ${new Date(data.updated * 1000).toLocaleTimeString()}` : t('empty');
        } catch (_) { summary.textContent = t('error'); if (totals) totals.hidden = true; }
        setTimeout(refresh, Number(section.dataset.refresh) * 1000);
    }
    refresh();
})();
