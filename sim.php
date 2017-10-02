<?php
/**
 *
 *     php sim.php -s -c 300 -v
 *     php sim.php -s -c 12 -v
 *     php sim.php -s -c 24 -v
 *     php sim.php -s -c 0
 *     php sim.php -s -c 12
 *     php sim.php -s -c 24
 *     php sim.php -s -c 36
 *     php sim.php -s -c 48
 *     php sim.php -s -c 60
 *     php sim.php -s -a 1
 *     php sim.php -s -a 30
 *     php sim.php -s -a 1
 *     php sim.php -s -a 2
 *     php sim.php -s -a 3
 *     php sim.php -s -a 4
 *     php sim.php -s -a 5
 *     php sim.php -s -a 6
 *     php sim.php -s -a 7
 *     php sim.php -s -f 2 -a 7
 *     php sim.php -s -f 2 -a 1
 *     php sim.php -s -f 2 -a 2
 *     php sim.php -s -f 8 -a 10
 *     php sim.php -s -f 2 -a 10
 *     php sim.php -s -f 1 -a 3
 *     php sim.php -s -f 1 -a 4
 *     php sim.php -s -f 1 -a 5
 *     php sim.php -s -f 0.5 -a 5
 *     php sim.php -s -f 0.5 -a 10
 *     php sim.php -s -f 0.5 -a 5
 *     php sim.php -s -f 0.5 -a 6
 *     php sim.php -s -f 0.5 -a 3
 *     php sim.php -s -f 0.5 -a 4
 *     php sim.php -s -f 0.5 -a 5
 *     php sim.php -s -f 0.5 -a 9
 *     php sim.php -s -f 0.5 -a 10
 *     php sim.php -s -f 1 -a 10
 *     php sim.php -s -f 1 -a 5
 *     reset
 *     php sim.php -s -f 0.5 -a 10
 *     php sim.php -s -f 1 -a 5
 *     php sim.php -s -f 1 -a 5 -e 24:300000
 *     php sim.php -s -f 1 -a 5 -e 1:0
 *     php sim.php -s -f 1 -a 5
 *     php sim.php -s -f 1 -a 5 -e 12:100000
 *     php sim.php -s -f 0.5 -a 5 -e 12:100000
 *     php sim.php -s -f 0.5 -a 5 -e 12:200000
 *     php sim.php -s -f 0.5 -a 5 -e 12:500000
 *     php sim.php -s -f 0.5 -a 5 -e 12:800000
 *     php sim.php -s -f 0.5 -a 5 -e 12:1000000
 *     php sim.php -s -f 0.5 -e 12:1000000
 *     php sim.php -s -f 0.5 -a 2 -e 12:1000000
 *     php sim.php -s -f 0.5 -a 3 -e 12:1000000
 *     php sim.php -s -f 0.5 -a 3 -e 12:300000
 *     php sim.php -s -f 0.5 -a 3
 *     php sim.php -s -f 0.5 -a 8
 *     php sim.php -s -f 0.5 -a 3 -e 36:500000
 *     php sim.php -s -f 0.5 -e 36:500000
 *     php sim.php -s -f 0.5 -a 5 -e 36:500000
 *     php sim.php -s -f 0.5 -a 6 -e 36:500000
 *     php sim.php -s -f 0.5 -a 6 -e 24:400000
 *     php sim.php -s -f 0.25 -a 6 -e 12:400000
 *
 */

define('MONTO', 'MONTO');
define('PORCENTAJE', 'PORCENTAJE');

// default values for parameters
$withoutInflation = false;
$factorAdelanto = 1;
$verbose = false;
$cuotaFinAdelanto = null;
$anioFinAdelanto = null;
$mesCapitalExtra = 0;
$capitalExtra = null;

/**
 * Cuando ya considero que no se justifica adelantar.
 * ejemplo: 40 significa que si el valor de la cuota pura es un 40% menor que la cuota real entonces
 * ya no quiero adelantar.
 */
$porcentajeCorteAdelantar = 0;


$argv = $_SERVER['argv'];
foreach ($argv as $key => $argument) {
    switch($argument) {
        case "--sin-inflacion":
        case "-s":
            $withoutInflation = true;
            break;
        case "--factor-adelanto":
        case "-f":
            $factorAdelanto = $argv[$key + 1];
            break;
        case "-v":
        case "--verbose":
            $verbose = true;
            break;
        case "-c":
        case "--cuota-fin-adelanto":
            $cuotaFinAdelanto = (int) $argv[$key + 1];
            break;
        case "-a":
        case "--anio-fin-adelanto":
            $anioFinAdelanto = (int) $argv[$key + 1];
            break;
        case "-e":
        case "--capital-extra":
            $raw = explode(':', $argv[$key + 1]);
            $mesCapitalExtra = (int) $raw[0];
            $capitalExtra = (int) $raw[1];
            break;
        case "-p":
        case "--porcentaje-corte-adelanto":
            // en que momento considero que ya no se justifica adelantar.
            // 25 -> significa que si el valor de la cuota pura es solo un 25% menor que la cuota entonces, no adelanto.
            $porcentajeCorteAdelantar = (int) $argv[$key + 1];
            $estrategia = PORCENTAJE;
            break;
        default:
            break;
    }
}

if (!is_null($anioFinAdelanto)) {
    $cuotaFinAdelanto = $anioFinAdelanto * 12;
}

if ($verbose === true) {
    echo "withoutInflation: "; echo ($withoutInflation)?'true':'false'; echo PHP_EOL;
    echo "factorAdelanto: $factorAdelanto".PHP_EOL;
    echo "capitalExtra:  $capitalExtra".PHP_EOL;
    echo "cuotaFinAdelanto: $cuotaFinAdelanto".PHP_EOL;
    echo "mesCapitalExtra: $mesCapitalExtra".PHP_EOL;
    echo PHP_EOL;
}

$file = 'cuotas.csv';
if ($withoutInflation === true) {
    $file = 'cuotas_sin_inflacion.csv';
}


$total = 0;
$aCuotaPura = array();
$aCuota = array();

if (($gestor = fopen($file, "r")) !== false) {
    while (($datos = fgetcsv($gestor, 1000, ",")) !== false) {
        $aCuotaPura[] = str_replace('.', '', $datos[0]);
        $aCuota[] = str_replace('.', '', $datos[1]);
    }
    fclose($gestor);
}


$i = 0;
$k = 1;
$interesesPagados = 0;
$totalPagado = 0;

$m = count($aCuota);
$cuotaFinAdelanto = is_null($cuotaFinAdelanto) ? $m : $cuotaFinAdelanto;

$hayCapitalExtra = ($capitalExtra > 0 && is_null($mesCapitalExtra) === false);

$cuotasAdelantadasAcum = 0;

$ultimoMesEnQueAdelante = 0;

while ($i + 1 < $m - 1) {

    $cuotasAdelantadas = 0;

    $totalPagado += $aCuota[$i];
    $interesesPagados += $aCuota[$i] - $aCuotaPura[$i];

    $cuotaActualPagada = $aCuota[$i];
    $aux = 0;

    $porcentaje = 100 * ($aCuota[$i] - $aCuotaPura[$i]) / $aCuota[$i];
    if ($verbose === true) {
        echo $porcentaje. PHP_EOL;
    }

    if ($hayCapitalExtra && $mesCapitalExtra == $k) {
        $montoExtra = $capitalExtra;
    } else {
        $montoExtra = $factorAdelanto * $cuotaActualPagada;
    }

    while ($k+1 <= $cuotaFinAdelanto
        && $aux <= $montoExtra
        && $i + 1 < $m - 1
        && $porcentaje > $porcentajeCorteAdelantar
        && $aCuota[$i] > 0
    ) {
        $aux += $aCuotaPura[$i+1];
        $i++;
        $cuotasAdelantadas++;

        $porcentaje = 100 * ($aCuota[$i] - $aCuotaPura[$i]) / $aCuota[$i];
        if ($verbose === true) {
            echo $porcentaje. PHP_EOL;
        }

        $ultimoMesEnQueAdelante = $k;
    }

    $totalPagado += $aux;

    $k++;
    $i++;

    if ($verbose === true) {
        echo "mes: $k - adelantadas: $cuotasAdelantadas".PHP_EOL;
    }

    $cuotasAdelantadasAcum += $cuotasAdelantadas;

}

$duracionTotal = round($k / 12, 1);
$aniosDeAdelantar = round($cuotasAdelantadasAcum/12, 1);
$ultimoAnioEnQueAdelante = round($ultimoMesEnQueAdelante/12, 1);

echo "total Pagado: $totalPagado".PHP_EOL;
echo "interes Pagado: $interesesPagados".PHP_EOL;
echo PHP_EOL;
echo "cuotas Adelantadas: $cuotasAdelantadasAcum ($aniosDeAdelantar años)".PHP_EOL;
echo "Ultimo adelanto de cuota: $ultimoMesEnQueAdelante ($ultimoAnioEnQueAdelante años)".PHP_EOL;
echo PHP_EOL;
echo "duración total: $k meses ($duracionTotal años)".PHP_EOL;


