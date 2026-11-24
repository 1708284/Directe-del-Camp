<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Panell d'Administració - Validació de Ramaders</title>
</head>
<body>
    <h1>Panell d'Administració - Validació d'Explotacions Ramaderes</h1>
    <p>
        <a href="/perfil">El meu perfil</a> |
        <a href="/">Tornar a l'inici</a>
    </p>

    <?php if (!empty($flash_success)) { ?>
        <p><strong><?php echo htmlspecialchars((string) $flash_success, ENT_QUOTES, 'UTF-8'); ?></strong></p>
    <?php } ?>

    <?php if (!empty($flash_error)) { ?>
        <p><strong><?php echo htmlspecialchars((string) $flash_error, ENT_QUOTES, 'UTF-8'); ?></strong></p>
    <?php } ?>

    <h2>Sol·licituds d'alta pendents</h2>

    <?php if (empty($pending)) { ?>
        <p>No hi ha cap sol·licitud pendent de validació.</p>
    <?php } else { ?>
        <table border="1" cellpadding="6" cellspacing="0">
            <thead>
                <tr>
                    <th>Explotació</th>
                    <th>Titular</th>
                    <th>Contacte</th>
                    <th>CIF / NIF</th>
                    <th>Comarca</th>
                    <th>Codi REGA</th>
                    <th>Document Sanitari</th>
                    <th>Acció</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($pending as $r) {
                    $nomExplotacio = '';
                    if (isset($r['nom_explotacio'])) {
                        $nomExplotacio = (string) $r['nom_explotacio'];
                    }

                    $nomTitular = '';
                    if (isset($r['nom'])) {
                        $nomTitular = (string) $r['nom'];
                    }
                    if (isset($r['cognoms'])) {
                        $nomTitular = trim($nomTitular . ' ' . (string) $r['cognoms']);
                    }

                    $email = '';
                    if (isset($r['email'])) {
                        $email = (string) $r['email'];
                    }

                    $telefon = 'No indicat';
                    if (isset($r['telefon']) && $r['telefon'] !== '') {
                        $telefon = (string) $r['telefon'];
                    }

                    $cifNif = '';
                    if (isset($r['cif_nif'])) {
                        $cifNif = (string) $r['cif_nif'];
                    }

                    $comarca = '';
                    if (isset($r['comarca'])) {
                        $comarca = (string) $r['comarca'];
                    }

                    $codiRega = '';
                    if (isset($r['codi_rega'])) {
                        $codiRega = (string) $r['codi_rega'];
                    }
                ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($nomExplotacio, ENT_QUOTES, 'UTF-8'); ?></strong></td>
                        <td><?php echo htmlspecialchars($nomTitular, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?><br>
                            <?php echo htmlspecialchars($telefon, ENT_QUOTES, 'UTF-8'); ?>
                        </td>
                        <td><?php echo htmlspecialchars($cifNif, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($comarca, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($codiRega, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <?php if (!empty($r['document_sanitari_url'])) { ?>
                                <a href="<?php echo htmlspecialchars((string) $r['document_sanitari_url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">Veure document PDF</a>
                            <?php } else { ?>
                                <em>Sense document</em>
                            <?php } ?>
                        </td>
                        <td>
                            <form method="POST" action="/admin/ramaders/validar" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="id_ramader" value="<?php echo htmlspecialchars((string) $r['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="accio" value="aprovar">
                                <button type="submit" onclick="return confirm('Segur que vols aprovar aquesta explotació?');">Aprovar</button>
                            </form>

                            <form method="POST" action="/admin/ramaders/validar" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="id_ramader" value="<?php echo htmlspecialchars((string) $r['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="accio" value="rebutjar">
                                <button type="submit" onclick="return confirm('Segur que vols rebutjar aquesta sol·licitud?');">Rebutjar</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } ?>
</body>
</html>
