<?php
/**
 * Copyright (C) 2026 Pascal 'JamesDaRock' Neumann
 * SPDX-License-Identifier: GPL-2.0-or-later
 * Player directory filters, results and pagination.
 */
if (!defined('GUNGAME_WEB')) { http_response_code(404); exit; }
require_once __DIR__.'/../includes/match_display.php';
function playersPagination(): void {
    global $page,$maxPage;
    ?> <nav class="leader-pagination" aria-label="<?= translate('pagination.page') ?>">
    <?php if ($page>1): ?><a class="detail-button" href="<?= h(url(['p'=>$page-1])) ?>#rankings"><?= translate('pagination.previous') ?></a><?php endif; ?>
    <span><?= translate('pagination.page') ?> <?= num($page) ?> / <?= num($maxPage) ?></span>
    <?php if ($page<$maxPage): ?><a class="detail-button" href="<?= h(url(['p'=>$page+1])) ?>#rankings"><?= translate('pagination.next') ?></a><?php endif; ?></nav><?php
}
?>
<form class="filters history-filters leader-filters" method="get" data-route="players" action="<?= h(strtok(portal_url(['view'=>'players']), '?')) ?>">
<?php if (!portal_rewrite()): ?><input type="hidden" name="view" value="players"><?php endif; ?><?php if ($languagePreference !== 'auto'): ?><input type="hidden" name="lang" value="<?= h($languagePreference) ?>"><?php endif; ?>
<label><?= translate('filter.server') ?><select name="server"><option value=""><?= translate('filter.all_servers') ?></option><?php foreach ($servers as $s): ?><option value="<?= h($s) ?>" <?= $s===$server?'selected':'' ?>><?= h(matchServerName($s)) ?></option><?php endforeach; ?></select></label>
<label><?= translate('filter.mode') ?><select name="mode"><?php foreach (['all','solo','team'] as $m): ?><option value="<?= $m ?>" <?= $mode===$m?'selected':'' ?>><?= translate($m==='all'?'history.all_modes':'mode.'.$m) ?></option><?php endforeach; ?></select></label>
<label><?= translate('filter.sort') ?><select name="sort"><?php foreach (['name','wins','points','streak'] as $key): ?><option value="<?= $key ?>" <?= $sort===$key?'selected':'' ?>><?= translate($key==='name'?'player.sort_name':'stats.'.$key) ?></option><?php endforeach; ?></select></label>
<label><?= translate('filter.search') ?><input name="q" value="<?= h($q) ?>" placeholder="<?= translate('filter.placeholder') ?>"></label>
<label><?= translate('ranking.per_page') ?><select name="per_page"><?php foreach ([25,50,100] as $n): ?><option value="<?= $n ?>" <?= $limit===$n?'selected':'' ?>><?= $n ?></option><?php endforeach; ?></select></label>
<div class="leader-actions"><a class="detail-button" href="<?= h(url(['server'=>null,'mode'=>'all','sort'=>'name','q'=>null,'p'=>1,'per_page'=>25])) ?>"><?= translate('history.reset') ?></a><button type="submit"><?= translate('history.find') ?></button></div>
</form>
<section class="panel leader-panel" id="rankings">
<div class="section-title"><h2><?= translate('navigation.players') ?> <span class="muted">· <?= translate($mode==='all'?'history.all_modes':'mode.'.$mode) ?></span></h2><span class="muted"><?= num($total) ?> <?= translate('ranking.results') ?></span></div>
<?php if ($maxPage>1) playersPagination(); ?>
<div class="table-scroll"><table class="leader-table directory-table"><thead><tr><th <?= $sort==='name'?'aria-sort="ascending" class="leader-active"':'' ?>><a href="<?= h(url(['sort'=>'name','p'=>1])) ?>#rankings"><?= translate('player.name') ?><?= $sort==='name'?' ↑':'' ?></a></th><?php foreach (['wins','points','streak'] as $key): ?><th <?= $sort===$key?'aria-sort="descending" class="leader-active"':'' ?>><a href="<?= h(url(['sort'=>$key,'p'=>1])) ?>#rankings"><?= translate('stats.'.$key) ?><?= $sort===$key?' ↓':'' ?></a></th><?php endforeach; ?><th><?= translate('stats.last_saved') ?></th></tr></thead><tbody>
<?php foreach ($players as $p): $name=matchServerName($p['serverip']); $name=preg_replace('/^\([^)]*\)\s*>\s*/','',$name); $name=preg_replace('/\s+vs\s+GunGame::(?:Solo|Team)$/i','',$name); ?>
<tr><td><a class="player" href="<?= h(url(['view'=>'player','player'=>$p['authid'],'server'=>$p['serverip'],'mode'=>$p['mode'],'return_server'=>$server,'return_mode'=>$mode,'return_page'=>$page,'return_limit'=>$limit,'return_view'=>'players','return_sort'=>$sort,'return_q'=>$q])) ?>"><?= h($p['name']) ?></a><small class="sub"><?= h($name) ?> · <?= translate('mode.'.$p['mode']) ?></small></td><?php foreach (['wins','points','streak'] as $key): ?><td class="<?= $sort===$key?'accent':'' ?>"><?= num($p[$key]) ?></td><?php endforeach; ?><td class="muted"><?= dt($p['timestamp']) ?></td></tr>
<?php endforeach; if (!$players): ?><tr><td colspan="5" class="empty"><?= translate('ranking.empty_filtered') ?></td></tr><?php endif; ?>
</tbody></table></div>
<?php playersPagination(); ?>
</section>
