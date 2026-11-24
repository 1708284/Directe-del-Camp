<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="section-header">
    <div>
        <h1 class="section-title">Catàleg de productes</h1>
        <p class="section-subtitle">Productes ramaders de proximitat amb traçabilitat verificada.</p>
    </div>
</div>

<div class="catalog-layout">
    <aside class="filter-sidebar">
        <form method="GET" action="/productes" id="catalog_filters_form">
            <?php if ($filters['q'] !== '') { ?>
                <input type="hidden" name="q" value="<?php echo htmlspecialchars((string) $filters['q'], ENT_QUOTES, 'UTF-8'); ?>">
            <?php } ?>
            <?php if ($filters['id_ramader'] !== '') { ?>
                <input type="hidden" name="id_ramader" value="<?php echo htmlspecialchars((string) $filters['id_ramader'], ENT_QUOTES, 'UTF-8'); ?>">
            <?php } ?>
            <input type="hidden" name="ordre" value="<?php echo htmlspecialchars((string) $filters['ordre'], ENT_QUOTES, 'UTF-8'); ?>">

            <div class="filter-group">
                <div class="filter-title">Espècie / Categoria</div>
                <select name="categoria" class="form-select">
                    <option value="">Totes les espècies</option>
                    <?php foreach ($categories as $cat) {
                        $catId = (int) $cat['id'];
                        $isCatSel = false;
                        if ($activeCategoryId > 0 && $activeCategoryId === $catId) {
                            $isCatSel = true;
                        }
                    ?>
                        <option value="<?php echo $catId; ?>" <?php if ($isCatSel) { echo 'selected'; } ?>>
                            <?php echo htmlspecialchars((string) $cat['nom'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="filter-group">
                <div class="filter-title">Preu màxim (€/kg)</div>
                <input type="number" name="preu_max" class="form-control" min="1" step="0.50" placeholder="Ex. 25.00" value="<?php echo htmlspecialchars((string) $filters['preu_max'], ENT_QUOTES, 'UTF-8'); ?>">
                <span class="form-help">Filtra pel preu calculat per quilogram.</span>
            </div>

            <?php if (!empty($races)) { ?>
                <div class="filter-group">
                    <div class="filter-title">Races</div>
                    <div class="checkbox-list">
                        <?php foreach ($races as $raca) {
                            $rId = (int) $raca['id'];
                            $isChecked = in_array($rId, $filters['races'], true);
                        ?>
                            <label class="checkbox-label">
                                <input type="checkbox" name="races[]" value="<?php echo $rId; ?>" <?php if ($isChecked) { echo 'checked'; } ?>>
                                <span><?php echo htmlspecialchars((string) $raca['nom'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </label>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>

            <div class="filter-group">
                <div class="filter-title">Comarca</div>
                <select name="comarca" class="form-select">
                    <option value="">Totes les comarques</option>
                    <?php foreach ($comarques as $com) {
                        $isComSel = false;
                        if (mb_strtolower((string) $filters['comarca']) === mb_strtolower((string) $com)) {
                            $isComSel = true;
                        }
                    ?>
                        <option value="<?php echo htmlspecialchars((string) $com, ENT_QUOTES, 'UTF-8'); ?>" <?php if ($isComSel) { echo 'selected'; } ?>>
                            <?php echo htmlspecialchars((string) $com, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="filter-group">
                <div class="filter-title">Certificacions i cria</div>
                <div class="checkbox-list">
                    <label class="checkbox-label">
                        <input type="checkbox" name="ecologic" value="1" <?php if (!empty($filters['ecologic'])) { echo 'checked'; } ?>>
                        <span>Certificat ecològic</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="pastura" value="1" <?php if (!empty($filters['pastura'])) { echo 'checked'; } ?>>
                        <span>Pastura lliure</span>
                    </label>
                </div>
            </div>

            <div class="actions-row">
                <button type="submit" class="btn btn-primary btn-sm" style="flex: 1;">Aplicar filtres</button>
                <a href="/productes" class="btn btn-secondary btn-sm">Netejar</a>
            </div>
        </form>
    </aside>

    <div>
        <div class="catalog-toolbar">
            <div class="text-muted" style="font-size: 14px;">
                <strong><?php echo count($products); ?></strong> productes trobats
                <?php if ($filters['comarca'] !== '') { ?>
                    · Comarca <strong><?php echo htmlspecialchars((string) $filters['comarca'], ENT_QUOTES, 'UTF-8'); ?></strong>
                <?php } ?>
                <?php if ($filters['q'] !== '') { ?>
                    · Cerca "<strong><?php echo htmlspecialchars((string) $filters['q'], ENT_QUOTES, 'UTF-8'); ?></strong>"
                <?php } ?>
            </div>

            <form method="GET" action="/productes" class="actions-row">
                <?php if ($filters['categoria'] !== '') { ?>
                    <input type="hidden" name="categoria" value="<?php echo htmlspecialchars((string) $filters['categoria'], ENT_QUOTES, 'UTF-8'); ?>">
                <?php } ?>
                <?php if ($filters['comarca'] !== '') { ?>
                    <input type="hidden" name="comarca" value="<?php echo htmlspecialchars((string) $filters['comarca'], ENT_QUOTES, 'UTF-8'); ?>">
                <?php } ?>
                <?php if ($filters['id_ramader'] !== '') { ?>
                    <input type="hidden" name="id_ramader" value="<?php echo htmlspecialchars((string) $filters['id_ramader'], ENT_QUOTES, 'UTF-8'); ?>">
                <?php } ?>
                <?php if ($filters['preu_max'] !== '') { ?>
                    <input type="hidden" name="preu_max" value="<?php echo htmlspecialchars((string) $filters['preu_max'], ENT_QUOTES, 'UTF-8'); ?>">
                <?php } ?>
                <?php if (!empty($filters['ecologic'])) { ?>
                    <input type="hidden" name="ecologic" value="1">
                <?php } ?>
                <?php if (!empty($filters['pastura'])) { ?>
                    <input type="hidden" name="pastura" value="1">
                <?php } ?>
                <?php if ($filters['q'] !== '') { ?>
                    <input type="hidden" name="q" value="<?php echo htmlspecialchars((string) $filters['q'], ENT_QUOTES, 'UTF-8'); ?>">
                <?php } ?>
                <?php foreach ($filters['races'] as $rHidden) { ?>
                    <input type="hidden" name="races[]" value="<?php echo (int) $rHidden; ?>">
                <?php } ?>

                <label for="sort_select" class="text-muted" style="font-size: 13px;">Ordenar</label>
                <select id="sort_select" name="ordre" class="form-select" style="width: auto; padding: 7px 10px;">
                    <option value="recents" <?php if ($filters['ordre'] === 'recents') { echo 'selected'; } ?>>Més recents</option>
                    <option value="preu_asc" <?php if ($filters['ordre'] === 'preu_asc') { echo 'selected'; } ?>>Preu de menor a major</option>
                    <option value="preu_desc" <?php if ($filters['ordre'] === 'preu_desc') { echo 'selected'; } ?>>Preu de major a menor</option>
                </select>
                <button type="submit" class="btn btn-secondary btn-sm">Ordenar</button>
            </form>
        </div>

        <?php if (empty($products)) { ?>
            <div class="card">
                <h2 style="font-size: 18px;">No s'han trobat productes amb aquests filtres</h2>
                <p class="text-muted mt-2">Prova d'ampliar el rang de preu o treure algun filtre per veure més opcions.</p>
                <div class="mt-4">
                    <a href="/productes" class="btn btn-primary btn-sm">Veure tots els productes</a>
                </div>
            </div>
        <?php } else { ?>
            <div class="products-grid">
                <?php foreach ($products as $prod) {
                    $pesGrams = (int) $prod['pes_net_grams'];
                    $preuPack = (float) $prod['preu_pack'];
                    $preuKg = 0;
                    if ($pesGrams > 0) {
                        $preuKg = ($preuPack * 1000) / $pesGrams;
                    }
                    $estoc = (int) $prod['estoc_unitats'];
                    $nomRaca = 'Raça autòctona';
                    if (isset($prod['nom_raca']) && $prod['nom_raca'] !== '') {
                        $nomRaca = (string) $prod['nom_raca'];
                    }
                    $nomGranja = '';
                    if (isset($prod['nom_explotacio'])) {
                        $nomGranja = (string) $prod['nom_explotacio'];
                    }
                    $comarcaGranja = '';
                    if (isset($prod['comarca'])) {
                        $comarcaGranja = (string) $prod['comarca'];
                    }
                ?>
                    <article class="product-card">
                        <a href="/producte?id=<?php echo urlencode((string) $prod['id']); ?>" class="product-thumb-wrap">
                            <?php if (!empty($prod['imatge_url'])) { ?>
                                <img src="<?php echo htmlspecialchars((string) $prod['imatge_url'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string) $prod['nom'], ENT_QUOTES, 'UTF-8'); ?>">
                            <?php } else { ?>
                                <div class="product-thumb-placeholder">
                                    <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                </div>
                            <?php } ?>
                            <div class="product-badges-overlay">
                                <?php if (!empty($prod['certificat_ecologic'])) { ?>
                                    <span class="badge badge-eco">Ecològic</span>
                                <?php } ?>
                                <?php if (!empty($prod['pastura_lliure'])) { ?>
                                    <span class="badge">Pastura lliure</span>
                                <?php } ?>
                            </div>
                        </a>

                        <div class="product-body">
                            <div class="product-farm"><?php echo htmlspecialchars($nomGranja, ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars($comarcaGranja, ENT_QUOTES, 'UTF-8'); ?></div>
                            <a href="/producte?id=<?php echo urlencode((string) $prod['id']); ?>" class="product-name">
                                <?php echo htmlspecialchars((string) $prod['nom'], ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                            <div class="product-meta-row">
                                <span class="badge"><?php echo htmlspecialchars($nomRaca, ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="text-muted" style="font-size: 13px;"><?php echo $pesGrams; ?> g</span>
                            </div>

                            <div class="product-price-box">
                                <div>
                                    <span class="price-pack"><?php echo number_format($preuPack, 2, ',', '.'); ?> €</span>
                                    <span class="text-muted" style="font-size: 12px;"> / pack</span>
                                </div>
                                <span class="price-kg"><?php echo number_format($preuKg, 2, ',', '.'); ?> €/kg</span>
                            </div>

                            <div class="product-card-footer">
                                <?php if ($estoc > 0) { ?>
                                    <form method="POST" action="/cistella/afegir" style="flex: 1;">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="id_producte" value="<?php echo htmlspecialchars((string) $prod['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="quantitat" value="1">
                                        <button type="submit" class="btn btn-primary btn-sm btn-block">Afegir al carretó</button>
                                    </form>
                                <?php } else { ?>
                                    <span class="badge badge-danger" style="width: 100%; justify-content: center; padding: 7px;">Sense estoc</span>
                                <?php } ?>
                                <a href="/producte?id=<?php echo urlencode((string) $prod['id']); ?>" class="btn btn-secondary btn-sm">Detall</a>
                            </div>
                        </div>
                    </article>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
