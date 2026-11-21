<?php

namespace App\Actions\Reportes;

use DateTimeInterface;
use RuntimeException;
use ZipArchive;

/**
 * Generador mínimo de archivos .xlsx (Office Open XML) sin dependencias externas.
 *
 * Cada hoja lleva encabezados en negritas, fila superior fija, autofiltro y
 * anchos de columna calculados. Las columnas marcadas como moneda usan el
 * formato #,##0.00 para que Excel pueda sumarlas.
 */
class LibroExcel
{
    /**
     * @var list<array{nombre: string, encabezados: list<string>, filas: list<list<mixed>>, moneda: list<int>}>
     */
    private array $hojas = [];

    /**
     * Agrega una hoja al libro.
     *
     * @param  list<string>  $encabezados
     * @param  iterable<list<mixed>>  $filas
     * @param  list<int>  $columnasMoneda  Índices (desde 0) de columnas con importes.
     */
    public function hoja(string $nombre, array $encabezados, iterable $filas, array $columnasMoneda = []): static
    {
        $this->hojas[] = [
            'nombre' => $this->nombreHojaValido($nombre),
            'encabezados' => $encabezados,
            'filas' => is_array($filas) ? array_values($filas) : iterator_to_array($filas, false),
            'moneda' => $columnasMoneda,
        ];

        return $this;
    }

    /**
     * Escribe el libro en un archivo temporal y devuelve su ruta.
     */
    public function guardar(): string
    {
        if ($this->hojas === []) {
            throw new RuntimeException('El libro necesita al menos una hoja.');
        }

        $ruta = tempnam(sys_get_temp_dir(), 'xlsx');
        $zip = new ZipArchive;

        if ($zip->open($ruta, ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('No se pudo crear el archivo de Excel.');
        }

        $zip->addFromString('[Content_Types].xml', $this->tiposDeContenido());
        $zip->addFromString('_rels/.rels', $this->relacionesRaiz());
        $zip->addFromString('xl/workbook.xml', $this->libro());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->relacionesLibro());
        $zip->addFromString('xl/styles.xml', $this->estilos());

        foreach ($this->hojas as $indice => $hoja) {
            $zip->addFromString('xl/worksheets/sheet'.($indice + 1).'.xml', $this->hojaXml($hoja));
        }

        $zip->close();

        return $ruta;
    }

    /**
     * @param  array{nombre: string, encabezados: list<string>, filas: list<list<mixed>>, moneda: list<int>}  $hoja
     */
    private function hojaXml(array $hoja): string
    {
        $totalColumnas = count($hoja['encabezados']);
        $ultimaColumna = $this->letraColumna($totalColumnas - 1);
        $ultimaFila = count($hoja['filas']) + 1;

        $columnas = '';
        foreach ($this->anchosColumna($hoja) as $indice => $ancho) {
            $numero = $indice + 1;
            $columnas .= "<col min=\"{$numero}\" max=\"{$numero}\" width=\"{$ancho}\" customWidth=\"1\"/>";
        }

        $filas = $this->filaXml(1, $hoja['encabezados'], [], encabezado: true);
        foreach ($hoja['filas'] as $indice => $fila) {
            $filas .= $this->filaXml($indice + 2, $fila, $hoja['moneda']);
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            ."<cols>{$columnas}</cols>"
            ."<sheetData>{$filas}</sheetData>"
            ."<autoFilter ref=\"A1:{$ultimaColumna}{$ultimaFila}\"/>"
            .'</worksheet>';
    }

    /**
     * @param  list<mixed>  $valores
     * @param  list<int>  $columnasMoneda
     */
    private function filaXml(int $numeroFila, array $valores, array $columnasMoneda, bool $encabezado = false): string
    {
        $celdas = '';

        foreach (array_values($valores) as $indice => $valor) {
            $referencia = $this->letraColumna($indice).$numeroFila;

            if ($encabezado) {
                $celdas .= "<c r=\"{$referencia}\" t=\"inlineStr\" s=\"1\"><is><t>{$this->escapar((string) $valor)}</t></is></c>";

                continue;
            }

            if ($valor === null || $valor === '') {
                continue;
            }

            if (is_int($valor) || is_float($valor)) {
                $estilo = in_array($indice, $columnasMoneda, true) ? ' s="2"' : '';
                $celdas .= "<c r=\"{$referencia}\"{$estilo}><v>{$valor}</v></c>";

                continue;
            }

            if (is_bool($valor)) {
                $valor = $valor ? 'Sí' : 'No';
            }

            if ($valor instanceof DateTimeInterface) {
                $valor = $valor->format('Y-m-d H:i');
            }

            $celdas .= "<c r=\"{$referencia}\" t=\"inlineStr\"><is><t xml:space=\"preserve\">{$this->escapar((string) $valor)}</t></is></c>";
        }

        return "<row r=\"{$numeroFila}\">{$celdas}</row>";
    }

    /**
     * Ancho aproximado de cada columna según su contenido más largo (entre 8 y 60).
     *
     * @param  array{encabezados: list<string>, filas: list<list<mixed>>}  $hoja
     * @return list<int>
     */
    private function anchosColumna(array $hoja): array
    {
        $anchos = array_map(fn (string $titulo) => mb_strlen($titulo) + 4, $hoja['encabezados']);

        foreach (array_slice($hoja['filas'], 0, 500) as $fila) {
            foreach (array_values($fila) as $indice => $valor) {
                $texto = $valor instanceof DateTimeInterface ? '0000-00-00 00:00' : (string) (is_float($valor) ? number_format($valor, 2) : $valor);
                $anchos[$indice] = max($anchos[$indice] ?? 8, mb_strlen($texto) + 2);
            }
        }

        return array_map(fn (int $ancho) => min(max($ancho, 8), 60), $anchos);
    }

    /**
     * 0 → A, 25 → Z, 26 → AA…
     */
    private function letraColumna(int $indice): string
    {
        $letra = '';

        for ($indice++; $indice > 0; $indice = intdiv($indice - 1, 26)) {
            $letra = chr(65 + ($indice - 1) % 26).$letra;
        }

        return $letra;
    }

    private function escapar(string $texto): string
    {
        // Quita caracteres de control que XML no permite.
        $texto = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $texto) ?? '';

        return htmlspecialchars($texto, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /**
     * Excel limita los nombres de hoja a 31 caracteres y prohíbe : \ / ? * [ ].
     */
    private function nombreHojaValido(string $nombre): string
    {
        return mb_substr(str_replace([':', '\\', '/', '?', '*', '[', ']'], ' ', $nombre), 0, 31) ?: 'Hoja';
    }

    private function tiposDeContenido(): string
    {
        $hojas = '';
        foreach (array_keys($this->hojas) as $indice) {
            $numero = $indice + 1;
            $hojas .= "<Override PartName=\"/xl/worksheets/sheet{$numero}.xml\" ContentType=\"application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml\"/>";
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .$hojas
            .'</Types>';
    }

    private function relacionesRaiz(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    private function libro(): string
    {
        $hojas = '';
        foreach ($this->hojas as $indice => $hoja) {
            $numero = $indice + 1;
            $hojas .= "<sheet name=\"{$this->escapar($hoja['nombre'])}\" sheetId=\"{$numero}\" r:id=\"rId{$numero}\"/>";
        }

        $filtros = '';
        foreach ($this->hojas as $indice => $hoja) {
            $ultimaColumna = $this->letraColumna(count($hoja['encabezados']) - 1);
            $ultimaFila = count($hoja['filas']) + 1;
            $nombre = str_replace("'", "''", $hoja['nombre']);
            $filtros .= "<definedName name=\"_xlnm._FilterDatabase\" localSheetId=\"{$indice}\" hidden=\"1\">'{$this->escapar($nombre)}'!\$A\$1:\${$ultimaColumna}\${$ultimaFila}</definedName>";
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            ."<sheets>{$hojas}</sheets>"
            ."<definedNames>{$filtros}</definedNames>"
            .'</workbook>';
    }

    private function relacionesLibro(): string
    {
        $relaciones = '';
        foreach (array_keys($this->hojas) as $indice) {
            $numero = $indice + 1;
            $relaciones .= "<Relationship Id=\"rId{$numero}\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet\" Target=\"worksheets/sheet{$numero}.xml\"/>";
        }

        $estilos = count($this->hojas) + 1;

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .$relaciones
            ."<Relationship Id=\"rId{$estilos}\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles\" Target=\"styles.xml\"/>"
            .'</Relationships>';
    }

    /**
     * Estilos: 0 = normal, 1 = encabezado (negritas, fondo gris), 2 = moneda.
     */
    private function estilos(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
            .'<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFE5E7EB"/><bgColor indexed="64"/></patternFill></fill></fills>'
            .'<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="3">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .'<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>'
            .'<xf numFmtId="4" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
            .'</cellXfs>'
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            .'</styleSheet>';
    }
}
