<?php
include 'db.php';
session_start();
if (!isset($_SESSION['user'])) {
  $_SESSION['error'] = 'Debes iniciar sesión para ver el panel.';
  header('Location: index.php'); exit;
}
$userRaw = $_SESSION['user'];
$user = htmlspecialchars(($userRaw['nombre'] ?? $userRaw['correo'] ?? 'Entrenador'));
$user_id = (int)($userRaw['id'] ?? 0);

$inventory = [];
$box = [];
$team = [];
$pokedex = [];
$species = [];
// caja, inventario, equipo, pokedex. inicial de prueba
if ($user_id > 0) {
  $sql = "SELECT i.cantidad, i.item_id, it.nombre, it.clave, it.icono, it.descripcion, it.effect_type, it.effect_value FROM inventario i JOIN items it ON it.id=i.item_id WHERE i.user_id = ?";
  if ($stmt = $mysqli->prepare($sql)) { $stmt->bind_param('i', $user_id); $stmt->execute(); $res = $stmt->get_result(); while ($r = $res->fetch_assoc()) $inventory[] = $r; $stmt->close(); }

  $sql = "SELECT pb.*, ps.nombre AS especie, ps.sprite AS sprite FROM pokemon_box pb JOIN pokemon_species ps ON ps.id = pb.species_id WHERE pb.user_id = ? ORDER BY pb.created_at DESC";
  if ($stmt = $mysqli->prepare($sql)) { $stmt->bind_param('i', $user_id); $stmt->execute(); $res = $stmt->get_result(); while ($r = $res->fetch_assoc()) $box[] = $r; $stmt->close(); }

  $sql = "SELECT t.slot, pb.id AS box_id, ps.nombre AS especie, ps.sprite AS sprite, pb.apodo, pb.nivel FROM team t LEFT JOIN pokemon_box pb ON t.pokemon_box_id = pb.id LEFT JOIN pokemon_species ps ON pb.species_id = ps.id WHERE t.user_id = ? ORDER BY t.slot ASC";
  if ($stmt = $mysqli->prepare($sql)) { $stmt->bind_param('i', $user_id); $stmt->execute(); $res = $stmt->get_result(); while ($r = $res->fetch_assoc()) $team[] = $r; $stmt->close(); }

  $sql = "SELECT p.species_id, ps.nombre, p.visto, p.capturado, p.veces_visto, p.first_seen_at FROM pokedex p JOIN pokemon_species ps ON p.species_id = ps.id WHERE p.user_id = ?";
  if ($stmt = $mysqli->prepare($sql)) { $stmt->bind_param('i', $user_id); $stmt->execute(); $res = $stmt->get_result(); while ($r = $res->fetch_assoc()) $pokedex[$r['species_id']] = $r; $stmt->close(); }

  $sql = "SELECT id, nombre, sprite FROM pokemon_species ORDER BY id LIMIT 200";
  if ($stmt = $mysqli->prepare($sql)) { $stmt->execute(); $res = $stmt->get_result(); while ($r = $res->fetch_assoc()) $species[] = $r; $stmt->close(); }
}

$shop_items = [];
if ($user_id > 0) {
  $sql = "SELECT id, clave, nombre, descripcion, icono, price FROM items WHERE price IS NOT NULL ORDER BY id";
  if ($stmt = $mysqli->prepare($sql)) { $stmt->execute(); $res = $stmt->get_result(); while ($r = $res->fetch_assoc()) $shop_items[] = $r; $stmt->close(); }
}

$money = 0.00;
if ($user_id > 0) {
  $sql = "SELECT money FROM usuarios WHERE id = ? LIMIT 1";
  if ($stmt = $mysqli->prepare($sql)) { $stmt->bind_param('i', $user_id); $stmt->execute(); $res = $stmt->get_result(); if ($r = $res->fetch_assoc()) $money = (float)$r['money']; $stmt->close(); }
}

$equippedBoxMap = [];
foreach ($team as $t) {
  if (!empty($t['box_id'])) $equippedBoxMap[$t['box_id']] = $t['slot'];
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Panel - Pokémon Rol</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body class="p-4">
  <div class="container">
      <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1>Bienvenido, <?= $user; ?></h1>
        <p class="small-muted"><?= htmlspecialchars($userRaw['correo'] ?? '') ?> · Tu panel de entrenador</p>
        <?php if (isset($money)): ?>
          <div class="mt-1"><span class="badge bg-warning text-dark">Saldo: € <?= number_format($money, 2, ',', '.'); ?></span></div>
        <?php endif; ?>
      </div>
      <div>
        <?php if ($user_id === 67): ?>
          <a href="admin.php" class="btn btn-primary me-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-shield-fill-check" viewBox="0 0 16 16">
              <path fill-rule="evenodd" d="M8 0c-.69 0-1.843.265-2.928.56-1.11.3-2.229.655-2.887.87a1.54 1.54 0 0 0-1.044 1.262c-.596 4.477.787 7.795 2.465 9.99a11.8 11.8 0 0 0 2.517 2.453c.386.273.744.482 1.048.625.28.132.581.24.829.24s.548-.108.829-.24a7 7 0 0 0 1.048-.625 11.8 11.8 0 0 0 2.517-2.453c1.678-2.195 3.061-5.513 2.465-9.99a1.54 1.54 0 0 0-1.044-1.263 63 63 0 0 0-2.887-.87C9.843.266 8.69 0 8 0m2.146 5.146a.5.5 0 0 1 .708.708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793z"/>
            </svg>
            Panel Master
          </a>
        <?php endif; ?>
        <a href="index.php" class="btn btn-outline-dark">Cerrar sesión</a>
      </div>
    </div>
    <div aria-live="polite" aria-atomic="true" style="position: relative; min-height: 100px;">
      <div id="toastContainer" style="position: absolute; top: 0; right: 0;"></div>
    </div>
    <div class="dashboard-container">
      <aside class="sidebar">
        <h5 class="mb-3">Menú</h5>
          <ul class="nav nav-pills flex-column" id="menuTabs" role="tablist">
            <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#inventario" type="button"> <span class="emoji">👜</span> Inventario</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#caja" type="button"> <span class="emoji">📦</span> Caja Pokémon</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#equipo" type="button"> <span class="emoji">⚔️</span> Equipo</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#pokedex" type="button"> <span class="emoji">📘</span> Pokédex</button></li>
             <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#d100" type="button"> <span class="emoji">🎲</span> Tirar D100</button></li>
          </ul>
      </aside>
        <main class="flex-fill">
          <div class="d-block d-md-none mb-3">
            <nav class="nav nav-pills justify-content-around">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#d100">🎲</button>
              <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#inventario">👜</button>
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#caja">📦</button>
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#equipo">⚔️</button>
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pokedex">📘</button>
            </nav>
          </div>
          <div class="tab-content">
            <!-- D100 TAB -->
            <div class="tab-pane fade" id="d100" role="tabpanel">
              <div class="card p-3 card-section">
                <div class="section-title">Tirar Dado</div>
                <div class="small-muted mb-2">Tira un dado del rango que elijas. Se guarda el historial en tu navegador.</div>
                <div class="d-flex align-items-center gap-3 mb-3">
                  <label for="diceRange" class="fw-bold me-2">Rango (1 -</label>
                  <input type="number" id="diceRange" class="form-control" style="width: 120px;" value="100" min="1" max="10000">
                  <span class="fw-bold">)</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                  <div class="d-flex flex-column">
                    <div>
                      <button class="btn btn-lg btn-primary me-2" onclick="rollD100()">🎲 Tirar Dado</button>
                    </div>
                    <div class="mt-2"><small class="text-muted" id="diceRangeText">Tirada de 1-100. Se registra si estás autenticado.</small></div>
                  </div>
                  <div id="d100Result" class="fw-bold fs-3">--</div>
                </div>
                <div class="mt-3">
                  <div class="d-flex justify-content-between align-items-center">
                    <div class="fw-bold">Historial</div>
                    <button class="btn btn-sm btn-outline-danger" onclick="clearD100History()" title="Borrar todo el historial">
                      🗑️ Limpiar
                    </button>
                  </div>
                  <ul id="d100History" class="list-group mt-2"></ul>
                </div>
              </div>
            </div>
            <!-- INVENTARIO TAB -->
            <div class="tab-pane fade show active" id="inventario" role="tabpanel">
              <div class="card p-3 card-section">
                <div class="section-title">Inventario</div>
                <div class="small-muted mb-2">Objetos disponibles</div>
                <div class="d-grid gap-2">
                  <?php if (count($inventory) === 0): ?>
                  <div class="small-muted">No tienes items en el inventario.</div>
                  <?php else: foreach ($inventory as $it): ?>
                  <?php $iconClass = 'ball';
                        if (strpos(strtolower($it['clave']), 'potion') !== false) $iconClass = 'potion';
                        if (strpos(strtolower($it['clave']), 'great') !== false || strpos(strtolower($it['clave']), 'super') !== false) $iconClass = 'pokeball';
                  ?>
                  <div class="item-card" data-item-id="<?= (int)$it['item_id'] ?>" data-item-clave="<?= htmlspecialchars($it['clave']) ?>" data-effect-type="<?= htmlspecialchars($it['effect_type'] ?? '') ?>" data-effect-value="<?= htmlspecialchars($it['effect_value'] ?? '') ?>">
                    <?php
                      $iconPath = '';
                      if (!empty($it['icono'])) {
                        $given = $it['icono'];
                        $base = pathinfo($given, PATHINFO_FILENAME);
                        $svgPathLocal = __DIR__ . '/img/items/' . $base . '.svg';
                        $givenLocal = __DIR__ . '/img/items/' . $given;
                        $pngLocal = __DIR__ . '/img/items/' . $base . '.png';
                        if (file_exists($svgPathLocal)) {
                          $iconPath = 'img/items/' . $base . '.svg';
                        } elseif (file_exists($givenLocal)) {
                          $iconPath = 'img/items/' . $given;
                        } elseif (file_exists($pngLocal)) {
                          $iconPath = 'img/items/' . $base . '.png';
                        }
                      }
                      if ($iconPath === '') $iconPath = 'img/items/default.svg';
                    ?>
                    <div class="item-avatar <?= $iconClass ?>">
                      <img class="item-img" src="<?= htmlspecialchars($iconPath) ?>" alt="<?= htmlspecialchars($it['nombre'] ?? '') ?>">
                    </div>
                    <div class="item-meta">
                      <div class="fw-bold"><?= htmlspecialchars($it['nombre']); ?></div>
                      <div class="small-muted">Cantidad: <span class="item-qty"><?= (int)$it['cantidad'] ?></span></div>
                      <?php if (!empty($it['effect_type'])): ?>
                      <div class="tiny-muted">Efecto: <?= htmlspecialchars($it['effect_type']) ?><?= isset($it['effect_value']) && $it['effect_value'] !== null ? ' (' . htmlspecialchars($it['effect_value']) . ')' : '' ?></div>
                      <?php endif; ?>
                    </div>
                    <div class="item-actions">
                      <button class="btn btn-sm btn-outline-primary" onclick="useItem(<?= (int)$it['item_id'] ?>, this)">Usar</button>
                      <button class="btn btn-sm btn-outline-secondary" onclick="showSendItemModal(<?= (int)$it['item_id'] ?>)">Enviar</button>
                    </div>
                  </div>
                  <?php endforeach; endif; ?>
                </div>
              </div>
              <!-- Tienda: items con precio -->
              <div class="card p-3 mt-3">
                <div class="section-title">Tienda</div>
                <div class="small-muted mb-2">Compra objetos disponibles</div>
                <div class="d-grid gap-2">
                  <?php if (empty($shop_items)): ?>
                    <div class="small-muted">No hay artículos en la tienda.</div>
                  <?php else: foreach ($shop_items as $si): ?>
                    <div class="d-flex align-items-center justify-content-between shop-item">
                      <div class="d-flex align-items-center">
                        <?php
                          $shopIconPath = '';
                          if (!empty($si['icono'])) {
                            $given = $si['icono'];
                            $base = pathinfo($given, PATHINFO_FILENAME);
                            $svgLocal = __DIR__ . '/img/items/' . $base . '.svg';
                            $givenLocal = __DIR__ . '/img/items/' . $given;
                            $pngLocal = __DIR__ . '/img/items/' . $base . '.png';
                            if (file_exists($svgLocal)) {
                              $shopIconPath = 'img/items/' . $base . '.svg';
                            } elseif (file_exists($givenLocal)) {
                              $shopIconPath = 'img/items/' . $given;
                            } elseif (file_exists($pngLocal)) {
                              $shopIconPath = 'img/items/' . $base . '.png';
                            }
                          }
                          if ($shopIconPath === '') $shopIconPath = 'img/items/default.svg';
                        ?>
                        <div class="item-avatar" style="width:48px;height:48px; margin-right:12px;">
                          <img src="<?= htmlspecialchars($shopIconPath) ?>" alt="<?= htmlspecialchars($si['nombre']) ?>">
                        </div>
                        <div>
                          <div class="fw-bold"><?= htmlspecialchars($si['nombre']) ?></div>
                          <div class="small-muted"><?= htmlspecialchars($si['descripcion'] ?? '') ?></div>
                        </div>
                      </div>
                      <div class="text-end">
                        <div class="fw-bold">€ <?= number_format((float)$si['price'], 2, ',', '.') ?></div>
                        <div class="mt-1"><button class="btn btn-sm btn-primary" onclick="buyItem('<?= htmlspecialchars($si['clave']) ?>', 1)">Comprar</button></div>
                      </div>
                    </div>
                  <?php endforeach; endif; ?>
                </div>
              </div>
            </div>

            <!-- CAJA TAB -->
            <div class="tab-pane fade" id="caja" role="tabpanel">
              <div class="card p-3 card-section">
                <div class="section-title">Caja Pokémon</div>
                <div class="small-muted mb-2">Pokémon guardados</div>
                <div class="d-grid gap-2">
                  <?php if (count($box) === 0): ?>
                  <div class="small-muted">No hay pokémon en la caja.</div>
                  <?php else: foreach($box as $pb): ?>
                  <?php $equippedSlot = isset($equippedBoxMap[$pb['id']]) ? (int)$equippedBoxMap[$pb['id']] : null; ?>
                  <div class="pokemon-card" data-box-id="<?= (int)$pb['id'] ?>" data-species-id="<?= (int)$pb['species_id'] ?>">
                    <div class="pokemon-avatar">
                      <?php if (!empty($pb['sprite'])): ?>
                        <img src="img/pokemon/<?= htmlspecialchars($pb['sprite']) ?>.jpg" class="pokemon-img" alt="<?= htmlspecialchars($pb['apodo'] ?? $pb['especie'] ?? 'Pokémon') ?>">
                      <?php else: ?>
                        ⚡
                      <?php endif; ?>
                    </div>
                    <div class="pokemon-meta">
                      <h5><?= htmlspecialchars($pb['apodo'] ?? $pb['especie']); ?></h5>
                      <small>Nivel <?= (int)($pb['nivel'] ?? 0) ?><?php if ($pb['hp'] !== null): ?> · HP <?= (int)$pb['hp'] ?><?php endif; ?></small>
                    </div>
                    <div class="item-actions">
                      <?php if ($equippedSlot): ?>
                      <button class="btn btn-sm btn-outline-success" disabled>Equipado (<?= $equippedSlot ?>)</button>
                      <button class="btn btn-sm btn-outline-primary" onclick="showEquipModal(<?= (int)$pb['id'] ?>)">Mover</button>
                      <?php else: ?>
                      <button class="btn btn-sm btn-outline-primary" onclick="showEquipModal(<?= (int)$pb['id'] ?>)">Mover</button>
                      <?php endif; ?>
                      <button class="btn btn-sm btn-outline-secondary" onclick="showSendItemModal(<?= (int)$pb['id'] ?>)">Enviar</button>
                      <button class="btn btn-sm btn-outline-info" onclick="markAsSeen(<?= (int)$pb['species_id'] ?>)">Marcar Pokédex</button>
                      <button class="btn btn-sm btn-outline-warning pokemon-info-btn" onclick="showPokemonInfo(<?= (int)$pb['id'] ?>)">ℹ️ Info</button>
                    </div>
                  </div>
                  <?php endforeach; endif; ?>
                </div>
              </div>
            </div>

            <!-- EQUIPO TAB -->
            <div class="tab-pane fade" id="equipo" role="tabpanel">
              <div class="card p-3 card-section">
                <div class="section-title">Equipo Pokémon (debes tener un pokémon en la caja pokémon)</div>
                <div class="small-muted mb-2">Tu equipo activo</div>
                <div class="team-grid">
                  <?php for ($s=1;$s<=6;$s++): 
                      $slot = null;
                      foreach ($team as $t) if ((int)$t['slot'] === $s) { $slot = $t; break; }
                  ?>
                  <div class="team-slot" data-slot="<?= $s ?>">
                    <div class="pokemon-avatar">
                      <?php if (!$slot): ?>
                        ✨
                      <?php elseif (!empty($slot['sprite'])): ?>
                        <img src="img/pokemon/<?= htmlspecialchars($slot['sprite']) ?>.jpg" class="pokemon-img" alt="<?= htmlspecialchars($slot['especie'] ?? ($slot['apodo'] ?? 'Pokémon')) ?>">
                      <?php else: ?>
                        ⚔️
                      <?php endif; ?>
                    </div>
                    <div>
                      <div class="fw-bold"><?= $slot ? htmlspecialchars($slot['apodo'] ?? ($slot['especie'] ?? '')) : 'Libre' ?></div>
                      <div class="small-muted"><?= $slot ? ('Nivel ' . (int)($slot['nivel'] ?? 0)) : 'Vacío' ?></div>
                    </div>
                    <div class="item-actions">
                      <?php if ($slot): ?>
                      <button class="btn btn-sm btn-outline-danger" onclick="unequip(<?= $s ?>)">Desequipar</button>
                      <button class="btn btn-sm btn-outline-warning pokemon-info-btn" onclick="showPokemonInfo(<?= (int)$slot['box_id'] ?>)">ℹ️ Info</button>
                      <?php else: ?>
                      <button class="btn btn-sm btn-outline-primary" onclick="showEquipModal(null, <?= $s ?>)">Equipar</button>
                      <?php endif; ?>
                    </div>
                  </div>
                  <?php endfor; ?>
                </div>
              </div>
            </div>

            <!-- POKEDEX TAB -->
            <div class="tab-pane fade" id="pokedex" role="tabpanel">
              <div class="card p-3 card-section">
                <div class="section-title">Pokédex</div>
                <ul class="nav nav-tabs" id="pokedexTabs" role="tablist">
                  <li class="nav-item" role="presentation"><button class="nav-link active" id="pokemons-tab" data-bs-toggle="tab" data-bs-target="#pokemons" type="button" role="tab">Pokémon</button></li>
                  <li class="nav-item" role="presentation"><button class="nav-link" id="personajes-tab" data-bs-toggle="tab" data-bs-target="#personajes" type="button" role="tab">Personajes</button></li>
                </ul>
                <div class="tab-content mt-3" id="pokedexContent">
                  <div class="tab-pane fade show active" id="pokemons" role="tabpanel">
                    <div class="small-muted mb-2">Tu lista de Pokémon en la Pokédex</div>
                    <div class="grid-unknown">
                      <?php foreach ($species as $sp):
                        $entry = $pokedex[$sp['id']] ?? null;
                        $seen = $entry && (int)$entry['visto'] === 1;
                      ?>
                      <div class="unknown-item" data-species-id="<?= $sp['id'] ?>" data-seen="<?= $seen ? '1' : '0' ?>">
                        <div class="unknown-avatar">
                          <?php if ($seen && !empty($sp['sprite'])): ?>
                            <img src="img/pokemon/<?= htmlspecialchars($sp['sprite']) ?>.jpg" class="pokemon-img" alt="<?= htmlspecialchars($sp['nombre']) ?>">
                          <?php else: ?>
                            <?= $seen ? '🐾' : '?' ?>
                          <?php endif; ?>
                        </div>
                        <div>
                          <div class="fw-bold"><?= $seen ? htmlspecialchars($sp['nombre']) : 'Desconocido' ?></div>
                          <div class="small-muted"><?= $seen ? 'Especie' : 'Sin registrar' ?></div>
                        </div>
                      </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                  <div class="tab-pane fade" id="personajes" role="tabpanel">
                    <div class="small-muted mb-2">Personajes descubiertos en tu aventura</div>
                    <div class="grid-unknown">
                      <?php for ($i=0;$i<8;$i++): ?>
                      <div class="unknown-item">
                        <div class="unknown-avatar">?</div>
                        <div>
                          <div class="fw-bold">Desconocido</div>
                          <div class="small-muted">Rol --</div>
                        </div>
                      </div>
                      <?php endfor; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </main>
      </div>
    </div>

  <div class="modal fade" id="equipModal" tabindex="-1" aria-labelledby="equipModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="equipModalLabel">Equipar Pokémon</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div id="equipModalPokemonInfo" class="mb-2"></div>
          <div class="d-grid gap-2">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary" id="equipConfirmBtn">Confirmar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="sendItemModal" tabindex="-1" aria-labelledby="sendItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="sendItemModalLabel">Enviar Item</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div id="sendItemModalInfo" class="mb-2">Introduce el correo del destinatario</div>
          <input type="email" id="sendRecipientEmail" class="form-control mb-2" placeholder="correo@ejemplo.com">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary" id="sendItemConfirmBtn">Enviar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="useItemModal" tabindex="-1" aria-labelledby="useItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="useItemModalLabel">Usar Item</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div id="useItemModalInfo" class="mb-2">Selecciona el Pokémon objetivo</div>
          <div id="useItemModalGrid" class="d-grid gap-2"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL: Información detallada del Pokémon -->
  <div class="modal fade" id="pokemonInfoModal" tabindex="-1" aria-labelledby="pokemonInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header pokemon-info-header" style="margin: 0;">
          <h3 class="modal-title" id="pokemonInfoModalLabel" style="flex:1; text-align:center;">Información Pokémon</h3>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar" style="position: absolute; right: 10px; top: 10px;"></button>
        </div>
        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
          <div id="pokemonInfoContent" class="pokemon-info-content">
            <!-- Content loaded by JavaScript -->
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

      </main>
    </div>
  </div>
</body>
</html>
<script>
  const tabLinks = document.querySelectorAll('.sidebar .nav-link, .d-block.d-md-none .nav-link');
  tabLinks.forEach(link => {
    link.addEventListener('click', function(e) {
      tabLinks.forEach(l=>l.classList.remove('active'));
      this.classList.add('active');
      try {
        const target = this.dataset.bsTarget || this.getAttribute('data-bs-target');
        if (target) localStorage.setItem('dashboard_active_tab', target);
      } catch(e) {}
    });
  });

  async function useItem(itemId, btn) {
    try {
      const card = btn.closest('.item-card');
      const effectType = card ? (card.dataset.effectType || '').toLowerCase() : '';
      const clave = card ? (card.dataset.itemClave || '').toLowerCase() : '';
      const isPokeball = !!(clave && /ball/.test(clave));
      const needsTarget = effectType && effectType.trim() !== '' && !isPokeball;
      if (needsTarget) {
        showUseItemModal(itemId, btn);
        return;
      }
      let captureRoll = null;
      if (isPokeball) {
        // hacer una tirada local 1-100 para la captura
        captureRoll = Math.floor(Math.random() * 100) + 1;
        showToast('Tirada de captura: ' + captureRoll, 'success');
        try {
          if (card) {
            const badge = document.createElement('div');
            badge.className = 'badge bg-info text-dark ms-2 temp-roll-badge';
            badge.style.position = 'absolute'; badge.style.right = '12px'; badge.style.top = '8px'; badge.style.zIndex = '50';
            badge.textContent = captureRoll;
            card.style.position = 'relative';
            card.appendChild(badge);
            setTimeout(() => badge.remove(), 3000);
          }
        } catch(e) {}
      }
      const origText = btn.textContent;
      btn.disabled = true;
      btn.textContent = 'Usando...';
      const res = await fetch('api/use_item.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ item_id: itemId, roll: captureRoll }) });
      const j = await res.json();
      if (!j.success) showToast(j.error || 'Error al usar item', 'danger');
      else {
        if (card) {
          const qtySpan = card.querySelector('.item-qty');
          if (qtySpan) qtySpan.textContent = j.remaining;
          if (j.remaining <= 0) {
            card.remove();
          }
        }
        if (j.applied) {
          const appliedMsg = j.applied.healed ? ('+'+j.applied.healed+' HP') : 'OK';
          if (isPokeball && captureRoll !== null) showToast('Tirada: ' + captureRoll + '. Resultado: ' + appliedMsg, 'success');
          else showToast('Item aplicado: ' + appliedMsg, 'success');
          const pcard = document.querySelector('.pokemon-card[data-box-id="' + j.applied.box_id + '"]');
          if (pcard) {
            pcard.classList.add('border-success'); setTimeout(()=>pcard.classList.remove('border-success'), 1000);
          }
        } else {
          if (isPokeball && captureRoll !== null) showToast('Tirada: ' + captureRoll + '. Item usado. ' + (j.remaining !== undefined ? ('Quedan ' + j.remaining) : ''), 'success');
          else showToast('Item usado: ' + (j.remaining !== undefined ? ('Quedan ' + j.remaining) : ''), 'success');
        }
        if (j.inventory) renderInventory(j.inventory);
      }
    } catch (e) { showToast('Error de red', 'danger'); }
    finally { if (btn.textContent !== 'Agotado') { btn.textContent = 'Usar'; } btn.disabled = false; }
  }

try {
  if (window.__initial_team && typeof updateBoxEquippedState === 'function') {
    updateBoxEquippedState(window.__initial_team);
    if (typeof renderTeam === 'function') renderTeam(window.__initial_team);
  }
} catch(e) { console.warn('init team ui failed', e); }
  async function equipPokemon(boxId, slot) {
    const confirmBtn = document.getElementById('equipConfirmBtn');
    if (confirmBtn) confirmBtn.disabled = true;
    try {
      const res = await fetch('api/move_pokemon.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ action: 'equip', box_id: boxId, slot: slot }) });
      const j = await res.json();
      if (!j.success) showToast(j.error || 'Error al equipar', 'danger');
      else {
        showToast(j.message || 'Equipado', 'success');
        if (j.team) { renderTeam(j.team); updateBoxEquippedState(j.team); }
      }
    } catch (e) { showToast('Error de red', 'danger'); }
    finally { if (confirmBtn) confirmBtn.disabled = false; }
  }

  async function unequip(slot) {
    try {
      const res = await fetch('api/move_pokemon.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ action: 'unequip', slot: slot }) });
      const j = await res.json();
      if (!j.success) showToast(j.error || 'Error al desequipar', 'danger');
      else { showToast(j.message || 'Desequipado', 'success'); if (j.team) { renderTeam(j.team); updateBoxEquippedState(j.team); } }
    } catch (e) { showToast('Error de red', 'danger'); }
  }

  let equipModalBoxId = null;
  let equipModalSelectedSlot = null;
  function showEquipModal(boxId, targetSlot = null) {
    const equipModalEl = document.getElementById('equipModal');
    const equipModal = bootstrap.Modal.getOrCreateInstance(equipModalEl);
    equipModalBoxId = boxId || null;
    equipModalSelectedSlot = targetSlot || null;
    const pokInfo = document.getElementById('equipModalPokemonInfo');
    const grid = equipModalEl.querySelector('.modal-body .d-grid');
    grid.innerHTML = '';
    if (!boxId && targetSlot) {
      pokInfo.textContent = 'Selecciona un Pokémon de la caja para equipar en el slot ' + targetSlot;
      document.querySelectorAll('.pokemon-card[data-box-id]').forEach(el => {
        const bId = el.dataset.boxId;
        const title = el.querySelector('.pokemon-meta h5') ? el.querySelector('.pokemon-meta h5').textContent : ('#' + bId);
        const btn = document.createElement('button');
        btn.type = 'button'; btn.className = 'btn btn-outline-primary text-start';
        btn.textContent = title + ' (ID ' + bId + ')';
        btn.addEventListener('click', () => { equipModalBoxId = parseInt(bId); equipModalSelectedSlot = targetSlot; showEquipModal(equipModalBoxId, equipModalSelectedSlot); });
        grid.appendChild(btn);
      });
    } else {
      pokInfo.textContent = 'Pokémon ID: ' + (boxId ? boxId : '---');
      for (let s = 1; s <= 6; s++) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-outline-primary';
        btn.textContent = 'Slot ' + s;
        btn.dataset.slot = s;
        if (s === targetSlot) btn.classList.add('active');
        btn.addEventListener('click', () => {
          equipModalSelectedSlot = s;
          grid.querySelectorAll('button').forEach(b => b.classList.remove('active'));
          btn.classList.add('active');
        });
        grid.appendChild(btn);
      }
    }
    const confirmBtn = document.getElementById('equipConfirmBtn');
    confirmBtn.onclick = async () => {
      if (!equipModalSelectedSlot) { showToast('Selecciona un slot', 'danger'); return; }
      if (!equipModalBoxId) { showToast('Selecciona primero un Pokémon de la caja.', 'danger'); return; }
      const origText = confirmBtn.textContent;
      confirmBtn.textContent = 'Equipando...';
      confirmBtn.disabled = true;
      await equipPokemon(equipModalBoxId, equipModalSelectedSlot);
      confirmBtn.textContent = origText;
      confirmBtn.disabled = false;
      equipModal.hide();
    };
    equipModal.show();
  }

  function renderTeam(teamData) {
    const grid = document.querySelector('.team-grid');
    if (!grid) return;
    const map = {};
    for (let t of teamData) map[parseInt(t.slot)] = t;
    for (let s = 1; s <= 6; s++) {
      const slotEl = grid.querySelector('.team-slot[data-slot="' + s + '"]');
      if (!slotEl) continue;
      const t = map[s] || null;
      const avatar = slotEl.querySelector('.pokemon-avatar');
      const title = slotEl.querySelector('.fw-bold');
      const sub = slotEl.querySelector('.small-muted');
      const actions = slotEl.querySelector('.item-actions');
      if (t && t.box_id) {
        if (t.sprite) {
          avatar.innerHTML = '<img src="img/pokemon/' + t.sprite + '.jpg" class="pokemon-img">';
        } else {
          avatar.innerHTML = '⚔️';
        }
        title.textContent = (t.apodo && t.apodo.trim() !== '') ? t.apodo : (t.especie || '');
        sub.textContent = 'Nivel ' + (t.nivel || 0);
        actions.innerHTML = '<button class="btn btn-sm btn-outline-danger" onclick="unequip(' + s + ')">Desequipar</button> <button class="btn btn-sm btn-outline-warning pokemon-info-btn" onclick="showPokemonInfo(' + t.box_id + ')">ℹ️ Info</button>';
      } else {
        avatar.innerHTML = '✨';
        title.textContent = 'Libre';
        sub.textContent = 'Vacío';
        actions.innerHTML = '<button class="btn btn-sm btn-outline-primary" onclick="showEquipModal(null, ' + s + ')">Equipar</button>';
      }
    }
  }

  function updateBoxEquippedState(teamData) {
    const map = {};
    for (let t of teamData) if (t.box_id) map[parseInt(t.box_id)] = t.slot;
    document.querySelectorAll('.pokemon-card[data-box-id]').forEach(el => {
      const bId = parseInt(el.dataset.boxId);
      const speciesId = el.dataset.speciesId || '';
      const actions = el.querySelector('.item-actions');
      if (map[bId]) {
        actions.innerHTML = '<button class="btn btn-sm btn-outline-success" disabled>Equipado (' + map[bId] + ')</button> <button class="btn btn-sm btn-outline-primary" onclick="showEquipModal(' + bId + ')">Mover</button> <button class="btn btn-sm btn-outline-secondary" onclick="showSendItemModal(' + bId + ')">Enviar</button> <button class="btn btn-sm btn-outline-info" onclick="markAsSeen(' + speciesId + ')">Marcar Pokédex</button> <button class="btn btn-sm btn-outline-warning pokemon-info-btn" onclick="showPokemonInfo(' + bId + ')">ℹ️ Info</button>';
      } else {
        actions.innerHTML = '<button class="btn btn-sm btn-outline-primary" onclick="showEquipModal(' + bId + ')">Mover</button> <button class="btn btn-sm btn-outline-secondary" onclick="showSendItemModal(' + bId + ')">Enviar</button> <button class="btn btn-sm btn-outline-info" onclick="markAsSeen(' + speciesId + ')">Marcar Pokédex</button> <button class="btn btn-sm btn-outline-warning pokemon-info-btn" onclick="showPokemonInfo(' + bId + ')">ℹ️ Info</button>';
      }
    });
  }

  function renderInventory(inv) {
    try {
      const map = {};
      for (const it of inv) map[parseInt(it.item_id)] = it;
      document.querySelectorAll('.item-card').forEach(card => {
        const id = parseInt(card.dataset.itemId || '0');
        const it = map[id];
        if (!it || (it.cantidad !== undefined && parseInt(it.cantidad) <= 0)) {
          card.remove();
          return;
        }
        const qtySpan = card.querySelector('.item-qty'); if (qtySpan) qtySpan.textContent = it.cantidad;
        const useBtn = card.querySelector('button'); if (useBtn) { useBtn.disabled = (it.cantidad <= 0); if (it.cantidad <= 0) useBtn.textContent = 'Agotado'; else useBtn.textContent = 'Usar'; }
      });
    } catch(e) { console.warn('renderInventory error', e); }
  }

  function showToast(message, type='success') {
    const container = document.getElementById('toastContainer'); if (!container) return;
    const id = 'toast-' + Math.random().toString(36).substr(2,9);
    const toast = document.createElement('div');
    toast.className = 'toast align-items-center text-white bg-' + (type === 'danger' ? 'danger' : 'success') + ' border-0';
    toast.role = 'alert'; toast.ariaLive = 'assertive'; toast.ariaAtomic = 'true';
    toast.id = id;
    toast.innerHTML = '<div class="d-flex"><div class="toast-body">' + message + '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
    container.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast, { delay: 3000 }); bsToast.show();
    toast.addEventListener('hidden.bs.toast', () => { toast.remove(); });
  }

  let useModalItemId = null;
  function showUseItemModal(itemId, btn) {
    useModalItemId = itemId;
    const modalEl = document.getElementById('useItemModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    const info = document.getElementById('useItemModalInfo');
    const grid = document.getElementById('useItemModalGrid');
    let effectInfo = '';
    if (btn) {
      const card = btn.closest('.item-card');
      if (card) {
        const et = card.dataset.effectType || '';
        const ev = card.dataset.effectValue || '';
        if (et) effectInfo = ' (' + et + (ev ? ' ' + ev : '') + ')';
      }
    }
    info.textContent = 'Selecciona el Pokémon objetivo para usar el ítem' + effectInfo;
    grid.innerHTML = '';
    document.querySelectorAll('.pokemon-card[data-box-id]').forEach(el => {
      const bId = parseInt(el.dataset.boxId);
      const title = el.querySelector('.pokemon-meta h5') ? el.querySelector('.pokemon-meta h5').textContent : ('#' + bId);
      const btn2 = document.createElement('button'); btn2.type = 'button'; btn2.className = 'btn btn-outline-primary text-start';
      btn2.textContent = title + ' (ID ' + bId + ')';
      btn2.addEventListener('click', () => {
        useItemConfirm(useModalItemId, bId);
        modal.hide();
      });
      grid.appendChild(btn2);
    });
    modal.show();
  }

  async function useItemConfirm(itemId, boxId) {
    try {
      const res = await fetch('api/use_item.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ item_id: itemId, box_id: boxId }) });
      const j = await res.json();
      if (!j.success) showToast(j.error || 'Error al usar item', 'danger');
      else {
        showToast(j.message || 'Item aplicado', 'success');
        if (j.applied) {
          const pcard = document.querySelector('.pokemon-card[data-box-id="' + j.applied.box_id + '"]');
          if (pcard) {
            pcard.classList.add('border-success'); setTimeout(()=>pcard.classList.remove('border-success'), 1000);
          }
        }
        if (j.inventory) renderInventory(j.inventory);
      }
    } catch (e) { showToast('Error de red', 'danger'); }
  }

  let sendItemCurrentBoxId = null;
  function showSendItemModal(boxId) {
    const sendModalEl = document.getElementById('sendItemModal');
    const modal = bootstrap.Modal.getOrCreateInstance(sendModalEl);
    const input = document.getElementById('sendRecipientEmail'); input.value = '';
    sendItemCurrentBoxId = boxId;
    const btn = document.getElementById('sendItemConfirmBtn');
    btn.onclick = async () => {
      const email = input.value.trim(); if (!email) { showToast('Introduce un correo válido', 'danger'); return; }
      btn.disabled = true; btn.textContent = 'Enviando...';
      try {
        const res = await fetch('api/send_item.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ item_id: sendItemCurrentBoxId, to_email: email }) });
        const j = await res.json();
        if (!j.success) showToast(j.error || 'Error enviando item', 'danger'); else { showToast(j.message || 'Enviado', 'success'); if (j.inventory) renderInventory(j.inventory); }
      } catch (e) { showToast('Error de red', 'danger'); }
      finally { btn.disabled = false; btn.textContent = 'Enviar'; modal.hide(); }
    };
    modal.show();
  }

  async function markAsSeen(speciesId) {
    try {
      const res = await fetch('api/mark_pokedex.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ species_id: speciesId }) });
      const j = await res.json();
      if (!j.success) { showToast(j.error || 'Error marcando en Pokedex', 'danger'); return; }
      showToast(j.message || 'Pokédex actualizada', 'success');

      // Actualizar la UI de la Pokédex si está visible
      try {
        const entry = j.entry || null;
        const el = document.querySelector('.unknown-item[data-species-id="' + speciesId + '"]');
        if (el && entry) {
          el.setAttribute('data-seen', entry.visto ? '1' : '0');
          const avatar = el.querySelector('.unknown-avatar');
          const nameEl = el.querySelector('.fw-bold');
          const smallEl = el.querySelector('.small-muted');
          if (entry.visto) {
            if (entry.sprite) {
              avatar.innerHTML = '<img src="img/pokemon/' + entry.sprite + '.jpg" class="pokemon-img">';
            } else {
              avatar.textContent = '🐾';
            }
            nameEl.textContent = entry.nombre;
            smallEl.textContent = 'Especie';
          }
        }
      } catch (e) {
        // ignore UI update errors
      }

    } catch (e) { showToast('Error de red', 'danger'); }
  }

  window.__initial_team = <?php echo json_encode(array_values($team)); ?>;

  try {
    if (window.__initial_team && typeof updateBoxEquippedState === 'function') {
      updateBoxEquippedState(window.__initial_team);
      if (typeof renderTeam === 'function') renderTeam(window.__initial_team);
    }
  } catch(e) { console.warn('init team ui failed', e); }
</script>

<script>
  async function buyItem(clave, quantity) {
    try {
      const res = await fetch('api/buy_item.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ clave: clave, quantity: quantity }) });
      const j = await res.json();
      if (!j.success) { showToast(j.error || 'Error en la compra', 'danger'); return; }

      const badge = document.querySelector('.badge.bg-warning');
      if (badge) {
        const val = (j.raw_balance !== undefined) ? j.raw_balance : parseFloat(j.balance || 0);
        badge.textContent = 'Saldo: € ' + new Intl.NumberFormat('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(val);
      }

      try {
        const r2 = await fetch('api/get_inventory.php');
        const j2 = await r2.json();
        if (j2 && j2.success && Array.isArray(j2.items)) {
          renderInventory(j2.items);
        }
      } catch (e) {
      }

      showToast('Compra realizada: ' + (j.item || clave), 'success');
    } catch (e) { showToast('Error de red', 'danger'); }
  }
</script>

<script>
  async function rollD100() {
    try {
      const rangeInput = document.getElementById('diceRange');
      let maxRange = parseInt(rangeInput ? rangeInput.value : 100);
      if (!maxRange || maxRange < 1) {
        showToast('El rango debe ser mayor a 0', 'danger');
        return;
      }
      if (maxRange > 10000) maxRange = 10000;
      const val = Math.floor(Math.random() * maxRange) + 1;
      const resEl = document.getElementById('d100Result');
      if (resEl) {
        resEl.textContent = val;
        resEl.classList.remove('text-success','text-danger','text-dark');
        if (val === maxRange) resEl.classList.add('text-success');
        else if (val === 1) resEl.classList.add('text-danger');
        else resEl.classList.add('text-dark');
      }
      showToast('Tirada 1-' + maxRange + ': ' + val, 'success');

      const entry = { value: val, range: maxRange, at: (new Date()).toISOString() };
      try {
        const r = await fetch('api/d100_roll.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ value: val }) });
        const jr = await r.json();
        if (jr && jr.success) {
          const hist = document.getElementById('d100History');
          if (hist) {
            const li = document.createElement('li'); li.className = 'list-group-item';
            const time = new Date().toLocaleTimeString(); li.textContent = time + ' — ' + val + ' (1-' + maxRange + ')'; hist.insertBefore(li, hist.firstChild);
          }
          return;
        }
      } catch (e) {}

      try {
        const hist = document.getElementById('d100History');
        if (hist) {
          const li = document.createElement('li'); li.className = 'list-group-item';
          const time = new Date(entry.at).toLocaleTimeString(); li.textContent = time + ' — ' + entry.value + ' (1-' + entry.range + ')'; hist.insertBefore(li, hist.firstChild);
        }
        const prev = JSON.parse(localStorage.getItem('d100_history') || '[]'); prev.unshift(entry); localStorage.setItem('d100_history', JSON.stringify(prev.slice(0,50)));
      } catch(e) {}
    } catch(e) { showToast('Error al tirar el dado', 'danger'); }
  }

  async function initD100History() {
    try {
      const hist = document.getElementById('d100History');
      const resEl = document.getElementById('d100Result');
      try {
        const r = await fetch('api/d100_history.php?limit=50');
        if (r.ok) {
          const j = await r.json();
          if (j && j.success && Array.isArray(j.rolls)) {
            hist.innerHTML = '';
            for (const e of j.rolls) {
              const li = document.createElement('li'); li.className = 'list-group-item';
              const time = new Date(e.created_at).toLocaleTimeString(); li.textContent = time + ' — ' + e.value; hist.appendChild(li);
            }
            if (j.rolls.length && resEl) resEl.textContent = j.rolls[0].value;
            return;
          }
        }
      } catch (e) {}

      const prev = JSON.parse(localStorage.getItem('d100_history') || '[]');
      if (hist && Array.isArray(prev)) {
        hist.innerHTML = '';
        for (const e of prev.slice(0,50)) {
          const li = document.createElement('li'); li.className = 'list-group-item';
          const time = new Date(e.at).toLocaleTimeString(); 
          const rangeText = e.range ? ' (1-' + e.range + ')' : '';
          li.textContent = time + ' — ' + e.value + rangeText; 
          hist.appendChild(li);
        }
        if (prev.length && resEl) resEl.textContent = prev[0].value;
      }
    } catch(e) { }
  }

  async function clearD100History() {
    if (!confirm('¿Estás seguro de que quieres borrar todo el historial de dados?')) {
      return;
    }
    
    try {
      // Intentar borrar del servidor
      try {
        const r = await fetch('api/d100_clear.php', { 
          method: 'POST',
          headers: {'Content-Type': 'application/json'}
        });
        const j = await r.json();
        
        if (j && j.success) {
          showToast('Historial borrado correctamente', 'success');
        }
      } catch (e) {
        // Si falla el servidor, continuar con localStorage
        console.log('Error al borrar del servidor:', e);
      }
      
      // Borrar localStorage
      localStorage.removeItem('d100_history');
      
      // Limpiar la UI
      const hist = document.getElementById('d100History');
      const resEl = document.getElementById('d100Result');
      if (hist) hist.innerHTML = '';
      if (resEl) resEl.textContent = '--';
      
    } catch(e) {
      console.error('Error al limpiar historial:', e);
      showToast('Error al borrar el historial', 'danger');
    }
  }

  // Actualizar texto descriptivo cuando cambia el rango del dado
  function updateDiceRangeText() {
    const rangeInput = document.getElementById('diceRange');
    const rangeText = document.getElementById('diceRangeText');
    if (rangeInput && rangeText) {
      let maxRange = parseInt(rangeInput.value);
      if (!maxRange || maxRange < 1) maxRange = 1;
      if (maxRange > 10000) maxRange = 10000;
      rangeText.textContent = `Tirada de 1-${maxRange}. Se registra si estás autenticado.`;
    }
  }

  try { 
    document.addEventListener('DOMContentLoaded', function() {
      initD100History();
      // Agregar listener para cambio de rango
      const rangeInput = document.getElementById('diceRange');
      if (rangeInput) {
        rangeInput.addEventListener('input', updateDiceRangeText);
        updateDiceRangeText(); // Inicializar texto
      }
    }); 
  } catch(e) { 
    initD100History(); 
  }
</script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
  <script src="scripts/pokemon-info.js?v=6"></script>
  <script>
    // Restaurar pestaña activa guardada en localStorage (se ejecuta después de cargar Bootstrap)
    (function(){
      try {
        const saved = localStorage.getItem('dashboard_active_tab');
        if (!saved) return;
        const selector = '.sidebar .nav-link[data-bs-target="' + saved + '"], .d-block.d-md-none .nav-link[data-bs-target="' + saved + '"]';
        let btn = document.querySelector(selector);
        if (!btn) btn = document.querySelector('button[data-bs-target="' + saved + '"]');
        if (btn && window.bootstrap && bootstrap.Tab) {
          const tab = bootstrap.Tab.getOrCreateInstance(btn);
          tab.show();
          document.querySelectorAll('.sidebar .nav-link, .d-block.d-md-none .nav-link').forEach(l=>l.classList.remove('active'));
          btn.classList.add('active');
        }
      } catch(e) { console.warn('restore dashboard tab failed', e); }
    })();
  </script>
</body>
</html>
