<?php
error_reporting(E_ALL);
ob_start("ob_gzhandler");
date_default_timezone_set ('America/El_Salvador');
ini_set("memory_limit","128M");
set_time_limit(600);
require_once('../include/const.php');
require_once('../include/sesion.php');
require_once('../include/fecha.php');
require_once('sub.php');
//====================PROCESAR REPORTES============================//

if (isset($_GET['sub']) && isset($_GET['reporte']) && $session->logged_in)
{
    $andUsuario = ($session->isAdmin()) ? "" : "AND codigo='" . $session->codigo . "'";
    switch ($_GET['reporte'])
    {
        case "rapido_todos_los_mupis":
        $c = "SELECT concat(codigo_calle, '.', codigo_mupi) 'Código', (SELECT ubicacion FROM emupi_calles AS b WHERE b.codigo_calle = a.codigo_calle) AS 'Ubicación', direccion 'Dirección' FROM emupi_mupis AS a ORDER BY codigo_calle, CAST(codigo_mupi as UNSIGNED)";
        break;

        case "rapido_mupis_catorcena_anterior":
        $c = "SELECT @codigo_mupi := (SELECT id_mupi FROM ".TBL_MUPI." as b WHERE a.codigo_mupi=b.id_mupi) as codigo_mupi, @codigo_mupi_traducido := (SELECT CONCAT((SELECT @ubicacion := b.ubicacion FROM emupi_calles AS b WHERE c.codigo_calle=b.codigo_calle), '. ', direccion , ' | ' , c.codigo_calle, '.' , @codigo_mupi_parcial := c.codigo_mupi ) FROM emupi_mupis as c WHERE c.id_mupi= @codigo_mupi) AS ubicacion, tipo_pantalla, id_pantalla FROM ".TBL_MUPI_FACES. " AS a WHERE catorcena = '".Obtener_catorcena_anterior()."' ORDER BY ubicacion, @codigo_mupi_parcial, tipo_pantalla";
        break;

        case "rapido_mupis_catorcena_actual":
        $c = "";
        break;

        case "rapido_usuarios_catorcena_anterior":

        break;

        case "rapido_usuarios_catorcena_actual":

        break;
    }
    DEPURAR($c,0);
    $resultado = $database->query($c);
    $html = db_ui_tabla($resultado, 'style="border:1px"');
    //======GENERAR PDF==========================//
    require_once('../include/tcpdf/config/lang/eng.php');
    require_once('../include/tcpdf/tcpdf.php');
    $pdf = new TCPDF('L', PDF_UNIT, "LETTER", true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Ecomupis, CEPASA DE C.V.');
    $pdf->SetTitle('Ecomupis I·PRINT');
    $pdf->SetSubject('Reporte solicitado vía interfaz web');
    $pdf->SetKeywords('ECOMUPIS, CEPASA, REPORTE');
    // set default header data
    $pdf->SetHeaderData("logo.png", 20, "Reporte de Ecomupis", date("h:m:ia.d-m-Y"));

    // set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    //set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    //set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    //set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    //set some language-dependent strings
    $pdf->setLanguageArray($l);

    // ---------------------------------------------------------

    // set font
    $pdf->SetFont('dejavusans', '', 10);

    // add a page
    $pdf->AddPage();

    // add HTML
    $pdf->writeHTML($html, true, 0, true, 0);

    // reset pointer to the last page
    $pdf->lastPage();

    //Close and output PDF document
    $pdf->Output('Reporte Ecomupis.'.date("h:m:ia.d-m-Y").'.pdf', 'I');
}
?>
