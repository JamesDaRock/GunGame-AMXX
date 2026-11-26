<?php
/** Copyright (C) 2026 Pascal 'JamesDaRock' Neumann
 * SPDX-License-Identifier: GPL-2.0-or-later
 * Complete stored match results. */
if (!defined('GUNGAME_WEB')) { http_response_code(404); exit; }
require_once __DIR__.'/../includes/match_display.php';
?>
<p><a class="match-detail-link" href="<?= h(url(['view'=>'matches','server'=>null,'mode'=>'all','p'=>1,'q'=>null])) ?>">← <?= translate('history.back') ?></a></p>
<?php if ($meta): $hideMatchDetailLink=true; require __DIR__.'/../includes/match_cards.php'; endif; ?>
<section class="panel match-results">
<h2><?= translate('match.result') ?></h2>
<?php if ($lastgame && $meta):
$isTeam=(int)$meta['flags']!==0;
$groups=$isTeam?matchGroups($lastgame):[0=>$lastgame];
?>
<div class="<?= $isTeam?'match-result-groups':'match-solo-results' ?>">
<?php foreach($groups as $team=>$rows):
$winning=$isTeam && $team===(int)$meta['current_streak'];
$levels=array_unique(array_column($rows,'level'));
?>
<div class="match-result-team team-panel-<?= $team ?>">
<?php if($isTeam): ?><h3 class="team-name team-<?= $team ?>"><?= matchTeamLabel($team) ?> (<?= num(count($rows)) ?>) <?= $winning?' · '.translate('match.winner'):'' ?></h3><?php if(count($levels)===1): ?><p class="muted"><?= translate('match.level') ?>: <?= num(reset($levels)) ?></p><?php endif; endif; ?>
<div class="table-scroll"><table><thead><tr>
<?php if(!$isTeam): ?><th><?= translate('match.place') ?></th><?php endif; ?>
<?php foreach(['player.name','match.level','match.weapon','match.total_wins','match.total_points'] as $key): ?><th><?= translate($key) ?></th><?php endforeach; ?>
</tr></thead><tbody>
<?php $position=0;$rank=0;$previous=null; foreach($rows as $r): $position++; $level=(int)$r['level']; if($previous!==$level)$rank=$position; $previous=$level; $winner=!$isTeam && (int)$r['id']===(int)$meta['points']; ?>
<tr class="<?= $winner?'match-winning-row':'' ?>">
<?php if(!$isTeam): ?><td><?php if($winner): ?><svg class="match-rank-trophy" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3h8v7a4 4 0 0 1-8 0V3ZM8 5H4v2a4 4 0 0 0 4 4m8-6h4v2a4 4 0 0 1-4 4M12 14v5m-4 2h8m-6-2h4"/></svg><?php endif; ?><?= num($rank) ?></td><?php endif; ?>
<td><a class="team-name team-<?= $team ?>" href="<?= h(url(['view'=>'player','player'=>$r['authid'],'server'=>$meta['serverip']])) ?>"><?= h($r['name']) ?></a><?php if($isTeam && $winning && ((int)$r['flags']&4)): ?> <small class="solo-winner"><?= translate('match.final_kill') ?></small><?php endif; ?></td>
<td><?= num($level) ?></td><td><?= h(matchWeapon((string)$r['weapon'])) ?></td><td><?= num($r['wins']) ?></td><td><?= num($r['points']) ?></td></tr>
<?php endforeach; ?></tbody></table></div></div>
<?php endforeach; ?></div>
<?php else: ?><p class="muted"><?= translate('match.empty') ?></p><?php endif; ?>
</section>
