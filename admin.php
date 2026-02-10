<?php
include 'db.php';
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user'])) {
  $_SESSION['error'] = 'Debes iniciar sesión para acceder.';
  header('Location: index.php'); exit;
}

$userRaw = $_SESSION['user'];
$user_id = (int)($userRaw['id'] ?? 0);

// Verificar que sea el administrador (ID 67)
if ($user_id !== 67) {
  $_SESSION['error'] = 'No tienes permisos para acceder al panel de administrador.';
  header('Location: dashboard.php'); exit;
}

session_write_close();

$user = htmlspecialchars(($userRaw['nombre'] ?? 'Master'));

// Obtener lista de todos los usuarios
$usuarios = [];
$sql = "SELECT id, nombre, apellido, correo, money FROM usuarios WHERE id != 67 ORDER BY nombre";
if ($stmt = $mysqli->prepare($sql)) {
  $stmt->execute();
  $res = $stmt->get_result();
  while ($r = $res->fetch_assoc()) $usuarios[] = $r;
  $stmt->close();
}

// Obtener todas las especies disponibles
$especies = [];
$sql = "SELECT id, nombre, sprite FROM pokemon_species ORDER BY nombre";
if ($stmt = $mysqli->prepare($sql)) {
  $stmt->execute();
  $res = $stmt->get_result();
  while ($r = $res->fetch_assoc()) $especies[] = $r;
  $stmt->close();
}

// Obtener todos los items disponibles
$items = [];
$sql = "SELECT id, nombre, icono FROM items ORDER BY nombre";
if ($stmt = $mysqli->prepare($sql)) {
  $stmt->execute();
  $res = $stmt->get_result();
  while ($r = $res->fetch_assoc()) $items[] = $r;
  $stmt->close();
}

// Obtener todos los movimientos
$movimientos = [];
$sql = "SELECT id, nombre, tipo_id, categoria, potencia, nivel_requerido FROM movimientos ORDER BY nivel_requerido, nombre";
if ($stmt = $mysqli->prepare($sql)) {
  $stmt->execute();
  $res = $stmt->get_result();
  while ($r = $res->fetch_assoc()) $movimientos[] = $r;
  $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel de Master - Pokémon Rol</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="style.css">
  <style>
    .master-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 1.5rem;
      border-radius: 10px;
      margin-bottom: 2rem;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .player-card {
      transition: all 0.3s ease;
      cursor: pointer;
      border: 2px solid transparent;
    }
    .player-card:hover {
      border-color: #667eea;
      box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
      transform: translateY(-2px);
    }
    .player-card.active {
      border-color: #764ba2;
      background-color: #f8f9fa;
    }
    .stat-badge {
      display: inline-block;
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 0.85rem;
      margin: 2px;
    }
    .stat-hp { background-color: #ff5959; color: white; }
    .stat-level { background-color: #4CAF50; color: white; }
    .pokemon-item {
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 10px;
      margin: 8px 0;
      background: white;
    }
    .edit-btn {
      font-size: 0.85rem;
      padding: 4px 12px;
    }
    .admin-section {
      background: white;
      border-radius: 10px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .move-item {
      background: #f8f9fa;
      border-left: 3px solid #667eea;
      padding: 8px;
      margin: 4px 0;
      border-radius: 4px;
    }
    .species-autocomplete {
      position: relative;
    }
    .species-results {
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      max-height: 300px;
      overflow-y: auto;
      background: white;
      border: 1px solid #ddd;
      border-top: none;
      border-radius: 0 0 4px 4px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      z-index: 1000;
      display: none;
    }
    .species-results.active {
      display: block;
    }
    .species-result-item {
      padding: 10px;
      cursor: pointer;
      border-bottom: 1px solid #f0f0f0;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .species-result-item:hover {
      background-color: #f8f9fa;
    }
    .species-result-item img {
      width: 40px;
      height: 40px;
      object-fit: contain;
    }
    .species-result-item .species-name {
      font-weight: 500;
    }
  </style>
</head>
<body>
  <div class="container-fluid py-4">
    <div class="master-header">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h1 class="mb-0"><i class="bi bi-shield-fill-check"></i> Panel de Master</h1>
          <p class="mb-0 mt-2 opacity-75">Bienvenido, <?= $user ?> - Gestión de jugadores y Pokémon</p>
        </div>
        <div>
          <a href="dashboard.php" class="btn btn-light"><i class="bi bi-house"></i> Volver al Dashboard</a>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- Lista de jugadores -->
      <div class="col-md-3">
        <div class="admin-section">
          <h4 class="mb-3"><i class="bi bi-people-fill"></i> Jugadores</h4>
          <div id="players-list">
            <?php foreach ($usuarios as $u): ?>
              <div class="player-card card mb-2 p-2" data-user-id="<?= $u['id'] ?>" onclick="loadPlayerData(<?= $u['id'] ?>)">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <strong><?= htmlspecialchars($u['nombre']) ?> <?= htmlspecialchars($u['apellido']) ?></strong>
                    <br><small class="text-muted"><?= htmlspecialchars($u['correo']) ?></small>
                  </div>
                  <span class="badge bg-warning"><?= number_format($u['money'], 2) ?>₽</span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          
          <hr>
          
          <!-- Acciones rápidas -->
          <h5 class="mt-4 mb-3">Acciones Rápidas</h5>
          <button class="btn btn-primary btn-sm w-100 mb-2" onclick="showGivePokemonModal()">
            <i class="bi bi-plus-circle"></i> Dar Pokémon
          </button>
          <button class="btn btn-success btn-sm w-100 mb-2" onclick="showGiveItemModal()">
            <i class="bi bi-gift"></i> Dar Item
          </button>
          <button class="btn btn-info btn-sm w-100 mb-2" onclick="healPlayerPokemon()" id="heal-btn" disabled>
            <i class="bi bi-heart-pulse-fill"></i> Curar Pokémon
          </button>
        </div>
      </div>

      <!-- Panel principal -->
      <div class="col-md-9">
        <div id="player-details" class="admin-section">
          <div class="text-center text-muted py-5">
            <i class="bi bi-arrow-left-circle" style="font-size: 3rem;"></i>
            <p class="mt-3">Selecciona un jugador para ver y editar sus datos</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: Editar Pokémon -->
  <div class="modal fade" id="editPokemonModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Editar Pokémon</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="edit-pokemon-id">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Apodo</label>
              <input type="text" class="form-control" id="edit-apodo">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Nivel</label>
              <input type="number" class="form-control" id="edit-nivel" min="1" max="100">
            </div>
          </div>
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">HP Actual</label>
              <input type="number" class="form-control" id="edit-hp" min="0">
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">HP Máximo</label>
              <input type="number" class="form-control" id="edit-max-hp" min="1">
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Estado</label>
              <select class="form-select" id="edit-status">
                <option value="">Normal</option>
                <option value="poisoned">Envenenado</option>
                <option value="paralyzed">Paralizado</option>
                <option value="burned">Quemado</option>
                <option value="frozen">Congelado</option>
                <option value="sleeping">Dormido</option>
                <option value="fainted">Debilitado</option>
              </select>
            </div>
          </div>
          <hr>
          <h6>Movimientos</h6>
          <div id="moves-list"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary" onclick="savePokemonChanges()">Guardar Cambios</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: Dar Pokémon -->
  <div class="modal fade" id="givePokemonModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Dar Pokémon a Jugador</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Jugador</label>
            <select class="form-select" id="give-pokemon-user">
              <?php foreach ($usuarios as $u): ?>
                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nombre']) ?> <?= htmlspecialchars($u['apellido']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Especie</label>
            <div class="species-autocomplete">
              <input type="text" class="form-control" id="species-search-input" placeholder="Escribe el nombre del Pokémon..." autocomplete="off">
              <input type="hidden" id="give-pokemon-species">
              <div class="species-results" id="species-results"></div>
            </div>
            <small class="text-muted">Escribe al menos 2 caracteres para buscar</small>
          </div>
          <div class="mb-3">
            <label class="form-label">Apodo (opcional)</label>
            <input type="text" class="form-control" id="give-pokemon-apodo">
          </div>
          <div class="row">
            <div class="col-6 mb-3">
              <label class="form-label">Nivel</label>
              <input type="number" class="form-control" id="give-pokemon-nivel" value="5" min="1" max="100">
            </div>
            <div class="col-6 mb-3">
              <label class="form-label">HP</label>
              <input type="number" class="form-control" id="give-pokemon-hp" value="35" min="1">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary" onclick="givePokemon()">Dar Pokémon</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: Dar Item -->
  <div class="modal fade" id="giveItemModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Dar Item a Jugador</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Jugador</label>
            <select class="form-select" id="give-item-user">
              <?php foreach ($usuarios as $u): ?>
                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nombre']) ?> <?= htmlspecialchars($u['apellido']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Item</label>
            <select class="form-select" id="give-item-id">
              <?php foreach ($items as $item): ?>
                <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Cantidad</label>
            <input type="number" class="form-control" id="give-item-cantidad" value="1" min="1">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-success" onclick="giveItem()">Dar Item</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: Enseñar Movimiento -->
  <div class="modal fade" id="teachMoveModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Enseñar Movimiento</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="teach-pokemon-id">
          <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> <strong id="teach-pokemon-name"></strong> (Nivel <span id="teach-pokemon-level"></span>)
          </div>
          <div class="mb-3">
            <label class="form-label">Selecciona Movimiento</label>
            <select class="form-select" id="teach-move-select" size="10">
              <?php foreach ($movimientos as $mov): ?>
                <option value="<?= $mov['id'] ?>" data-nivel="<?= isset($mov['nivel_requerido']) ? $mov['nivel_requerido'] : 1 ?>">
                  <?= htmlspecialchars($mov['nombre']) ?> (Nv.<?= isset($mov['nivel_requerido']) ? $mov['nivel_requerido'] : 1 ?>) - POT:<?= $mov['potencia'] ? $mov['potencia'] : '-' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary" onclick="teachMove()">
            <i class="bi bi-book"></i> Enseñar Movimiento
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: Evolucionar Pokémon -->
  <div class="modal fade" id="evolutionModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Selecciona evolución</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div id="evolution-options" class="d-grid gap-2"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    let currentUserId = null;
    let currentPokemonData = {};
    const evolutionCache = new Map();
    
    // Función para escapar HTML y prevenir XSS
    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }
    
    // Sistema de búsqueda de especies
    let searchTimeout = null;
    let selectedSpeciesId = null;
    
    function setupSpeciesSearch() {
      const searchInput = document.getElementById('species-search-input');
      const resultsDiv = document.getElementById('species-results');
      const hiddenInput = document.getElementById('give-pokemon-species');
      
      if (!searchInput || !resultsDiv || !hiddenInput) return;
      
      searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        if (query.length < 2) {
          resultsDiv.classList.remove('active');
          resultsDiv.innerHTML = '';
          hiddenInput.value = '';
          selectedSpeciesId = null;
          return;
        }
        
        // Debounce - esperar 300ms después de que el usuario deje de escribir
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
          searchSpecies(query);
        }, 300);
      });
      
      // Cerrar resultados al hacer clic fuera
      document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !resultsDiv.contains(e.target)) {
          resultsDiv.classList.remove('active');
        }
      });
    }
    
    function searchSpecies(query) {
      const resultsDiv = document.getElementById('species-results');
      
      // Mostrar indicador de carga
      resultsDiv.innerHTML = '<div class="species-result-item" style="cursor: default;"><span class="text-muted">🔍 Buscando...</span></div>';
      resultsDiv.classList.add('active');
      
      fetch(`api/search_species.php?q=${encodeURIComponent(query)}`)
        .then(r => {
          if (!r.ok) console.warn('Response status:', r.status);
          return r.json();
        })
        .then(data => {
          // Verificar si hay error
          if (data.error) {
            console.error('Error del servidor:', data.error);
            resultsDiv.innerHTML = `<div class="species-result-item" style="cursor: default;"><span class="text-danger">Error: ${escapeHtml(data.error)}</span></div>`;
            resultsDiv.classList.add('active');
            return;
          }
          
          if (!Array.isArray(data) || data.length === 0) {
            resultsDiv.innerHTML = '<div class="species-result-item" style="cursor: default;"><span class="text-muted">No se encontraron resultados</span></div>';
            resultsDiv.classList.add('active');
            return;
          }
          
          let html = '';
          data.forEach(species => {
            // Usar sprite_url que viene del servidor (ya con el formato correcto)
            let imgTag;
            if (species.sprite_url) {
              imgTag = `<img src="img/pokemon/${escapeHtml(species.sprite_url)}">`;
            } else {
              imgTag = '<span style="font-size: 1.5rem;">❔</span>';
            }
            
            html += `
              <div class="species-result-item" onclick="selectSpecies(${species.id}, '${escapeHtml(species.nombre)}')">
                ${imgTag}
                <span class="species-name">${escapeHtml(species.nombre)}</span>
              </div>
            `;
          });
          
          resultsDiv.innerHTML = html;
          resultsDiv.classList.add('active');
        })
        .catch(err => {
          console.error('Error buscando especies:', err);
          resultsDiv.innerHTML = '<div class="species-result-item" style="cursor: default;"><span class="text-danger">Error: ' + err.message + '</span></div>';
          resultsDiv.classList.add('active');
        });
    }
    
    function selectSpecies(id, nombre) {
      const searchInput = document.getElementById('species-search-input');
      const resultsDiv = document.getElementById('species-results');
      const hiddenInput = document.getElementById('give-pokemon-species');
      
      searchInput.value = nombre;
      hiddenInput.value = id;
      selectedSpeciesId = id;
      resultsDiv.classList.remove('active');
      resultsDiv.innerHTML = '';
    }
    
    // Verificar que Bootstrap se cargó correctamente
    if (typeof bootstrap === 'undefined') {
      console.error('Bootstrap no se cargó correctamente');
      alert('Error: Bootstrap no se cargó. Verifica tu conexión a internet.');
    }
    
    // Log para depuración
    console.log('Admin Panel cargado correctamente');
    console.log('Bootstrap version:', typeof bootstrap !== 'undefined' ? 'OK' : 'ERROR');

    function loadPlayerData(userId) {
      userId = parseInt(userId);
      
      if (!userId || userId <= 0) {
        alert('ID de jugador inválido');
        return;
      }
      
      currentUserId = userId;
      
      // Habilitar botón de curar
      document.getElementById('heal-btn').disabled = false;
      
      // Marcar jugador activo
      document.querySelectorAll('.player-card').forEach(card => {
        card.classList.remove('active');
      });
      document.querySelector(`[data-user-id="${userId}"]`)?.classList.add('active');

      // Cargar datos
      fetch(`api/admin_get_player.php?user_id=${userId}`)
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            displayPlayerData(data);
          } else {
            alert('Error: ' + data.error);
          }
        })
        .catch(err => {
          console.error('Error al cargar datos del jugador:', err);
          alert('Error al cargar los datos del jugador');
        })
        .catch(err => {
          console.error('Error:', err);
          alert('Error al cargar datos del jugador');
        });
    }

    function displayPlayerData(data) {
      const container = document.getElementById('player-details');
      const player = data.player;
      
      let html = `
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h3>${player.nombre} ${player.apellido}</h3>
            <p class="text-muted mb-0">${player.correo}</p>
          </div>
          <div>
            <span class="badge bg-warning fs-5">${parseFloat(player.money).toFixed(2)}₽</span>
            <button class="btn btn-sm btn-outline-primary ms-2" onclick="editPlayerMoney(${player.id}, ${player.money})">
              <i class="bi bi-pencil"></i> Editar dinero
            </button>
          </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-3" role="tablist">
          <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#team-tab">
              <i class="bi bi-stars"></i> Equipo (${data.team.length})
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#box-tab">
              <i class="bi bi-box"></i> Caja (${data.box.length})
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#inventory-tab">
              <i class="bi bi-bag"></i> Inventario (${data.inventory.length})
            </button>
          </li>
        </ul>

        <div class="tab-content">
          <!-- Equipo -->
          <div class="tab-pane fade show active" id="team-tab">
            ${displayPokemonList(data.team, 'team')}
          </div>

          <!-- Caja -->
          <div class="tab-pane fade" id="box-tab">
            ${displayPokemonList(data.box, 'box')}
          </div>

          <!-- Inventario -->
          <div class="tab-pane fade" id="inventory-tab">
            ${displayInventory(data.inventory)}
          </div>
        </div>
      `;
      
      container.innerHTML = html;
      evolutionCache.clear();
      prepareEvolutionButtons([...(data.team || []), ...(data.box || [])]);
    }

    function displayPokemonList(pokemon, type) {
      if (pokemon.length === 0) {
        return '<p class="text-muted text-center py-4">No hay Pokémon en ' + (type === 'team' ? 'el equipo' : 'la caja') + '</p>';
      }

      let html = '<div class="row">';
      pokemon.forEach(p => {
        // Usar sprite_url que viene del servidor con el formato correcto
        const img = p.sprite_url ? `img/pokemon/${escapeHtml(p.sprite_url)}` : '';
        const fallback = p.emoji || '❔';
        const apodo = p.apodo ? escapeHtml(p.apodo) : '';
        const especie = escapeHtml(p.especie);
        const nombre = apodo ? `${apodo} (${especie})` : especie;
        const hp = parseInt(p.hp) || 0;
        const maxHp = parseInt(p.max_hp) || 100;
        const nivel = parseInt(p.nivel) || 1;
        const status = p.status || '';
        const statusText = status ? `<span class="badge bg-danger">${escapeHtml(status)}</span>` : '';
        
        // Stats adicionales
        const atk = parseInt(p.ataque) || 0;
        const def = parseInt(p.defensa) || 0;
        const spAtk = parseInt(p.sp_ataque) || 0;
        const spDef = parseInt(p.sp_defensa) || 0;
        const vel = parseInt(p.velocidad) || 0;

        html += `
          <div class="col-md-6 mb-3">
            <div class="pokemon-item">
              <div class="d-flex align-items-center">
                <div class="me-3">
                  ${img ? `<img src="${img}" alt="${especie}" style="width: 50px; height: 50px; object-fit: contain;">` : `<span style="font-size: 2rem;">${fallback}</span>`}
                </div>
                <div class="flex-grow-1">
                  <strong>${nombre}</strong>
                  <br>
                  <span class="stat-badge stat-level">Nv. ${nivel}</span>
                  <span class="stat-badge stat-hp">${hp}/${maxHp} HP</span>
                  ${statusText}
                  <br>
                  <small class="text-muted">
                    ATK: ${atk} | DEF: ${def} | SP.ATK: ${spAtk} | SP.DEF: ${spDef} | VEL: ${vel}
                  </small>
                </div>
                <div>
                  <button class="btn btn-sm btn-outline-primary edit-btn" onclick="editPokemon(${parseInt(p.id)})">
                    <i class="bi bi-pencil"></i> Editar
                  </button>
                  <button class="btn btn-sm btn-outline-success edit-btn" onclick="showTeachMoveModal(${parseInt(p.id)}, '${nombre}', ${nivel})" title="Enseñar movimiento">
                    <i class="bi bi-book"></i> Enseñar
                  </button>
                  <button class="btn btn-sm btn-outline-danger edit-btn" onclick="deletePokemon(${parseInt(p.id)}, '${nombre.replace(/'/g, "\\'")}')" title="Borrar Pokémon">
                    <i class="bi bi-trash"></i> Borrar
                  </button>
                  <button class="btn btn-sm btn-outline-warning edit-btn evolve-btn d-none" data-pokemon-id="${parseInt(p.id)}" data-loading="1" onclick="handleEvolveClick(${parseInt(p.id)})" title="Evolucionar" disabled>
                    <i class="bi bi-arrow-up-right-circle"></i> Evolucionar
                  </button>
                </div>
              </div>
            </div>
          </div>
        `;
      });
      html += '</div>';
      return html;
    }

    function prepareEvolutionButtons(pokemonList) {
      const ids = pokemonList
        .map(p => parseInt(p.id))
        .filter(id => Number.isInteger(id) && id > 0);

      if (ids.length === 0) return;

      ids.forEach(id => {
        const buttons = document.querySelectorAll(`.evolve-btn[data-pokemon-id="${id}"]`);
        buttons.forEach(btn => {
          btn.disabled = true;
          btn.dataset.loading = '1';
        });
      });

      fetch('api/admin_get_evolution_options.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ pokemon_ids: ids })
      })
      .then(r => r.json())
      .then(response => {
        if (!response.success) return;
        const results = response.results || {};
        ids.forEach(id => {
          const options = Array.isArray(results[id]) ? results[id] : [];
          const buttons = document.querySelectorAll(`.evolve-btn[data-pokemon-id="${id}"]`);
          if (options.length > 0) {
            evolutionCache.set(id, options);
            buttons.forEach(btn => {
              btn.classList.remove('d-none');
              btn.disabled = false;
              delete btn.dataset.loading;
            });
          } else {
            buttons.forEach(btn => {
              btn.classList.add('d-none');
              btn.disabled = true;
              delete btn.dataset.loading;
            });
          }
        });
      })
      .catch(err => {
        console.warn('Error al cargar evoluciones:', err);
      });
    }

    function handleEvolveClick(pokemonId) {
      pokemonId = parseInt(pokemonId);
      if (!pokemonId || pokemonId <= 0) {
        alert('ID de Pokémon inválido');
        return;
      }

      if (evolutionCache.has(pokemonId)) {
        openEvolutionOptions(pokemonId, evolutionCache.get(pokemonId));
        return;
      }

      fetch('api/admin_get_evolution_options.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ pokemon_id: pokemonId })
      })
      .then(r => r.json())
      .then(response => {
        if (!response.success) {
          alert('Error: ' + (response.error || 'No se pudo obtener la evolución'));
          return;
        }
        const options = response.options || [];
        evolutionCache.set(pokemonId, options);
        openEvolutionOptions(pokemonId, options);
      })
      .catch(err => {
        console.error('Error al obtener evolución:', err);
        alert('Error al obtener la evolución');
      });
    }

    function openEvolutionOptions(pokemonId, options) {
      if (!Array.isArray(options) || options.length === 0) {
        alert('Este Pokémon no tiene evolución disponible en la base de datos');
        return;
      }

      if (options.length === 1) {
        confirmEvolution(pokemonId, options[0].id, options[0].nombre);
        return;
      }

      const container = document.getElementById('evolution-options');
      const html = options.map(option => {
        const nombre = escapeHtml(option.nombre || 'Evolucion');
        const sprite = option.sprite ? `img/pokemon/${escapeHtml(option.sprite)}` : '';
        const img = sprite
          ? `<img src="${sprite}" alt="${nombre}" style="width: 36px; height: 36px; object-fit: contain; margin-right: 8px;" onerror="this.style.display='none'">`
          : '';
        return `
          <button class="btn btn-outline-primary d-flex align-items-center" onclick="confirmEvolution(${pokemonId}, ${parseInt(option.id)}, '${nombre.replace(/'/g, "\\'")}')">
            ${img}
            <span>${nombre}</span>
          </button>
        `;
      }).join('');

      container.innerHTML = html;
      new bootstrap.Modal(document.getElementById('evolutionModal')).show();
    }

    function confirmEvolution(pokemonId, targetSpeciesId, targetName) {
      const name = targetName ? ` a ${targetName}` : '';
      if (!confirm(`¿Evolucionar este Pokémon${name}?`)) {
        return;
      }

      fetch('api/admin_evolve_pokemon.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ pokemon_id: pokemonId, target_species_id: targetSpeciesId })
      })
      .then(r => r.json())
      .then(response => {
        if (response.success) {
          alert('Pokémon evolucionado correctamente');
          const modalInstance = bootstrap.Modal.getInstance(document.getElementById('evolutionModal'));
          if (modalInstance) modalInstance.hide();
          if (currentUserId) loadPlayerData(currentUserId);
        } else {
          alert('Error: ' + (response.error || 'No se pudo evolucionar'));
        }
      })
      .catch(err => {
        console.error('Error al evolucionar:', err);
        alert('Error al evolucionar el Pokémon');
      });
    }

    function displayInventory(inventory) {
      if (inventory.length === 0) {
        return '<p class="text-muted text-center py-4">El inventario está vacío</p>';
      }

      let html = '<div class="row">';
      inventory.forEach(item => {
        const icon = item.icono ? `img/items/${escapeHtml(item.icono)}` : '';
        const nombre = escapeHtml(item.nombre);
        const cantidad = parseInt(item.cantidad) || 0;
        const itemId = parseInt(item.item_id);
        
        html += `
          <div class="col-md-4 mb-3">
            <div class="card">
              <div class="card-body d-flex align-items-center">
                ${icon ? `<img src="${icon}" alt="${nombre}" style="width: 40px; height: 40px; margin-right: 10px;" onerror="this.style.display='none'">` : ''}
                <div class="flex-grow-1">
                  <strong>${nombre}</strong>
                  <br><span class="badge bg-primary">${cantidad}</span>
                </div>
                <button class="btn btn-sm btn-outline-danger" onclick="removeItem(${currentUserId}, ${itemId})">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </div>
          </div>
        `;
      });
      html += '</div>';
      return html;
    }

    function editPokemon(pokemonId) {
      // Validar pokemonId
      pokemonId = parseInt(pokemonId);
      if (!pokemonId || pokemonId <= 0) {
        alert('ID de Pokémon inválido');
        return;
      }
      
      // Cargar datos del Pokémon
      fetch(`api/admin_get_pokemon.php?pokemon_id=${pokemonId}`)
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            currentPokemonData = data.pokemon;
            
            // Llenar el formulario con validación
            document.getElementById('edit-pokemon-id').value = parseInt(data.pokemon.id) || 0;
            document.getElementById('edit-apodo').value = data.pokemon.apodo || '';
            document.getElementById('edit-nivel').value = parseInt(data.pokemon.nivel) || 1;
            document.getElementById('edit-hp').value = parseInt(data.pokemon.hp) || 0;
            document.getElementById('edit-max-hp').value = parseInt(data.pokemon.max_hp) || 100;
            document.getElementById('edit-status').value = data.pokemon.status || '';

            // Mostrar movimientos
            displayMoves(data.moves || []);

            // Abrir modal
            new bootstrap.Modal(document.getElementById('editPokemonModal')).show();
          } else {
            alert('Error: ' + data.error);
          }
        })
        .catch(err => {
          console.error('Error al cargar Pokémon:', err);
          alert('Error al cargar los datos del Pokémon');
        });
    }

    function displayMoves(moves) {
      const container = document.getElementById('moves-list');
      if (moves.length === 0) {
        container.innerHTML = '<p class="text-muted">Este Pokémon no tiene movimientos.</p>';
        return;
      }

      let html = '';
      moves.forEach((move, index) => {
        const nombre = escapeHtml(move.nombre);
        const categoria = escapeHtml(move.categoria);
        const potencia = move.potencia ? parseInt(move.potencia) : null;
        const pokemonBoxId = parseInt(move.pokemon_box_id);
        const movimientoId = parseInt(move.movimiento_id);
        
        html += `
          <div class="move-item d-flex justify-content-between align-items-center">
            <div>
              <strong>${nombre}</strong>
              <span class="badge bg-secondary">${categoria}</span>
              ${potencia ? `<span class="badge bg-danger">${potencia} POW</span>` : ''}
            </div>
          </div>
        `;
      });
      container.innerHTML = html;
    }

    function savePokemonChanges() {
      const pokemonId = parseInt(document.getElementById('edit-pokemon-id').value);
      const nivel = parseInt(document.getElementById('edit-nivel').value);
      const hp = parseInt(document.getElementById('edit-hp').value);
      const maxHp = parseInt(document.getElementById('edit-max-hp').value);
      
      // Validaciones
      if (nivel < 1 || nivel > 100) {
        alert('El nivel debe estar entre 1 y 100');
        return;
      }
      
      if (hp < 0) {
        alert('El HP no puede ser negativo');
        return;
      }
      
      if (maxHp < 1) {
        alert('El HP máximo debe ser mayor a 0');
        return;
      }
      
      
      const data = {
        pokemon_id: pokemonId,
        apodo: document.getElementById('edit-apodo').value,
        nivel: nivel,
        hp: hp,
        max_hp: maxHp,
        status: document.getElementById('edit-status').value
      };

      fetch('api/admin_update_pokemon.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      })
      .then(r => {
        console.log('Response status:', r.status);
        return r.text(); // Primero obtener como texto
      })
      .then(text => {
        console.log('Response text:', text);
        try {
          const response = JSON.parse(text);
          if (response.success) {
            alert('Pokémon actualizado correctamente');
            bootstrap.Modal.getInstance(document.getElementById('editPokemonModal')).hide();
            loadPlayerData(currentUserId); // Recargar datos
          } else {
            alert('Error: ' + response.error);
          }
        } catch (e) {
          console.error('Error parsing JSON:', e);
          console.error('Response was:', text);
          alert('Error: La respuesta del servidor no es válida. Revisa la consola.');
        }
      })
      .catch(err => {
        console.error('Error al actualizar:', err);
        alert('Error al actualizar el Pokémon');
      });

    }

    function deletePokemon(pokemonId, nombre) {
      const id = parseInt(pokemonId);
      if (!id || id <= 0) {
        alert('ID de Pokémon inválido');
        return;
      }
      const label = nombre ? ` (${nombre})` : '';
      if (!confirm(`¿Borrar este Pokémon${label}? Esta acción no se puede deshacer.`)) {
        return;
      }

      fetch('api/admin_delete_pokemon.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ pokemon_id: id })
      })
      .then(r => r.json())
      .then(response => {
        if (response.success) {
          alert('Pokémon borrado correctamente');
          if (currentUserId) loadPlayerData(currentUserId);
        } else {
          alert('Error: ' + (response.error || 'No se pudo borrar el Pokémon'));
        }
      })
      .catch(err => {
        console.error('Error al borrar Pokémon:', err);
        alert('Error al borrar el Pokémon');
      });
    }

    function showGivePokemonModal() {
      try {
        if (!currentUserId) {
          // Preseleccionar primer usuario
          const firstUser = document.querySelector('.player-card');
          if (firstUser) {
            document.getElementById('give-pokemon-user').value = firstUser.dataset.userId;
          }
        } else {
          document.getElementById('give-pokemon-user').value = currentUserId;
        }
        
        // Limpiar campos de búsqueda de especies
        const searchInput = document.getElementById('species-search-input');
        const hiddenInput = document.getElementById('give-pokemon-species');
        const resultsDiv = document.getElementById('species-results');
        if (searchInput) searchInput.value = '';
        if (hiddenInput) hiddenInput.value = '';
        if (resultsDiv) {
          resultsDiv.classList.remove('active');
          resultsDiv.innerHTML = '';
        }
        selectedSpeciesId = null;
        
        const modalElement = document.getElementById('givePokemonModal');
        if (!modalElement) {
          console.error('Modal givePokemonModal no encontrado');
          alert('Error: No se puede abrir el modal. Recarga la página.');
          return;
        }
        
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
      } catch (error) {
        console.error('Error al abrir modal:', error);
        alert('Error al abrir el modal: ' + error.message);
      }
    }

    function showGiveItemModal() {
      try {
        if (!currentUserId) {
          const firstUser = document.querySelector('.player-card');
          if (firstUser) {
            document.getElementById('give-item-user').value = firstUser.dataset.userId;
          }
        } else {
          document.getElementById('give-item-user').value = currentUserId;
        }
        
        const modalElement = document.getElementById('giveItemModal');
        if (!modalElement) {
          console.error('Modal giveItemModal no encontrado');
          alert('Error: No se puede abrir el modal. Recarga la página.');
          return;
        }
        
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
      } catch (error) {
        console.error('Error al abrir modal:', error);
        alert('Error al abrir el modal: ' + error.message);
      }
    }

    function givePokemon() {
      const userId = parseInt(document.getElementById('give-pokemon-user').value);
      const speciesId = parseInt(document.getElementById('give-pokemon-species').value);
      const nivel = parseInt(document.getElementById('give-pokemon-nivel').value);
      const hp = parseInt(document.getElementById('give-pokemon-hp').value);
      
      // Validaciones
      if (!userId || userId <= 0) {
        alert('Selecciona un jugador válido');
        return;
      }
      
      if (!speciesId || speciesId <= 0) {
        alert('Selecciona una especie válida');
        return;
      }
      
      if (nivel < 1 || nivel > 100) {
        alert('El nivel debe estar entre 1 y 100');
        return;
      }
      
      if (hp < 1) {
        alert('El HP debe ser mayor a 0');
        return;
      }
      
      const data = {
        user_id: userId,
        species_id: speciesId,
        apodo: document.getElementById('give-pokemon-apodo').value,
        nivel: nivel,
        hp: hp
      };

      console.log('Enviando datos:', data);

      fetch('api/admin_give_pokemon.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      })
      .then(r => {
        console.log('Response status:', r.status);
        return r.text(); // Primero obtener como texto para ver qué llega
      })
      .then(text => {
        console.log('Response text:', text);
        try {
          const response = JSON.parse(text);
          if (response.success) {
            alert('Pokémon entregado correctamente');
            bootstrap.Modal.getInstance(document.getElementById('givePokemonModal')).hide();
            // Actualizar datos si es el usuario seleccionado actualmente
            const targetUserId = parseInt(data.user_id);
            if (currentUserId && currentUserId == targetUserId) {
              loadPlayerData(currentUserId);
            }
          } else {
            alert('Error: ' + response.error);
          }
        } catch (e) {
          console.error('Error parsing JSON:', e);
          console.error('Response was:', text);
          alert('Error: La respuesta del servidor no es válida. Revisa la consola para más detalles.');
        }
      })
      .catch(err => {
        console.error('Error en la petición:', err);
        alert('Error al dar Pokémon: ' + err.message);
      });
    }

    function giveItem() {
      const userId = parseInt(document.getElementById('give-item-user').value);
      const itemId = parseInt(document.getElementById('give-item-id').value);
      const cantidad = parseInt(document.getElementById('give-item-cantidad').value);
      
      // Validaciones
      if (!userId || userId <= 0) {
        alert('Selecciona un jugador válido');
        return;
      }
      
      if (!itemId || itemId <= 0) {
        alert('Selecciona un item válido');
        return;
      }
      
      if (!cantidad || cantidad <= 0) {
        alert('La cantidad debe ser mayor a 0');
        return;
      }
      
      const data = {
        user_id: userId,
        item_id: itemId,
        cantidad: cantidad
      };

      fetch('api/admin_give_item.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      })
      .then(r => r.json())
      .then(response => {
        if (response.success) {
          alert('Item entregado correctamente');
          bootstrap.Modal.getInstance(document.getElementById('giveItemModal')).hide();
          // Actualizar datos si es el usuario seleccionado actualmente
          const targetUserId = parseInt(data.user_id);
          if (currentUserId && currentUserId == targetUserId) {
            loadPlayerData(currentUserId);
          }
        } else {
          alert('Error: ' + response.error);
        }
      })
      .catch(err => {
        console.error('Error al dar item:', err);
        alert('Error al dar item');
      });
    }

    function editPlayerMoney(userId, currentMoney) {
      const newMoney = prompt('Introduce el nuevo dinero:', currentMoney);
      if (newMoney !== null && newMoney !== '') {
        const moneyValue = parseFloat(newMoney);
        
        // Validaciones
        if (isNaN(moneyValue)) {
          alert('El valor debe ser un número válido');
          return;
        }
        
        if (moneyValue < 0) {
          alert('El dinero no puede ser negativo');
          return;
        }
        
        fetch('api/admin_update_money.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ user_id: parseInt(userId), money: moneyValue })
        })
        .then(r => r.json())
        .then(response => {
          if (response.success) {
            alert('Dinero actualizado correctamente');
            // Actualizar datos del jugador
            loadPlayerData(userId);
            // Actualizar también la tarjeta del jugador en la lista
            const playerCard = document.querySelector(`[data-user-id="${userId}"]`);
            if (playerCard) {
              const badge = playerCard.querySelector('.badge');
              if (badge) {
                badge.textContent = parseFloat(moneyValue).toFixed(2) + '₽';
              }
            }
          } else {
            alert('Error: ' + response.error);
          }
        })
        .catch(err => {
          console.error('Error al actualizar dinero:', err);
          alert('Error al actualizar el dinero');
        });
      }
    }

    function removeItem(userId, itemId) {
      if (!confirm('¿Eliminar este item del inventario?')) return;
      
      const userIdInt = parseInt(userId);
      const itemIdInt = parseInt(itemId);
      
      fetch('api/admin_remove_item.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id: userIdInt, item_id: itemIdInt })
      })
      .then(r => r.json())
      .then(response => {
        if (response.success) {
          alert('Item eliminado');
          // Actualizar datos automáticamente
          if (currentUserId && currentUserId == userIdInt) {
            loadPlayerData(userIdInt);
          }
        } else {
          alert('Error: ' + response.error);
        }
      })
      .catch(err => {
        console.error('Error al eliminar item:', err);
        alert('Error al eliminar el item');
      });
    }

    function healPlayerPokemon() {
      if (!currentUserId) {
        alert('Selecciona un jugador primero');
        return;
      }
      
      if (!confirm('¿Curar todos los Pokémon de este jugador? (Restaurará HP y eliminará estados)')) {
        return;
      }
      
      fetch('api/admin_heal_pokemon.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id: currentUserId })
      })
      .then(r => r.json())
      .then(response => {
        if (response.success) {
          alert(`✨ ¡Pokémon curados! Se curaron ${response.pokemon_curados} Pokémon`);
          loadPlayerData(currentUserId); // Recargar datos
        } else {
          alert('Error: ' + response.error);
        }
      })
      .catch(err => {
        console.error('Error al curar Pokémon:', err);
        alert('Error al curar los Pokémon');
      });
    }

    function showTeachMoveModal(pokemonId, pokemonName, pokemonLevel) {
      // Validar datos
      pokemonId = parseInt(pokemonId);
      pokemonLevel = parseInt(pokemonLevel);
      
      if (!pokemonId || pokemonId <= 0) {
        alert('ID de Pokémon inválido');
        return;
      }
      
      // Establecer datos del Pokémon
      document.getElementById('teach-pokemon-id').value = pokemonId;
      document.getElementById('teach-pokemon-name').textContent = pokemonName;
      document.getElementById('teach-pokemon-level').textContent = pokemonLevel;
      
      // Filtrar movimientos disponibles según nivel
      const select = document.getElementById('teach-move-select');
      const options = select.querySelectorAll('option');
      
      options.forEach(option => {
        const nivelRequerido = parseInt(option.dataset.nivel) || 1;
        if (pokemonLevel < nivelRequerido) {
          option.disabled = true;
          option.style.color = '#ccc';
          option.textContent = option.textContent.replace(/\(Nv\.\d+\)/, `(Nv.${nivelRequerido} - BLOQUEADO)`);
        } else {
          option.disabled = false;
          option.style.color = '';
        }
      });
      
      // Abrir modal
      const modal = new bootstrap.Modal(document.getElementById('teachMoveModal'));
      modal.show();
    }

    function teachMove() {
      const pokemonId = parseInt(document.getElementById('teach-pokemon-id').value);
      const moveSelect = document.getElementById('teach-move-select');
      const movimientoId = parseInt(moveSelect.value);
      
      if (!pokemonId || pokemonId <= 0) {
        alert('Pokémon inválido');
        return;
      }
      
      if (!movimientoId || movimientoId <= 0) {
        alert('Selecciona un movimiento');
        return;
      }
      
      // Confirmar
      const selectedOption = moveSelect.options[moveSelect.selectedIndex];
      const moveName = selectedOption.text;
      
      if (!confirm(`¿Enseñar ${moveName}?`)) {
        return;
      }
      
      // Enviar petición
      fetch('api/admin_teach_move.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          pokemon_box_id: pokemonId,
          movimiento_id: movimientoId
        })
      })
      .then(r => r.json())
      .then(response => {
        if (response.success) {
          alert('✓ ' + response.message);
          bootstrap.Modal.getInstance(document.getElementById('teachMoveModal')).hide();
          // Recargar datos del jugador
          if (currentUserId) {
            loadPlayerData(currentUserId);
          }
        } else {
          alert('Error: ' + response.error);
        }
      })
      .catch(err => {
        console.error('Error al enseñar movimiento:', err);
        alert('Error al enseñar el movimiento');
      });
    }
    
    // Inicializar búsqueda de especies al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
      setupSpeciesSearch();
      console.log('Sistema de búsqueda de especies inicializado');
    });
  </script>
</body>
</html>
