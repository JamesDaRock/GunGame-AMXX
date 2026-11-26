<?php
/**
 * Copyright (C) 2026 Pascal 'JamesDaRock' Neumann
 * SPDX-License-Identifier: GPL-2.0-or-later
 * Read-only leaderboard queries. Rank before applying player search.
 */
if (!defined('GUNGAME_WEB')) { http_response_code(404); exit; }
$mode = in_array($_GET['mode'] ?? '', ['solo','team'], true) ? $_GET['mode'] : 'all';
$sort = in_array($_GET['sort'] ?? '', ['wins','points','streak'], true) ? $_GET['sort'] : 'wins';
$limit = $leaderLimit;
$table = $c['tables']['stats'];
$servers = $db->query("SELECT DISTINCT serverip FROM $table ORDER BY serverip")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array($server,$servers,true)) $server='';
// Keep server and mode identities separate, including in the combined view.
$parts=[];
foreach ($mode==='all'?['solo','team']:[$mode] as $m) {
    $suffix=$m==='team'?'_tp':'';
    $parts[]="SELECT authid,name,serverip,timestamp,'$m' AS mode,wins$suffix AS wins,points$suffix AS points,streak$suffix AS streak FROM $table";
}
$scope=$server===''?'':' AND serverip=:server';
$params=$server===''?[]:['server'=>$server];
// Competition ranking uses the selected metric only; secondary fields stabilize display order.
$source='('.implode(' UNION ALL ',$parts).') AS stats_modes';
$ranked="SELECT stats_modes.*, RANK() OVER (ORDER BY $sort DESC) AS player_rank FROM $source WHERE (wins>0 OR points>0 OR streak>0)$scope";
$search='';
if ($q!=='') { $search=' WHERE (name LIKE :name OR authid LIKE :auth)'; $params['name']='%'.$q.'%'; $params['auth']='%'.$q.'%'; }
$from=" FROM ($ranked) AS ranked$search";
$st=$db->prepare('SELECT COUNT(*)'.$from); $st->execute($params); $total=(int)$st->fetchColumn();
$maxPage=max(1,(int)ceil($total/$limit)); $page=min($page,$maxPage); $offset=($page-1)*$limit;
$st=$db->prepare("SELECT *$from ORDER BY player_rank,wins DESC,points DESC,streak DESC,name,authid,serverip,mode LIMIT :lim OFFSET :off");
foreach ($params as $k=>$v) $st->bindValue(':'.$k,$v);
$st->bindValue(':lim',$limit,PDO::PARAM_INT); $st->bindValue(':off',$offset,PDO::PARAM_INT); $st->execute(); $players=$st->fetchAll();
