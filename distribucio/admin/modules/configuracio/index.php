<?php
/**
 * El Celler Nou - Configuració Web
 */
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

// Necessitem les funcions de configuració
require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/includes/functions.php';

$pageTitle = 'Configuració Web';
$successMessage = '';
$errorMessage = '';

// Processar formulari
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errorMessage = 'Error de seguretat. Torna a intentar-ho.';
    } else {
        $descuento = floatval(str_replace(',', '.', $_POST['descuento_web'] ?? 0));
        $ibanFactura = trim($_POST['iban_factura'] ?? '');

        // Validar que el descuento esté entre 0 y 100
        if ($descuento < 0 || $descuento > 100) {
            $errorMessage = 'El descompte ha d\'estar entre 0 i 100%.';
        } else {
            setConfiguracionWeb('descuento_web', $descuento, 'Descompte general per a compres web (%)');
            setConfiguracionWeb('iban_factura', $ibanFactura, 'IBAN que apareix a les factures de client');
            logActivity('config_update', "Configuració actualitzada");
            $successMessage = 'Configuració guardada correctament.';
        }
    }
}

// Obtener configuración actual
$descuentoActual = getDescuentoWeb();
$ibanActual = getConfiguracionWeb('iban_factura') ?: 'ES20 0081 0379 8500 0126 4437';

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<?php if ($successMessage): ?>
<div class="alert alert-success" style="margin-bottom: 1rem;">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px;">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <?php echo e($successMessage); ?>
</div>
<?php endif; ?>

<?php if ($errorMessage): ?>
<div class="alert alert-error" style="margin-bottom: 1rem;">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px;">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <?php echo e($errorMessage); ?>
</div>
<?php endif; ?>

<form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                Descompte Web
            </h3>
        </div>
        <div class="card-body">
            <p style="color: var(--gray-600); margin-bottom: 1.5rem;">
                Configura un descompte general que s'aplicarà a tots els preus mostrats a la botiga web.
                Aquest descompte es mostra als clients quan naveguen pels productes.
            </p>

            <div class="form-group" style="max-width: 300px;">
                <label class="form-label" for="descuento_web">Percentatge de descompte (%)</label>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="number"
                           id="descuento_web"
                           name="descuento_web"
                           class="form-input"
                           value="<?php echo number_format($descuentoActual, 2, '.', ''); ?>"
                           min="0"
                           max="100"
                           step="0.01"
                           style="text-align: right;">
                    <span style="font-size: 1.25rem; font-weight: 600; color: var(--gray-700);">%</span>
                </div>
                <small style="color: var(--gray-500); display: block; margin-top: 0.5rem;">
                    Introdueix 0 per desactivar el descompte. Màxim 100%.
                </small>
            </div>

            <?php if ($descuentoActual > 0): ?>
            <div class="discount-preview" style="margin-top: 1.5rem; padding: 1rem; background: var(--gray-50); border-radius: 8px; border-left: 4px solid var(--primary);">
                <h4 style="font-size: 0.875rem; color: var(--gray-700); margin-bottom: 0.5rem;">
                    Vista prèvia amb el descompte actual (<?php echo number_format($descuentoActual, 0); ?>%)
                </h4>
                <div style="display: flex; gap: 2rem; align-items: center;">
                    <div>
                        <span style="color: var(--gray-500); text-decoration: line-through;">10,00 &euro;</span>
                        <span style="margin-left: 0.5rem; color: var(--primary); font-weight: 600; font-size: 1.125rem;">
                            <?php echo number_format(10 * (1 - $descuentoActual/100), 2, ',', '.'); ?> &euro;
                        </span>
                    </div>
                    <div>
                        <span style="color: var(--gray-500); text-decoration: line-through;">25,50 &euro;</span>
                        <span style="margin-left: 0.5rem; color: var(--primary); font-weight: 600; font-size: 1.125rem;">
                            <?php echo number_format(25.50 * (1 - $descuentoActual/100), 2, ',', '.'); ?> &euro;
                        </span>
                    </div>
                    <div>
                        <span style="color: var(--gray-500); text-decoration: line-through;">100,00 &euro;</span>
                        <span style="margin-left: 0.5rem; color: var(--primary); font-weight: 600; font-size: 1.125rem;">
                            <?php echo number_format(100 * (1 - $descuentoActual/100), 2, ',', '.'); ?> &euro;
                        </span>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <div class="card-footer" style="display: flex; justify-content: flex-end; padding: 1rem 1.5rem; background: var(--gray-50); border-top: 1px solid var(--gray-200);">
        </div>
    </div>

    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                Compte Bancari (Factures)
            </h3>
        </div>
        <div class="card-body">
            <p style="color: var(--gray-600); margin-bottom: 1.5rem;">
                IBAN que apareixerà al peu de les factures de client.
            </p>

            <div class="form-group" style="max-width: 400px;">
                <label class="form-label" for="iban_factura">IBAN</label>
                <input type="text"
                       id="iban_factura"
                       name="iban_factura"
                       class="form-input"
                       value="<?php echo e($ibanActual); ?>"
                       placeholder="ES00 0000 0000 0000 0000 0000"
                       maxlength="34">
                <small style="color: var(--gray-500); display: block; margin-top: 0.5rem;">
                    Deixa en blanc per no mostrar cap IBAN a les factures.
                </small>
            </div>
        </div>
        <div class="card-footer" style="display: flex; justify-content: flex-end; padding: 1rem 1.5rem; background: var(--gray-50); border-top: 1px solid var(--gray-200);">
            <button type="submit" class="btn btn-primary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Guardar Configuració
            </button>
        </div>
    </div>
</form>

<style>
.form-group {
    margin-bottom: 0;
}
.form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--gray-700);
    margin-bottom: 0.5rem;
}
.form-input {
    width: 100%;
    padding: 0.625rem 0.875rem;
    font-size: 1rem;
    border: 1px solid var(--gray-300);
    border-radius: 6px;
    transition: all 0.2s;
}
.form-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(114, 47, 55, 0.1);
}
.alert {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    border-radius: 8px;
}
.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}
.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}
</style>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
