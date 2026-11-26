<?php
/** Copyright (C) 2026 Pascal 'JamesDaRock' Neumann
 * SPDX-License-Identifier: GPL-2.0-or-later
 * Shared match presentation helpers. */
if (!defined('GUNGAME_WEB')) { http_response_code(404); exit; }
function matchServerName(string $address): string {
    static $names = null;
    if ($names === null) {
        $names=[];
        $file=dirname(__DIR__).'/cache/servers.json';
        if (is_readable($file)) {
            $data=json_decode((string)file_get_contents($file),true);
            foreach (($data['servers'] ?? []) as $s) if (!empty($s['address']) && !empty($s['name'])) $names[$s['address']]=$s['name'];
        }
    }
    return $names[$address] ?? $address;
}
function matchGroups(array $rows): array {
    $groups=[1=>[],2=>[],0=>[]];
    foreach ($rows as $r) $groups[in_array((int)$r['team'],[1,2],true)?(int)$r['team']:0][]=$r;
    return array_filter($groups);
}
function matchTeamLabel(int $team): string { return translate([1=>'team.terrorists',2=>'team.counter_terrorists'][$team] ?? 'match.other_players'); }
function matchWeapon(string $weapon): string {
    if ($weapon==='') return '–';
    return ['knife'=>'Knife','hegrenade'=>'HE Grenade','glock18'=>'Glock 18','mp5navy'=>'MP5 Navy','fiveseven'=>'Five-SeveN','deagle'=>'Desert Eagle','sg552'=>'SG 552','m4a1'=>'M4A1','ak47'=>'AK-47'][$weapon] ?? strtoupper($weapon);
}
