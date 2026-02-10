/**
 * pokemon-info.js
 * Maneja el modal de información detallada del Pokémon con visualización de stats en rombo
 */

// Variable global para almacenar datos del Pokémon
let currentPokemonData = null;

/**
 * Función helper para oscurecer/aclarar colores hexadecimales
 * @param {string} color - Color en formato hexadecimal (#RRGGBB)
 * @param {number} percent - Porcentaje de cambio (-100 a 100)
 * @returns {string} Color modificado
 */
function shadeColor(color, percent) {
  let R = parseInt(color.substring(1,3), 16);
  let G = parseInt(color.substring(3,5), 16);
  let B = parseInt(color.substring(5,7), 16);

  R = parseInt(R * (100 + percent) / 100);
  G = parseInt(G * (100 + percent) / 100);
  B = parseInt(B * (100 + percent) / 100);

  R = (R < 255) ? R : 255;
  G = (G < 255) ? G : 255;
  B = (B < 255) ? B : 255;

  const RR = ((R.toString(16).length == 1) ? "0" + R.toString(16) : R.toString(16));
  const GG = ((G.toString(16).length == 1) ? "0" + G.toString(16) : G.toString(16));
  const BB = ((B.toString(16).length == 1) ? "0" + B.toString(16) : B.toString(16));

  return "#" + RR + GG + BB;
}

/**
 * Muestra el modal con información detallada del Pokémon
 */
async function showPokemonInfo(boxId) {
  try {
    // Cargar información desde API
    const res = await fetch('api/get_pokemon_info.php?box_id=' + boxId);
    if (!res.ok) {
      showToast('Error al cargar información del Pokémon', 'danger');
      return;
    }

    const data = await res.json();
    if (!data.success) {
      showToast(data.error || 'Error desconocido', 'danger');
      return;
    }

    // Debug: verificar datos de pokémon
    console.log('Pokemon:', data.pokemon.nombre_especie, 'Sprite URL:', data.pokemon.sprite_url);

    currentPokemonData = data;
    renderPokemonInfoModal(data);

    // Mostrar modal
    const modalEl = document.getElementById('pokemonInfoModal');
    
    // Remover aria-hidden ANTES de mostrar el modal
    modalEl.removeAttribute('aria-hidden');
    
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
    // Animación de entrada para el contenido del modal
    setTimeout(() => {
      const content = modalEl.querySelector('.modal-content');
      if (content) content.classList.add('modal-content-animate');
    }, 10);
    // Limpiar clase al cerrar para futuras aperturas
    modalEl.addEventListener('hidden.bs.modal', function handler() {
      const content = modalEl.querySelector('.modal-content');
      if (content) content.classList.remove('modal-content-animate');
      // No restaurar aria-hidden para evitar warnings
      modalEl.removeEventListener('hidden.bs.modal', handler);
    });

  } catch (e) {
    console.error('Error cargando info:', e);
    showToast('Error de red al cargar información', 'danger');
  }
}

/**
 * Renderiza el contenido del modal con toda la información
 */
function renderPokemonInfoModal(data) {
  const pokemon = data.pokemon;
  const stats = data.stats;
  const statsBase = data.stats_base;
  let movimientos = data.movimientos || [];
  const movimientosDisponibles = data.movimientos_disponibles || [];

  // Debug: log stats to ensure rendering
  if (window && window.console && typeof console.debug === 'function') {
    console.debug('renderPokemonInfoModal - stats', stats, 'statsBase', statsBase, 'movimientos', movimientos);
  }

  // Ordenar movimientos por slot para una presentación consistente
  movimientos.sort((a,b) => (Number(a.slot) || 0) - (Number(b.slot) || 0));

  // Computar primer slot libre (1..4). Usado al enseñar un movimiento nuevo
  const usedSlots = movimientos.map(m => Number(m.slot));
  const slotAvailable = [1,2,3,4].find(s => !usedSlots.includes(s)) || null;

  const container = document.getElementById('pokemonInfoContent');
  
  // Construir badges de tipos con gradientes
  let tiposHTML = '';
  if (pokemon.tipo_primario) {
    const color1 = pokemon.tipo_primario_color || '#888';
    const gradientStyle = `background: linear-gradient(135deg, ${color1} 0%, ${shadeColor(color1, -20)} 100%);`;
    tiposHTML += `<span class="type-badge" style="${gradientStyle}">${escapeHtml(pokemon.tipo_primario)}</span>`;
  }
  if (pokemon.tipo_secundario) {
    const color2 = pokemon.tipo_secundario_color || '#888';
    const gradientStyle = `background: linear-gradient(135deg, ${color2} 0%, ${shadeColor(color2, -20)} 100%);`;
    tiposHTML += `<span class="type-badge" style="${gradientStyle}">${escapeHtml(pokemon.tipo_secundario)}</span>`;
  }
  
  // Construir HTML del modal
  const nombreMostrar = pokemon.apodo || pokemon.nombre_especie || `Pokemon ID ${pokemon.id}`;
  
  let html = `
    <div class="pokemon-info-header" style="margin: -1rem -1rem 1rem -1rem;">
      <h3 style="color:white; font-weight:700; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
        ${escapeHtml(nombreMostrar)}
      </h3>
      <div class="pokemon-info-sprite">
        ${pokemon.sprite_url ? `<img src="img/pokemon/${escapeHtml(pokemon.sprite_url)}" alt="${escapeHtml(pokemon.nombre_especie)}" onerror="console.warn('Imagen no encontrada:', this.src); this.onerror=null; this.style.display='none'; this.nextElementSibling ? this.nextElementSibling.remove() : null; const fallback = document.createElement('div'); fallback.textContent = '⚡'; fallback.style.fontSize = '3rem'; this.parentElement.appendChild(fallback);">` : '<div style="font-size: 3rem;">⚡</div>'}
      </div>
      ${tiposHTML ? `
      <div class="pokemon-types-display">
        ${tiposHTML}
      </div>
      ` : ''}
      <div class="pokemon-info-meta">
        <div class="pokemon-info-meta-item">
          <strong>Especie</strong>
          ${escapeHtml(pokemon.nombre_especie || 'Desconocida')}
        </div>
        <div class="pokemon-info-meta-item">
          <strong>Nivel</strong>
          ${pokemon.nivel}
        </div>
        <div class="pokemon-info-meta-item">
          <strong>HP</strong>
          ${pokemon.hp_actual}/${pokemon.hp_maximo}
        </div>
      </div>
    </div>

    <!-- STATS (BARRAS HORIZONTALES) -->
    <div class="stats-bars-container">
      ${renderStatsBars(stats, statsBase, pokemon.stat_aumentado, pokemon.stat_reducido)}
    </div>

    <!-- NATURALEZA Y HABILIDAD -->
    <div class="pokemon-nature-ability">
      <div class="pokemon-nature-section">
        <div class="pokemon-nature-name">🎭 Naturaleza: ${escapeHtml(pokemon.naturaleza)}</div>
        <div class="pokemon-nature-effect">
          ${pokemon.stat_aumentado || pokemon.stat_reducido ? `
            ${pokemon.stat_aumentado ? `<span style="color:#4caf50;">↑ ${pokemon.stat_aumentado}</span>` : ''}
            ${pokemon.stat_reducido ? `<span style="color:#f44336;">↓ ${pokemon.stat_reducido}</span>` : ''}
          ` : 'Sin efectos (neutral)'}
        </div>
      </div>

      <div class="pokemon-ability-section">
        <div class="pokemon-ability-name">⚡ Habilidad: ${escapeHtml(pokemon.habilidad)}</div>
        <div class="pokemon-ability-desc">${escapeHtml(pokemon.habilidad_descripcion || 'Sin descripción')}</div>
      </div>
    </div>

    <!-- MOVIMIENTOS APRENDIDOS -->
    <div class="pokemon-moves-section">
      <div class="pokemon-moves-title">
        <span>🎯 Movimientos (${movimientos.length}/4)</span>
      </div>
      <div class="pokemon-moves-list">
        ${movimientos.length === 0 ? `
          <div class="small-muted" style="padding: 1rem; text-align:center;">
            Este Pokémon no conoce ningún movimiento aún
          </div>
        ` : movimientos.map((move, idx) => `
          <div class="move-card">
            <div class="move-slot-badge">${move.slot}</div>
            <div class="move-type-badge" style="background-color: ${escapeHtml(move.tipo_color || '#888')};">
              ${escapeHtml(move.tipo || 'Normal')}
            </div>
            <div class="move-info">
              <div class="move-name">${escapeHtml(move.nombre)}</div>
              <div class="move-stats">
                ${move.potencia > 0 ? `<div class="move-stat-item"><strong>Potencia:</strong> ${move.potencia}</div>` : ''}
                ${move.categoria !== 'estado' ? `<div class="move-stat-item"><strong>Precisión:</strong> ${move.precision}%</div>` : ''}
              </div>
            </div>
            <button class="btn btn-sm btn-outline-danger" onclick="forgetMove(${pokemon.id}, ${move.slot})">Olvidar</button>
          </div>
        `).join('')}
      </div>
    </div>

    <!-- MOVIMIENTOS DISPONIBLES PARA APRENDER -->
    <div class="learn-moves-section">
      <div class="learn-moves-title">
        📚 Movimientos disponibles para aprender (${movimientosDisponibles.length})
      </div>
      <div class="learn-moves-grid">
        ${movimientosDisponibles.length === 0 ? `
          <div class="small-muted" style="padding: 1rem; text-align:center;">
            No hay movimientos disponibles para esta especie
          </div>
        ` : movimientosDisponibles.map((move) => {
          const nivelRequerido = move.nivel_aprendizaje || 1;
          const nivelActual = pokemon.nivel || 1;
          const puedeAprender = nivelActual >= nivelRequerido;
          return `
          <div class="available-move-card ${!puedeAprender ? 'move-locked' : ''}">
            <div class="available-move-name">${escapeHtml(move.nombre)}</div>
            <div class="available-move-level">Nv. ${nivelRequerido}${!puedeAprender ? ' 🔒' : ''}</div>
            ${slotAvailable ? `
              <button class="btn btn-sm btn-primary" 
                      onclick="learnMove(${pokemon.id}, ${move.id}, ${slotAvailable})" 
                      ${!puedeAprender ? 'disabled title="Nivel insuficiente"' : ''}>
                ${puedeAprender ? 'Enseñar' : 'Bloqueado'}
              </button>
            ` : ''}
          </div>
        `}).join('')}
      </div>
    </div>
  `;

  container.innerHTML = html;
  // Animar barras de stats tras render
  animateStatsBars();
}

function renderStatsBars(stats, statsBase, statUp, statDown) {
  const order = ['hp','ataque','defensa','sp_ataque','sp_defensa','velocidad'];
  if (window && window.console && typeof console.debug === 'function') console.debug('renderStatsBars', {stats, statsBase, statUp, statDown});
  const labelMap = { 'hp':'HP','ataque':'ATQ','defensa':'DEF','sp_ataque':'ESP.ATQ','sp_defensa':'ESP.DEF','velocidad':'VEL' };

  // Escala: basamos la anchura en la stat base máxima (con margen)
  const baseMax = Math.max(...Object.values(statsBase), 1);
  const maxScale = baseMax * 1.2;

  const rows = order.map(k => {
    const val = stats[k] || 0;
    const pct = Math.max(0, Math.min(100, Math.round((val / maxScale) * 100)));
    const isUp = statUp === k;
    const isDown = statDown === k;
    return { key: k, label: labelMap[k], val, pct, isUp, isDown, base: statsBase[k] || 0 };
  });

  const rowsHtml = rows.map(r => `
    <div class="stat-row" role="group" aria-label="${r.label}">
      <div class="stat-row-left">
        <div class="stat-label-name">${r.label}</div>
        <div class="stat-base">(${r.base})</div>
      </div>
      <div class="stat-row-right">
        <div class="stat-bar" aria-hidden="true">
          <div class="stat-bar-fill" data-target="${r.pct}" style="width:0%;"></div>
        </div>
        <div class="stat-value">${r.val} ${r.isUp?'<span class="nature up">↑</span>':''}${r.isDown?'<span class="nature down">↓</span>':''}</div>
      </div>
    </div>
  `).join('');

  const html = `
    <div class="stats-bars">
      ${rowsHtml}
      <div class="small-muted mt-2">Escala basada en base máxima: ${Math.round(maxScale)}</div>
    </div>
  `;
  return html;
}

function animateStatsBars() {
  const container = document.querySelector('.stats-bars');
  if (!container) return;
  container.classList.add('visible');
  const fills = Array.from(container.querySelectorAll('.stat-bar-fill'));
  fills.forEach((el, idx) => {
    const tgt = el.getAttribute('data-target') || '0';
    el.style.transition = 'width 700ms cubic-bezier(0.2,0.9,0.2,1)';
    el.style.transitionDelay = (idx * 90) + 'ms';
    void el.offsetWidth;
    setTimeout(() => { el.style.width = tgt + '%'; }, 20);
  });
}

/**
 * Enseña un movimiento al Pokémon
 */
async function learnMove(pokemonId, moveId, slot) {
  try {
    const res = await fetch('api/learn_move.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({
        box_id: pokemonId,
        movimiento_id: moveId,
        slot: slot,
        action: 'add'
      })
    });

    const data = await res.json();
    if (res.status === 409) {
      showToast(data.error || 'El Pokémon ya conoce ese movimiento', 'warning');
      return;
    }

    if (!data.success) {
      showToast(data.error || 'Error enseñando movimiento', 'danger');
      return;
    }

    showToast('¡Movimiento aprendido!', 'success');
    
    // Recargar información
    if (currentPokemonData) {
      currentPokemonData.movimientos = data.movimientos;
      renderPokemonInfoModal(currentPokemonData);
    }

  } catch (e) {
    console.error('Error:', e);
    showToast('Error de red', 'danger');
  }
}

/**
 * Olvida un movimiento
 */
async function forgetMove(pokemonId, slot) {
  if (!confirm('¿Deseas que olvide este movimiento?')) return;

  try {
    const res = await fetch('api/learn_move.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({
        box_id: pokemonId,
        slot: slot,
        action: 'remove'
      })
    });

    const data = await res.json();
    if (!data.success) {
      showToast(data.error || 'Error olvidando movimiento', 'danger');
      return;
    }

    showToast('Movimiento olvidado', 'success');
    
    // Recargar información
    if (currentPokemonData) {
      currentPokemonData.movimientos = data.movimientos;
      renderPokemonInfoModal(currentPokemonData);
    }

  } catch (e) {
    console.error('Error:', e);
    showToast('Error de red', 'danger');
  }
}

/**
 * Escapa caracteres HTML para evitar XSS
 */
function escapeHtml(text) {
  if (!text) return '';
  const map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };
  return text.replace(/[&<>"']/g, m => map[m]);
}
