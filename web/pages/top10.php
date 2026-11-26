<?php
/**
 * Copyright (C) 2026 Pascal 'JamesDaRock' Neumann
 * SPDX-License-Identifier: GPL-2.0-or-later
 * Hall of Fame presentation.
 */
if (!defined('GUNGAME_WEB')) { http_response_code(404); exit; }
require_once __DIR__.'/../includes/match_display.php';
require_once __DIR__.'/../includes/gameserver.php';
function hallServer(array $r): string {
    $name=matchServerName($r['serverip']);
    $name=preg_replace('/^\([^)]*\)\s*>\s*/','',$name);
    $name=preg_replace('/\s+vs\s+GunGame::(?:Solo|Team)$/i','',$name);
    return $name.(isset($r['mode'])?' · '.translate('mode.'.$r['mode']):'');
}
// Local inline SVG icons, without external assets or dependencies.
function hallIcon(string $icon): string {
    $paths=['trophy'=>'<path d="M8 3h8v8a4 4 0 0 1-8 0zM8 5H4v3a4 4 0 0 0 4 4m8-7h4v3a4 4 0 0 1-4 4m-4 3v5m-4 1h8"/>','star'=>'<path d="m12 3 3 6 7 1-5 5 1 7-6-3-6 3 1-7-5-5 7-1z"/>','flame'=>'<path d="M12 2c2 6 8 8 8 14a8 8 0 0 1-16 0c0-4 3-7 5-9 0 4 2 5 3 5 2-3 1-7 0-10z"/>','users'=>'<circle cx="9" cy="7" r="3"/><path d="M3 20v-3a6 6 0 0 1 12 0v3M16 4a3 3 0 0 1 0 6m2 3a5 5 0 0 1 3 5v2"/>','target'=>'<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/>','map'=>'<path d="m3 5 6-2 6 2 6-2v16l-6 2-6-2-6 2zM9 3v16m6-14v16"/>','clock'=>'<circle cx="12" cy="12" r="9"/><path d="M12 6v6l4 2"/>'];
    return '<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.$paths[$icon].'</svg>';
}
function hallLink(array $r,string $kind): string {
    if ($kind==='player'||$kind==='rate') return url(['view'=>'player','player'=>$r['authid'],'server'=>$r['serverip'],'mode'=>$r['mode']??'solo']);
    if ($kind==='match') return url(['view'=>'match','server'=>$r['serverip'],'ts'=>$r['timestamp']]);
    return url(['view'=>'matches','map'=>$r['name'],'p'=>1]);
}
?>
<form class="filters hall-filters" method="get" data-route="top10" action="<?= h(strtok(portal_url(['view'=>'top10']), '?')) ?>">
    <?php if (!portal_rewrite()): ?><input type="hidden" name="view" value="top10"><?php endif; ?><?php if ($languagePreference !== 'auto'): ?><input type="hidden" name="lang" value="<?= h($languagePreference) ?>"><?php endif; ?>
    <label><?= translate('filter.server') ?><select name="server"><option value=""><?= translate('filter.all_servers') ?></option><?php foreach ($servers as $s): ?><option value="<?= h($s) ?>" <?= $s===$server?'selected':'' ?>><?= h(matchServerName($s)) ?></option><?php endforeach; ?></select></label>
    <label><?= translate('filter.mode') ?><select name="mode"><option value="all" <?= $mode==='all'?'selected':'' ?>><?= translate('history.all_modes') ?></option><option value="solo" <?= $mode==='solo'?'selected':'' ?>><?= translate('mode.solo') ?></option><option value="team" <?= $mode==='team'?'selected':'' ?>><?= translate('mode.team') ?></option></select></label>
    <button type="submit"><?= translate('filter.apply') ?></button>
</form>
<section class="hall-section">
    <div class="section-title"><h2><?= translate('hall.records') ?></h2></div>
    <div class="hall-highlights">
    <?php foreach (['wins','points','streak'] as $key): $list=$hallLists[$key]; $r=$list['rows'][0]??null; ?>
        <article class="panel hall-highlight"><span class="hall-icon"><?= hallIcon($list['icon']) ?></span><h3><?= translate('hall.'.$key) ?></h3><?php if ($r): ?><a href="<?= h(hallLink($r,'player')) ?>"><?= h($r['name']) ?></a><strong><?= h(hallValue($r,'player')) ?></strong><small><?= h(hallServer($r)) ?></small><?php else: ?><p class="muted"><?= translate('player.empty') ?></p><?php endif; ?></article>
    <?php endforeach; ?>
    </div>
    <p class="muted hall-note"><?= translate('hall.scope') ?></p>
</section>
<?php foreach (['records'=>['wins','points','streak'],'history'=>['played','rate','maps','longest','shortest','average']] as $group=>$keys): ?>
<section class="hall-section">
    <div class="section-title"><h2><?= translate('hall.'.$group.'_lists') ?></h2></div>
    <?php if ($group==='history'): ?><p class="muted hall-note"><?= translate('hall.history_note') ?></p><?php endif; ?>
    <div class="hall-grid">
    <?php foreach ($keys as $key): $list=$hallLists[$key]; $kind=$list['kind']; ?>
        <article class="panel hall-list"><h3><span class="hall-icon"><?= hallIcon($list['icon']) ?></span> <?= translate('hall.'.$key) ?></h3>
        <?php if ($key==='rate'): ?><p class="muted hall-note"><?= translate('hall.rate_note') ?></p><?php endif; ?>
        <?php if (!$list['rows']): ?><p class="muted"><?= translate($key==='rate'?'hall.rate_empty':'player.empty') ?></p><?php endif; ?>
        <ol><?php foreach ($list['rows'] as $i=>$r): $rank=($i>0 && (float)$r['value']===(float)$list['rows'][$i-1]['value'])?$rank:$i+1; ?><li><span class="hall-rank hall-rank-<?= $rank ?>"><?= $rank ?></span><div class="hall-name"><a href="<?= h(hallLink($r,$kind)) ?>"><?= h($r['name'] ?: translate('match.unknown')) ?></a><?php if (isset($r['serverip'])): ?><small><?= h(hallServer($r)) ?><?= $kind==='match'?' · '.h(dt($r['timestamp'])):'' ?></small><?php endif; ?><?php if ($kind==='match'): ?><small><?= h($r['mode']==='team'?matchTeamLabel((int)$r['winning_team']):($r['winner']?:translate('match.unknown'))) ?></small><?php endif; ?></div><div class="hall-value"><?= h(hallValue($r,$kind)) ?><?php if ($kind==='rate'): ?><small><?= num($r['won']) ?>/<?= num($r['played']) ?></small><?php endif; ?><?php if ($kind==='average'): ?><small><?= num($r['played']) ?> <?= translate('hall.sample_matches') ?></small><?php endif; ?></div></li><?php endforeach; ?></ol>
        </article>
    <?php endforeach; ?>
    </div>
</section>
<?php endforeach; ?>
