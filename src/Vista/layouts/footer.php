    </div>
</main>
<footer class="site-footer">
    <div class="container footer-inner">
        <div>© 2026 Directe del Camp · Fet aquí, amb confiança.</div>
        <div class="footer-links">
            <a href="/productes">Catàleg</a>
            <a href="/granges">Granges verificades</a>
            <a href="/register-ramader">Vull vendre-hi</a>
            <a href="/cistella">Cistella</a>
            <?php if (!empty($currentUser)) { ?>
                <a href="/perfil">El meu perfil</a>
                <a href="/perfil/comandes">Les meves comandes</a>
            <?php } else { ?>
                <a href="/login">Accedir</a>
            <?php } ?>
        </div>
    </div>
</footer>
</body>
</html>
