<?php
/**
 * footer.php
 * Cierre de estructura HTML común y scripts.
 */
?>
</div> <!-- Fin de .app-layout -->
<script src="assets/js/main.js?v=<?php echo file_exists(__DIR__ . '/../public/assets/js/main.js') ? filemtime(__DIR__ . '/../public/assets/js/main.js') : time(); ?>"></script>
</body>
</html>
