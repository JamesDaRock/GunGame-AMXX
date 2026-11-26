<?php
/** Copyright (C) 2026 Pascal 'JamesDaRock' Neumann
 * SPDX-License-Identifier: GPL-2.0-or-later
 * Read-only match history using the existing WinMOTD schema.
 */
if (!defined('GUNGAME_WEB')) { http_response_code(404); exit; }
$mt = $c['tables']['lastgame'];
$complete = "m.id=0 AND EXISTS (SELECT 1 FROM $mt p WHERE p.serverip=m.serverip AND p.timestamp=m.timestamp AND p.id<>0)";
$loadRows = $db->prepare("SELECT * FROM $mt WHERE serverip=:s AND timestamp=:t AND id<>0 ORDER BY level DESC,new_points DESC,id ASC");
function matchEntry(array $m, array $rows): array {
    return ['metadata'=>$m,'participants'=>count($rows),'rows'=>$rows,'winners'=>array_values(array_filter($rows, static fn($r)=>(int)$m['flags']!==0 ? ((int)$r['flags']&1)!==0 : (int)$r['id']===(int)$m['points']))];
}
$mode = in_array($_GET['mode'] ?? '', ['solo','team'], true) ? $_GET['mode'] : 'all';
if ($route === 'match') {
    $ts = filter_var($_GET['ts'] ?? '', FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
    $matchLanguageParams = ['ts'=>$ts ?: ''];
    $st=$db->prepare("SELECT m.* FROM $mt m WHERE $complete AND m.serverip=:s AND m.timestamp=:t");
    $st->execute(['s'=>$server,'t'=>$ts ?: 0]);
    $meta=$st->fetch() ?: null;
    if ($meta) { $loadRows->execute(['s'=>$server,'t'=>$ts]); $lastgame=$loadRows->fetchAll(); $latestMatches=[matchEntry($meta,$lastgame)]; }
    else http_response_code(404);
    return;
}
// Latest complete match is independent of every history filter.
$m=$db->query("SELECT m.* FROM $mt m WHERE $complete ORDER BY m.timestamp DESC,m.serverip ASC LIMIT 1")->fetch();
if ($m) { $loadRows->execute(['s'=>$m['serverip'],'t'=>$m['timestamp']]); $latestMatches=[matchEntry($m,$loadRows->fetchAll())]; }
$servers=$db->query("SELECT DISTINCT serverip FROM $mt WHERE id=0 ORDER BY serverip")->fetchAll(PDO::FETCH_COLUMN);
$maps=$db->query("SELECT DISTINCT authid FROM $mt WHERE id=0 AND authid<>'' ORDER BY authid")->fetchAll(PDO::FETCH_COLUMN);
$mapFilter=is_string($_GET['map'] ?? null) && in_array($_GET['map'],$maps,true) ? $_GET['map'] : '';
if (!in_array($server,$servers,true)) $server='';
function historyDate($v): string { if (!is_string($v) || !preg_match('/^\d{4}-\d{2}-\d{2}$/D',$v)) return ''; $d=DateTimeImmutable::createFromFormat('!Y-m-d',$v); return $d && $d->format('Y-m-d')===$v ? $v : ''; }
$from=historyDate($_GET['from'] ?? ''); $to=historyDate($_GET['to'] ?? '');
$matchLanguageParams=['map'=>$mapFilter,'from'=>$from,'to'=>$to];
$where=$complete; $args=[];
if ($server!=='') { $where.=' AND m.serverip=:s'; $args['s']=$server; }
if ($mode!=='all') $where.=$mode==='team'?' AND m.flags<>0':' AND m.flags=0';
if ($mapFilter!=='') { $where.=' AND m.authid=:map'; $args['map']=$mapFilter; }
if ($from!=='') { $where.=' AND m.timestamp>=:from'; $args['from']=strtotime($from); }
if ($to!=='') { $where.=' AND m.timestamp<:until'; $args['until']=strtotime($to.' +1 day'); }
if ($q!=='') { $where.=" AND EXISTS (SELECT 1 FROM $mt p WHERE p.serverip=m.serverip AND p.timestamp=m.timestamp AND p.id<>0 AND (p.name LIKE :q1 OR p.authid LIKE :q2))"; $args['q1']=$args['q2']='%'.$q.'%'; }
$st=$db->prepare("SELECT COUNT(*) FROM $mt m WHERE $where"); $st->execute($args); $total=(int)$st->fetchColumn();
$limit=in_array((int)($_GET['limit'] ?? 25),[10,25,50,100],true)?(int)($_GET['limit'] ?? 25):25; $matchLanguageParams['limit']=$limit; $maxPage=max(1,(int)ceil($total/$limit)); $page=min($page,$maxPage); $offset=($page-1)*$limit;
$st=$db->prepare("SELECT m.* FROM $mt m WHERE $where ORDER BY m.timestamp DESC,m.serverip ASC LIMIT $limit OFFSET $offset"); $st->execute($args); $history=[];
foreach ($st->fetchAll() as $m) { $loadRows->execute(['s'=>$m['serverip'],'t'=>$m['timestamp']]); $history[]=matchEntry($m,$loadRows->fetchAll()); }
