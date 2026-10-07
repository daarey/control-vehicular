<?php
/**
 * footer.php
 * Cierre de estructura HTML común y scripts.
 */
if (!isset($pathToAssets)) {
    $pathToAssets = 'assets/';
}
?>
</div> <!-- Fin de .app-layout -->
<script src="<?php echo $pathToAssets; ?>js/main.js?v=<?php echo time(); ?>" defer></script>
</body>
</html>

