<?php
/**
 * Copyright (C) 2026 Pascal 'JamesDaRock' Neumann
 * SPDX-License-Identifier: GPL-2.0-or-later
 * Read-only Hall of Fame queries; existing per-server player identity is preserved.
 */
if (!defined('GUNGAME_WEB')) { http_response_code(404); exit; }
$mode = in_array($_GET['mode'] ?? '', ['solo','team'], true) ? $_GET['mode'] : 'all';
$statsTable = $c['tables']['stats'];
$matchTable = $c['tables']['lastgame'];
$servers = $db->query("SELECT serverip FROM $statsTable UNION SELECT serverip FROM $matchTable WHERE id=0 ORDER BY serverip")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array($server, $servers, true)) $server = '';
$args = $server === '' ? [] : ['s'=>$server];
$scope = $server === '' ? '' : ' AND serverip=:s';
$matchScope = $server === '' ? '' : ' AND m.serverip=:s';
$complete = 'm.id=0'.($mode === 'all' ? '' : ' AND m.flags'.($mode === 'team' ? '<>0' : '=0')).$matchScope." AND EXISTS (SELECT 1 FROM $matchTable x WHERE x.serverip=m.serverip AND x.timestamp=m.timestamp AND x.id<>0)";
$winExpr = '((m.flags<>0 AND (p.flags & 1)<>0) OR (m.flags=0 AND p.id=m.points))';
$modeExpr = "CASE WHEN m.flags<>0 THEN 'team' ELSE 'solo' END";
// Separate mode rows even when both modes are selected.
$statParts=[];
foreach ($mode==='all'?['solo','team']:[$mode] as $statMode) {
    $suffix=$statMode==='team'?'_tp':'';
    $statParts[]="SELECT authid,serverip,name,'$statMode' AS mode,wins$suffix AS wins,points$suffix AS points,streak$suffix AS streak FROM $statsTable";
}
$statSource='('.implode(' UNION ALL ',$statParts).') ranked';
// Aggregate each identity on its original server, never sum historical cumulative counters.
$identity = "p.authid,p.serverip,COALESCE(MAX(s.name),MAX(p.name)) AS name,$modeExpr AS mode";
$participation = "FROM $matchTable m JOIN $matchTable p ON p.serverip=m.serverip AND p.timestamp=m.timestamp AND p.id<>0 LEFT JOIN $statsTable s ON s.authid=p.authid AND s.serverip=p.serverip WHERE $complete AND p.authid<>'' GROUP BY p.authid,p.serverip,$modeExpr";
$durationFilter = " AND m.weapon REGEXP '^[0-9]+$' AND CAST(m.weapon AS UNSIGNED)>0";
$hallDefinitions = [
 'wins'=>['player','trophy',"SELECT authid,serverip,name,mode,wins AS value FROM $statSource WHERE wins>0$scope ORDER BY value DESC,points DESC,name,authid,serverip"],
 'points'=>['player','star',"SELECT authid,serverip,name,mode,points AS value FROM $statSource WHERE points>0$scope ORDER BY value DESC,wins DESC,name,authid,serverip"],
 'streak'=>['player','flame',"SELECT authid,serverip,name,mode,streak AS value FROM $statSource WHERE streak>0$scope ORDER BY value DESC,name,authid,serverip"],
 'played'=>['player','users',"SELECT $identity,COUNT(DISTINCT m.timestamp) AS value $participation ORDER BY value DESC,name,authid,serverip"],
 'rate'=>['rate','target',"SELECT $identity,COUNT(DISTINCT m.timestamp) AS played,COUNT(DISTINCT CASE WHEN $winExpr THEN m.timestamp END) AS won,100.0*COUNT(DISTINCT CASE WHEN $winExpr THEN m.timestamp END)/COUNT(DISTINCT m.timestamp) AS value $participation HAVING played>=10 ORDER BY value DESC,played DESC,name,authid,serverip"],
 'maps'=>['map','map',"SELECT m.authid AS name,COUNT(*) AS value FROM $matchTable m WHERE $complete AND m.authid<>'' GROUP BY m.authid ORDER BY value DESC,name"],
 'longest'=>['match','clock',"SELECT m.authid AS name,m.serverip,m.timestamp,$modeExpr AS mode,m.current_streak AS winning_team,(SELECT MAX(w.name) FROM $matchTable w WHERE w.serverip=m.serverip AND w.timestamp=m.timestamp AND w.id=m.points AND w.id<>0) AS winner,CAST(m.weapon AS UNSIGNED) AS value FROM $matchTable m WHERE $complete$durationFilter ORDER BY value DESC,m.timestamp DESC,m.serverip"],
 'shortest'=>['match','clock',"SELECT m.authid AS name,m.serverip,m.timestamp,$modeExpr AS mode,m.current_streak AS winning_team,(SELECT MAX(w.name) FROM $matchTable w WHERE w.serverip=m.serverip AND w.timestamp=m.timestamp AND w.id=m.points AND w.id<>0) AS winner,CAST(m.weapon AS UNSIGNED) AS value FROM $matchTable m WHERE $complete$durationFilter ORDER BY value ASC,m.timestamp DESC,m.serverip"],
];
// Map averages use only recorded positive durations.
$hallDefinitions['average']=['average','clock',"SELECT m.authid AS name,AVG(CAST(m.weapon AS UNSIGNED)) AS value,COUNT(*) AS played FROM $matchTable m WHERE $complete$durationFilter AND m.authid<>'' GROUP BY m.authid ORDER BY value DESC,name"];
$hallLists=[];
foreach ($hallDefinitions as $key=>[$kind,$icon,$sql]) {
    $stmt=$db->prepare($sql.' LIMIT 10');
    $stmt->execute($args);
    $hallLists[$key]=['kind'=>$kind,'icon'=>$icon,'rows'=>$stmt->fetchAll()];
}
function hallValue(array $row,string $kind): string {
    if ($kind==='rate') return number_format((float)$row['value'],1,($GLOBALS['lang']==='de'?',':'.'),'').'%';
    if ($kind==='match'||$kind==='average') { $v=(int)round($row['value']); return $v>=3600?sprintf('%d:%02d:%02d',intdiv($v,3600),intdiv($v%3600,60),$v%60):sprintf('%02d:%02d',intdiv($v,60),$v%60); }
    return num($row['value']);
}
