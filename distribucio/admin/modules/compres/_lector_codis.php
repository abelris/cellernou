<?php
/**
 * Lector de codis de barres per a les línies de compra.
 *
 * S'inclou a crear.php i modificar.php, dins del formulari i abans de la
 * targeta de línies. Fa servir el que ja defineixen aquestes pàgines:
 * `productes`, afegirLinia(), onProducteChange() i calcularTotals().
 *
 * - Codi trobat: suma 1 a la línia del producte o n'afegeix una de nova.
 * - Codi d'un producte inactiu: proposa reactivar-lo.
 * - Codi desconegut: deixa associar-lo a un producte existent o crear-ne un.
 */

$lcSectores = [];
$lcTipos = [];
$lcProcedencias = [];
foreach ([
    'lcSectores'     => "SELECT id, nombre_es FROM sectores ORDER BY nombre_es",
    'lcTipos'        => "SELECT id, nombre_es FROM tipo_producto ORDER BY nombre_es",
    'lcProcedencias' => "SELECT id, nombre_es FROM procedencia ORDER BY nombre_es",
] as $var => $sql) {
    $r = mysqli_query($bd, $sql);
    while ($r && ($row = mysqli_fetch_assoc($r))) {
        ${$var}[] = $row;
    }
}
?>
<div class="card lc-card" style="margin-bottom: 1.5rem;">
    <div class="card-body">
        <label class="form-label" for="lcCodi">Codi de barres</label>
        <div class="lc-fila">
            <input type="text" id="lcCodi" class="form-input lc-input" autocomplete="off" inputmode="numeric"
                   placeholder="Escaneja o escriu el codi i prem Enter" autofocus>
            <button type="button" class="btn btn-secondary" onclick="lcEscanejat()">Afegir</button>
        </div>
        <div id="lcMissatge" class="lc-missatge" role="status" aria-live="polite"></div>
    </div>
</div>

<div id="lcModal" class="lc-modal" style="display: none;" role="dialog" aria-modal="true" aria-labelledby="lcModalTitol">
    <div class="lc-modal-caixa">
        <div class="lc-modal-cap">
            <h3 id="lcModalTitol">Codi <code id="lcModalCodi"></code> no trobat</h3>
            <button type="button" class="btn-remove" onclick="lcTancar()" title="Tancar" aria-label="Tancar">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div id="lcInactiu" class="lc-avis" style="display: none;"></div>

        <div class="lc-pestanyes">
            <button type="button" class="lc-pestanya active" data-panell="lcAssociar" onclick="lcPestanya(this)">Associar a un producte existent</button>
            <button type="button" class="lc-pestanya" data-panell="lcCrear" onclick="lcPestanya(this)">Crear producte nou</button>
        </div>

        <div id="lcAssociar" class="lc-panell">
            <input type="text" id="lcCerca" class="form-input" placeholder="Busca pel nom del producte..." autocomplete="off" oninput="lcCercar()">
            <ul id="lcResultats" class="lc-resultats"></ul>
        </div>

        <div id="lcCrear" class="lc-panell" style="display: none;">
            <div class="lc-graella">
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="form-label" for="lcNom">Nom *</label>
                    <input type="text" id="lcNom" class="form-input" maxlength="100">
                </div>
                <div class="form-group">
                    <label class="form-label" for="lcSector">Sector *</label>
                    <select id="lcSector" class="form-input">
                        <option value="">Selecciona...</option>
                        <?php foreach ($lcSectores as $s): ?>
                        <option value="<?php echo (int)$s['id']; ?>"><?php echo e($s['nombre_es']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="lcTipus">Tipus *</label>
                    <select id="lcTipus" class="form-input">
                        <option value="">Selecciona...</option>
                        <?php foreach ($lcTipos as $t): ?>
                        <option value="<?php echo (int)$t['id']; ?>"><?php echo e($t['nombre_es']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="lcProcedencia">Procedència</label>
                    <select id="lcProcedencia" class="form-input">
                        <option value="0">Sense procedència</option>
                        <?php foreach ($lcProcedencias as $p): ?>
                        <option value="<?php echo (int)$p['id']; ?>"><?php echo e($p['nombre_es']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="lcIva">IVA %</label>
                    <input type="number" id="lcIva" class="form-input" value="21" step="1">
                </div>
                <div class="form-group">
                    <label class="form-label" for="lcPreu">Preu compra s/IVA</label>
                    <input type="text" id="lcPreu" class="form-input" placeholder="0,00">
                </div>
                <div class="form-group">
                    <label class="form-label" for="lcPvp">PVP (amb IVA)</label>
                    <input type="text" id="lcPvp" class="form-input" placeholder="0,00">
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-primary" onclick="lcCrear()">Crear i afegir a la compra</button>
            </div>
        </div>

        <div id="lcError" class="lc-error" style="display: none;"></div>
    </div>
</div>

<style>
.lc-fila { display: flex; gap: 0.75rem; }
.lc-input { font-family: monospace; font-size: 1rem; }
.lc-missatge { min-height: 1.25rem; margin-top: 0.5rem; font-size: 0.875rem; }
.lc-missatge.ok { color: #059669; }
.lc-missatge.ko { color: #dc2626; }
.lc-modal { position: fixed; inset: 0; background: rgba(0,0,0,0.45); display: flex; align-items: flex-start; justify-content: center; z-index: 1000; padding: 5vh 1rem; overflow-y: auto; }
.lc-modal-caixa { background: #fff; border-radius: 10px; width: 100%; max-width: 640px; padding: 1.5rem; box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
.lc-modal-cap { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.lc-modal-cap h3 { margin: 0; font-size: 1.125rem; }
.lc-pestanyes { display: flex; gap: 0.5rem; border-bottom: 1px solid var(--gray-200, #e5e7eb); margin-bottom: 1rem; }
.lc-pestanya { background: none; border: none; padding: 0.625rem 0.75rem; cursor: pointer; font-size: 0.875rem; color: var(--gray-500, #6b7280); border-bottom: 2px solid transparent; }
.lc-pestanya.active { color: var(--primary); border-bottom-color: var(--primary); font-weight: 600; }
.lc-resultats { list-style: none; margin: 0.75rem 0 0; padding: 0; max-height: 320px; overflow-y: auto; }
.lc-resultats li { display: flex; justify-content: space-between; align-items: center; gap: 0.75rem; padding: 0.5rem 0.25rem; border-bottom: 1px solid var(--gray-100, #f3f4f6); font-size: 0.875rem; }
.lc-resultats code { color: var(--gray-500, #6b7280); font-size: 0.75rem; }
.lc-graella { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; }
.lc-avis { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.875rem; }
.lc-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; padding: 0.75rem 1rem; border-radius: 8px; margin-top: 1rem; font-size: 0.875rem; }
@media (max-width: 768px) { .lc-graella { grid-template-columns: 1fr; } }
</style>

<script>
const lcAjaxUrl = <?php echo json_encode(getAdminUrl('modules/compres/codi_barres_ajax.php')); ?>;
let lcCodiActual = '';

function lcCsrf() {
    return document.querySelector('input[name="csrf_token"]').value;
}

function lcMissatge(text, ok) {
    const el = document.getElementById('lcMissatge');
    el.textContent = text;
    el.className = 'lc-missatge ' + (ok ? 'ok' : 'ko');
}

function lcErrorModal(text) {
    const el = document.getElementById('lcError');
    el.textContent = text || '';
    el.style.display = text ? 'block' : 'none';
}

function lcNormalitza(s) {
    return String(s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
}

async function lcPost(dades) {
    const fd = new FormData();
    fd.append('csrf_token', lcCsrf());
    Object.entries(dades).forEach(([k, v]) => fd.append(k, v));
    try {
        const r = await fetch(lcAjaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' });
        return await r.json();
    } catch (e) {
        // Normalment, sessió caducada: el servidor torna la pàgina de login.
        return { success: false, error: 'No s\'ha pogut desar. Si fa estona que tens la pàgina oberta, recarrega-la.' };
    }
}

/** Afegeix (o actualitza) el producte a `productes` perquè els selects nous el tinguin. */
function lcRecordaProducte(p) {
    const i = productes.findIndex(x => x.id == p.id);
    if (i >= 0) productes[i] = Object.assign(productes[i], p);
    else productes.push(p);
}

/** Suma 1 a la línia del producte, o en posa una de nova. */
function lcAfegeixALinia(p) {
    const files = Array.from(document.querySelectorAll('#liniesBody tr'));

    const existent = files.find(tr => tr.querySelector('.linea-producte')?.value == p.id);
    if (existent) {
        const qty = existent.querySelector('input[name="linea_cantidad[]"]');
        qty.value = (parseInt(qty.value, 10) || 0) + 1;
        calcularTotals();
        existent.classList.add('lc-ressalta');
        setTimeout(() => existent.classList.remove('lc-ressalta'), 800);
        return 'Quantitat de «' + p.nombre + '»: ' + qty.value;
    }

    let fila = files.find(tr => tr.querySelector('.linea-producte')?.value === '');
    if (!fila) {
        afegirLinia();
        const totes = document.querySelectorAll('#liniesBody tr');
        fila = totes[totes.length - 1];
    }

    const select = fila.querySelector('.linea-producte');
    if (!select.querySelector('option[value="' + p.id + '"]')) {
        const opt = document.createElement('option');
        opt.value = p.id;
        opt.dataset.preu = p.precio_medio;
        opt.dataset.iva = p.iva;
        opt.dataset.pvp = p.pvp;
        opt.dataset.pvd = p.precio_venta_dis;
        opt.textContent = p.nombre + (p.codigo ? ' (' + p.codigo + ')' : '');
        select.appendChild(opt);
    }
    select.value = p.id;
    onProducteChange(select, parseInt(fila.id.replace('linia-', ''), 10));
    return 'Afegit: ' + p.nombre;
}

function lcFet(p) {
    lcRecordaProducte(p);
    lcMissatge(lcAfegeixALinia(p), true);
    lcTancar();
}

async function lcEscanejat() {
    const input = document.getElementById('lcCodi');
    const codi = input.value.replace(/\s+/g, '');
    input.value = '';
    input.focus();
    if (!codi) return;

    const local = productes.find(p => String(p.codigo || '').trim() === codi);
    if (local) {
        lcMissatge(lcAfegeixALinia(local), true);
        return;
    }

    // No és a la llista d'actius: pot ser d'un producte inactiu.
    try {
        const r = await fetch(lcAjaxUrl + '?accio=buscar&codigo=' + encodeURIComponent(codi), { credentials: 'same-origin' });
        const res = await r.json();
        if (!res.success) { lcMissatge(res.error || 'Error en buscar el codi.', false); return; }
        // Actiu però no carregat a la pàgina (creat des d'una altra pestanya).
        if (res.producte && res.producte.activo == 1) { lcFet(res.producte); return; }
        lcObre(codi, res.producte);
    } catch (e) {
        lcMissatge('No s\'ha pogut buscar el codi: ' + e.message, false);
    }
}

function lcObre(codi, inactiu) {
    lcCodiActual = codi;
    document.getElementById('lcModalCodi').textContent = codi;
    lcErrorModal('');

    const avis = document.getElementById('lcInactiu');
    if (inactiu) {
        avis.innerHTML = '';
        avis.append('Aquest codi és de «' + inactiu.nombre + '», que està desactivat. ');
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'btn btn-sm btn-primary';
        b.textContent = 'Reactivar-lo i afegir-lo';
        b.onclick = async () => {
            const res = await lcPost({ accio: 'reactivar', id: inactiu.id });
            res.success ? lcFet(res.producte) : lcErrorModal(res.error);
        };
        avis.appendChild(b);
        avis.style.display = 'block';
    } else {
        avis.style.display = 'none';
    }

    document.getElementById('lcCerca').value = '';
    document.getElementById('lcResultats').innerHTML = '';
    ['lcNom', 'lcPreu', 'lcPvp'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('lcModal').style.display = 'flex';
    lcPestanya(document.querySelector('.lc-pestanya[data-panell="lcAssociar"]'));
    document.getElementById('lcCerca').focus();
}

function lcTancar() {
    document.getElementById('lcModal').style.display = 'none';
    document.getElementById('lcCodi').focus();
}

function lcPestanya(boto) {
    document.querySelectorAll('.lc-pestanya').forEach(b => b.classList.toggle('active', b === boto));
    document.querySelectorAll('.lc-panell').forEach(p => p.style.display = p.id === boto.dataset.panell ? 'block' : 'none');
    if (boto.dataset.panell === 'lcCrear') {
        // El que s'hagi escrit a la cerca sol ser el nom del producte nou.
        const nom = document.getElementById('lcNom');
        if (!nom.value) nom.value = document.getElementById('lcCerca').value;
        nom.focus();
    }
}

function lcCercar() {
    const q = lcNormalitza(document.getElementById('lcCerca').value).trim();
    const ul = document.getElementById('lcResultats');
    ul.innerHTML = '';
    if (q.length < 2) return;

    const paraules = q.split(/\s+/);
    const trobats = productes
        .filter(p => { const n = lcNormalitza(p.nombre); return paraules.every(w => n.includes(w)); })
        .slice(0, 30);

    if (!trobats.length) {
        const li = document.createElement('li');
        li.textContent = 'Cap producte. Pots crear-lo a la pestanya «Crear producte nou».';
        ul.appendChild(li);
        return;
    }

    trobats.forEach(p => {
        const li = document.createElement('li');
        const nom = document.createElement('span');
        nom.textContent = p.nombre + ' ';
        const codi = document.createElement('code');
        codi.textContent = p.codigo ? p.codigo : 'sense codi';
        nom.appendChild(codi);
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'btn btn-sm btn-secondary';
        b.textContent = 'Associar';
        b.onclick = () => lcAssociar(p);
        li.append(nom, b);
        ul.appendChild(li);
    });
}

async function lcAssociar(p, substituir) {
    lcErrorModal('');
    const res = await lcPost({ accio: 'associar', codigo: lcCodiActual, id: p.id, substituir: substituir ? 1 : '' });
    if (res.success) { lcFet(res.producte); return; }
    if (res.confirmar) {
        if (confirm(res.error + '\nVols substituir-lo per ' + lcCodiActual + '? El codi antic deixarà de funcionar al TPV.')) {
            lcAssociar(p, true);
        }
        return;
    }
    lcErrorModal(res.error);
}

async function lcCrear() {
    lcErrorModal('');
    const res = await lcPost({
        accio: 'crear',
        codigo: lcCodiActual,
        nombre: document.getElementById('lcNom').value.trim(),
        idSector: document.getElementById('lcSector').value,
        idTipo: document.getElementById('lcTipus').value,
        idProcedencia: document.getElementById('lcProcedencia').value,
        iva: document.getElementById('lcIva').value,
        precio_medio: document.getElementById('lcPreu').value,
        pvp: document.getElementById('lcPvp').value
    });
    res.success ? lcFet(res.producte) : lcErrorModal(res.error);
}

// Els lectors envien el codi seguit d'Enter: que no enviï el formulari.
document.getElementById('lcCodi').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); lcEscanejat(); }
});
document.getElementById('lcCerca').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') e.preventDefault();
});
document.getElementById('lcCrear').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); lcCrear(); }
});
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && document.getElementById('lcModal').style.display !== 'none') lcTancar();
});
</script>
<style>.lc-ressalta { background: #ecfdf5; transition: background 0.3s; }</style>
