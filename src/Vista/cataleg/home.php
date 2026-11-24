<?php require __DIR__ . '/../layouts/header.php'; ?>

<section class="hero-section">
    <div>
        <span class="hero-eyebrow">Proximitat real · Traçabilitat REGA verificada</span>
        <h1 class="hero-title">Carn de qualitat directe del ramader a casa teva</h1>
        <p class="hero-desc">Compra carn fresca envasada al buit directament a explotacions ramaderes verificades de Catalunya. Sense intermediaris, amb cadena de fred garantida de 0 a 4 °C.</p>
        <div class="hero-actions">
            <a href="/productes" class="btn btn-primary">Explorar el catàleg</a>
            <a href="/granges" class="btn btn-secondary">Descobrir les granges</a>
        </div>
    </div>
    <div class="hero-metrics">
        <div class="hero-metric-card">
            <div class="hero-metric-value"><?php echo count($farms); ?>+</div>
            <div class="hero-metric-label">Granges verificades amb codi REGA</div>
        </div>
        <div class="hero-metric-card">
            <div class="hero-metric-value">0-4 °C</div>
            <div class="hero-metric-label">Transport refrigerat directe</div>
        </div>
        <div class="hero-metric-card">
            <div class="hero-metric-value">100%</div>
            <div class="hero-metric-label">Traçabilitat de lot i origen</div>
        </div>
        <div class="hero-metric-card">
            <div class="hero-metric-value">24-48h</div>
            <div class="hero-metric-label">Del camp directament a la taula</div>
        </div>
    </div>
</section>

<section class="section-block">
    <div class="section-header">
        <div>
            <h2 class="section-title">Compra per categoria</h2>
            <p class="section-subtitle">Tria el tipus de carn que busques i descobreix les races autòctones.</p>
        </div>
        <a href="/productes" class="btn btn-secondary btn-sm">Veure-ho tot</a>
    </div>
    <div class="category-pills">
        <a href="/productes" class="category-pill active">Tots els productes</a>
        <?php if (!empty($categories)) { ?>
            <?php foreach ($categories as $cat) { ?>
                <a href="/productes?categoria=<?php echo urlencode((string) $cat['id']); ?>" class="category-pill">
                    <?php echo htmlspecialchars((string) $cat['nom'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
            <?php } ?>
        <?php } else { ?>
            <a href="/productes?categoria=vedella" class="category-pill">Vedella</a>
            <a href="/productes?categoria=porc" class="category-pill">Porc</a>
            <a href="/productes?categoria=xai" class="category-pill">Xai</a>
            <a href="/productes?categoria=aviram" class="category-pill">Aviram</a>
        <?php } ?>
    </div>
</section>

<section class="section-block">
    <div class="comarques-box">
        <div>
            <span class="badge badge-eco mb-4">Mapa de proximitat</span>
            <h2 class="section-title">Granges arreu de les nostres comarques</h2>
            <p class="section-subtitle mt-2">Selecciona una comarca per filtrar els productes criats al teu territori i donar suport a la ramaderia local.</p>
            <div class="comarques-grid mt-4">
                <?php
                $comarquesMostrar = $comarques;
                if (empty($comarquesMostrar)) {
                    $comarquesMostrar = [];
                    $comarquesMostrar[] = 'Osona';
                    $comarquesMostrar[] = 'Ripollès';
                    $comarquesMostrar[] = 'Cerdanya';
                    $comarquesMostrar[] = 'Berguedà';
                    $comarquesMostrar[] = 'Pallars Sobirà';
                    $comarquesMostrar[] = 'Garrotxa';
                    $comarquesMostrar[] = 'Empordà';
                    $comarquesMostrar[] = 'Segrià';
                }
                foreach ($comarquesMostrar as $comNom) {
                ?>
                    <a href="/productes?comarca=<?php echo urlencode((string) $comNom); ?>" class="comarca-chip">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        <?php echo htmlspecialchars((string) $comNom, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                <?php } ?>
            </div>
        </div>
        <div class="card" style="background-color: var(--bg-subtle); text-align: center;">
            <svg viewBox="0 0 360 220" width="100%" height="190" role="img" aria-label="Mapa territorial de proximitat">
                <rect x="0" y="0" width="360" height="220" rx="8" fill="#F0F4F1"></rect>
                <path d="M70 50 L190 38 L295 65 L275 115 L220 148 L145 182 L85 162 L62 110 Z" fill="#FFFFFF" stroke="#111827" stroke-width="2"></path>
                <a href="/productes?comarca=Cerdanya">
                    <circle cx="145" cy="64" r="7" fill="#111827"></circle>
                    <text x="145" y="52" font-size="10" text-anchor="middle" fill="#111827" font-family="Inter, sans-serif">Cerdanya</text>
                </a>
                <a href="/productes?comarca=Ripollès">
                    <circle cx="205" cy="70" r="7" fill="#111827"></circle>
                    <text x="205" y="58" font-size="10" text-anchor="middle" fill="#111827" font-family="Inter, sans-serif">Ripollès</text>
                </a>
                <a href="/productes?comarca=Garrotxa">
                    <circle cx="255" cy="84" r="7" fill="#111827"></circle>
                    <text x="255" y="72" font-size="10" text-anchor="middle" fill="#111827" font-family="Inter, sans-serif">Garrotxa</text>
                </a>
                <a href="/productes?comarca=Osona">
                    <circle cx="200" cy="106" r="8" fill="#166534"></circle>
                    <text x="200" y="124" font-size="11" font-weight="700" text-anchor="middle" fill="#111827" font-family="Inter, sans-serif">Osona</text>
                </a>
                <a href="/productes?comarca=Berguedà">
                    <circle cx="140" cy="100" r="7" fill="#111827"></circle>
                    <text x="140" y="117" font-size="10" text-anchor="middle" fill="#111827" font-family="Inter, sans-serif">Berguedà</text>
                </a>
                <a href="/productes?comarca=Segrià">
                    <circle cx="95" cy="142" r="7" fill="#111827"></circle>
                    <text x="95" y="159" font-size="10" text-anchor="middle" fill="#111827" font-family="Inter, sans-serif">Segrià</text>
                </a>
            </svg>
            <p class="form-help">Fes clic sobre qualsevol punt o comarca per veure la carn disponible d'aquella zona.</p>
        </div>
    </div>
</section>

<section class="section-block">
    <div class="section-header">
        <div>
            <h2 class="section-title">Productes destacats</h2>
            <p class="section-subtitle">Selecció fresca preparada directament pels nostres ramaders.</p>
        </div>
        <a href="/productes" class="btn btn-secondary btn-sm">Veure tot el catàleg</a>
    </div>

    <?php if (empty($featuredProducts)) { ?>
        <div class="card">
            <p class="text-muted">Encara no hi ha productes actius disponibles al catàleg públic.</p>
        </div>
    <?php } else { ?>
        <div class="products-grid">
            <?php foreach ($featuredProducts as $prod) {
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
</section>

<section class="section-block">
    <div class="section-header">
        <div>
            <h2 class="section-title">Granges destacades</h2>
            <p class="section-subtitle">Explotacions familiars verificades oficialment abans de vendre.</p>
        </div>
        <a href="/granges" class="btn btn-secondary btn-sm">Totes les granges</a>
    </div>

    <?php if (empty($farms)) { ?>
        <div class="card">
            <p class="text-muted">Actualment estem incorporant noves explotacions verificades.</p>
        </div>
    <?php } else { ?>
        <div class="farm-card-grid">
            <?php
            $farmShown = 0;
            foreach ($farms as $farm) {
                if ($farmShown >= 3) {
                    break;
                }
                $farmShown = $farmShown + 1;
            ?>
                <div class="farm-card">
                    <div class="product-meta-row">
                        <span class="badge badge-eco">Verificada</span>
                        <span class="text-muted" style="font-size: 12px;"><?php echo htmlspecialchars((string) $farm['comarca'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <h3 style="font-size: 18px;"><?php echo htmlspecialchars((string) $farm['nom_explotacio'], ENT_QUOTES, 'UTF-8'); ?></h3>
                    <p class="text-muted" style="font-size: 13px;">Codi REGA <?php echo htmlspecialchars((string) $farm['codi_rega'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p style="font-size: 13px;"><?php echo (int) $farm['total_productes']; ?> productes disponibles</p>
                    <div class="mt-2">
                        <a href="/productes?id_ramader=<?php echo urlencode((string) $farm['id']); ?>" class="btn btn-secondary btn-sm btn-block">Veure productes</a>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</section>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
