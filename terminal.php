<?php 
session_start();  
// Verifica las variables de sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit();
}
require_once 'config.php'; // Conexión a la base de datos
class PlataformaAprendizajeLinux {
    private $comandos = [
        'cd' => [
            'descripcion' => 'Cambiar directorio',
            'sintaxis' => 'cd [ruta]',
            'ejemplos' => [
                'cd /' => 'Ir a directorio raíz',
                'cd ~' => 'Ir a directorio home',
                'cd ..' => 'Subir un nivel'
            ]
        ],
        'ls' => [
            'descripcion' => 'Listar archivos',
            'sintaxis' => 'ls [ruta]',
            'ejemplos' => [
                'ls' => 'Listar contenido actual',
                'ls /var' => 'Listar contenido de /var'
            ]
        ],
        'help' => [
            'descripcion' => 'Mostrar ayuda',
            'sintaxis' => 'help [comando]',
            'ejemplos' => [
                'help' => 'Mostrar todos los comandos',
                'help cd' => 'Mostrar ayuda para cd'
            ]
        ],
        'clear' => [
            'descripcion' => 'Limpiar pantalla',
            'sintaxis' => 'clear',
            'ejemplos' => []
        ]
    ];
    
    private $sistemaVirtual = [];
    private $rutaActual = '';
    
    public function __construct() {
        $userDir = '/home/' . $_SESSION['username'];
        $this->rutaActual = $userDir;
        
        if (!isset($_SESSION['sistemaVirtual'])) {
            $this->inicializarSistemaVirtual();
        } else {
            $this->sistemaVirtual = $_SESSION['sistemaVirtual'];
            $this->rutaActual = $_SESSION['rutaActual'];
        }
    }
    
    private function inicializarSistemaVirtual() {
        $this->sistemaVirtual = [
            'home' => [
                $_SESSION['username'] => [
                    'documentos' => [
                        'notas.txt' => 'Bienvenido ' . $_SESSION['username'] . "\n" . date('Y-m-d H:i:s')
                    ],
                    'descargas' => [],
                    'proyectos' => [
                        'README.md' => '# Mis proyectos'
                    ]
                ]
            ],
            'etc' => [
                'hosts' => '127.0.0.1 localhost',
                'passwd' => 'root:x:0:0:root:/root:/bin/bash'
            ],
            'var' => [
                'log' => [
                    'syslog' => 'Log del sistema...'
                ]
            ],
            'tmp' => []
        ];
        $this->guardarEstado();
    }
    
    private function guardarEstado() {
        $_SESSION['sistemaVirtual'] = $this->sistemaVirtual;
        $_SESSION['rutaActual'] = $this->rutaActual;
    }
    
    public function ejecutarComando($comando) {
        $comando = trim($comando);
        if (empty($comando)) return '';
        
        $partes = explode(' ', $comando, 2); // Máximo 2 partes: comando y argumentos
        $cmd = strtolower($partes[0]);
        $args = isset($partes[1]) ? [$partes[1]] : [];
        
        switch ($cmd) {
            case 'ls':
                return $this->simularLS($args);
            case 'cd':
                return $this->simularCD($args);
            case 'help':
                return $this->mostrarAyuda(isset($args[0]) ? $args[0] : '');
            case 'clear':
                return "\033[H\033[J"; // Códigos ANSI para limpiar pantalla
            case 'whoami':
                return $_SESSION['username'];
            case 'pwd':
                return $this->rutaActual;
            case 'date':
                return date('Y-m-d H:i:s');
            default:
                return "$cmd: comando no encontrado. Escribe 'help' para ayuda";
        }
    }
    
    private function simularLS($args) {
        $ruta = empty($args) ? '.' : $args[0];
        $ruta = $this->resolverRuta($ruta);
        $nodo = $this->obtenerNodo($ruta);
        
        if ($nodo === null) {
            return "ls: no se puede acceder a '{$args[0]}': No existe el archivo o directorio";
        }
        
        if (is_array($nodo)) {
            return implode("  ", array_keys($nodo));
        } else {
            return basename($ruta);
        }
    }
    
    private function simularCD($args) {
        if (empty($args)) {
            $this->rutaActual = '/home/' . $_SESSION['username'];
            $this->guardarEstado();
            return '';
        }
        
        $nuevaRuta = $this->resolverRuta($args[0]);
        $nodo = $this->obtenerNodo($nuevaRuta);
        
        if ($nodo !== null && is_array($nodo)) {
            $this->rutaActual = $nuevaRuta;
            $this->guardarEstado();
            return '';
        }
        
        return "cd: {$args[0]}: No existe el archivo o directorio";
    }
    
    private function mostrarAyuda($comando) {
        if (empty($comando)) {
            $output = "Comandos disponibles:\n";
            foreach ($this->comandos as $cmd => $info) {
                $output .= sprintf("%-8s - %s\n", $cmd, $info['descripcion']);
            }
            return $output;
        }
        
        if (isset($this->comandos[$comando])) {
            $info = $this->comandos[$comando];
            $output = "{$comando}: {$info['descripcion']}\n";
            $output .= "Sintaxis: {$info['sintaxis']}\n";
            
            if (!empty($info['ejemplos'])) {
                $output .= "Ejemplos:\n";
                foreach ($info['ejemplos'] as $ejemplo => $desc) {
                    $output .= "  {$ejemplo} - {$desc}\n";
                }
            }
            
            return $output;
        }
        
        return "help: no hay ayuda disponible para '{$comando}'";
    }
    
    private function resolverRuta($ruta) {
        if ($ruta === '~' || $ruta === '~/') return '/home/' . $_SESSION['username'];
        if ($ruta === '/') return '/';
        if ($ruta === '.') return $this->rutaActual;
        if ($ruta === '..') {
            if ($this->rutaActual === '/') return '/';
            return dirname($this->rutaActual);
        }
        
        // Si es una ruta absoluta
        if (substr($ruta, 0, 1) === '/') return $ruta;
        
        // Si es una ruta relativa
        return rtrim($this->rutaActual, '/') . '/' . $ruta;
    }
    
    private function obtenerNodo($ruta) {
        if ($ruta === '/') return $this->sistemaVirtual;
        
        $partes = array_filter(explode('/', $ruta), 'strlen');
        
        $nodo = &$this->sistemaVirtual;
        
        foreach ($partes as $parte) {
            if (!isset($nodo[$parte])) return null;
            $nodo = &$nodo[$parte];
        }
        
        return $nodo;
    }
}

// Manejo de solicitud AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comando'])) {
    $terminal = new PlataformaAprendizajeLinux();
    $resultado = $terminal->ejecutarComando($_POST['comando']);
    
    // Registrar comando en la base de datos (si existe la tabla terminal_logs)
    try {
        // Verificar si la tabla existe
        $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'terminal_logs'");
        
        if (mysqli_num_rows($check_table) > 0) {
            $stmt = mysqli_prepare($conn, "INSERT INTO terminal_logs (user_id, comando, resultado) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "iss", $_SESSION["id"], $_POST['comando'], $resultado);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    } catch (Exception $e) {
        error_log("Error al registrar comando: " . $e->getMessage());
    }
    
    echo $resultado;
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminal de <?= htmlspecialchars($_SESSION['username']) ?></title>
    <style>
        body {
            background: #1e1e1e;
            color: #00ff00;
            font-family: 'Courier New', monospace;
            margin: 0;
            padding: 20px;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        #terminal-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        #terminal {
            flex: 1;
            overflow-y: auto;
            white-space: pre-wrap;
            line-height: 1.5;
            padding: 10px;
            margin-bottom: 10px;
            background: #121212;
            border-radius: 5px;
        }
        #input-line {
            display: flex;
            align-items: center;
            padding: 10px;
            background: #252525;
            border-radius: 5px;
        }
        #prompt {
            color: #4CAF50;
            margin-right: 10px;
            white-space: nowrap;
        }
        #command-input {
            background: transparent;
            border: none;
            color: #00ff00;
            font-family: 'Courier New', monospace;
            flex-grow: 1;
            outline: none;
            font-size: 16px;
        }
        .command {
            color: #4CAF50;
        }
        .output {
            margin-bottom: 10px;
        }
        #navbar {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            background: #252525;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        #navbar a {
            color: #4CAF50;
            text-decoration: none;
            padding: 5px 10px;
        }
        #navbar a:hover {
            background: #333;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div id="navbar">
        <div>
            <span style="color: white;">Usuario: <?= htmlspecialchars($_SESSION['username']) ?></span>
        </div>
        <div>
            <a href="logout.php">Cerrar sesión</a>
        </div>
    </div>
    <div id="terminal-container">
        <div id="terminal"></div>
        <div id="input-line">
            <span id="prompt"><?= htmlspecialchars($_SESSION['username']) ?>@linux:$</span>
            <input type="text" id="command-input" autofocus>
        </div>
    </div>
    <script>
        const terminal = document.getElementById('terminal');
        const commandInput = document.getElementById('command-input');
        const history = [];
        let historyIndex = -1;
        
        // Mostrar mensaje de bienvenida
        terminal.innerHTML = `<div class="output">Bienvenido a la terminal virtual, ${escapeHtml('<?= htmlspecialchars($_SESSION['username']) ?>')}.</div>
                            <div class="output">Escribe 'help' para ver los comandos disponibles.</div>`;
        
        commandInput.focus();
        
        commandInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const command = this.value.trim();
                if (command) {
                    history.push(command);
                    historyIndex = history.length;
                    
                    // Mostrar comando en terminal
                    terminal.innerHTML += `<div class="command">${escapeHtml('<?= htmlspecialchars($_SESSION['username']) ?>')}@linux:$ ${escapeHtml(command)}</div>`;
                    
                    // Enviar comando al servidor
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'terminal.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === XMLHttpRequest.DONE) {
                            if (xhr.status === 200) {
                                const result = xhr.responseText;
                                if (result === '\033[H\033[J') {
                                    // Limpiar pantalla
                                    terminal.innerHTML = '';
                                } else {
                                    terminal.innerHTML += `<div class="output">${escapeHtml(result)}</div>`;
                                }
                                terminal.scrollTop = terminal.scrollHeight;
                            } else {
                                terminal.innerHTML += `<div class="output">Error: No se pudo procesar el comando</div>`;
                            }
                        }
                    };
                    xhr.send(`comando=${encodeURIComponent(command)}`);
                    
                    this.value = '';
                }
            }
            // Navegación por historial
            if (e.key === 'ArrowUp' && history.length > 0) {
                e.preventDefault();
                if (historyIndex > 0) historyIndex--;
                this.value = history[historyIndex] || '';
            }
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (historyIndex < history.length) historyIndex++;
                this.value = historyIndex < history.length ? history[historyIndex] : '';
            }
        });
        
        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
        
        // Mantener el foco en el input
        document.addEventListener('click', function() {
            commandInput.focus();
        });
    </script>
</body>
</html>