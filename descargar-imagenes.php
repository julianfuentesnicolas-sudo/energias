<?php
/**
 * ============================================================
 * DESCARGA DE IMÁGENES REALES (Pexels)
 * ------------------------------------------------------------
 * Todas las fotos proceden de Pexels y tienen licencia libre:
 * uso gratuito, también comercial, sin necesidad de atribución
 * (aunque se agradece). Ver: https://www.pexels.com/license/
 *
 * CÓMO USARLO:
 *   1. Abre en el navegador:
 *      http://localhost/energia-web/descargar-imagenes.php
 *   2. Espera a que termine (unos segundos).
 *   3. Listo: las fotos quedan guardadas en la carpeta images/
 *
 * Si alguna descarga falla (sin internet, firewall...), la imagen
 * de respaldo que ya venía en la carpeta se mantiene intacta y el
 * sitio sigue funcionando.
 * ============================================================
 */

$carpeta = __DIR__ . '/images/';

// idPexels => [nombre de archivo, descripción, página de la foto]
$imagenes = [
    [
        'id'      => 1292464,
        'archivo' => 'hero-energia.jpg',
        'desc'    => 'Aerogeneradores al atardecer (Zahara de los Atunes, España)',
        'pagina'  => 'https://www.pexels.com/photo/photo-of-wind-turbines-lot-1292464/',
        'ancho'   => 1920,
    ],
    [
        'id'      => 4148472,
        'archivo' => 'noticia1.jpg',
        'desc'    => 'Parque solar sobre campo verde (vista aérea)',
        'pagina'  => 'https://www.pexels.com/photo/solar-panels-on-a-green-field-4148472/',
        'ancho'   => 1200,
    ],
    [
        'id'      => 35237908,
        'archivo' => 'noticia2.jpg',
        'desc'    => 'Operario instalando paneles solares en el tejado de una vivienda',
        'pagina'  => 'https://www.pexels.com/photo/worker-installing-solar-panels-on-residential-roof-35237908/',
        'ancho'   => 1200,
    ],
    [
        'id'      => 532192,
        'archivo' => 'noticia3.jpg',
        'desc'    => 'Aerogeneradores en la costa (energía eólica marina)',
        'pagina'  => 'https://www.pexels.com/photo/wind-turbine-landscape-photography-532192/',
        'ancho'   => 1200,
    ],
    [
        'id'      => 8853536,
        'archivo' => 'equipo-solvolt.jpg',
        'desc'    => 'Técnicos instalando paneles solares',
        'pagina'  => 'https://www.pexels.com/photo/solar-technicians-installing-solar-panels-8853536/',
        'ancho'   => 1200,
    ],
    [
        'id'      => 356049,
        'archivo' => 'placeholder-noticia.jpg',
        'desc'    => 'Detalle de panel solar (imagen por defecto)',
        'pagina'  => 'https://www.pexels.com/photo/solar-panel-356049/',
        'ancho'   => 1200,
    ],
];

/**
 * Descarga una URL y devuelve su contenido, o null si falla.
 */
function descargar(string $url): ?string {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (SolVolt descarga de imagenes)',
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $datos = curl_exec($ch);
        $codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($datos !== false && $codigo === 200) return $datos;
        return null;
    }

    // Alternativa si cURL no está activado
    $contexto = stream_context_create(['http' => ['timeout' => 30]]);
    $datos = @file_get_contents($url, false, $contexto);
    return $datos === false ? null : $datos;
}

/**
 * Comprueba que lo descargado es realmente un JPEG (cabecera FF D8 FF).
 */
function esJpegValido(?string $datos): bool {
    return $datos !== null
        && strlen($datos) > 5000
        && substr($datos, 0, 3) === "\xFF\xD8\xFF";
}

$resultados = [];

foreach ($imagenes as $img) {
    $destino = $carpeta . $img['archivo'];

    // Patrones de URL de la CDN de Pexels (se prueban en orden)
    $urls = [
        "https://images.pexels.com/photos/{$img['id']}/pexels-photo-{$img['id']}.jpeg?auto=compress&cs=tinysrgb&w={$img['ancho']}",
        "https://images.pexels.com/photos/{$img['id']}/pexels-photo-{$img['id']}.jpeg?cs=srgb&fm=jpg&w={$img['ancho']}",
        "https://images.pexels.com/photos/{$img['id']}/pexels-photo-{$img['id']}.jpeg?w={$img['ancho']}",
        "https://images.pexels.com/photos/{$img['id']}/pexels-photo-{$img['id']}.jpeg",
    ];

    $ok = false;
    foreach ($urls as $url) {
        $datos = descargar($url);
        if (esJpegValido($datos)) {
            // Solo se sobrescribe el archivo si la descarga es válida
            if (file_put_contents($destino, $datos) !== false) {
                $ok = true;
                $resultados[] = [
                    'archivo' => $img['archivo'],
                    'estado'  => 'ok',
                    'peso'    => round(strlen($datos) / 1024) . ' KB',
                    'desc'    => $img['desc'],
                    'pagina'  => $img['pagina'],
                ];
            }
            break;
        }
    }

    if (!$ok) {
        $resultados[] = [
            'archivo' => $img['archivo'],
            'estado'  => 'fallo',
            'peso'    => '—',
            'desc'    => $img['desc'],
            'pagina'  => $img['pagina'],
        ];
    }
}

$correctas = count(array_filter($resultados, fn($r) => $r['estado'] === 'ok'));
$total = count($resultados);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Descarga de imágenes | SolVolt Energía</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="contenedor">
    <h1 style="color:var(--verde-oscuro);">Descarga de imágenes reales</h1>

    <?php if ($correctas === $total): ?>
        <div class="mensaje ok">
            Se han descargado correctamente las <?= $total ?> imágenes. Ya puedes
            <a href="index.php">ver el sitio web</a>.
        </div>
    <?php elseif ($correctas > 0): ?>
        <div class="mensaje error">
            Se han descargado <?= $correctas ?> de <?= $total ?> imágenes. Las que han fallado
            conservan la imagen de respaldo; puedes descargarlas a mano desde los enlaces de la tabla.
        </div>
    <?php else: ?>
        <div class="mensaje error">
            No se ha podido descargar ninguna imagen. Comprueba tu conexión a internet
            (o descárgalas a mano desde los enlaces de la tabla y guárdalas en la carpeta
            <strong>images/</strong> con el nombre indicado).
        </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr><th>Archivo</th><th>Contenido</th><th>Estado</th><th>Peso</th><th>Origen</th></tr>
        </thead>
        <tbody>
        <?php foreach ($resultados as $r): ?>
            <tr>
                <td><code><?= htmlspecialchars($r['archivo']) ?></code></td>
                <td><?= htmlspecialchars($r['desc']) ?></td>
                <td><?= $r['estado'] === 'ok' ? '✅ Descargada' : '❌ Ha fallado' ?></td>
                <td><?= htmlspecialchars($r['peso']) ?></td>
                <td><a href="<?= htmlspecialchars($r['pagina']) ?>" target="_blank" rel="noopener">Ver en Pexels</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <p style="margin-top:2rem;color:var(--gris-medio);font-size:0.9rem;">
        Todas las fotografías proceden de <strong>Pexels</strong> y se publican bajo su licencia gratuita,
        que permite el uso libre (incluido el comercial) sin necesidad de atribución.
        Consulta los términos en <a href="https://www.pexels.com/license/" target="_blank" rel="noopener">pexels.com/license</a>.
    </p>

    <p><a href="index.php" class="btn">Ir al sitio web</a></p>
</div>
</body>
</html>
