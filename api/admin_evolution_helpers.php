<?php

function admin_fetch_json($url) {
    $context = stream_context_create([
        'http' => [
            'timeout' => 6,
            'user_agent' => 'PokemonRol/1.0'
        ]
    ]);

    $raw = @file_get_contents($url, false, $context);
    if (!$raw) {
        return null;
    }

    $data = json_decode($raw, true);
    return is_array($data) ? $data : null;
}

function admin_extract_species_id($url) {
    if (!is_string($url) || $url === '') {
        return null;
    }

    if (preg_match('/pokemon-species\/(\d+)/', $url, $matches)) {
        return (int)$matches[1];
    }

    return null;
}

function admin_find_next_evolutions($node, $targetId) {
    if (!is_array($node)) {
        return null;
    }

    $currentId = admin_extract_species_id($node['species']['url'] ?? '');
    if ($currentId === $targetId) {
        $children = $node['evolves_to'] ?? [];
        $ids = [];
        foreach ($children as $child) {
            $childId = admin_extract_species_id($child['species']['url'] ?? '');
            if ($childId) {
                $ids[] = $childId;
            }
        }
        return $ids;
    }

    foreach ($node['evolves_to'] ?? [] as $child) {
        $result = admin_find_next_evolutions($child, $targetId);
        if ($result !== null) {
            return $result;
        }
    }

    return null;
}

function admin_get_evolution_options_by_species($speciesId, $mysqli) {
    static $cache = [];

    $speciesId = (int)$speciesId;
    if ($speciesId <= 0) {
        return [];
    }

    if (array_key_exists($speciesId, $cache)) {
        return $cache[$speciesId];
    }

    $apiBase = 'https://pokeapi.co/api/v2';
    $species = admin_fetch_json($apiBase . '/pokemon-species/' . $speciesId);
    if (!$species || empty($species['evolution_chain']['url'])) {
        $cache[$speciesId] = [];
        return [];
    }

    $chain = admin_fetch_json($species['evolution_chain']['url']);
    if (!$chain || empty($chain['chain'])) {
        $cache[$speciesId] = [];
        return [];
    }

    $nextIds = admin_find_next_evolutions($chain['chain'], $speciesId);
    if ($nextIds === null || count($nextIds) === 0) {
        $cache[$speciesId] = [];
        return [];
    }

    $nextIds = array_values(array_unique(array_filter($nextIds, fn($id) => (int)$id > 0)));
    if (count($nextIds) === 0) {
        $cache[$speciesId] = [];
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($nextIds), '?'));
    $orderBy = ' ORDER BY FIELD(id, ' . implode(',', array_map('intval', $nextIds)) . ')';
    $sql = "SELECT id, nombre, sprite FROM pokemon_species WHERE id IN ($placeholders)" . $orderBy;

    $options = [];
    if ($stmt = $mysqli->prepare($sql)) {
        $types = str_repeat('i', count($nextIds));
        $stmt->bind_param($types, ...$nextIds);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $options[] = $row;
            }
        }
        $stmt->close();
    }

    $cache[$speciesId] = $options;
    return $options;
}
