<?php
/**
 * Copyright (C) 2026 Pascal 'JamesDaRock' Neumann
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 *
 * This file is part of GunGame AMXX 3++ - pur3gaming Edition.
 *
 * See LICENSE for the full license text.
 */

/** Render compact summaries of the latest stored match on each server. */
if (!defined('GUNGAME_WEB')) { http_response_code(404); exit; }
require_once __DIR__ . '/gameserver.php';
require_once __DIR__ . '/match_display.php';
?>
<section class="latest-matches">
    <?php if (empty($hideMatchDetailLink)): ?><div class="section-title"><h2><?= translate($matchHeading ?? 'match.latest_per_server') ?></h2></div><?php endif; ?>
    <?php if (!$latestMatches): ?><p class="muted"><?= translate('match.empty') ?></p><?php endif; ?>
    <div class="match-card-grid">
    <?php foreach ($latestMatches as $entry):
        $match = $entry['metadata'];
        // RC3 stores played map in metadata authid, duration in weapon and next map in name.
        $isTeam = (int)$match['flags'] !== 0;
        // Metadata current_streak stores the winning team (1=T, 2=CT).
        $winningTeam = (int)$match['current_streak'];
        $map = ggMapName($match['authid']);
        $nextMap = ggMapName($match['name']);
        $duration = ctype_digit((string)$match['weapon']) ? (int)$match['weapon'] : null;
        $image = ggMapImage('cs16', $map);
        if ($image === 'assets/images/maps/noimage.jpg' && $map !== '') $image = ggMapImage('cscz', $map);
    ?>
        <article class="panel match-summary-card">
            <div class="section-title"><strong data-match-server="<?= h($match['serverip']) ?>"><?= h(matchServerName($match['serverip'])) ?></strong><span class="match-mode"><?= translate((int)$match['flags'] !== 0 ? 'mode.team' : 'mode.solo') ?></span></div>
            <!-- Match map, recorded facts and winners. -->
            <div class="match-summary-body">
                <figure class="match-map">
                    <img src="<?= h(asset_url($image)) ?>" alt="" loading="lazy" width="240" height="140">
                    <figcaption><?= $map !== '' ? h($map) : translate('match.unknown') ?></figcaption>
                </figure>
                <dl class="match-summary-facts">
                    <div><dt><?= translate('match.duration') ?></dt><dd class="match-metric"><?= $duration === null ? translate('match.unknown') : h(($duration >= 3600 ? sprintf('%d:%02d:%02d', intdiv($duration,3600), intdiv($duration%3600,60), $duration%60) : sprintf('%02d:%02d', intdiv($duration,60), $duration%60))) ?></dd></div>
                    <div><dt><?= translate('match.participants') ?></dt><dd class="match-metric"><?= num($entry['participants']) ?></dd></div>
                    <div><dt><?= translate('match.ended') ?></dt><dd><?= h(dt($match['timestamp'])) ?></dd></div>
                    <div><dt><?= translate('match.next_map') ?></dt><dd><?= $nextMap !== '' ? h($nextMap) : translate('match.unknown') ?></dd></div>
                </dl>
                <div class="match-winners">
                    <svg class="match-trophy" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3h8v7a4 4 0 0 1-8 0V3ZM8 5H4v2a4 4 0 0 0 4 4m8-6h4v2a4 4 0 0 1-4 4M12 14v5m-4 2h8m-6-2h4"/></svg>
                    <div><small><?= translate($isTeam ? 'match.winning_team' : 'match.winner') ?></small>
                        <?php if ($isTeam): ?>
                            <strong class="team-name team-<?= $winningTeam ?>"><?= translate($winningTeam === 1 ? 'team.terrorists' : ($winningTeam === 2 ? 'team.counter_terrorists' : 'match.unknown')) ?></strong>
                            <small><?= num(count($entry['winners'])) ?> <?= translate('match.winners') ?></small>
                        <?php else: ?>
                        <?php foreach ($entry['winners'] as $winner): ?>
                            <a href="<?= h(url(['view'=>'player','player'=>$winner['authid'],'server'=>$match['serverip']])) ?>"><?= h($winner['name']) ?></a>
                        <?php endforeach; ?>
                        <?php if (!$entry['winners']): ?><strong><?= translate('match.unknown') ?></strong><?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php if (empty($hideMatchDetailLink) && !empty($entry['rows'])): ?>
                <details class="match-team-list"><summary><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m20 0v-2a4 4 0 0 0-3-4m-3-12a4 4 0 0 1 0 8"/><circle cx="9" cy="7" r="4"/></svg><?= translate('match.show_result') ?> (<?= num($entry['participants']) ?>)<svg class="match-chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg></summary>
                    <div class="match-result-groups">
                    <?php foreach ($isTeam?matchGroups($entry['rows']):[0=>$entry['rows']] as $team=>$rows): ?>
                        <div><?php if ($isTeam): ?><h3 class="team-name team-<?= $team ?>"><?= matchTeamLabel($team) ?> (<?= num(count($rows)) ?>)<?= $team===$winningTeam?' · '.translate('match.winner'):'' ?></h3><?php endif; ?>
                        <div class="match-team-members<?= $isTeam?'':' match-solo-members' ?>"><?php foreach ($rows as $r): $soloWinner=!$isTeam && (int)$r['id']===(int)$match['points']; ?><a class="team-name team-<?= $team ?><?= $soloWinner?' match-member-winner':'' ?>" href="<?= h(url(['view'=>'player','player'=>$r['authid'],'server'=>$match['serverip']])) ?>"><?php if($soloWinner): ?><svg class="match-rank-trophy" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3h8v7a4 4 0 0 1-8 0V3ZM8 5H4v2a4 4 0 0 0 4 4m8-6h4v2a4 4 0 0 1-4 4M12 14v5m-4 2h8m-6-2h4"/></svg><?php endif; ?><?= h($r['name']) ?></a><?php endforeach; ?></div></div>
                    <?php endforeach; ?></div>
                </details>
            <?php endif; ?>
            <?php if (empty($hideMatchDetailLink)): ?><div class="match-summary-footer">
                <a class="match-detail-link" href="<?= h(url(['view'=>'match','ts'=>$match['timestamp'],'server'=>$match['serverip'],'q'=>'','p'=>1,'player'=>''])) ?>"><?= translate('match.details') ?> →</a>
            </div><?php endif; ?>
        </article>
    <?php endforeach; ?>
    </div>
</section>
