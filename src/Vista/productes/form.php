<?php
require __DIR__ . '/../layouts/sidebar.php';

$nomVal = '';
if (isset($old['nom'])) {
    $nomVal = (string) $old['nom'];
} elseif ($isEdit && isset($product['nom'])) {
    $nomVal = (string) $product['nom'];
}

$descripcioVal = '';
if (isset($old['descripcio'])) {
    $descripcioVal = (string) $old['descripcio'];
} elseif ($isEdit && isset($product['descripcio'])) {
    $descripcioVal = (string) $product['descripcio'];
}

$formatVal = '';
if (isset($old['format_envasat'])) {
    $formatVal = (string) $old['format_envasat'];
} elseif ($isEdit && isset($product['format_envasat'])) {
    $formatVal = (string) $product['format_envasat'];
}

$pesVal = '';
if (isset($old['pes_net_grams'])) {
    $pesVal = (string) $old['pes_net_grams'];
} elseif ($isEdit && isset($product['pes_net_grams'])) {
    $pesVal = (string) $product['pes_net_grams'];
}

$preuVal = '';
if (isset($old['preu_pack'])) {
    $preuVal = (string) $old['preu_pack'];
} elseif ($isEdit && isset($product['preu_pack'])) {
    $preuVal = (string) $product['preu_pack'];
}

$estocVal = '0';
if (isset($old['estoc_unitats'])) {
    $estocVal = (string) $old['estoc_unitats'];
} elseif ($isEdit && isset($product['estoc_unitats'])) {
    $estocVal = (string) $product['estoc_unitats'];
}

$lotVal = '';
if (isset($old['codi_lot_tracabilitat'])) {
    $lotVal = (string) $old['codi_lot_tracabilitat'];
} elseif ($isEdit && isset($product['codi_lot_tracabilitat'])) {
    $lotVal = (string) $product['codi_lot_tracabilitat'];
}

$ecoChecked = false;
if (isset($old['certificat_ecologic'])) {
    $ecoChecked = !empty($old['certificat_ecologic']);
} elseif ($isEdit && isset($product['certificat_ecologic'])) {
    $ecoChecked = !empty($product['certificat_ecologic']);
}

$pasturaChecked = false;
if (isset($old['pastura_lliure'])) {
    $pasturaChecked = !empty($old['pastura_lliure']);
} elseif ($isEdit && isset($product['pastura_lliure'])) {
    $pasturaChecked = !empty($product['pastura_lliure']);
}

$selectedRaca = null;
if (isset($old['id_raca'])) {
    $selectedRaca = (int) $old['id_raca'];
} elseif ($isEdit && isset($product['id_raca'])) {
    $selectedRaca = (int) $product['id_raca'];
}

$selectedCategoria = null;
if (isset($old['id_categoria_animal'])) {
    $selectedCategoria = (int) $old['id_categoria_animal'];
} elseif ($isEdit && isset($product['id_categoria_animal'])) {
    $selectedCategoria = (int) $product['id_categoria_animal'];
} elseif ($selectedRaca !== null) {
    foreach ($races as $r) {
        if ((int) $r['id'] === $selectedRaca) {
            $selectedCategoria = (int) $r['id_categoria_animal'];
            break;
        }
    }
}

$currentImg = '';
if ($isEdit && isset($product['imatge_url']) && !empty($product['imatge_url'])) {
    $currentImg = (string) $product['imatge_url'];
}
?>

<div class="section-header">
    <div>
        <h1 class="section-title"><?php if ($isEdit) { echo 'Editar producte'; } else { echo 'Crear nou producte'; } ?></h1>
        <p class="section-subtitle">Omple les dades comercials i de traçabilitat del lot.</p>
    </div>
    <a href="/ramader/productes" class="btn btn-secondary btn-sm">← Tornar a la llista</a>
</div>

<div class="card" style="max-width: 820px;">
    <form id="product_form" method="POST" action="<?php if ($isEdit) { echo '/ramader/productes/editar'; } else { echo '/ramader/productes/crear'; } ?>" enctype="multipart/form-data">
        <input type="hidden" id="csrf_token" name="csrf_token" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
        <?php if ($isEdit) { ?>
            <input type="hidden" name="id" value="<?php echo htmlspecialchars((string) $product['id'], ENT_QUOTES, 'UTF-8'); ?>">
        <?php } ?>

        <div class="form-group">
            <label for="prod_nom" class="form-label">Nom del producte *</label>
            <input id="prod_nom" type="text" name="nom" class="form-control" required maxlength="150" value="<?php echo htmlspecialchars($nomVal, ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="select_categoria" class="form-label">Espècie animal *</label>
                <div class="form-inline-action">
                    <select id="select_categoria" name="id_categoria_animal" class="form-select" required>
                        <option value="">-- Selecciona una espècie --</option>
                        <?php foreach ($categories as $cat) {
                            $catId = (int) $cat['id'];
                            $isCatSelected = false;
                            if ($selectedCategoria !== null && $selectedCategoria === $catId) {
                                $isCatSelected = true;
                            }
                        ?>
                            <option value="<?php echo $catId; ?>" <?php if ($isCatSelected) { echo 'selected'; } ?>>
                                <?php echo htmlspecialchars((string) $cat['nom'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php } ?>
                    </select>
                    <button type="button" id="btn_nova_categoria" class="btn btn-secondary btn-sm" style="white-space: nowrap;">+ Nova espècie</button>
                </div>
            </div>

            <div class="form-group">
                <label for="select_raca" class="form-label">Raça animal *</label>
                <div class="form-inline-action">
                    <select id="select_raca" name="id_raca" class="form-select" required>
                        <option value="">-- Selecciona una raça --</option>
                        <?php foreach ($races as $raca) {
                            $racaId = (int) $raca['id'];
                            $racaCatId = (int) $raca['id_categoria_animal'];
                            $isSelected = false;
                            if ($selectedRaca !== null && $selectedRaca === $racaId) {
                                $isSelected = true;
                            }
                        ?>
                            <option value="<?php echo $racaId; ?>" data-categoria="<?php echo $racaCatId; ?>" <?php if ($isSelected) { echo 'selected'; } ?>>
                                <?php echo htmlspecialchars((string) $raca['nom'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php } ?>
                    </select>
                    <button type="button" id="btn_nova_raca" class="btn btn-secondary btn-sm" style="white-space: nowrap;">+ Nova raça</button>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="prod_desc" class="form-label">Descripció detallada</label>
            <textarea id="prod_desc" name="descripcio" class="form-textarea" rows="4"><?php echo htmlspecialchars($descripcioVal, ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="prod_format" class="form-label">Format d'envasat *</label>
                <input id="prod_format" type="text" name="format_envasat" class="form-control" required maxlength="100" placeholder="Ex. Paquet al buit 500g" value="<?php echo htmlspecialchars($formatVal, ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="form-group">
                <label for="prod_lot" class="form-label">Codi de lot de traçabilitat *</label>
                <input id="prod_lot" type="text" name="codi_lot_tracabilitat" class="form-control" required maxlength="50" value="<?php echo htmlspecialchars($lotVal, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="prod_pes" class="form-label">Pes net (en grams) *</label>
                <input id="prod_pes" type="number" name="pes_net_grams" class="form-control" required min="1" step="1" value="<?php echo htmlspecialchars($pesVal, ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="form-group">
                <label for="prod_preu" class="form-label">Preu del pack (€) *</label>
                <input id="prod_preu" type="number" name="preu_pack" class="form-control" required min="0.01" step="0.01" value="<?php echo htmlspecialchars($preuVal, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="prod_estoc" class="form-label">Unitats en estoc *</label>
                <input id="prod_estoc" type="number" name="estoc_unitats" class="form-control" required min="0" step="1" value="<?php echo htmlspecialchars($estocVal, ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="form-group">
                <label for="input_imatge" class="form-label">Imatge del producte (JPG, PNG, WEBP o GIF)</label>
                <input type="file" id="input_imatge" name="imatge" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif">
                <?php if ($currentImg !== '') { ?>
                    <span class="form-help">Imatge actual - <a href="<?php echo htmlspecialchars($currentImg, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" style="text-decoration: underline;">Veure imatge actual</a></span>
                <?php } ?>
            </div>
        </div>

        <div class="form-group">
            <div class="checkbox-list">
                <label class="checkbox-label">
                    <input type="checkbox" name="certificat_ecologic" value="1" <?php if ($ecoChecked) { echo 'checked'; } ?>>
                    <span>Certificat d'agricultura o ramaderia ecològica</span>
                </label>
                <label class="checkbox-label">
                    <input type="checkbox" name="pastura_lliure" value="1" <?php if ($pasturaChecked) { echo 'checked'; } ?>>
                    <span>Animals criats en pastura lliure</span>
                </label>
            </div>
        </div>

        <div class="actions-row mt-4">
            <button type="submit" class="btn btn-primary"><?php if ($isEdit) { echo 'Actualitzar producte'; } else { echo 'Desar producte'; } ?></button>
            <a href="/ramader/productes" class="btn btn-secondary">Cancel·lar</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var selectCategoria = document.getElementById('select_categoria');
    var selectRaca = document.getElementById('select_raca');
    var btnNovaCategoria = document.getElementById('btn_nova_categoria');
    var btnNovaRaca = document.getElementById('btn_nova_raca');
    var csrfInput = document.getElementById('csrf_token');

    function filtrarRaces() {
        var selectedCat = selectCategoria.value;
        var currentRacaSelected = false;
        var options = selectRaca.querySelectorAll('option');

        for (var i = 0; i < options.length; i++) {
            var opt = options[i];
            if (opt.value === '') {
                opt.hidden = false;
                opt.disabled = false;
                continue;
            }

            var optCat = opt.getAttribute('data-categoria');
            if (selectedCat !== '' && optCat === selectedCat) {
                opt.hidden = false;
                opt.disabled = false;
                if (opt.selected) {
                    currentRacaSelected = true;
                }
            } else {
                opt.hidden = true;
                opt.disabled = true;
                if (opt.selected) {
                    opt.selected = false;
                }
            }
        }

        if (!currentRacaSelected && selectRaca.value !== '') {
            selectRaca.value = '';
        }
    }

    selectCategoria.addEventListener('change', filtrarRaces);
    filtrarRaces();

    btnNovaCategoria.addEventListener('click', function() {
        var nom = window.prompt('Introdueix el nom de la nova espècie animal');
        if (!nom) {
            return;
        }
        nom = nom.trim();
        if (nom === '') {
            return;
        }

        var fd = new FormData();
        fd.append('csrf_token', csrfInput.value);
        fd.append('nom', nom);

        var reqOpts = new Object();
        reqOpts['method'] = 'POST';
        reqOpts['body'] = fd;

        fetch('/ramader/categories/crear', reqOpts)
            .then(function(res) {
                return res.json();
            })
            .then(function(data) {
                if (data && data.ok) {
                    var opt = document.createElement('option');
                    opt.value = data.id;
                    opt.textContent = data.nom;
                    opt.selected = true;
                    selectCategoria.appendChild(opt);
                    filtrarRaces();
                } else {
                    var errMsg = 'Error en crear l\'espècie.';
                    if (data && data.error) {
                        errMsg = data.error;
                    }
                    alert(errMsg);
                }
            })
            .catch(function() {
                alert('Error de connexió en desar la nova espècie.');
            });
    });

    btnNovaRaca.addEventListener('click', function() {
        var catId = selectCategoria.value;
        if (!catId) {
            alert('Primer has de triar una espècie animal.');
            selectCategoria.focus();
            return;
        }

        var nom = window.prompt('Introdueix el nom de la nova raça');
        if (!nom) {
            return;
        }
        nom = nom.trim();
        if (nom === '') {
            return;
        }

        var fd = new FormData();
        fd.append('csrf_token', csrfInput.value);
        fd.append('id_categoria_animal', catId);
        fd.append('nom', nom);

        var reqOpts = new Object();
        reqOpts['method'] = 'POST';
        reqOpts['body'] = fd;

        fetch('/ramader/races/crear', reqOpts)
            .then(function(res) {
                return res.json();
            })
            .then(function(data) {
                if (data && data.ok) {
                    var opt = document.createElement('option');
                    opt.value = data.id;
                    opt.textContent = data.nom;
                    opt.setAttribute('data-categoria', data.id_categoria_animal);
                    opt.selected = true;
                    selectRaca.appendChild(opt);
                    filtrarRaces();
                    selectRaca.value = data.id;
                } else {
                    var errMsg = 'Error en crear la raça.';
                    if (data && data.error) {
                        errMsg = data.error;
                    }
                    alert(errMsg);
                }
            })
            .catch(function() {
                alert('Error de connexió en desar la nova raça.');
            });
    });
});
</script>

<?php require __DIR__ . '/../layouts/sidebar_end.php'; ?>
