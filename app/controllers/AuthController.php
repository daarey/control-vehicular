<?php

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class AuthController
{
    private string $error = '';

    public function __construct()
    {
        AuthMiddleware::iniciarSesion();
    }

    public function manejarLogin(): void
    {
        // Si el usuario ya cuenta con sesión activa, redirigir según su rol
        if (AuthMiddleware::estaAutenticado()) {
            $this->redirigirPorRol();
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarLogin();
        }

        $this->mostrarVista();
    }

    private function procesarLogin(): void
    {
        $correo   = trim($_POST['correo'] ?? '');
        $password = trim($_POST['password'] ?? $_POST['contrasena'] ?? '');

        if (empty($correo) || empty($password)) {
            $this->error = 'Por favor llene todos los campos requeridos.';
            return;
        }

        $db      = Database::getConnection();
        $modelo  = new UsuarioModel($db);
        $usuario = $modelo->buscarPorCorreo($correo);

        if ($usuario && password_verify($password, $usuario['password'])) {
            session_regenerate_id(true);

            $_SESSION['id_usuario']     = (int)$usuario['id_usuario'];
            $_SESSION['nombre_completo']= $usuario['nombre_completo'];
            $_SESSION['id_rol']         = (int)$usuario['id_rol'];
            $_SESSION['id_area']        = (int)($usuario['id_area'] ?? 0);
            $_SESSION['correo']         = $usuario['correo'];

            $this->redirigirPorRol();
            exit();
        }

        $this->error = 'Credenciales incorrectas. Verifique su correo y contraseña.';
    }

    public function cerrarSesion(): void
    {
        AuthMiddleware::iniciarSesion();

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        header('Location: index.php?mensaje=sesion_cerrada');
        exit();
    }

    /**
     * Redirección inteligente post-autenticación según el rol del usuario.
     * Encargado Vehicular va directo a Caseta; el resto al dashboard general.
     */
    private function redirigirPorRol(): void
    {
        $idRol = (int)($_SESSION['id_rol'] ?? 0);

        // ROL 2 = Encargado Vehicular → directo a Control de Caseta
        if ($idRol === 2) {
            header('Location: index.php?action=caseta');
        } else {
            header('Location: index.php?action=dashboard');
        }
    }

    private function mostrarVista(): void
    {
        $error = $this->error;
        require_once __DIR__ . '/../views/auth/login.php';
    }
}
