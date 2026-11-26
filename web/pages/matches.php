<?php
/** Copyright (C) 2026 Pascal 'JamesDaRock' Neumann
 * SPDX-License-Identifier: GPL-2.0-or-later
 * Latest match and searchable historical results.
 */
if (!defined('GUNGAME_WEB')) { http_response_code(404); exit; }
$matchHeading='match.title'; require __DIR__.'/../includes/match_cards.php';
?>
<section class="panel match-history">
<div class="section-title"><h2><?= translate('history.history') ?></h2><span class="muted"><?= num($total) ?> <?= translate('history.count') ?></span></div>
<form class="filters history-filters" method="get" data-route="matches" action="<?= h(strtok(portal_url(['view'=>'matches']), '?')) ?>">
<?php if (!portal_rewrite()): ?><input type="hidden" name="view" value="matches"><?php endif; ?><?php if ($languagePreference !== 'auto'): ?><input type="hidden" name="lang" value="<?= h($languagePreference) ?>"><?php endif; ?>
<label><?= translate('filter.server') ?><select name="server"><option value=""><?= translate('filter.all_servers') ?></option><?php foreach($servers as $s): ?><option value="<?= h($s) ?>" <?= $s===$server?'selected':'' ?>><?= h(matchServerName($s)) ?></option><?php endforeach; ?></select></label>
<label><?= translate('filter.mode') ?><select name="mode"><?php foreach(['all'=>'history.all_modes','solo'=>'mode.solo','team'=>'mode.team'] as $v=>$k): ?><option value="<?= h($v) ?>" <?= $mode===$v?'selected':'' ?>><?= translate($k) ?></option><?php endforeach; ?></select></label>
<label><?= translate('match.map') ?><select name="map"><option value=""><?= translate('history.all_maps') ?></option><?php foreach($maps as $m): ?><option value="<?= h($m) ?>" <?= $mapFilter===$m?'selected':'' ?>><?= h($m) ?></option><?php endforeach; ?></select></label>
<label><?= translate('history.from') ?><input type="date" name="from" value="<?= h($from) ?>"></label><label><?= translate('history.to') ?><input type="date" name="to" value="<?= h($to) ?>"></label>
<label class="search"><?= translate('filter.search') ?><input name="q" value="<?= h($q) ?>" placeholder="<?= translate('history.search') ?>"></label><div class="history-actions"><label><?= translate('history.per_page') ?><select name="limit"><?php foreach([10,25,50,100] as $n): ?><option <?= $limit===$n?'selected':'' ?>><?= $n ?></option><?php endforeach; ?></select></label><div><a class="match-detail-link" href="<?= h(url(['view'=>'matches','server'=>null,'mode'=>'all','q'=>null,'p'=>1,'map'=>null,'from'=>null,'to'=>null,'limit'=>25])) ?>"><?= translate('history.reset') ?></a> <button type="submit"><?= translate('history.find') ?></button></div></div>
</form>
<div class="table-scroll history-table"><table><colgroup><col style="width:13%"><col style="width:27%"><col style="width:10%"><col style="width:9%"><col style="width:17%"><col style="width:8%"><col style="width:7%"><col style="width:9%"></colgroup><thead><tr><?php foreach(['match.ended','filter.server','match.map','match.mode','match.winner','match.duration','match.participants','match.details'] as $k): ?><th><?= translate($k) ?></th><?php endforeach; ?></tr></thead><tbody>
<?php foreach($history as $entry): $m=$entry['metadata']; $team=(int)$m['flags']!==0; $duration=ctype_digit((string)$m['weapon'])?(int)$m['weapon']:null; ?>
<tr><td><?= h(dt($m['timestamp'])) ?></td><td><?= h(matchServerName($m['serverip'])) ?></td><td><?= h(ggMapName($m['authid'])) ?: translate('match.unknown') ?></td><td><?= translate($team?'mode.team':'mode.solo') ?></td><td><?php if($team): ?><strong class="team-name team-<?= (int)$m['current_streak'] ?>"><?= translate([1=>'team.terrorists',2=>'team.counter_terrorists'][(int)$m['current_streak']] ?? 'match.unknown') ?></strong><?php else: ?><strong class="solo-winner"><?= isset($entry['winners'][0])?h($entry['winners'][0]['name']):translate('match.unknown') ?></strong><?php endif; ?></td><td><?= $duration===null?translate('match.unknown'):h(sprintf('%02d:%02d',intdiv($duration,60),$duration%60)) ?></td><td><?= num($entry['participants']) ?></td><td><a class="match-detail-link" href="<?= h(url(['view'=>'match','server'=>$m['serverip'],'ts'=>$m['timestamp'],'q'=>null,'p'=>null])) ?>"><?= translate('match.details') ?> →</a></td></tr>
<?php endforeach; ?>
</tbody></table></div><?php if(!$history): ?><p class="muted"><?= translate('history.empty') ?></p><?php endif; ?>
<?php if($maxPage>1): ?><div class="history-pagination"><?php if($page>1): ?><a href="<?= h(url(array_merge($matchLanguageParams,['p'=>$page-1]))) ?>">← <?= translate('history.prev') ?></a><?php endif; ?><span><?= num($page) ?> / <?= num($maxPage) ?></span><?php if($page<$maxPage): ?><a href="<?= h(url(array_merge($matchLanguageParams,['p'=>$page+1]))) ?>"><?= translate('history.next') ?> →</a><?php endif; ?></div><?php endif; ?>
</section>
